<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Validation\Rule;

/**
 * Sumber tunggal skema & nilai pengaturan situs.
 *
 * Skema di sini menggerakkan form admin sekaligus nilai yang dipakai
 * di tampilan publik (header, footer, meta, dsb).
 */
class SiteSettings
{
    /** Grup yang dikelola di halaman Tema (bukan Pengaturan). */
    public const THEME_GROUP = 'tema';

    /**
     * Skema lengkap: grup => label + daftar field.
     *
     * Field: key, label, type (text|textarea|email|url|number|boolean|image|select),
     * default, help (opsional), options (khusus select).
     *
     * @return array<string, array{label: string, description?: string, fields: array<int, array<string, mixed>>}>
     */
    public static function schema(): array
    {
        return [
            'identitas' => [
                'label' => 'Identitas & Branding',
                'description' => 'Nama, tagline, deskripsi, logo, dan favicon situs.',
                'fields' => [
                    ['key' => 'site_name', 'label' => 'Nama Situs', 'type' => 'text', 'default' => 'TikumPost'],
                    ['key' => 'site_tagline', 'label' => 'Tagline', 'type' => 'text', 'default' => 'Portal berita terkini, akurat, dan berimbang'],
                    ['key' => 'site_description', 'label' => 'Deskripsi Situs', 'type' => 'textarea', 'default' => 'Portal berita independen yang menyajikan informasi terkini, akurat, dan berimbang untuk pembaca Indonesia.'],
                    ['key' => 'site_logo', 'label' => 'Logo (pratinjau SEO)', 'type' => 'image', 'default' => null, 'help' => 'Dipakai untuk og:image dan schema.org. Logo di situs memakai SVG bawaan yang warnanya mengikuti tema.'],
                    ['key' => 'site_favicon', 'label' => 'Favicon', 'type' => 'image', 'default' => null, 'help' => 'Ikon tab browser (disarankan PNG/SVG kotak).'],
                ],
            ],
            self::THEME_GROUP => [
                'label' => 'Tema',
                'description' => 'Tata letak halaman (layout) dan mode warna default untuk pengunjung.',
                'fields' => [
                    [
                        'key' => 'layout_preset',
                        'label' => 'Layout Situs',
                        'type' => 'select',
                        'default' => 'tvri',
                        'options' => ['tvri' => 'TVRI (header biru, ticker, hero grid)', 'klasik' => 'Klasik (header putih, hero besar, kartu)'],
                        'help' => 'Berlaku untuk header dan beranda.',
                    ],
                    [
                        'key' => 'default_theme',
                        'label' => 'Tema Warna Default',
                        'type' => 'select',
                        'default' => 'light',
                        'options' => ['light' => 'Terang (White Mode)', 'dark' => 'Gelap (Dark Mode)'],
                        'help' => 'Pilihan manual pengunjung lewat tombol tema tetap diutamakan.',
                    ],
                ],
            ],
            'kontak' => [
                'label' => 'Kontak',
                'description' => 'Informasi kontak yang tampil di footer.',
                'fields' => [
                    ['key' => 'contact_email', 'label' => 'Email', 'type' => 'email', 'default' => null],
                    ['key' => 'contact_phone', 'label' => 'Telepon / WhatsApp', 'type' => 'text', 'default' => null],
                    ['key' => 'contact_address', 'label' => 'Alamat', 'type' => 'textarea', 'default' => null],
                ],
            ],
            'sosial' => [
                'label' => 'Media Sosial',
                'description' => 'Kosongkan bila akun belum ada. Ikon hanya tampil untuk tautan yang diisi.',
                'fields' => [
                    ['key' => 'social_facebook', 'label' => 'Facebook', 'type' => 'url', 'default' => null],
                    ['key' => 'social_twitter', 'label' => 'X / Twitter', 'type' => 'url', 'default' => null],
                    ['key' => 'social_instagram', 'label' => 'Instagram', 'type' => 'url', 'default' => null],
                    ['key' => 'social_youtube', 'label' => 'YouTube', 'type' => 'url', 'default' => null],
                    ['key' => 'social_tiktok', 'label' => 'TikTok', 'type' => 'url', 'default' => null],
                ],
            ],
            'seo' => [
                'label' => 'SEO Default',
                'description' => 'Dipakai sebagai meta bawaan bila halaman tidak mengisi sendiri.',
                'fields' => [
                    ['key' => 'meta_description', 'label' => 'Meta Description', 'type' => 'textarea', 'default' => 'Portal berita terkini, akurat, dan berimbang untuk pembaca Indonesia.'],
                    ['key' => 'meta_keywords', 'label' => 'Meta Keywords', 'type' => 'text', 'default' => null, 'help' => 'Pisahkan dengan koma.'],
                    ['key' => 'google_site_verification', 'label' => 'Google Site Verification', 'type' => 'text', 'default' => null, 'help' => 'Tempel hanya nilai token dari Google Search Console (metode "Tag HTML", bagian content="..."). Kosongkan bila belum dipakai.'],
                ],
            ],
            'footer' => [
                'label' => 'Footer & Copyright',
                'fields' => [
                    ['key' => 'footer_text', 'label' => 'Teks Footer', 'type' => 'textarea', 'default' => 'Portal berita independen yang menyajikan informasi terkini, akurat, dan berimbang untuk pembaca Indonesia.'],
                    ['key' => 'copyright_text', 'label' => 'Teks Copyright', 'type' => 'text', 'default' => 'TikumPost. Seluruh hak cipta dilindungi.', 'help' => 'Tahun ditambahkan otomatis di depannya.'],
                ],
            ],
            'konten' => [
                'label' => 'Preferensi Konten',
                'fields' => [
                    ['key' => 'posts_per_page', 'label' => 'Artikel per Halaman', 'type' => 'number', 'default' => '9', 'help' => 'Jumlah artikel pada halaman daftar/kategori.'],
                    ['key' => 'comments_enabled', 'label' => 'Aktifkan Komentar', 'type' => 'boolean', 'default' => true],
                    ['key' => 'maintenance_mode', 'label' => 'Mode Perawatan', 'type' => 'boolean', 'default' => false, 'help' => 'Situs publik ditutup sementara; staf tetap bisa mengakses.'],
                ],
            ],
        ];
    }

