<?php namespace EventLog;

use Assert;

final class ExpectedStreamRevision
{
    const ANY = -10;
    const NONE = -20;
    private $expectedStreamRevision;

    protected function __construct($expectedStreamRevision)
    {
        $this->expectedStreamRevision = $expectedStreamRevision;
    }

    /**
     * Create an ExpectedVersion value object from the given expectation.
     * @param integer $expectedStreamRevision
     * @return ExpectedStreamRevision
     */
    public static function is($expectedStreamRevision)
    {
        Assert\that($expectedStreamRevision)->notEmpty()->integer();
        if ($expectedStreamRevision < 0) {
            Assert\that($expectedStreamRevision)->choice([self::ANY, self::NONE]);
        }
        return new ExpectedStreamRevision($expectedStreamRevision);
    }
}
