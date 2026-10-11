<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\AuthServiceV1;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AuthControllerV1 extends Controller implements HasMiddleware
{
    public function __construct(private readonly AuthServiceV1 $authServiceV1) {}

    public static function middleware(): array
    {
        return [
            new Middleware('guest', except: ['logout']),
        ];
    }

    public function getLogin(): View
    {
        return view('auth.login');
    }
}
