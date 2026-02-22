<?php

namespace Alexisgt01\CmsCore\Http\Middleware;

use Alexisgt01\CmsCore\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        $redirects = Redirect::getCachedRedirects();

        if (! isset($redirects[$path])) {
            return $next($request);
        }

        $match = $redirects[$path];

        // Record hit asynchronously
        dispatch(function () use ($match): void {
            Redirect::withoutEvents(function () use ($match): void {
                $redirect = Redirect::find($match['id']);
                $redirect?->recordHit();
            });
        })->afterResponse();

        if ($match['status_code'] === 410) {
            abort(410);
        }

        return redirect($match['destination_url'], $match['status_code']);
    }
}
