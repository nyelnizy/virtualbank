<?php

namespace Tests\Unit\V1;

use App\Clients\V1\UserClient;
use App\Models\V1\RefreshToken;
use App\Services\V1\AuthService;
use App\Services\V1\TokenService;
use App\Repositories\V1\AuthRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\IdentifiedBy;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Mockery;
use Mockery\MockInterface;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;
    private $user_id = 1;
    public function test_issue_token_returns_valid_token_if_user_is_found()
    {
        $this->instance(
            UserClient::class,
            Mockery::mock(UserClient::class, function (MockInterface $mock) {
                $mock->shouldReceive('getUser')
                    ->once()
                    ->andReturn(['id' => $this->user_id,"password"=>"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi"]);
            })
        );
        $this->instance(
            AuthRepository::class,
            Mockery::mock(AuthRepository::class, function (MockInterface $mock){
                $mock->shouldReceive('createToken')
                    ->once()
                    ->andReturn(new RefreshToken([
                        'user_id' => $this->user_id,
                        'token' => 'fake token',
                        'expires' => now(),
                        'access_token_expires' => now()]));
            })
        );
        $authService = $this->app->make(AuthService::class);
        $token = null;
        try {
            $token = $authService->issueToken(['username' => 'testuser', 'password' => 'password']);
        } catch (\ErrorException $e) {
            var_dump($e->getMessage());
        }
        $this->assertNotNull($token);
        $this->assertTrue($this->verifyToken($token['access']));
    }

    public function test_issue_token_throws_exception_if_user_is_not_found()
    {
        $this->instance(
            UserClient::class,
            Mockery::mock(UserClient::class, function (MockInterface $mock) {
                $mock->shouldReceive('getUser')
                    ->once()
                    ->andReturn(null);
            })
        );
        $authService = $this->app->make(AuthService::class);
        $token = null;
        $exception_message = null;
        try {
            $token = $authService->issueToken(['username' => 'testuser', 'password' => 'password']);
        } catch (\ErrorException $e) {
            $exception_message = $e->getMessage();

        }
        $this->assertNull($token);
        $this->assertSame('Invalid username or password', $exception_message);

    }

    public function test_remember_me_extends_refresh_token_expiry_to_thirty_days()
    {
        $this->mockObjects();
        $authService = $this->app->make(AuthService::class);
        $token = null;
        try {
            $token = $authService->issueToken(['username' => 'testuser', 'password' => 'password','remember'=>true]);
        } catch (\ErrorException $e) {
            var_dump($e->getMessage());
        }

        $this->assertNotNull($token);
        $expiry_date = Carbon::parse($token['expires']);
        $days = now()->daysUntil($expiry_date)->count();
        $this->assertSame(30,$days);
    }

    public function test_refresh_token_expiry_is_one_day_without_remember_me()
    {
        $this->mockObjects();
        $authService = $this->app->make(AuthService::class);
        $token = null;
        try {
            $token = $authService->issueToken(['username' => 'testuser', 'password' => 'password','remember'=>false]);
        } catch (\ErrorException $e) {
            var_dump($e->getMessage());
        }

        $this->assertNotNull($token);
        $expiry_date = Carbon::parse($token['expires']);
        $days = now()->daysUntil($expiry_date)->count();
        $this->assertSame(1,$days);
    }

    public function test_valid_refresh_token_can_be_used_to_obtain_access_token()
    {
        $authService = $this->app->make(AuthService::class);
        $refresh = RefreshToken::factory()->create([
            'user_id'=>$this->user_id,
            'token'=>Uuid::uuid4(),
            'expires'=>now()->addDays(1),
            'access_token_expires'=>now()->subHours(1),
        ]);
        $token = null;
        try {
            $token = $authService->refreshToken($refresh->token);
        } catch (\ErrorException $e) {
            var_dump($e->getMessage());
        }

        $this->assertNotNull($token);
        $this->assertTrue($this->verifyToken($token));
    }

    public function test_access_token_request_fails_if_refresh_token_is_invalid()
    {
        $authService = $this->app->make(AuthService::class);
        $refresh = RefreshToken::factory()->create([
            'user_id'=>$this->user_id,
            'token'=>Uuid::uuid4(),
            'expires'=>now()->addDays(1),
            'access_token_expires'=>now()->subHours(1),
            'invalidated'=>true,
        ]);
        $token = null;
        try {
            $token = $authService->refreshToken($refresh->token);
        } catch (\ErrorException $e) {
            var_dump($e->getMessage());
        }

        $this->assertNull($token);
    }

    public function test_access_token_request_fails_if_refresh_token_is_expired()
    {
        $authService = $this->app->make(AuthService::class);
        $refresh = RefreshToken::factory()->create([
            'user_id'=>$this->user_id,
            'token'=>Uuid::uuid4(),
            'expires'=>now()->subDays(1),
            'access_token_expires'=>now()->subHours(1),
        ]);
        $token = null;
        try {
            $token = $authService->refreshToken($refresh->token);
        } catch (\ErrorException $e) {
            var_dump($e->getMessage());
        }

        $this->assertNull($token);
    }

    public function test_access_token_request_fails_if_access_token_is_not_expired()
    {
        $authService = $this->app->make(AuthService::class);
        $refresh = RefreshToken::factory()->create([
            'user_id'=>$this->user_id,
            'token'=>Uuid::uuid4(),
            'expires'=>now()->addDays(1),
            'access_token_expires'=>now()->addHours(1),
        ]);
        $token = null;
        try {
            $token = $authService->refreshToken($refresh->token);
        } catch (\ErrorException $e) {
            var_dump($e->getMessage());
        }

        $this->assertNull($token);
    }

    public function test_refresh_token_is_invalidated_when_logged_out()
    {
        $refresh = RefreshToken::factory()->create([
            'user_id'=>$this->user_id,
            'token'=>Uuid::uuid4(),
            'expires'=>now()->addDays(1),
            'access_token_expires'=>now()->addHours(1),
        ]);
        $authService = $this->app->make(AuthService::class);
        $success = $authService->revokeToken($refresh->user_id);
        $this->assertNotNull($success);
        $this->assertTrue($success);
    }

    private function verifyToken(string $jwt): bool
    {
        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::empty(),
            InMemory::file(base_path('keys/tym-pub.pem'))
        );
        $config->setValidationConstraints(new IssuedBy(config('app.url')));
        $config->setValidationConstraints(new IdentifiedBy("token". 1));
        $token = $config->parser()->parse($jwt);
        $this->assertInstanceOf(UnencryptedToken::class, $token);
        $constraints = $config->validationConstraints();
        try {
            $config->validator()->assert($token, ...$constraints);
            return true;
        } catch (RequiredConstraintsViolated $e) {
            return false;
        }
    }
    private function mockObjects():void{
        $this->instance(
            UserClient::class,
            Mockery::mock(UserClient::class, function (MockInterface $mock) {
                $mock->shouldReceive('getUser')
                    ->once()
                    ->andReturn(['id' => $this->user_id,"password"=>"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi"]);
            })
        );
        $this->instance(
            TokenService::class,
            Mockery::mock(TokenService::class, function (MockInterface $mock) {
                $mock->shouldReceive('generateToken')
                    ->once()
                    ->andReturn('fake token');
            })
        );
    }
}
