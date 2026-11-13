<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected bool $actsAsAdmin = false;

    public function test_the_home_page_redirects_to_the_initial_setup_when_no_account_exists(): void
    {
        $this->get('/')->assertRedirect(route('setup'));
    }

    public function test_the_home_page_redirects_to_the_login_once_an_account_exists(): void
    {
        User::factory()->admin()->create();

        $this->get('/')->assertRedirect(route('login'));
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_sellers_cannot_reach_admin_pages(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('statistiques'))->assertForbidden();
        $this->get(route('users.index'))->assertForbidden();
    }
}
