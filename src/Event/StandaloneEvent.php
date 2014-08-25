<?php namespace EventLog\Event;

use EventLog\Event;

class StandaloneEvent implements Event
{
    private $eventName;
    private $eventData = [];
    private $eventMetadata = [];
    private $occurredAt;

    public function __construct($eventName, $eventData = [], $eventMetadata = [], \DateTimeImmutable $occurredAt = null)
    {
        $this->eventName = $eventName;
        $this->eventData = $eventData;
        $this->eventMetadata = $eventMetadata;
        $this->occurredAt = is_null($occurredAt) ? new \DateTimeImmutable() : $occurredAt;
    }

    /**
     * @return mixed
     */
    public function eventName()
    {
        return $this->eventName;
    }

    /**
     * @return array
     */
    public function eventData()
    {
        return $this->eventData;
    }

    /**
     * @return array
     */
    public function eventMetadata()
    {
        return $this->eventMetadata;
    }

    /**
     * @return mixed
     */
    public function occurredAt()
    {
        return $this->occurredAt;
    }
}
