<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->session()->get('user'),
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
            ],
            'logoUrl' => route('legacy.image', ['path' => 'ncbii_logo_transparent.png']),
        ]);
    }
}
