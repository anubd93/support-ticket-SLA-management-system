<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\LoginRequestV1;
use App\Services\V1\AuthServiceV1;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

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

    public function login(LoginRequestV1 $request): JsonResponse
    {
        try {
            $response = $this->authServiceV1->login($request->validated(), $request->ip());

            if ($response === true) {
                $request->session()->regenerate();

                return response()->json([
                    'message' => 'Signed in successfully.',
                    'redirect' => $request->session()->pull('url.intended', route('dashboard')),
                ]);
            }

            return response()->json([
                'message' => $response
            ], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $exception) {
            report($exception);

            return response()->json([
                'message' => 'Something went wrong. Please try again.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
