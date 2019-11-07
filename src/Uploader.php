<?php


namespace Jackal\Youtubbalo;

use Jackal\Youtubbalo\Model\Video;

class Uploader extends BaseYoutubeApi
{
    public function upload(Video $video, $privacyStatus, $channelId = null, $playlistId = null)
    {
        $service = $this->getService();

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

        if ($channelId) {
            $resourceId->setChannelId($channelId);
        }

        /*if($playlistId){
            $playlistItemSnippet = new \Google_Service_YouTube_PlaylistItemSnippet();
            $playlistItemSnippet->setTitle($video->getTitle());
            $playlistItemSnippet->setPlaylistId($playlistId);
            $playlistItemSnippet->setResourceId($resourceId);
            $playlistItemSnippet->setPosition(0);

            $playlistItem = new \Google_Service_YouTube_PlaylistItem();
            $playlistItem->setSnippet($playlistItemSnippet);
            $service->playlistItems->insert('snippet,contentDetails', $playlistItem, []);
        } */

        return $uploaded->getId();
    }
}
