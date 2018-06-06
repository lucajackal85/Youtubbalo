<?php

namespace Jackal\Youtubbalo;

use Symfony\Component\Cache\Simple\FilesystemCache;

class Reader extends BaseYoutubeApi
{

    /**
     * @param $playlistId
     * @param int $maxResult
     * @return array
     */
    public function getVideoFromPlaylist($playlistId,$maxResult = 25,$cacheTTL = 3600){

        $cacheKey = 'youtubbalo.playlist_'.$playlistId;

        $cache = new FilesystemCache();

        if(!$cache->has($cacheKey)) {
            $service = $this->getService();

            $response = $service->playlistItems->listPlaylistItems('snippet,contentDetails', [
                'maxResults' => $maxResult,
                'playlistId' => $playlistId
            ]);

            $out = [];
            foreach ($response->getItems() as $item) {
                /** @var \Google_Service_YouTube_PlaylistItemSnippet $snippet */
                $snippet = $item->getSnippet();

                $out[] = [
                    'id' => $snippet->getResourceId()->getVideoId(),
                    'title' => $snippet->getTitle(),
                    'description' => $snippet->getDescription(),
                    'published_at' => new \DateTime($snippet->getPublishedAt()),
                    'playlist_id' => $snippet->getPlaylistId(),
                    'channel_id' => $snippet->getChannelId(),
                    'channel_title' => $snippet->getChannelTitle()
                ];
            }

            $cache->set($cacheKey,$out,$cacheTTL);
        }



        return $cache->get($cacheKey);
    }
}