<?php

namespace spec\EventLog;

use EventLog\CollectionOfEvents\CollectionOfEventsInMemory;
use EventLog\Event\StandaloneEvent;
use EventLog\ExpectedStreamRevision;
use EventLog\Persistence;
use EventLog\StreamName;
use EventLog\StreamPosition;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StreamSpec extends ObjectBehavior
{
    private $persistence;
    function let (Persistence $persistence)
    {
        $this->persistence = $persistence;
        $this->beConstructedThrough('open', [$persistence, StreamName::using('Test', 'streamId')]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('EventLog\Stream');
    }

    function it_appends_events_to_a_stream()
    {
        $events = $this->getCollectionOfEvents();
        $this->persistence->writeEventsToAStream(StreamName::using('Test', 'streamId'), $events, ExpectedStreamRevision::ANY)->shouldBeCalled();
        $this->append($events);
    }

    function it_enqueues_events_to_a_stream()
    {
        $events = $this->getCollectionOfEvents();
        $this->enqueue($events);
        $this->eventsInQueue()->shouldHaveCount(2);
    }

    function it_can_clear_enqueued_events()
    {
        $events = $this->getCollectionOfEvents();
        $this->enqueue($events);
        $this->eventsInQueue()->shouldHaveCount(2);
        $this->clearEventsInQueue()->shouldReturn(2);
        $this->eventsInQueue()->shouldHaveCount(0);
    }

    /** The actual concurrency verification is handled by Persistence, so not tested for here */
    function it_sets_stream_version_number_for_concurrency_checks()
    {
        $events = $this->getCollectionOfEvents();
        $this->enqueue($events, ExpectedStreamRevision::NONE);
        $this->expectedStreamRevisionForEnqueuedEvents()->shouldReturn(ExpectedStreamRevision::NONE);
        $this->enqueue($events, ExpectedStreamRevision::ANY);
        $this->expectedStreamRevisionForEnqueuedEvents()->shouldReturn(ExpectedStreamRevision::ANY);
    }

    function it_can_read_events_from_the_stream()
    {
        $events = $this->getCollectionOfEvents();
        $this->persistence->readEventsFromAStream(StreamName::using('Test', 'streamId'), StreamPosition::START)
            ->shouldBeCalled()
            ->willReturn($events);
        $this->events()->shouldReturn($events);
    }

    function it_can_read_events_from_the_stream_in_reverse()
    {
        $events = $this->getCollectionOfEvents();
        $this->persistence->readEventsFromAStreamInReverseOrder(StreamName::using('Test', 'streamId'), StreamPosition::END)
            ->shouldBeCalled()
            ->willReturn($events);
        $this->eventsInReverseOrder()->shouldReturn($events);
    }


    private function getCollectionOfEvents()
    {
        $collection = new CollectionOfEventsInMemory([
            new StandaloneEvent('RegistrationEmailSent', ['to'=>'billy@example.com']),
            new StandaloneEvent('PasswordResetEmailSent', ['to'=>'billy@example.com']),
        ]);
        return $collection;
    }
}
