<?php namespace EventLog;

/**
 * Identifies a specific stream.
 * A Stream name is broken down into two parts:
 * - a category: where events are related abstractly
 * - an identifier: for identifying a particular stream where events are related concretely
 */
final class StreamName
{
    /** @var StreamCategory */
    private $streamCategory;
    /** @var string */
    private $streamIdentifier;

    public function __construct(StreamCategory $streamCategory, $streamIdentifier = null)
    {
        $this->streamCategory = $streamCategory;
        $this->streamIdentifier = $streamIdentifier;
    }

    /**
     * @return StreamCategory
     */
    public function streamCategory()
    {
        return $this->streamCategory;
    }

    /**
     * @return string
     */
    public function streamIdentifier()
    {
        return $this->streamIdentifier;
    }

    public function __toString ()
    {
        return sprintf('%s::%s', $this->streamCategory->category(), $this->streamIdentifier);
    }

    public static function using ($streamCategory, $streamIdentifier)
    {
        return new StreamName(new StreamCategory($streamCategory), $streamIdentifier);
    }
}
