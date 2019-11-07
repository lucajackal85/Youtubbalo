<?php


namespace Jackal\Youtubbalo\Model;


class ReaderSearchOption
{
    protected $orderDirections = ['asc','desc'];
    protected $orderFields = ['published_at','title','id'];
    protected $maxResults = 25;
    protected $order = [];

    protected $playlistId = null;



    public function __construct($playlistId)
    {
        $this->playlistId = $playlistId;
    }

    /**
     * @return mixed
     */
    public function getPlaylistId()
    {
        return $this->playlistId;
    }

    /**
     * @return mixed
     */
    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * @param mixed $maxResults
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;
    }

    /**
     * @return array
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param string $orderField
     * @param string $orderDirection
     */
    public function setOrder($orderField,$orderDirection)
    {
        if(!in_array($orderField,$this->orderFields)){
            throw new \InvalidArgumentException(
                sprintf('Order field "%s" is not valid. Possible are [%s]',$orderField,implode(', ',$this->orderFields))
            );
        }

        if(!in_array($orderDirection,$this->orderDirections)){
            throw new \InvalidArgumentException(
                sprintf('Order direction "%s" is not valid. Possible are [%s]',$orderDirection,implode(', ',$this->orderDirections))
            );
        }

        $this->order = [
            'field' => $orderField,
            'direction' => $orderDirection
        ];
    }


}