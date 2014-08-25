<?php namespace EventLog\Event;

use DateTimeImmutable;
use EventLog\Event;

/**
 * A RecordedEvent has already happened and has been stored.
 */
class RecordedEvent implements Event
{
    /** @var mixed the id of the record - different persistence backends will use different types */
    public $recordId;
    /** @var string */
    public $streamCategory;
    /** @var string */
    public $streamIdentifier;
    /** @var integer each new event on a stream will increase by 1 the revision number, beginning at 1 */
    public $streamRevision;
    /** @var string a unique UUID of the event */
    public $eventId;
    /** @var string */
    public $eventName;
    /** @var array produced by the json decoding */
    public $eventData;
    /** @var array contextual data around the environment that triggered the event */
    public $eventMetadata;
    /** @var string a unique UUID of the commit */
    public $commitId;
    /** @var \DateTimeImmutable */
    public $occurredAt;
    /** @var float representing the milliseconds since the epoch when the Event was created in the system */
    public $occurredAtEpoch;

    public function eventName()
    {
        return $this->eventName;
    }
    public function eventData()
    {
        return $this->eventData;
    }
    public function eventMetadata()
    {
        return $this->eventMetadata;
    }
    public function occurredAt()
    {
        return $this->occurredAt;
    }
}
