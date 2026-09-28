<?php

namespace Tests\Feature;

use App\Models\LostItem;
use App\Repositories\FoundItemRepository;
use App\Repositories\ItemMatchRepository;
use App\Repositories\LostItemRepository;
use App\Services\AuditLogService;
use App\Services\AuthService;
use App\Services\ItemImage;
use App\Services\JsonDatabase;
use App\Services\LostItemService;
use App\Services\NlpMatchingService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ItemImageStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_bytes_and_item_record_are_saved_and_served_from_sqlite(): void
    {
        $this->app->instance(AuthService::class, \Mockery::mock(AuthService::class));
        $upload = UploadedFile::fake()->image('bag.png', 120, 100);
        $image = app(ItemImage::class)->fromUpload($upload);
        $matching = \Mockery::mock(NlpMatchingService::class);
        $matching->shouldReceive('matchLostItem')->once()->andReturn([]);
        $audit = \Mockery::mock(AuditLogService::class);
        $audit->shouldReceive('log')->once();
        $user = new \App\Models\User(['name' => 'Tester']);
        $user->id = 1;
        $service = new LostItemService(app(LostItemRepository::class), $matching, $audit, app(ItemImage::class));
        $item = $service->create($user, [
            'item_name' => 'Bag',
            'description' => 'Black bag',
            'category_id' => 5,
            'date_lost' => '2026-09-25',
            'location_lost' => 'Library',
        ], $upload);

        $this->assertSame('database', $item->image_path);
        $this->assertSame($image['image_data'], DB::table('item_records')->where('type', 'lost')->where('item_id', $item->id)->value('image_data'));
        $this->assertSame('Bag', app(LostItemRepository::class)->find($item->id)->item_name);
        $this->withSession(['auth_user_id' => 1, 'auth_user_role' => 'student'])
            ->get($item->image_url)
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertContent($image['image_data']);
        $this->get(route('items.image', ['type' => 'lost', 'id' => 999]))->assertNotFound();

        $service->delete($item);
        $this->assertNull(app(LostItemRepository::class)->find($item->id));
        $replacement = app(LostItemRepository::class)->create(['item_name' => 'Another bag', 'user_id' => 1]);
        $this->assertGreaterThan($item->id, $replacement->id);
    }

    public function test_image_similarity_helps_rank_matching_items_but_not_unrelated_images(): void
    {
        $images = app(ItemImage::class);
        $photo = $images->fromUpload(UploadedFile::fake()->image('phone.png', 80, 80));
        $blue = imagecreatetruecolor(80, 80);
        imagefill($blue, 0, 0, imagecolorallocate($blue, 0, 0, 255));
        ob_start();
        imagepng($blue);
        $other = $images->fromBytes(ob_get_clean());
        imagedestroy($blue);
        $lost = app(LostItemRepository::class)->create(array_merge([
            'item_name' => 'Phone', 'category_id' => 2, 'user_id' => 1, 'status' => 'lost',
        ], $photo, ['image_path' => 'database']));
        $found = app(FoundItemRepository::class)->create(array_merge([
            'item_name' => 'Phone', 'category_id' => 2, 'user_id' => 2, 'status' => 'found',
        ], $photo, ['image_path' => 'database']));
        $unrelated = app(FoundItemRepository::class)->create(array_merge([
            'item_name' => 'Phone', 'category_id' => 2, 'user_id' => 3, 'status' => 'found',
        ], $other, ['image_path' => 'database']));
        $wrongCategory = app(FoundItemRepository::class)->create(array_merge([
            'item_name' => 'Phone', 'category_id' => 6, 'user_id' => 4, 'status' => 'found',
        ], $photo, ['image_path' => 'database']));

        $method = new \ReflectionMethod(NlpMatchingService::class, 'rankWithImages');
        $results = $method->invoke(app(NlpMatchingService::class), [], $lost, collect([$unrelated, $wrongCategory, $found]));

        $this->assertCount(1, $results);
        $this->assertSame($found->id, $results[0]['item_id']);
        $this->assertGreaterThanOrEqual(50, $results[0]['similarity_score']);
        $this->assertSame(0.0, $images->similarity($photo['image_hash'], $other['image_hash']));
        $this->assertSame(0.0, $images->similarity(null, $photo['image_hash']));

        Http::fake(['*' => Http::response(['matches' => []])]);
        $matches = \Mockery::mock(ItemMatchRepository::class);
        $matches->shouldReceive('create')->once()->withArgs(function ($data) use ($lost, $found) {
            return $data['lost_item_id'] === $lost->id
                && $data['found_item_id'] === $found->id
                && $data['similarity_score'] >= 50;
        });
        $notifications = \Mockery::mock(NotificationService::class);
        $notifications->shouldReceive('notify')->zeroOrMoreTimes();
        $matcher = new NlpMatchingService(
            app(LostItemRepository::class),
            app(FoundItemRepository::class),
            $matches,
            $notifications,
            $images
        );
        $this->assertSame($found->id, $matcher->matchFoundItem($found)[0]['item_id']);
    }

    public function test_existing_json_reports_and_photos_can_be_imported_without_duplication(): void
    {
        Storage::fake('public');
        $bytes = app(ItemImage::class)->fromUpload(UploadedFile::fake()->image('wallet.png'))['image_data'];
        Storage::disk('public')->put('items/lost/wallet.png', $bytes);
        $json = \Mockery::mock(JsonDatabase::class);
        $json->shouldReceive('all')->with('lost_items')->twice()->andReturn([[
            'id' => 42, 'item_name' => 'Wallet', 'user_id' => 1, 'category_id' => 4,
            'description' => 'Brown leather', 'image_path' => 'items/lost/wallet.png',
            'status' => 'lost', 'created_at' => '2026-01-01 12:00:00',
        ]]);
        $json->shouldReceive('all')->with('found_items')->twice()->andReturn([]);
        $json->shouldReceive('find')->with('categories', 4)->andReturn(['id' => 4, 'name' => 'Wallet']);
        $json->shouldReceive('find')->with('users', 1)->andReturn(null);
        $this->app->instance(JsonDatabase::class, $json);

        $this->assertSame(0, Artisan::call('items:import-json'));
        $this->assertSame(0, Artisan::call('items:import-json'));
        $this->assertSame(1, DB::table('item_records')->where('type', 'lost')->count());
        $this->assertSame($bytes, app(LostItemRepository::class)->image(42)->image_data);
        $this->assertSame('database', app(LostItemRepository::class)->find(42)->image_path);
        $this->assertTrue(Storage::disk('public')->exists('items/lost/wallet.png'));
        $next = app(LostItemRepository::class)->create(['item_name' => 'Scarf', 'user_id' => 1]);
        $this->assertSame(43, $next->id);
    }

    public function test_lost_and_found_form_uploads_persist_their_photos(): void
    {
        $auth = \Mockery::mock(AuthService::class);
        $auth->shouldReceive('user')->twice()->andReturn(['id' => 1, 'name' => 'Tester', 'role' => 'student']);
        $this->app->instance(AuthService::class, $auth);
        $matching = \Mockery::mock(NlpMatchingService::class);
        $matching->shouldReceive('matchLostItem')->once()->andReturn([]);
        $matching->shouldReceive('matchFoundItem')->once()->andReturn([]);
        $this->app->instance(NlpMatchingService::class, $matching);
        $audit = \Mockery::mock(AuditLogService::class);
        $audit->shouldReceive('log')->twice();
        $this->app->instance(AuditLogService::class, $audit);

        foreach (['lost' => 'location_lost', 'found' => 'location_found'] as $type => $location) {
            $this->withSession(['auth_user_id' => 1, 'auth_user_role' => 'student'])
                ->post('/report/'.$type, [
                    'item_name' => 'Backpack',
                    'category_id' => 5,
                    'description' => 'Blue backpack with a zipper',
                    $location => 'Library',
                    'date_'.$type => now()->format('Y-m-d'),
                    'image' => UploadedFile::fake()->image($type.'.png'),
                ])
                ->assertRedirect('/dashboard');

            $row = DB::table('item_records')->where('type', $type)->first();
            $this->assertNotNull($row->image_data);
            $this->assertSame('image/png', $row->image_mime);
        }
    }
}
