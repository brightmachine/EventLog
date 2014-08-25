<?php namespace EventLog\UnitOfWork;

use EventLog\Event;
use EventLog\ExpectedStreamRevision;
use EventLog\Stream;
use EventLog\StreamName;

interface EnqueuesEventsOnAStream
{
    /**
     * @param StreamName $streamName
     * @param Event $event
     * @param int $expectedStreamRevision
     * @return void
     */
    public function appendEventToStream(StreamName $streamName, Event $event, $expectedStreamRevision = ExpectedStreamRevision::ANY);

    /**
     * @return Stream[]
     */
    public function streamsWithChanges();
}
