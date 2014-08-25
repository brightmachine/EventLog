<?php namespace EventLog;

use EventLog\EventStore\AccessesPersistence;
use EventLog\EventStore\AccessesStreams;
use EventLog\EventStore\NotifiesSubscribersOfNewEvents;
use EventLog\EventStore\ProvidesAUnitOfWork;

/**
 * The API for interacting with EventLog from your code.
 * This EventStore:
 * - accesses streams
 * - notifies subscribers
 * - provides a UnitOfWork
 */
final class EventStore implements AccessesStreams, NotifiesSubscribersOfNewEvents, ProvidesAUnitOfWork
{
    private $persistence;

    /**
     * @param Persistence $persistence
     */
    public function __construct(Persistence $persistence)
    {
        $this->persistence = $persistence;
    }

    public function stream(StreamName $streamName)
    {
        return Stream::open($this->persistence, $streamName);
    }

    public function streams(array $streamCategories = [])
    {
        return Streams::open($this->persistence, $streamCategories);
    }

    public function subscribe(callable $subscriber)
    {
        $this->persistence->subscribe($subscriber);
    }
    public function subscribers()
    {
        return $this->persistence->subscribers();
    }
    public function notifySubscribersOfEvents()
    {
        $this->persistence->notifySubscribersOfEvents();
    }

    public function unitOfWork()
    {
        static $unitOfWork;
        if (!is_null($unitOfWork)) {
            return $unitOfWork;
        } else {
            $unitOfWork = new UnitOfWork($this->persistence);
            return $unitOfWork;
        }
    }
}
