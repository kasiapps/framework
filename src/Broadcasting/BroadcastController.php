<?php

namespace Kasi\Broadcasting;

use Kasi\Http\Request;
use Kasi\Routing\Controller;
use Kasi\Support\Facades\Broadcast;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BroadcastController extends Controller
{
    /**
     * Authenticate the request for channel access.
     *
     * @param  \Kasi\Http\Request  $request
     * @return \Kasi\Http\Response
     */
    public function authenticate(Request $request)
    {
        if ($request->hasSession()) {
            $request->session()->reflash();
        }

        return Broadcast::auth($request);
    }

    /**
     * Authenticate the current user.
     *
     * See: https://pusher.com/docs/channels/server_api/authenticating-users/#user-authentication.
     *
     * @param  \Kasi\Http\Request  $request
     * @return array|null
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function authenticateUser(Request $request)
    {
        if ($request->hasSession()) {
            $request->session()->reflash();
        }

        return Broadcast::resolveAuthenticatedUser($request)
                    ?? throw new AccessDeniedHttpException;
    }
}
