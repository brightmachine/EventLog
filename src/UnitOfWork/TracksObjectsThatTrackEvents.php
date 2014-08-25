<?php namespace EventLog\UnitOfWork;

use EventLog\ExpectedStreamRevision;
use EventLog\StreamName;
use EventLog\TracksChanges;

interface TracksObjectsThatTrackEvents
{
    /**
     * Tracks the given object for changes specifying a StreamName to put changed events
     * @param StreamName $streamName,
     * @param TracksChanges $subject
     * @param integer $expectedStreamRevision
     * @return mixed
     */
    public function track(StreamName $streamName, TracksChanges $subject, $expectedStreamRevision = ExpectedStreamRevision::ANY);

    /**
     * @return TrackedObject[] the objects being tracked
     */
    public function tracked();
}
