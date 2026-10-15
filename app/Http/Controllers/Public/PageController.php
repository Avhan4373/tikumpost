<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PageController extends Controller
{
    /** Halaman statis sederhana. */
    public function show(string $slug): View
    {
        $pages = [
            'tentang' => [
                'title' => 'Tentang Kami',
                'body' => 'KabarKita adalah portal berita independen yang menyajikan informasi terkini, akurat, dan berimbang untuk pembaca Indonesia.',
            ],
            'kontak' => [
                'title' => 'Kontak',
                'body' => 'Punya tips berita atau pertanyaan? Hubungi redaksi kami di redaksi@kabarkita.test atau (021) 1234-5678.',
            ],
            'kebijakan-privasi' => [
                'title' => 'Kebijakan Privasi',
                'body' => 'Kami menghormati privasi Anda. Data yang kami kumpulkan hanya digunakan untuk keperluan layanan berita dan tidak dibagikan ke pihak ketiga tanpa izin.',
            ],
        ];

        abort_unless(isset($pages[$slug]), 404);

        return view('public.page', ['page' => $pages[$slug]]);
    }
}
