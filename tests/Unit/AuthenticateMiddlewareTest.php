<?php

namespace Tests\Unit;

use App\Http\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthenticateMiddlewareTest extends TestCase
{
    public function test_redirect_to_returns_null_for_json_requests(): void
    {
        Route::get('/login', fn () => 'ok')->name('login');

        /** @var Authenticate $middleware */
        $middleware = $this->app->make(Authenticate::class);

        $request = Request::create('/api/anything', 'GET');
        $request->headers->set('Accept', 'application/json');

        $this->assertNull($middleware->redirectTo($request));
    }

    public function test_redirect_to_returns_login_route_for_non_json_requests(): void
    {
        Route::get('/login', fn () => 'ok')->name('login');

        /** @var Authenticate $middleware */
        $middleware = $this->app->make(Authenticate::class);

        $request = Request::create('/anything', 'GET');
        $request->headers->set('Accept', 'text/html');

        $this->assertSame(route('login'), $middleware->redirectTo($request));
    }
}
