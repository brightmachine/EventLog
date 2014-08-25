<?php

namespace spec\EventLog;

use EventLog\Event\StandaloneEvent;
use EventLog\Persistence;
use EventLog\StreamName;
use EventLog\TracksChanges;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UnitOfWorkSpec extends ObjectBehavior
{
    private $persistence;

    function let(Persistence $persistence)
    {
        $this->persistence = $persistence;
        $this->beConstructedWith($persistence);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('EventLog\UnitOfWork');
    }

    function it_tracks_objects_for_changes(TracksChanges $subject1, TracksChanges $subject2)
    {
        $this->track(StreamName::using('StreamCategory', 'streamId1'), $subject1);
        $this->track(StreamName::using('StreamCategory', 'streamId2'), $subject2);
        $this->tracked()->shouldHaveCount(2);
    }

    function it_queues_events_onto_a_stream()
    {
        $event1 = new StandaloneEvent('RegistrationEmailSent', ['to'=>'billy@example.com']);
        $event2 = new StandaloneEvent('PasswordResetEmailSent', ['to'=>'billy@example.com']);
        $this->appendEventToStream(StreamName::using('Emails', 'registration'), $event1);
        $this->appendEventToStream(StreamName::using('Emails', 'passwordReset'), $event2);
        $this->streamsWithChanges()->shouldHaveCount(2);
    }

    function it_can_commit_all_changes_in_one_go()
    {
        $this->persistence->commit($this, false)->shouldBeCalled();
        $this->commit();
    }

    function it_can_commit_all_changes_in_a_single_transaction_per_stream()
    {
        $this->persistence->commit($this, true)->shouldBeCalled();
        $this->commit(true);
    }
}
