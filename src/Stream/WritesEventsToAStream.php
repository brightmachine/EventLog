<?php namespace EventLog\Stream;

use EventLog\CollectionOfEvents;
use EventLog\ExpectedStreamRevision;

interface WritesEventsToAStream
{
    /**
     * Append 1 or more Events to a Stream
     * @param CollectionOfEvents $collectionOfEvents
     * @param integer $expectedStreamRevision the version at which we currently expect the stream to be in order that an
     *  optimistic concurrency check can be performed. This should either be a positive integer, or one of the constants
     *  ExpectedVersion::NONE, or to disable the check, ExpectedVersion::ANY
     * @return void
     */
    public function append(CollectionOfEvents $collectionOfEvents, $expectedStreamRevision = ExpectedStreamRevision::ANY);
    /**
     * Queue up 1 or more Events to be appended to a Stream intended to be appended as part of a transaction.
     * @param CollectionOfEvents $collectionOfEvents
     * @param integer $expectedStreamRevision the version at which we currently expect the stream to be in order that an
     *  optimistic concurrency check can be performed. This should either be a positive integer, or one of the constants
     *  ExpectedVersion::NONE, or to disable the check, ExpectedVersion::ANY
     * @return void
     */
    public function enqueue(CollectionOfEvents $collectionOfEvents, $expectedStreamRevision = ExpectedStreamRevision::ANY);

    /**
     * @return CollectionOfEvents return the events in the stream that have yet to be committed
     */
    public function eventsInQueue();
    /**
     * @return int
     */
    public function expectedStreamRevisionForEnqueuedEvents();

    /**
     * Clear the queue, returning the number of events cleared.
     * @return int the number
     */
    public function clearEventsInQueue();
}
