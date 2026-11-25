<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Rapikan kategori publik menjadi tiga kanal: Berita, Inspirasi, TikumJourney.
 * Tikum Reels punya halaman sendiri sehingga tidak dijadikan kategori artikel.
 * Artikel dari kategori lama dipindahkan ke Berita agar tidak kehilangan kategori.
 */
return new class extends Migration
{
    public function up(): void
    {
        $keep = [
            ['name' => 'Berita', 'description' => 'Berita terkini, akurat, dan berimbang.'],
            ['name' => 'Inspirasi', 'description' => 'Kisah dan gagasan yang menginspirasi.'],
            ['name' => 'TikumJourney', 'description' => 'Perjalanan, cerita, dan pengalaman pilihan.'],
        ];

        $now = now();

        foreach ($keep as $order => $row) {
            $slug = Str::slug($row['name']);
            $existing = DB::table('categories')->where('slug', $slug)->first();

            if ($existing) {
                DB::table('categories')->where('id', $existing->id)->update([
                    'name' => $row['name'],
                    'parent_id' => null,
                    'order' => $order,
                    'updated_at' => $now,
                ]);

                continue;
            }

            DB::table('categories')->insert([
                'name' => $row['name'],
                'slug' => $slug,
                'description' => $row['description'],
                'parent_id' => null,
                'order' => $order,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $keepIds = DB::table('categories')
            ->whereIn('slug', collect($keep)->map(fn ($row) => Str::slug($row['name']))->all())
            ->pluck('id');

        $oldIds = DB::table('categories')->whereNotIn('id', $keepIds)->pluck('id');

        if ($oldIds->isEmpty()) {
            return;
        }

        $beritaId = DB::table('categories')->where('slug', 'berita')->value('id');

        DB::table('articles')->whereIn('category_id', $oldIds)->update(['category_id' => $beritaId]);
        DB::table('categories')->whereIn('id', $oldIds)->update(['parent_id' => null]);
        DB::table('categories')->whereIn('id', $oldIds)->delete();
    }

    public function down(): void
    {
        // Tidak ada rollback: kategori lama tidak dapat dipulihkan.
    }
};
