<?php


namespace Jackal\Youtubbalo;


use Google_Client;
use Google_Service_YouTube;
use Jackal\Youtubbalo\Model\Credentials;
use Jackal\Youtubbalo\Model\Video;

class Uploader
{
    private $credentials;

    public function __construct(Credentials $credentials)
    {
        // client_secret.json
        $this->credentials = $credentials;
    }

    public function upload(Video $video,$privacyStatus){
        $client = $this->getClient();

        $service = new Google_Service_YouTube($client);

        $snippet = new \Google_Service_YouTube_VideoSnippet();
        $snippet->setTitle($video->getTitle());
        $snippet->setDescription($video->getDescription());

        $status = new \Google_Service_YouTube_VideoStatus();
        $status->privacyStatus = $privacyStatus;

        $youtubevideo = new \Google_Service_YouTube_Video();
        $youtubevideo->setSnippet($snippet);
        $youtubevideo->setStatus($status);


        $uploaded = $service->videos->insert(
            "status,snippet",
            $youtubevideo,
            array(
                'data' => file_get_contents($video->getFile()->getPathname()),
                'uploadType' => 'media'
            )
        );
        $resourceId = new \Google_Service_YouTube_ResourceId();
        $resourceId->setVideoId($uploaded->getId());
        $resourceId->setKind('youtube#video');

        return $uploaded->getId();
    }

    private function getClient() {
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
        return $client;
    }
}