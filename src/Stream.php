<?php namespace EventLog;

use EventLog\CollectionOfEvents\CollectionOfEventsInMemory;
use EventLog\Stream\ReadsAndWritesAStream;

/**
 * An API to interact with a single Stream of event:
 * - read the event history
 * - write 1 or more events
 * - queue up events for persisting
 *
 * Each Stream is identified using a Name and an Identifier.
 * - Name: represent a channel in which all events are related on an abstract level
 * - Identifier: narrows the channel down to that all events are similar in a concrete level
 *
 * A single stream, for example, might represent all the events that have happened in an instance of an Aggregate Root.
 */
final class Stream implements ReadsAndWritesAStream
{
    /** @var Persistence */
    private $persistence;
    /** @var StreamName */
    private $streamName;
    /** @var CollectionOfEvents */
    private $pendingEvents;
    /** @var int */
    private $expectedStreamRevisionForPendingEvents;

    protected function __construct(Persistence $persistence, StreamName $streamName)
    {
        $this->persistence = $persistence;
        $this->streamName = $streamName;
        $this->initQueue();
    }

    public function streamName()
    {
        return $this->streamName;
    }

    /**
     * Return a Stream API linked to the given EventStore
     * @param Persistence $persistence
     * @param StreamName $streamName
     * @return Stream
     */
    public static function open(Persistence $persistence, StreamName $streamName)
    {
        return new Stream($persistence, $streamName);
    }

    public function append(CollectionOfEvents $collectionOfEvents, $expectedStreamRevision = ExpectedStreamRevision::ANY)
    {
        $this->persistence->writeEventsToAStream(
            $this->streamName,
            $collectionOfEvents,
            $expectedStreamRevision
        );
    }

    private function initQueue ()
    {
        $this->pendingEvents = new CollectionOfEventsInMemory();
    }
    public function enqueue(CollectionOfEvents $collectionOfEvents, $expectedStreamRevision = ExpectedStreamRevision::ANY)
    {
        $this->pendingEvents = $this->pendingEvents->append($collectionOfEvents);
        $this->expectedStreamRevisionForPendingEvents = $expectedStreamRevision;
    }
    public function eventsInQueue()
    {
        return $this->pendingEvents;
    }
    public function expectedStreamRevisionForEnqueuedEvents()
    {
        return $this->expectedStreamRevisionForPendingEvents;
    }
    public function clearEventsInQueue()
    {
        $numberPendingEvents = count($this->pendingEvents);
        $this->initQueue();
        return $numberPendingEvents;
    }

    public function events($startFromEventId = StreamPosition::START)
    {
        return $this->persistence->readEventsFromAStream(
            $this->streamName,
            $startFromEventId
        );
    }

    public function eventsInReverseOrder($startFromEventId = StreamPosition::END)
    {
        return $this->persistence->readEventsFromAStreamInReverseOrder(
            $this->streamName,
            $startFromEventId
        );
    }
}
