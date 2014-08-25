<?php namespace EventLog\EventStore;

interface NotifiesSubscribersOfNewEvents
{
    /**
     * A subscriber to the EventStore will be notified of new events as soon as they're committed across all streams.
     * @param callable $subscriber
     * @return void
     */
    public function subscribe(callable $subscriber);
    /**
     * Return observers that are subscribed to the EventStore
     * @return array
     */
    public function subscribers();
    /**
     * Notifies each subscriber of any new Events by passing each callable subscriber a CollectionOfEvents, containing
     * a load of RecordedEvents.
     * @return void
     */
    public function notifySubscribersOfEvents();
}
