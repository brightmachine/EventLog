<?php namespace EventLog;

use EventLog\CollectionOfEvents\CollectionOfEventsInMemory;
use EventLog\UnitOfWork\TrackedObject;
use EventLog\UnitOfWork\TracksEventsAcrossStreams;

/**
 * A Unit of Work for the Event Store:
 * - can tracks changes to objects that support it
 * - can enqueue events on a stream
 * - can commit changes
 */
final class UnitOfWork implements TracksEventsAcrossStreams
{
    private $persistence;
    private $trackedObjects = [];
    private $streamsWithChanges = [];

    public function __construct(Persistence $persistence)
    {
        $this->persistence = $persistence;
    }

    public function track(StreamName $streamName, TracksChanges $subject, $expectedStreamRevision = ExpectedStreamRevision::ANY)
    {
        $this->trackedObjects[(string) $streamName] = new TrackedObject($streamName, $subject, $expectedStreamRevision);
    }

    public function tracked()
    {
        return $this->trackedObjects;
    }

    public function appendEventToStream(
        StreamName $streamName,
        Event $event,
        $expectedStreamRevision = ExpectedStreamRevision::ANY
    ) {
        $stream = $this->openStream($streamName);
        $stream->enqueue(new CollectionOfEventsInMemory([$event]), $expectedStreamRevision);
    }

    /**
     * @param StreamName $streamName
     * @return Stream
     */
    private function openStream(StreamName $streamName)
    {
        $streamKey = (string) $streamName;
        if (!array_key_exists($streamKey, $this->streamsWithChanges)) {
            $this->streamsWithChanges[$streamKey] = Stream::open(
                $this->persistence,
                $streamName
            );
        }
        return $this->streamsWithChanges[$streamKey];
    }

    public function streamsWithChanges()
    {
        return $this->streamsWithChanges;
    }


    /**
     * @param bool $commitPerStream whether to break the commit apart across each stream
     * @return mixed
     */
    public function commit($commitPerStream = false)
    {
        $this->persistence->commit($this, $commitPerStream);
    }
}
