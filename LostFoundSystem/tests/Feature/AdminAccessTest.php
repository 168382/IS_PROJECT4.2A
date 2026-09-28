<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private array $userIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'student'] as $role) {
            $this->userIds[$role] = User::factory()->create([
                'name' => ucfirst($role),
                'email' => $role.'@example.test',
                'role' => $role,
            ])->id;
        }
    }

    private function userIdForRole(string $role): int
    {
        return $this->userIds[$role];
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
