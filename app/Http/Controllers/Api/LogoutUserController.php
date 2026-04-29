<?php

namespace App\Http\Controllers\Api;

use App\Http\Contracts\LogoutUserControllerInterface;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Override;

/**
 * {@inheritDoc}
 */
class LogoutUserController extends Controller implements LogoutUserControllerInterface
{
    /**
     * Revoke the personal access token matching the request {@see Request::bearerToken}
     * (the same credential Sanctum used to authenticate this call).
     */
    #[Override]
    public function logout(Request $request): JsonResponse
    {
        $plain = $request->bearerToken();

        if ($plain !== null) {
            PersonalAccessToken::findToken($plain)?->delete();
        }

        return ApiResponse::success(null, 'Logout successful');
    }
}
