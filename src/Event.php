<?php namespace EventLog;

use DateTimeImmutable;

/**
 * To add Events to a Stream, you must create an object that implements this interface.
 */
interface Event
{
    /**
     * @return string the name of the event… *keep it simple*. E.g. `return end(explode('\\', __CLASS__));`
     */
    public function eventName();

    /**
     * @return array|null data that goes along with the actual event
     */
    public function eventData();

    /**
     * @return array|null meta data
     */
    public function eventMetadata();

    /**
     * @return DateTimeImmutable that represents when this event occurred
     */
    public function occurredAt();
}
