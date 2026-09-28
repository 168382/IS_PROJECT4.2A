<?php

namespace Tests\Unit;

use App\Repositories\CategoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryDropdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_categories_are_seeded_in_the_database_and_new_ones_can_be_added(): void
    {
        $categories = app(CategoryRepository::class);

        // The default categories are seeded by a migration, so they exist
        // in the real `categories` database table as soon as migrations run.
        $this->assertCount(11, $categories->allOrdered());
        $this->assertSame('Student ID Card', $categories->find(1)->name);

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

        $created = $categories->create(['name' => 'Umbrella', 'description' => 'Rain gear']);

        $this->assertSame('Umbrella', $categories->find($created->id)->name);
        $this->assertCount(12, $categories->allOrdered());
    }
}