    /**
     * Seluruh field yang di-flatten dengan key sebagai indeks.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function fields(): array
    {
        $fields = [];

        foreach (static::schema() as $group) {
            foreach ($group['fields'] as $field) {
                $fields[$field['key']] = $field;
            }
        }

        return $fields;
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        $defaults = [];

        foreach (static::fields() as $key => $field) {
            $defaults[$key] = $field['default'] ?? null;
        }

        return $defaults;
    }

    /**
     * Nilai efektif: default digabung dengan nilai tersimpan.
     *
     * Nilai kosong dianggap tidak diisi sehingga default dipakai kembali.
     *
     * @return array<string, mixed>
     */
    public static function values(): array
    {
        $values = static::defaults();

        foreach (Setting::map() as $key => $value) {
            if (array_key_exists($key, $values) && $value !== null && $value !== '') {
                $values[$key] = $value;
            }
        }

        return $values;
    }

    public static function value(string $key, mixed $default = null): mixed
    {
        $values = static::values();

        return array_key_exists($key, $values) ? $values[$key] : $default;
    }

    /** Jumlah artikel per halaman pada daftar publik. */
    public static function perPage(): int
    {
        return max(1, (int) static::value('posts_per_page', 9));
    }

    /**
     * Aturan validasi per field.
     *
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        $rules = [];

        foreach (static::fields() as $key => $field) {
            $rules[$key] = match ($field['type']) {
                'email' => ['nullable', 'email', 'max:255'],
                'url' => ['nullable', 'url', 'max:255'],
                'number' => ['nullable', 'integer', 'min:1', 'max:100'],
                'textarea' => ['nullable', 'string', 'max:5000'],
                'boolean' => ['nullable', 'boolean'],
                'select' => ['nullable', 'string', Rule::in(array_keys($field['options'] ?? []))],
                'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
                default => ['nullable', 'string', 'max:255'],
            };
        }

        return $rules;
    }

    /**
     * Skema untuk halaman Pengaturan (tanpa grup tema).
     *
     * @return array<string, array{label: string, description?: string, fields: array<int, array<string, mixed>>}>
     */
    public static function formSchema(): array
    {
        return array_diff_key(static::schema(), [self::THEME_GROUP => null]);
    }

    /**
     * Skema khusus halaman Tema.
     *
     * @return array<string, array{label: string, description?: string, fields: array<int, array<string, mixed>>}>
     */
    public static function themeSchema(): array
    {
        return [self::THEME_GROUP => static::schema()[self::THEME_GROUP]];
    }

    /**
     * Field (flatten) milik grup tertentu.
     *
     * @param  array<int, string>  $groups
     * @return array<string, array<string, mixed>>
     */
    public static function fieldsOf(array $groups): array
    {
        $fields = [];

        foreach ($groups as $group) {
            foreach (static::schema()[$group]['fields'] ?? [] as $field) {
                $fields[$field['key']] = $field;
            }
        }

        return $fields;
    }

    /**
     * Aturan validasi untuk field grup tertentu.
     *
     * @param  array<int, string>  $groups
     * @return array<string, array<int, string>>
     */
    public static function rulesFor(array $groups): array
    {
        return array_intersect_key(static::rules(), static::fieldsOf($groups));
    }

    /** Layout publik aktif: `tvri` atau `klasik`. */
    public static function layout(): string
    {
        return static::value('layout_preset') === 'klasik' ? 'klasik' : 'tvri';
    }
}
