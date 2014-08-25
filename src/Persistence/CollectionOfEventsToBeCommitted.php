<?php namespace EventLog\Persistence;


use EventLog\CollectionOfEvents;
use EventLog\StreamName;

class CollectionOfEventsToBeCommitted
{
    private $streamName;
    private $collectionOfEvents;
    /** @var integer */
    private $expectedStreamRevision;

    public function __construct(StreamName $streamName, CollectionOfEvents $collectionOfEvents, $expectedStreamRevision)
    {
        $this->streamName = $streamName;
        $this->collectionOfEvents = $collectionOfEvents;
        $this->expectedStreamRevision = $expectedStreamRevision;
    }

    /**
     * @return StreamName
     */
    public function streamName()
    {
        return $this->streamName;
    }

    /**
     * @return CollectionOfEvents
     */
    public function collectionOfEvents()
    {
        return $this->collectionOfEvents;
    }

    /**
     * @return int
     */
    public function expectedStreamRevision()
    {
        return $this->expectedStreamRevision;
    }
}
