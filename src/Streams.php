<?php namespace EventLog;

use EventLog\Stream\CombinesMultipleStreams;
use EventLog\Stream\ReadsEventsFromAStream;

/**
 * An API to read from a feed of multiple Streams, with Events merged together as if part of a single Stream:
 * - read the event history
 * - write 1 or more events
 * - queue up events for persisting
 *
 * A single stream, for example, might represent all the events that have happened in an instance of an Aggregate Root.
 */
final class Streams implements ReadsEventsFromAStream, CombinesMultipleStreams
{
    private $persistence;
    private $streamCategories;

    /**
     * @param Persistence $persistence
     * @param StreamCategory[] $streamCategories
     */
    protected function __construct(Persistence $persistence, array $streamCategories)
    {
        $this->persistence = $persistence;
        $this->streamCategories = $streamCategories;
    }

    /**
     * Return a Stream API linked to the given EventStore
     * @param Persistence $persistence
     * @param StreamCategory[] $streamCategories
     * @return Streams
     */
    public static function open(Persistence $persistence, array $streamCategories = [])
    {
        return new Streams($persistence, $streamCategories);
    }

    /**
     * @param StreamCategory[] $streamCategories
     * @return void
     */
    public function fromStreams(array $streamCategories)
    {
        $this->streamCategories = $streamCategories;
    }

    public function streams()
    {
        return $this->streamCategories;
    }

    public function events($startFromEventId = StreamPosition::START)
    {
        return $this->persistence->readEventsFromMultipleStreams(
            $this->streamCategories,
            $startFromEventId
        );
    }

    public function eventsInReverseOrder($startFromEventId = StreamPosition::END)
    {
        return $this->persistence->readEventsFromMultipleStreamsInReverseOrder(
            $this->streamCategories,
            $startFromEventId
        );
    }
}
