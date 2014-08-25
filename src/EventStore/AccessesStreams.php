<?php namespace EventLog\EventStore;

use EventLog\Stream;
use EventLog\StreamCategory;
use EventLog\StreamName;
use EventLog\Streams;

interface AccessesStreams
{
    /**
     * Access the API for interacting with a single stream.
     * @param StreamName $streamName
     * @return Stream
     */
    public function stream(StreamName $streamName);

    /**
     * Access the API for interacting with multiple streams.
     * @param StreamCategory[] $streamCategories
     * @return Streams
     */
    public function streams(array $streamCategories = []);
}
