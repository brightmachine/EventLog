<?php namespace EventLog\Persistence;

use EventLog\ExpectedStreamRevision;
use Exception;

/**
 * Class checks that a Stream is in the expected state, throwing an Exception in the case that it is not.
 */
class StreamRevisionShouldBeExpected extends Exception
{
    public static function enforce($currentStreamRevision, $expectedStreamRevision)
    {
        switch ($expectedStreamRevision) {
            case ExpectedStreamRevision::ANY:
                return; // we simply don't mind
            case ExpectedStreamRevision::NONE:
                if ($currentStreamRevision > 0) {
                    throw StreamRevisionShouldBeExpected::expectedAnEmptyStream($currentStreamRevision);
                }
                return;
            // we should have a positive integer at this point
            default:
                if ($currentStreamRevision != $expectedStreamRevision) {
                    throw StreamRevisionShouldBeExpected::unexpectedStreamRevision($expectedStreamRevision, $currentStreamRevision);
                }
                return;
        }
    }

    protected static function expectedAnEmptyStream($currentStreamRevision)
    {
        $message = sprintf("Expected an empty stream, but stream at revision %d", $currentStreamRevision);
        return new StreamRevisionShouldBeExpected($message);
    }
    protected static function unexpectedStreamRevision($expectedStreamRevision, $currentStreamRevision)
    {
        $message = sprintf("Stream revision is %d when expected %d", $currentStreamRevision, $expectedStreamRevision);
        return new StreamRevisionShouldBeExpected($message);
    }
}
