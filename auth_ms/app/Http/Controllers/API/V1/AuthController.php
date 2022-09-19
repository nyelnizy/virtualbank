<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\AuthService;
use Illuminate\Http\Request;
use Lcobucci\JWT\Encoding\CannotEncodeContent;
use Lcobucci\JWT\Signer\CannotSignPayload;
use Lcobucci\JWT\Signer\Ecdsa\ConversionFailed;
use Lcobucci\JWT\Signer\InvalidKeyProvided;

/**
 * @group Auth management
 *
 * APIs for logging in, refreshing tokens and logging out users
 */
class AuthController extends Controller
{
    /**
     * @var AuthService
     */
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Issue a token for a user
     * @bodyParam email string required The email of the user. Example: yhiamdan@gmail.com
     * @bodyParam password string required The password of the user.
     * @bodyParam remember boolean Indicates whether a user should be remembered.
     *
     * @response {
     *  "access": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.
     *   eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.
     *   SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c",
     *  "refresh": "e0f4cc30-846c-4689-8cea-1f3a3466fb4c",
     *  "expires": "2022-08-01 10:33",
     *  "access_token_expires": "2022-08-02 11:33"
     * }
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['username', 'password', 'remember']);
        try {
            $token = $this->authService->issueToken($credentials);
            return response()->json($token);
        } catch (\ErrorException $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    /**
     * Log a user out
     * @bodyParam user_id integer required
     * @response {
     * "status": true
     * }
     * @param Request $request
     * @return bool|null
     */
    public function logout(Request $request)
    {
        return $this->authService->revokeToken($request->user_id);
    }

    /**
     * Refresh a user's token with a refresh token
     * @bodyParam refresh_token string required
     * @response {
     *  "access": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.
     *   eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.
     *   SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c",
     * }
     * @param Request $request
     * @return string|null
     */
    public function refreshToken(Request $request)
    {
        return $this->authService->refreshToken($request->refresh_token);
    }
}
