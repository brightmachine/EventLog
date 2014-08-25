<?php namespace EventLog\UnitOfWork;

use EventLog\StreamName;
use EventLog\TracksChanges;

final class TrackedObject implements TracksChanges
{
    /** @var StreamName  */
    private $streamName;
    /** @var TracksChanges */
    private $subject;
    /** @var integer */
    private $expectedStreamRevision;

    public function __construct(StreamName $streamName, TracksChanges $subject, $expectedStreamRevision)
    {
        $this->streamName = $streamName;
        $this->subject = $subject;
        $this->expectedStreamRevision = $expectedStreamRevision;
    }

    /**
     * @return StreamName
     */
    public function streamName()
    {
        return $this->streamName;
    }

    /**
     * @return int
     */
    public function expectedStreamRevision()
    {
        return $this->expectedStreamRevision;
    }

    public function hasChanges()
    {
        return $this->subject->hasChanges();
    }

    public function changes()
    {
        return $this->subject->changes();
    }

    public function clearChanges()
    {
        return $this->subject->clearChanges();
    }
}
