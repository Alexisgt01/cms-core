<?php

namespace Alexisgt01\CmsCore\Http\Middleware;

use Alexisgt01\CmsCore\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleRestrictedAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = SiteSetting::instance();

        if (! $settings->restricted_access_enabled) {
            return $next($request);
        }

        $adminPath = config('cms-core.path', 'admin');

        if (str_starts_with(ltrim($request->getPathInfo(), '/'), $adminPath)) {
            return $next($request);
        }

        if ($request->is('cms/restricted-access')) {
            return $next($request);
        }

        if ($settings->restricted_access_admin_bypass && $request->user()) {
            return $next($request);
        }

        if ($request->cookie('cms_restricted_access') === 'granted') {
            return $next($request);
        }

        return response()->view('cms-core::restricted-access', [
            'message' => $settings->restricted_access_message,
        ]);
    }
}
