<?php namespace EventLog;

use Rhumsaa\Uuid\Uuid;

/**
 * A generic class to generate identities, e.g. for a Commit, an Event.
 */
class Identity
{
    /** @var Uuid */
    private $uuid;

    protected function __construct (Uuid $uuid)
    {
        $this->uuid = $uuid;
    }

    public function toString()
    {
        return $this->uuid->toString();
    }

    public function __toString()
    {
        return $this->toString();
    }

    public static function generate ()
    {
        return new Identity(Uuid::uuid4());
    }
}
