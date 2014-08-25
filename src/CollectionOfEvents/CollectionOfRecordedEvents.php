<?php namespace EventLog\CollectionOfEvents;

use EventLog\CollectionOfEvents;
use EventLog\Event\RecordedEvent;

/**
 * Wraps around whatever CollectionOfEvents is passed in.
 */
class CollectionOfRecordedEvents implements CollectionOfEvents
{
    private $collectionOfEvents;
    public function __construct(CollectionOfEvents $collectionOfEvents)
    {
        $this->collectionOfEvents = $collectionOfEvents;
    }

    public function append(CollectionOfEvents $other)
    {
        $collectionOfEvents = $this->collectionOfEvents->append($other);
        return new CollectionOfRecordedEvents($collectionOfEvents);
    }

    /**
     * @return RecordedEvent[] that can be iterated over which itself will return a RecordedEvent
     */
    public function events()
    {
        return $this->collectionOfEvents->events();
    }

    public function count()
    {
        return $this->collectionOfEvents->count();
    }
}
