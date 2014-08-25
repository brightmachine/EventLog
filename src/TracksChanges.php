<?php namespace EventLog;

/**
 * Tracks history using Events
 */
interface TracksChanges
{
    /**
     * @return bool as to whether the objects has new Events to emit
     */
    public function hasChanges();

    /**
     * @return CollectionOfEvents
     */
    public function changes();

    /**
     * Clear any pending events.
     * @return void
     */
    public function clearChanges();
}
