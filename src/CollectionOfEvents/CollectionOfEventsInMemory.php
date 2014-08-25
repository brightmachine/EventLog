<?php namespace EventLog\CollectionOfEvents;

use EventLog\CollectionOfEvents;

class CollectionOfEventsInMemory implements CollectionOfEvents
{
    private $events = [];
    public function __construct($events = [])
    {
        $this->events = $events;
    }

    public function append(CollectionOfEvents $other)
    {
        return new CollectionOfEventsInMemory(array_merge($this->events, iterator_to_array($other->events())));
    }

    public function events()
    {
        foreach ($this->events as $event) {
            yield $event;
        }
    }

    public function count()
    {
        return count($this->events);
    }
}
