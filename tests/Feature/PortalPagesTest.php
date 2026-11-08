<?php

namespace Tests\Feature;

use Tests\TestCase;

class PortalPagesTest extends TestCase
{
    public function test_role_portals_render_their_dashboard_headings(): void
    {
        $pages = [
            '/partner' => 'Commandes du restaurant',
            '/courier' => 'Votre tournée',
            '/ops' => 'Centre des opérations',
        ];

        foreach ($pages as $path => $heading) {
            $this->get($path)
                ->assertOk()
                ->assertSee($heading);
        }
    }
}