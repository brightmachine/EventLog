<?php namespace EventLog;

use EventLog\EventStore\NotifiesSubscribersOfNewEvents;
use EventLog\UnitOfWork\TracksEventsAcrossStreams;

/**
 * handles the following:
 * - setting up the persistence layer, e.g. creating tables or collections
 * - persisting a CollectionOfEvents
 * - committing a UnitOfWork
 * - read events from a stream
 * - read events from a collection of streams
 */
interface Persistence extends NotifiesSubscribersOfNewEvents
{
    /**
     * @param StreamName $streamName
     * @param CollectionOfEvents $events
     * @param int $expectedStreamRevision the version you expect the stream to be at
     * @return int number of events written
     */
    public function writeEventsToAStream(StreamName $streamName, CollectionOfEvents $events, $expectedStreamRevision);
    /**
     * Read Events from a Stream in the order they were committed.
     * @param StreamName $streamName
     * @param string $startFromEventId the UUID of the last event seen, or one of the StreamPosition constants
     * @return CollectionOfEvents
     */
    public function readEventsFromAStream(StreamName $streamName, $startFromEventId);
    /**
     * Read Events from a Stream in the reverse order in which they were committed.
     * @param StreamName $streamName
     * @param string $startFromEventId the UUID of the last event seen, or one of the StreamPosition constants
     * @return CollectionOfEvents
     */
    public function readEventsFromAStreamInReverseOrder(StreamName $streamName, $startFromEventId);
    /**
     * Read Events from a Stream in the order they were committed.
     * @param StreamCategory[] $streamCategories
     * @param string $startFromEventId the UUID of the last event seen, or one of the StreamPosition constants
     * @return CollectionOfEvents
     */
    public function readEventsFromMultipleStreams(array $streamCategories, $startFromEventId);
    /**
     * Read Events from a Stream in the reverse order in which they were committed.
     * @param StreamCategory[] $streamCategories
     * @param string $startFromEventId the UUID of the last event seen, or one of the StreamPosition constants
     * @return CollectionOfEvents
     */
    public function readEventsFromMultipleStreamsInReverseOrder(array $streamCategories, $startFromEventId);
    /**
     * @return CollectionOfEvents
     */
    public function readAllEventsAsRecorded();
    /**
     * @param TracksEventsAcrossStreams $unitOfWork
     * @param bool $commitPerStream
     * @return int the number of events committed
     */
    public function commit(TracksEventsAcrossStreams $unitOfWork, $commitPerStream = false);
}
