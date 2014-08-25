<?php namespace EventLog\Stream;

use EventLog\StreamPosition;

interface ReadsEventsFromAStream
{
    /**
     * Read the Events in the order in which they were originally written to the stream from a nominated starting point
     * @param string $startFromEventId
     * @return mixed @todo what is returned here: a generator?
     */
    public function events($startFromEventId = StreamPosition::START);
    /**
     * Read the Events from the most recent first and work backwards.
     * @param string $startFromEventId
     * @return mixed
     */
    public function eventsInReverseOrder($startFromEventId = StreamPosition::END);
}
