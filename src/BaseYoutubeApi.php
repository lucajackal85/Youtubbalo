<?php


namespace Jackal\Youtubbalo;


use Google_Client;
use Google_Service_YouTube;
use Jackal\Youtubbalo\Model\Credentials;
use Jackal\Youtubbalo\Model\Video;

abstract class BaseYoutubeApi
{
    protected $credentials;

    public function __construct(Credentials $credentials)
    {
        // client_secret.json
        $this->credentials = $credentials;
    }

    protected function getService() {
        $client = new Google_Client();
        $client->setAuthConfig($this->credentials->getClientSecret()->getPathname());
        // Set to valid redirect URI for your project.
        $client->setRedirectUri('http://localhost');
        $client->addScope([Google_Service_YouTube::YOUTUBE, Google_Service_YouTube::YOUTUBE_UPLOAD]);
        $client->setAccessType('offline');


        $accessToken = json_decode(file_get_contents($this->credentials->getOauth2()->getPathname()),true);


        $client->setAccessToken($accessToken);
        // Refresh the token if it's expired.

        if ($client->isAccessTokenExpired()) {
            $client->refreshToken($client->getRefreshToken());
            file_put_contents($this->credentials->getOauth2()->getPathname(), json_encode($client->getAccessToken()));
        }

        return new Google_Service_YouTube($client);
    }
}