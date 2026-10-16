<?php

namespace App\Http\Middleware;

use App\Support\SiteSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSiteIsLive
{
    /**
     * Tutup situs publik saat mode perawatan aktif; staf tetap bisa mengakses.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $maintenance = (bool) SiteSettings::value('maintenance_mode', false);

        if ($maintenance && ! $request->user()?->isStaff()) {
            return response()
                ->view('public.maintenance', ['site' => SiteSettings::values()], 503)
                ->header('Retry-After', '3600');
        }

        return $next($request);
    }
}
