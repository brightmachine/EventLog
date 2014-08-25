<?php

namespace spec\EventLog;

use EventLog\CollectionOfEvents\CollectionOfEventsInMemory;
use EventLog\Event\StandaloneEvent;
use EventLog\Persistence;
use EventLog\StreamCategory;
use EventLog\StreamPosition;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StreamsSpec extends ObjectBehavior
{
    private $persistence;
    function let (Persistence $persistence)
    {
        $this->persistence = $persistence;
        $this->beConstructedThrough('open', [$persistence, [new StreamCategory('Test')]]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('EventLog\Streams');
    }

    function it_can_combine_some_streams()
    {
        $streams = [new StreamCategory('Test'), new StreamCategory('Test1')];
        $this->fromStreams($streams);
        $this->streams()->shouldReturn($streams);
    }

    function it_can_combine_all_streams()
    {
        $this->fromStreams([]);
        $this->streams()->shouldReturn([]);
    }

    function it_can_read_events_from_the_stream()
    {
        $events = $this->getCollectionOfEvents();
        $this->persistence->readEventsFromMultipleStreams($this->streams(), StreamPosition::START)
            ->shouldBeCalled()
            ->willReturn($events);
        $this->events()->shouldReturn($events);
    }

    function it_can_read_events_from_the_stream_in_reverse()
    {
        $events = $this->getCollectionOfEvents();
        $this->persistence->readEventsFromMultipleStreamsInReverseOrder($this->streams(), StreamPosition::END)
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
