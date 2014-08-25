<?php namespace EventLog;

/**
 * A CollectionOfEvents is used to persist events to a Stream and is returned by the EventStore to be used for
 * reconstitution of state in various guises.
 */
interface CollectionOfEvents extends \Countable
{
    /**
     * @param CollectionOfEvents $other
     * @return CollectionOfEvents
     */
    public function append(CollectionOfEvents $other);

    /**
     * @return Event[] that can be iterated over which itself will return a RecordedEvent
     */
    public function events();
}
