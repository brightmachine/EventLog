<?php namespace EventLog\Stream;

use EventLog\StreamCategory;
use EventLog\Streams;

/**
 * Can combine multiple streams into a single stream/feed.
 * *NB: this should be considered like a filter in that an empty array[] means all streams*
 */
interface CombinesMultipleStreams
{
    /**
     * To restrict which streams you wish to read from, provide an array of them here.
     * @param StreamCategory[] $streamCategories
     * @return Streams
     */
    public function fromStreams(array $streamCategories);

    /**
     * @return Streams return the streams that are being combined
     */
    public function streams();
}
