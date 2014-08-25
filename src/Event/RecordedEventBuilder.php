<?php namespace EventLog\Event;

use DateTimeImmutable;
use EventLog\Event;
use EventLog\Identity;
use EventLog\StreamName;

/**
 * provides a fluent interface for building a RecordedEvent
 */
class RecordedEventBuilder
{
    private $data = [];

    /**
     * @param mixed $recordId
     * @return $this
     */
    public function withRecordId($recordId)
    {
        $this->data = array_merge($this->data, compact('recordId'));
        return $this;
    }
    /**
     * @param StreamName $streamName
     * @param int $streamRevision
     * @return $this
     */
    public function withStream(StreamName $streamName, $streamRevision = 1)
    {
        $streamCategory = $streamName->streamCategory()->category();
        $streamIdentifier = $streamName->streamIdentifier();
        $this->data = array_merge($this->data, compact('streamCategory', 'streamIdentifier', 'streamRevision'));
        return $this;
    }
    /**
     * @param Identity|string $eventId
     * @param Event $event
     * @return $this
     */
    public function withEvent($eventId, Event $event)
    {
        if ($eventId instanceof Identity) {
            $eventId = $eventId->toString();
        }
        $eventName = $event->eventName();
        $eventData = $event->eventData();
        $eventMetadata = $event->eventMetadata();

        $this->occurringAt($event->occurredAt());

        $this->data = array_merge($this->data, compact('eventId', 'eventName', 'eventData', 'eventMetadata'));
        return $this;
    }
    /**
     * @param Identity|string $commitId
     * @return $this
     */
    public function withCommitId($commitId)
    {
        if ($commitId instanceof Identity) {
            $commitId = $commitId->toString();
        }
        $this->data = array_merge($this->data, compact('commitId'));
        return $this;
    }

    /**
     * @param DateTimeImmutable|string $occurredAt
     * @return $this
     */
    private function occurringAt($occurredAt)
    {
        if (!($occurredAt instanceof DateTimeImmutable)) {
            $occurredAt = new DateTimeImmutable($occurredAt);
        }
        $occurredAtEpoch = $occurredAt->getTimestamp();

        $this->data = array_merge($this->data, compact('occurredAt', 'occurredAtEpoch'));
        return $this;
    }

    public function build()
    {
        if (!array_key_exists('occurredAt', $this->data)) {
            $this->occurringAt(new DateTimeImmutable()); // set date to now
        }
        $recordedEvent = new RecordedEvent;
        foreach ($this->data as $k => $v) {
            $recordedEvent->{$k} = $v;
        }
        return $recordedEvent;
    }
}
