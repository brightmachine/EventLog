<?php namespace EventLog\Persistence;

use EventLog\CollectionOfEvents;
use EventLog\CollectionOfEvents\CollectionOfEventsInMemory;
use EventLog\CollectionOfEvents\CollectionOfRecordedEvents;
use EventLog\Event\RecordedEventBuilder;
use EventLog\Identity;
use EventLog\Persistence;
use EventLog\StreamCategory;
use EventLog\StreamName;
use EventLog\StreamPosition;
use EventLog\UnitOfWork\TracksEventsAcrossStreams;

class PersistInMemory implements Persistence
{
    private $subscribers;
    /** @var CollectionOfEvents */
    private $recordedEvents;
    private $transaction; // temporary store for events for replicating as transaction
    private $eventsToNotify; // events to be pushed to subscribers
    private $streamRevisions = [];

    public function __construct()
    {
        $this->recordedEvents = new CollectionOfRecordedEvents(new CollectionOfEventsInMemory());
        $this->transaction = new CollectionOfEventsInMemory();
        $this->eventsToNotify = new CollectionOfEventsInMemory();
    }

    public function subscribe(callable $subscriber)
    {
        $this->subscribers[] = $subscriber;
    }

    public function subscribers()
    {
        return $this->subscribers;
    }

    public function notifySubscribersOfEvents()
    {
        if (count($this->eventsToNotify) && count($this->subscribers)) {
            try {
                foreach ($this->subscribers as $subscriber) {
                    call_user_func($subscriber, $this->eventsToNotify);
                }
            } catch (\Exception $e) {
            }
            $this->eventsToNotify = new CollectionOfEventsInMemory();
        }
    }

    public function writeEventsToAStream(StreamName $streamName, CollectionOfEvents $collectionOfEvents, $expectedStreamRevision)
    {
        $commitId = $this->beginTransaction();
        $this->writeEventsToATransaction($commitId, $streamName, $collectionOfEvents, $expectedStreamRevision);
        return $this->commitTransaction();
    }

    public function writeEventsToATransaction(Identity $commitId, StreamName $streamName, CollectionOfEvents $collectionOfEvents, $expectedStreamRevision)
    {
        // concurrency control
        $currentStreamRevision = $this->getCurrentStreamRevision($streamName);
        StreamRevisionShouldBeExpected::enforce($currentStreamRevision, $expectedStreamRevision);

        $eventsToBeAdded = [];
        foreach ($collectionOfEvents->events() as $event) {
            $recordedEvent = (new RecordedEventBuilder)
                ->withRecordId($this->getNextRecordId())
                ->withStream($streamName, $this->getNextStreamRevision($streamName))
                ->withEvent(Identity::generate(), $event)
                ->withCommitId($commitId)
                ->build()
            ;
            $eventsToBeAdded[] = $recordedEvent;
        }
        $this->transaction = $this->transaction->append(new CollectionOfEventsInMemory($eventsToBeAdded));
    }

    private function getNextRecordId()
    {
        static $lastId = 1;
        return $lastId++;
    }

    /**
     * Return a stream key to use to report on or modify the stream revision.
     * Also, adds the stream key to revision on first sight.
     * @param StreamName $streamName
     * @return string stream key
     */
    private function streamKey(StreamName $streamName)
    {
        $streamKey = (string) $streamName;
        if (!array_key_exists($streamKey, $this->streamRevisions)) {
            $this->streamRevisions[$streamKey] = 0;
        }
        return $streamKey;
    }
    private function getNextStreamRevision(StreamName $streamName)
    {
        $streamKey = $this->streamKey($streamName);
        return ++$this->streamRevisions[$streamKey];
    }
    private function getCurrentStreamRevision(StreamName $streamName)
    {
        $streamKey = $this->streamKey($streamName);
        return $this->streamRevisions[$streamKey];
    }

    /**
     * Starts a transactions and returns a CommitId
     * @return Identity a commitId
     */
    protected function beginTransaction()
    {
        $this->transaction = new CollectionOfEventsInMemory();
        return Identity::generate();
    }

    /**
     * @return int number of events written
     */
    protected function commitTransaction()
    {
        $numEventsWritten = count($this->transaction);
        $this->recordedEvents = $this->recordedEvents->append($this->transaction);
        $this->eventsToNotify = $this->eventsToNotify->append($this->transaction);
        $this->transaction = new CollectionOfEventsInMemory();
        $this->notifySubscribersOfEvents();
        return $numEventsWritten;
    }

    public function commit(TracksEventsAcrossStreams $unitOfWork, $commitPerStream = false)
    {
        $streamsWithChanges = $this->collateEventsToCommit($unitOfWork);

        if ($commitPerStream == true) {
            return $this->commitPerStream($streamsWithChanges);
        } else {
            return $this->commitAllStreams($streamsWithChanges);
        }
    }

