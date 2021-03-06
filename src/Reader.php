<?php

namespace Jackal\Youtubbalo;

use Jackal\Youtubbalo\Model\ReaderSearchOption;
use Symfony\Component\Cache\Simple\FilesystemCache;

class Reader extends BaseYoutubeApi
{
    const YOUTUBE_MAX_RESULTS = 50;
    /**
     * @param $playlistId
     * @param int $maxResult
     * @param int $cacheTTL
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getVideoFromPlaylist(ReaderSearchOption $readerSearchOption, $cacheTTL = 3600)
    {
        $maxResult = $readerSearchOption->getMaxResults();
        $playlistId = $readerSearchOption->getPlaylistId();
        $order = $readerSearchOption->getOrder();

        $cacheKey = 'youtubbalo.playlist_'.$maxResult.'_'.$playlistId.'_'.md5(json_encode($order));

        $cacheItem = $this->cacheAdapter->getItem($cacheKey);
        $cacheItem->expiresAfter($cacheTTL);

        if (!$cacheItem->isHit()) {
            $maxResultOriginal = $maxResult;
            $response = $this->doRequest($playlistId, min(self::YOUTUBE_MAX_RESULTS, $maxResult));
            $resultsArray = $response->getItems();

            if ($maxResult > self::YOUTUBE_MAX_RESULTS) {
                while (count($resultsArray) < min($maxResultOriginal, $response->pageInfo->totalResults)) {
                    $maxResult-=self::YOUTUBE_MAX_RESULTS;
                    $response = $this->doRequest($playlistId, min(self::YOUTUBE_MAX_RESULTS, $maxResult), $response->getNextPageToken());
                    $resultsArray = array_merge($resultsArray, $response->getItems());
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

            if($order){
                uasort($out,function($a,$b) use ($order){

                    $aField = $a[$order['field']];
                    $bField = $b[$order['field']];

                    if($order['direction'] == 'desc'){
                        return (($aField < $bField) ? 1: -1);
                    }else {
                        return (($aField < $bField) ? -1 : 1);
                    }
                });
                $out = array_values($out);
            }

            $this->cacheAdapter->save($cacheItem->set($out));
        }

        return $cacheItem->get();
    }

    private function doRequest($playlistId, $maxResults, $pageToken = null)
    {
        $params = [
            'maxResults' => $maxResults,
            'playlistId' => $playlistId,
        ];

        if ($pageToken) {
            $params['pageToken'] = $pageToken;
        }

        return $this->getService()->playlistItems->listPlaylistItems('snippet,contentDetails', $params);
    }

    public function clearVideoPlaylistCache()
    {
        $this->cacheAdapter->clear();
    }
}
