<?php

use App\Http\Middleware\EnsureNotBlocked;
use App\Http\Middleware\EnsureNotLocked;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureSetupCompleted;
use App\Http\Middleware\EnsureTenantContext;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant.context' => EnsureTenantContext::class,
            'role' => EnsureRole::class,
            'not.blocked' => EnsureNotBlocked::class,
            'not.locked' => EnsureNotLocked::class,
            'setup.completed' => EnsureSetupCompleted::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        $middleware->web(append: [
            SecurityHeaders::class,
            EnsureNotBlocked::class,
            EnsureNotLocked::class,
            EnsureTenantContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
