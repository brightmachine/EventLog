<?php namespace EventLog\Stream;

use EventLog\StreamName;

interface ReadsAndWritesAStream extends ReadsEventsFromAStream, WritesEventsToAStream
{
    /**
     * @return StreamName
     */
    public function streamName();
}
