<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_uses_only_available_routes_and_expands_the_active_group(): void
    {
        $response = $this->get(route('produits.index'));

        $response->assertOk()
            ->assertSee(route('dashboard'))
            ->assertSee(route('produits.index'))
            ->assertSee(route('categories.index'))
            ->assertSee(route('statistiques'))
            ->assertSee(route('ventes.create'))
            ->assertSee('aria-expanded="true"', false)
            ->assertDontSee('href="#"', false)
            ->assertSee('Nouvelle vente');
    }

    public function test_statistics_route_uses_the_admin_layout_and_active_navigation(): void
    {
        $this->get(route('statistiques'))
            ->assertOk()
            ->assertSee('Rapport des ventes')
            ->assertSee(route('statistiques'))
            ->assertSee('aria-current="page"', false)
            ->assertSee('Aucune activité sur cette période');
    }
}
