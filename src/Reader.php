<?php

namespace Jackal\Youtubbalo;

use Symfony\Component\Cache\Simple\FilesystemCache;

class Reader extends BaseYoutubeApi
{

    /**
     * @param $playlistId
     * @param int $maxResult
     * @param int $cacheTTL
     * @return mixed
     */
    public function getVideoFromPlaylist($playlistId,$maxResult = 25,$cacheTTL = 3600){

        $cacheKey = 'youtubbalo.playlist_'.$maxResult.'_'.$playlistId;

        $cacheItem = $this->cacheAdapter->getItem($cacheKey);

        if(!$cacheItem->isHit()) {
            $service = $this->getService();

            $response = $service->playlistItems->listPlaylistItems('snippet,contentDetails', [
                'maxResults' => min(50,$maxResult),
                'playlistId' => $playlistId,
            ]);
            $resultsArray = $response->getItems();


            if($maxResult > 50){
                while(count($resultsArray) < $response->pageInfo->totalResults){
                    $response = $service->playlistItems->listPlaylistItems('snippet,contentDetails', [
                        'maxResults' => min(50,$maxResult),
                        'playlistId' => $playlistId,
                        'pageToken' => $response->getNextPageToken()
                    ]);
                    $resultsArray = array_merge($resultsArray,$response->getItems());
                }
            }

            $out = [];
            foreach ($resultsArray as $item) {
                /** @var \Google_Service_YouTube_PlaylistItemSnippet $snippet */
                $snippet = $item->getSnippet();

                $out[] = [
                    'id' => $snippet->getResourceId()->getVideoId(),
                    'title' => $snippet->getTitle(),
                    'description' => $snippet->getDescription(),
                    'published_at' => new \DateTime($snippet->getPublishedAt()),
                    'playlist_id' => $snippet->getPlaylistId(),
                    'channel_id' => $snippet->getChannelId(),
                    'channel_title' => $snippet->getChannelTitle(),
                    'result_no' => $snippet->position,
                    'total_results' => $response->pageInfo->totalResults
                ];
            }

            $cacheItem->set($out);
            $cacheItem->expiresAfter($cacheTTL);
            $this->cacheAdapter->save($cacheItem);
        }

        return $cacheItem->get();
    }
}
