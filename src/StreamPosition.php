<?php namespace EventLog;

use Assert;

/**
 * Represents a position in a stream, usually to begin reading.
 */
final class StreamPosition
{
    const START = '00000000-0000-0000-0000-000000000010';
    const END   = '00000000-0000-0000-0000-000000000020';
    private $position;

    protected function __construct($position)
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function position()
    {
        return $this->position;
    }

    /**
     * Create a StreamPosition value object from the given eventId.
     * @param string $eventId
     * @return StreamPosition
     */
    public static function from($eventId)
    {
        Assert\that($eventId)->notEmpty()->uuid();
        if (substr($eventId, 0, 8) == '00000000') {
            Assert\that($eventId)->choice([self::START, self::END]);
        }
        return new StreamPosition($eventId);
    }
}
