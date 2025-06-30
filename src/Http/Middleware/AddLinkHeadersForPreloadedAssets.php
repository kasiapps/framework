<?php

namespace Kasi\Http\Middleware;

use Kasi\Http\Response;
use Kasi\Support\Collection;
use Kasi\Support\Facades\Vite;

class AddLinkHeadersForPreloadedAssets
{
    /**
     * Handle the incoming request.
     *
     * @param  \Kasi\Http\Request  $request
     * @param  \Closure  $next
     * @return \Kasi\Http\Response
     */
    public function handle($request, $next)
    {
        return tap($next($request), function ($response) {
            if ($response instanceof Response && Vite::preloadedAssets() !== []) {
                $response->header('Link', (new Collection(Vite::preloadedAssets()))
                    ->map(fn ($attributes, $url) => "<{$url}>; ".implode('; ', $attributes))
                    ->join(', '), false);
            }
        });
    }
}
