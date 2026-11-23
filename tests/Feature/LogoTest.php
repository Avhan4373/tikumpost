<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_beranda_memakai_logo_svg_inline_dengan_warna_dari_css_variable(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('class="logo-ink"', $html);
        $this->assertStringContainsString('class="logo-accent"', $html);
        $this->assertStringContainsString('logo-svg', $html);
        $this->assertStringNotContainsString('storage/settings/', $html);
    }

    public function test_halaman_maintenance_memakai_logo_dengan_warna_terang(): void
    {
        Setting::set('maintenance_mode', '1');

        $html = $this->get('/')->assertStatus(503)->getContent();

        $this->assertStringContainsString('logo-on-dark', $html);
        $this->assertStringContainsString('class="logo-ink"', $html);
    }
}
