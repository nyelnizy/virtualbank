<?php


namespace App\Services\V1;

use App\Clients\V1\UserClient;
use App\Repositories\V1\AuthRepository;
use ErrorException;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;

class AuthService
{
    /**
     * @var int
     */
    private $expiry_hours = 1;
    /**
     * @var UserClient
     */
    private $userServiceClient;

    /**
     * @var AuthRepository
     */
    private $authRepository;

    /**
     * @var TokenService
     */
    private $tokenService;

    public function __construct(
        UserClient $userServiceClient,
        AuthRepository $authRepository,
        TokenService $tokenService)
    {
        $this->userServiceClient = $userServiceClient;
        $this->authRepository = $authRepository;
        $this->tokenService = $tokenService;
    }

    /**
     * @param array $credentials
     * @return array
     * @throws ErrorException
     */
    public function issueToken(array $credentials): array
    {
        //verify user details from user microservice
        $user = $this->userServiceClient->getUser($credentials["username"]);
        //issue token if user was found
        if (!empty($user) && Hash::check($credentials["password"],$user["password"])) {
            $expiry_days = (isset($credentials['remember']) && $credentials['remember']) ? 30 : 1;
            $access = $this->tokenService->generateToken($user, $this->expiry_hours);
            $input = [
                'user_id' => $user["id"],
                'token' => Uuid::uuid4(),
                'expires' => now()->addDays($expiry_days),
                'access_token_expires' => now()->addHours($this->expiry_hours)
            ];
            $refresh = $this->authRepository->createToken($input);
            return [
                'access' => $access,
                'refresh' => $refresh->token,
                'expires' => $refresh->expires,
                'access_token_expires' => $refresh->access_token_expires
            ];
        }
        throw new ErrorException('Invalid username or password');
    }

    public function refreshToken(string $refresh_token) : ?string{
        $refresh = $this->authRepository->getRefreshToken($refresh_token);
        //client is trying to refresh token when its not expired,
        //that's not good, invalidate refresh token and force user to login
        if(!empty($refresh)){
            if ($refresh->access_token_expires >= now()) {
                $refresh->fill(['invalidated' => true])->save();
                return null;
            } else {
                $token = $this->tokenService->generateToken(['id'=>$refresh->user_id],$this->expiry_hours);
                //reset access token expire time
                $refresh->fill(['access_token_expires' => now()->addHours( $this->expiry_hours)]);
                return $token;
            }
        }
        return null;
    }
    public function revokeToken(int $user_id) : ?bool
    {
        $token = $this->authRepository->getLatestToken($user_id);
        if (!empty($token)) {
           return $token->fill(['invalidated' => true])->save();
        }
        return null;
    }
}