    private function collateEventsToCommit (TracksEventsAcrossStreams $unitOfWork)
    {
        // first, let's resolves all of the events we have to commit
        $streamsWithChanges = [];
            // streams with changes
        foreach ($unitOfWork->streamsWithChanges() as $stream) {
            $streamName = $stream->streamName();
            $streamsWithChanges[(string) $streamName] = new CollectionOfEventsToBeCommitted(
                $streamName,
                $stream->eventsInQueue(),
                $stream->expectedStreamRevisionForEnqueuedEvents()
            );
        }

            // tracked objects
        foreach ($unitOfWork->tracked() as $trackedObject) {
            if (!$trackedObject->hasChanges()) {
                continue;
            }
            $streamName = $trackedObject->streamName();
            $streamsWithChanges[(string) $streamName] = new CollectionOfEventsToBeCommitted(
                $streamName,
                $trackedObject->changes(),
                $trackedObject->expectedStreamRevision()
            );
        }
        return $streamsWithChanges;
    }

    /**
     * Commit all events with a seperate commit per stream
     * @param CollectionOfEventsToBeCommitted[] $streamsWithChanges
     * @return int number of events committed
     */
    private function commitPerStream(array $streamsWithChanges)
    {
        $numEventsCommitted = 0;
        foreach ($streamsWithChanges as $collectionOfEventsToBeCommitted) {
            $numEventsCommitted += $this->writeEventsToAStream(
                $collectionOfEventsToBeCommitted->streamName(),
                $collectionOfEventsToBeCommitted->collectionOfEvents(),
                $collectionOfEventsToBeCommitted->expectedStreamRevision()
            );
        }
        return $numEventsCommitted;
    }

    /**
     * Commit all events in 1 big ol' transaction
     * @param CollectionOfEventsToBeCommitted[] $streamsWithChanges
     * @return int number of events committed
     */
    private function commitAllStreams($streamsWithChanges)
    {
        $commitId = $this->beginTransaction();
        foreach ($streamsWithChanges as $collectionOfEventsToBeCommitted) {
            $this->writeEventsToATransaction(
                $commitId,
                $collectionOfEventsToBeCommitted->streamName(),
                $collectionOfEventsToBeCommitted->collectionOfEvents(),
                $collectionOfEventsToBeCommitted->expectedStreamRevision()
            );
        }
        return $this->commitTransaction();
    }

    public function readEventsFromAStream(StreamName $streamName, $startFromEventId)
    {
        return $this->findEventsInAStream($streamName, $startFromEventId, true);
    }

    /**
     * @param StreamName $streamName
     * @param $startFromEventId
     * @param bool $forwardDirection
     * @return CollectionOfEventsInMemory
     */
    private function findEventsInAStream(StreamName $streamName, $startFromEventId, $forwardDirection = true)
    {
        $seenStartingEvent = $startFromEventId == StreamPosition::START ? true : false;
        $eventsToReturn = [];
        $stream = $streamName->__toString();
        $events = iterator_to_array($this->recordedEvents->events());
        if (!$forwardDirection) {
            $events = array_reverse($events);
        }
        foreach ($events as $event) {
            $streamKey = StreamName::using($event->streamCategory, $event->streamIdentifier)->__toString();
            if ($streamKey != $stream) {
                continue;
            }
            if (!$seenStartingEvent) {
                if ($event->eventId == $startFromEventId) {
                    $seenStartingEvent = true;
                } else {
                    continue;
                }
            }
            $eventsToReturn[] = $event;
        }
        return new CollectionOfEventsInMemory($eventsToReturn);
    }

    /**
     * @param StreamCategory[] $streamCategories
     * @param $startFromEventId
     * @param bool $forwardDirection
     * @return CollectionOfEventsInMemory
     */
    private function findEventsInMultipleStreams(array $streamCategories, $startFromEventId, $forwardDirection = true)
    {
        $streams = [];
        foreach ($streamCategories as $streamCategory) {
            $streams[(string)$streamCategory] = (string)$streamCategory;
        }
        $seenStartingEvent = $startFromEventId == StreamPosition::START ? true : false;
        $eventsToReturn = [];
        $events = iterator_to_array($this->recordedEvents->events());
        if (!$forwardDirection) {
            $events = array_reverse($events);
        }
        foreach ($events as $event) {
            if (!empty($streams) && !array_key_exists($event->streamCategory, $streams)) {
                continue;
            }
            if (!$seenStartingEvent) {
                if ($event->eventId == $startFromEventId) {
                    $seenStartingEvent = true;
                } else {
                    continue;
                }
            }

            $eventsToReturn[] = $event;
        }
        return new CollectionOfEventsInMemory($eventsToReturn);
    }

    public function readEventsFromAStreamInReverseOrder(StreamName $streamName, $startFromEventId)
    {
        return $this->findEventsInAStream($streamName, $startFromEventId, false);
    }

    public function readEventsFromMultipleStreams(array $streamCategories, $startFromEventId)
    {
        return $this->findEventsInMultipleStreams($streamCategories, $startFromEventId, true);
    }

    public function readEventsFromMultipleStreamsInReverseOrder(array $streamCategories, $startFromEventId)
    {
        return $this->findEventsInMultipleStreams($streamCategories, $startFromEventId, false);
    }

    public function readAllEventsAsRecorded()
    {
        return $this->recordedEvents;
    }
}
