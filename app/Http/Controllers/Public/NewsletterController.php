<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriberRequest;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;

class NewsletterController extends Controller
{
    public function store(StoreSubscriberRequest $request): RedirectResponse
    {
        Subscriber::create($request->validated());

        return back()->with('status', 'Terima kasih telah berlangganan newsletter kami!');
    }
}
