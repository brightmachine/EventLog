<?php

namespace spec\EventLog;

use EventLog\Persistence;
use EventLog\Stream;
use EventLog\StreamCategory;
use EventLog\StreamName;
use EventLog\Streams;
use EventLog\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EventStoreSpec extends ObjectBehavior
{
    private $persistence;
    function let (Persistence $persistence)
    {
        $this->persistence = $persistence;
        $this->beConstructedWith($persistence);
    }
    function it_is_initializable()
    {
        $this->shouldHaveType('EventLog\EventStore');
    }

    function it_provides_access_to_a_stream()
    {
        $this->stream(StreamName::using('Test', 'stream'))->shouldImplement(Stream::class);
    }

    function it_provides_access_to_a_feed_of_streams()
    {
        $this->streams([])->shouldImplement(Streams::class);
        $this->streams([new StreamCategory('Test')])->shouldImplement(Streams::class);
    }

    function it_is_observable_by_event_listeners()
    {
        $callback = function ($events) {};
        $this->persistence->subscribe($callback)->shouldBeCalled();
        $this->persistence->subscribers()->shouldBeCalled();
        $this->persistence->subscribers()->willReturn([$callback, $callback]);

        $this->subscribe($callback);
        $this->subscribe($callback);
        $this->subscribers()->shouldHaveCount(2);
    }

    function it_can_generate_a_unit_of_work()
    {
        $this->unitOfWork()->shouldReturnAnInstanceOf(UnitOfWork::class);
    }

    function it_should_only_handle_one_unit_of_work_at_a_time ()
    {
        /** @var \PhpSpec\Wrapper\Subject $unitOfWorkSubject1 */
        $unitOfWorkSubject1 = $this->unitOfWork();
        /** @var \PhpSpec\Wrapper\Subject $unitOfWorkSubject2 */
        $unitOfWorkSubject2 = $this->unitOfWork();

        $unitOfWorkSubject1->shouldReturnAnInstanceOf(UnitOfWork::class);
        $unitOfWorkSubject2->shouldReturnAnInstanceOf(UnitOfWork::class);
        $unitOfWorkSubject1->shouldBeEqualTo($unitOfWorkSubject2->getWrappedObject());
    }

    public function listener()
    {

    }
}
