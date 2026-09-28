<?php

namespace Tests\Unit;

use App\Repositories\CategoryRepository;
use App\Services\JsonDatabase;
use Tests\TestCase;

class CategoryDropdownTest extends TestCase
{
    public function test_default_categories_are_available_until_local_categories_are_saved(): void
    {
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lost-found-categories-'.bin2hex(random_bytes(8));
        mkdir($directory);

        try {
            $database = new class ($directory) extends JsonDatabase {
                public function __construct(string $directory)
                {
                    $this->basePath = $directory;
                }
            };
            $categories = new CategoryRepository($database);

            $this->assertCount(11, $categories->allOrdered());
            $this->assertSame('Student ID Card', $categories->find(1)->name);
            $this->assertFileDoesNotExist($directory.DIRECTORY_SEPARATOR.'categories.json');

            foreach (['items.report_lost', 'items.report_found', 'items.search'] as $view) {
                $data = ['categories' => $categories->allOrdered(), 'errors' => new \Illuminate\Support\ViewErrorBag()];
                if ($view === 'items.search') {
                    $data += ['lostItems' => collect(), 'foundItems' => collect(), 'filters' => []];
                }

                $this->view($view, $data)
                    ->assertSee('name="category_id"', false)
                    ->assertSee('Student ID Card')
                    ->assertSee('Mobile Phone');
            }

            $categories->create(['name' => 'Umbrella', 'description' => 'Rain gear']);

            $this->assertSame('Umbrella', $categories->find(12)->name);
            $this->assertCount(12, $categories->allOrdered());

            file_put_contents($directory.DIRECTORY_SEPARATOR.'categories.json', json_encode([
                ['id' => 25, 'name' => 'Custom', 'description' => 'Local category'],
            ]));

            $this->assertSame(['Custom'], $categories->allOrdered()->pluck('name')->all());
            $this->assertNull($categories->find(1));
        } finally {
            $file = $directory.DIRECTORY_SEPARATOR.'categories.json';
            if (is_file($file)) {
                unlink($file);
            }
            rmdir($directory);
        }
    }
}
