<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Location;

class ShiftEmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $employee;
    protected $shift;
    protected $location;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        $this->createPermissions();

        // Create location
        $this->location = Location::factory()->create([
            'name' => 'Test Location',
            'is_active' => true
        ]);

        // Create admin role with all permissions
        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Administrator'
        ]);

        $permissions = Permission::all();
        $adminRole->permissions()->attach($permissions);

        // Create admin user
        $this->user = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'role_id' => $adminRole->id,
            'is_active' => true
        ]);

        // Create employee
        $this->employee = Employee::factory()->create([
            'name' => 'Test Employee',
            'employee_id' => 'EMP001',
            'position' => 'Worker',
            'status' => 'active',
            'location_id' => $this->location->id
        ]);

        // Create shift
        $this->shift = Shift::factory()->create([
            'name' => 'Morning Shift',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'is_active' => true
        ]);
    }

    private function createPermissions()
    {
        $permissions = [
            'view_shifts', 'create_shifts', 'edit_shifts', 'delete_shifts',
            'assign_shift_users', 'remove_shift_users'
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'display_name' => ucfirst(str_replace('_', ' ', $permission)),
                'description' => ucfirst(str_replace('_', ' ', $permission))
            ]);
        }
    }

    /** @test */
    public function user_can_assign_employee_to_shift()
    {
        $this->actingAs($this->user);

        $response = $this->post("/shifts/{$this->shift->id}/assign-employees", [
            'employee_ids' => [$this->employee->id],
            'date' => now()->format('Y-m-d'),
            'notes' => 'Test assignment'
        ]);

        $response->assertStatus(302); // Redirect after success

        // Verify employee is assigned to shift
        $this->assertTrue($this->shift->employees()->where('employees.id', $this->employee->id)->exists());
    }

    /** @test */
    public function user_can_remove_employee_from_shift()
    {
        // First assign employee to shift
        $this->shift->employees()->attach($this->employee->id, [
            'date' => now()->format('Y-m-d'),
            'notes' => 'Test assignment',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->actingAs($this->user);

        $response = $this->delete("/shifts/{$this->shift->id}/remove-employee/{$this->employee->id}", [
            'date' => now()->format('Y-m-d')
        ]);

        $response->assertStatus(302); // Redirect after success

        // Verify employee is removed from shift
        $this->assertFalse($this->shift->employees()->where('employees.id', $this->employee->id)->exists());
    }

    /** @test */
    public function shift_shows_correct_employee_count()
    {
        // Assign multiple employees
        $employee2 = Employee::factory()->create([
            'name' => 'Test Employee 2',
            'employee_id' => 'EMP002',
            'position' => 'Worker',
            'status' => 'active',
            'location_id' => $this->location->id
        ]);

        $this->shift->employees()->attach([
            $this->employee->id => [
                'date' => now()->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            $employee2->id => [
                'date' => now()->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        $this->actingAs($this->user);

        $response = $this->get('/shifts');

        $response->assertStatus(200);

        // Verify the shift data includes correct employee count
        $shifts = $response->original->getData()['page']['props']['shifts']['data'];
        $testShift = collect($shifts)->first(function ($shift) {
            return $shift['id'] === $this->shift->id;
        });

        $this->assertEquals(2, $testShift['employees_count']);
    }

    /** @test */
    public function shift_detail_shows_assigned_employees()
    {
        // Assign employee to shift
        $this->shift->employees()->attach($this->employee->id, [
            'date' => now()->format('Y-m-d'),
            'notes' => 'Test assignment',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->actingAs($this->user);

        $response = $this->get("/shifts/{$this->shift->id}");

        $response->assertStatus(200);

        // Verify the shift detail includes assigned employees
        $shift = $response->original->getData()['page']['props']['shift'];
        $this->assertCount(1, $shift['employees']);
        $this->assertEquals($this->employee->id, $shift['employees'][0]['id']);
    }

    /** @test */
    public function statistics_show_correct_employee_with_shifts_count()
    {
        // Create another employee and shift
        $employee2 = Employee::factory()->create([
            'name' => 'Test Employee 2',
            'employee_id' => 'EMP002',
            'position' => 'Worker',
            'status' => 'active',
            'location_id' => $this->location->id
        ]);

        $shift2 = Shift::factory()->create([
            'name' => 'Evening Shift',
            'start_time' => '16:00:00',
            'end_time' => '00:00:00',
            'is_active' => true
        ]);

        // Assign employees to shifts
        $this->shift->employees()->attach($this->employee->id, [
            'date' => now()->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $shift2->employees()->attach($employee2->id, [
            'date' => now()->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->actingAs($this->user);

        $response = $this->get('/shifts');

        $response->assertStatus(200);

        // Verify statistics show correct count
        $statistics = $response->original->getData()['page']['props']['statistics'];
        $this->assertEquals(2, $statistics['employees_with_shifts']);
    }
}
