<?php

namespace Tests\Feature;

use App\Services\JsonDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private string $fixtureDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtureDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lost-found-admin-'.bin2hex(random_bytes(8));
        mkdir($this->fixtureDirectory);
        $database = new class ($this->fixtureDirectory) extends JsonDatabase {
            public function __construct(string $directory)
            {
                $this->basePath = $directory;
            }
        };
        $this->app->instance(JsonDatabase::class, $database);

        foreach (['admin', 'student'] as $role) {
            $database->insert('users', [
                'name' => ucfirst($role),
                'email' => $role.'@example.test',
                'role' => $role,
            ]);
        }
    }

    protected function tearDown(): void
    {
        $file = $this->fixtureDirectory.DIRECTORY_SEPARATOR.'users.json';
        if (is_file($file)) {
            unlink($file);
        }
        rmdir($this->fixtureDirectory);

        parent::tearDown();
    }

    private function userIdForRole(string $role): int
    {
        $users = $this->app->make(JsonDatabase::class)->all('users');

        foreach ($users as $user) {
            if (($user['role'] ?? null) === $role) {
                return (int) $user['id'];
            }
        }

        $this->fail("Expected a {$role} test user.");
    }

    public function test_admin_can_open_the_operational_dashboard_and_reports(): void
    {
        $adminId = $this->userIdForRole('admin');
        $session = ['auth_user_id' => $adminId, 'auth_user_role' => 'admin'];

        $this->withSession($session)->get('/admin')->assertOk();
        $this->withSession($session)->get('/admin/users')->assertOk();
        $this->withSession($session)->get('/admin/reports/pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->withSession($session)->get('/admin/reports/excel')
            ->assertOk();
    }

    public function test_student_cannot_open_administrative_pages(): void
    {
        $studentId = $this->userIdForRole('student');
        $session = ['auth_user_id' => $studentId, 'auth_user_role' => 'student'];

        $this->withSession($session)->get('/admin')->assertRedirect('/dashboard');
        $this->withSession($session)->get('/admin/items')->assertRedirect('/dashboard');
        $this->withSession($session)->get('/admin/users')->assertRedirect('/dashboard');
        $this->withSession($session)->get('/admin/reports/pdf')->assertRedirect('/dashboard');
    }
}
