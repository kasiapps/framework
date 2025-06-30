<?php

namespace Kasi\Http\Middleware;

use Closure;
use Kasi\Http\Exceptions\PostTooLargeException;

class ValidatePostSize
{
    /**
     * Handle an incoming request.
     *
     * @param  \Kasi\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Kasi\Http\Exceptions\PostTooLargeException
     */
    public function handle($request, Closure $next)
    {
        $max = $this->getPostMaxSize();

        if ($max > 0 && $request->server('CONTENT_LENGTH') > $max) {
            throw new PostTooLargeException('The POST data is too large.');
        }

        return $next($request);
    }

    /**
     * Determine the server 'post_max_size' as bytes.
     *
     * @return int
     */
    protected function getPostMaxSize()
    {
        if (is_numeric($postMaxSize = ini_get('post_max_size'))) {
            return (int) $postMaxSize;
        }

        $metric = strtoupper(substr($postMaxSize, -1));

        $postMaxSize = (int) $postMaxSize;

        return match ($metric) {
            'K' => $postMaxSize * 1024,
            'M' => $postMaxSize * 1048576,
            'G' => $postMaxSize * 1073741824,
            default => $postMaxSize,
        };
    }
}
