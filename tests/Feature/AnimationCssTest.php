<?php

namespace Tests\Feature;

use Tests\TestCase;

class AnimationCssTest extends TestCase
{
    private function css(): string
    {
        return file_get_contents(resource_path('css/app.css'));
    }

    public function test_hero_rise_tidak_mulai_dari_opacity_nol(): void
    {
        preg_match('/@keyframes hero-rise\s*{\s*from\s*{([^}]*)}/s', $this->css(), $m);

        $this->assertNotEmpty($m, 'keyframes hero-rise tidak ditemukan');
        $this->assertStringNotContainsString('opacity', $m[1]);
    }

    public function test_transisi_global_tidak_mencakup_field_form(): void
    {
        preg_match('/^\s*a,\s*button,\s*(.*?)\s*{\s*transition:/ms', $this->css(), $m);

        $this->assertNotEmpty($m, 'blok transisi global tidak ditemukan');
        foreach (['input', 'select', 'textarea'] as $field) {
            $this->assertStringNotContainsString($field, $m[1]);
        }
    }

    public function test_hero_zoom_dipendekkan(): void
    {
        $this->assertStringContainsString('hero-zoom 0.6s', $this->css());
    }
}
