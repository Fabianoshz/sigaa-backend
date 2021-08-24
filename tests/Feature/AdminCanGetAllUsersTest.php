<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Domain\Entities\AccessControl\Role;
use Domain\Entities\AccessControl\User;
use Domain\Entities\AccessControl\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Infra\AccessControl\Repositories\UserRepository;

class AdminCanGetAllUsersTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users
        $this->admin = User::factory()->create();
        $this->user = User::factory()->create();

        // Create permission
        $this->permission = Permission::findByName('admin');
        $this->permissions = collect();
        $this->permissions->push($this->permission->name);

        // Create admin role
        $adminRole = Role::factory()->create();
        $adminRole->syncPermissions($this->permissions);

        // Assign role to user
        $this->admin->assignRole($adminRole->name);
    }

    public function test_admin_can_get_permissions()
    {
        Auth::login($this->admin);

        $response = $this->getJson(
            route("admin.users.index")
        );

        $responseIds = collect($response->json()['data'])->pluck('id');
        $repositoryIds = (new UserRepository)->all()->pluck('id');

        // TODO maybe check for pagination.
        $this->assertEquals($responseIds, $repositoryIds);
    }

    public function test_user_cant_get_permissions()
    {
        Auth::login($this->user);

        $response = $this->getJson(
            route("admin.users.index")
        );

        $response->assertForbidden();
    }
}
