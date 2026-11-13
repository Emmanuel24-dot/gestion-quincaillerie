<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Authenticate as an administrator by default so feature tests reach the protected pages.
     */
    protected bool $actsAsAdmin = true;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->actsAsAdmin && in_array(RefreshDatabase::class, class_uses_recursive($this), true)) {
            $this->actingAs(User::factory()->admin()->create());
        }
    }
}
