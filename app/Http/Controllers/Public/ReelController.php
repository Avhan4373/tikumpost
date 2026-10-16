<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Reel;
use Illuminate\View\View;

class ReelController extends Controller
{
    public function index(): View
    {
        $reels = Reel::published()->ordered()->paginate(12);

        return view('public.reels', compact('reels'));
    }
}
