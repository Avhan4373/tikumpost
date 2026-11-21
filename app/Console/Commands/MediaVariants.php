<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Infographic;
use App\Models\Media;
use App\Support\ImageVariant;
use Illuminate\Console\Command;

class MediaVariants extends Command
{
    protected $signature = 'media:variants';

    protected $description = 'Buat varian WebP untuk gambar artikel, infografis, dan media.';

    public function handle(): int
    {
        $sources = collect()
            ->merge(Article::query()->whereNotNull('featured_image')->pluck('featured_image'))
            ->merge(Infographic::query()->pluck('image'))
            ->merge(Media::query()->pluck('path'))
            ->filter()
            ->unique();

        $variants = 0;

        foreach ($sources as $path) {
            $variants += count(ImageVariant::ensure($path));
        }

        $this->info("Selesai: {$sources->count()} gambar diproses, {$variants} varian dibuat/ditemukan.");

        return self::SUCCESS;
    }
}
