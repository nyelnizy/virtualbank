<?php


namespace App\Clients\V1;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Http;
use Mockery\Exception;

class UserClient
{
    private $client;

    public function __construct($base_url = null)
    {
        $this->client = new Client(["base_uri"=>$base_url ?? config("virtualbank.userms")]);
    }

    /**
     * @param string $username
     * @return array|null
     */
    public function getUser(string $username): ?array
    {
        try {
            $res = $this->client->get("/api/v1/users?username=$username", [
                "headers" => [
                    "Accept" => "application/json",
                    "Content-Type" => "application/json"
                ]
            ]);
            if ($res->getStatusCode() == 200) {
                return json_decode($res->getBody()->getContents(), true);
            }
            return null;
        } catch (GuzzleException $e) {
            logger($e->getMessage());
            return null;
        }
    }
}
