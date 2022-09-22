<?php

namespace Tests\Contract\Provider;

use GuzzleHttp\Psr7\Uri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpPact\Standalone\ProviderVerifier\Model\VerifierConfig;
use PhpPact\Standalone\ProviderVerifier\Verifier;
use Tests\TestCase;

class AuthmsContractTest extends TestCase
{

    use RefreshDatabase;
    private $path;
    private $app_url;
    
    public function setUp(): void
    {
       parent::setUp();
       $this->path = base_path("../pact/output/authms-userms.json");
       $this->app_url = config("app.url");
    }
    public function test_get_user_returns_valid_user()
    {
        $config = new VerifierConfig();
        $config
            ->setProviderName('userms')
            ->setProviderVersion('1.0.0')
            ->setProviderBranch('main')
            ->setProviderBaseUrl(new Uri($this->app_url))
            ->setProviderStatesSetupUrl("$this->app_url/api/setup")
            ->setPublishResults(false)
            ->setEnablePending(true);

        $verifier = new Verifier($config);
        $verifier->verifyFiles([$this->path]);
        $this->assertTrue(true);
    }
}
