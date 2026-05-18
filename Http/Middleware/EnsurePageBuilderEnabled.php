<?php

namespace Modules\PageBuilder\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePageBuilderEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(page_builder_enabled(), 404);

        return $next($request);
    }
}
