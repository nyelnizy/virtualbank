<?php


namespace Tests\Contract\Consumer;

use App\Clients\V1\UserClient;
use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use PhpPact\Standalone\MockService\MockServerEnvConfig;
use PHPUnit\Framework\TestCase;

class UserClientTest extends TestCase
{
    public function test_get_user()
    {
        $request = new ConsumerRequest();
        $username = "yhiamdan";
        $password = "$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi";
        $user_id = 1;

        // sample request to send
        $request
            ->setMethod('GET')
            ->setPath("/api/v1/users")
            ->addQueryParameter("username", $username)
            ->addHeader('Accept', 'application/json')
            ->addHeader('Content-Type', 'application/json');

        // expected response
        $response = new ProviderResponse();
        $response
            ->setStatus(200)
            ->addHeader('Content-Type', 'application/json')
            ->setBody([
                'id' => $user_id,
                'password' => $password,
            ]);
        // Create a configuration that reflects the server that was started.
        // You can create a custom MockServerConfigInterface if needed.
        $config = new MockServerEnvConfig();
        $builder = new InteractionBuilder($config);
        $builder
            ->given("A user with username yhiamdan exists")
            ->uponReceiving("A get request to /api/v1/users?username=$username")
            ->with($request)
            ->willRespondWith($response); // This has to be last. This is what makes an API request to the Mock Server to set the interaction.

        $user_client = new UserClient($config->getBaseUri());
        $result = $user_client->getUser($username); // Make the real API request against the Mock Server.

        $this->assertTrue($builder->verify()); // This will verify that the interactions took place.
        $this->assertEquals($user_id, $result["id"]); // Make your assertions.
    }
}
