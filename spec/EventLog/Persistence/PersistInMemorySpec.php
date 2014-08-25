<?php

namespace spec\EventLog\Persistence;

use EventLog\CollectionOfEvents;
use EventLog\CollectionOfEvents\CollectionOfEventsInMemory;
use EventLog\Event\StandaloneEvent;
use EventLog\EventStore;
use EventLog\ExpectedStreamRevision;
use EventLog\Persistence\StreamRevisionShouldBeExpected;
use EventLog\Stream\ReadsAndWritesAStream;
use EventLog\StreamName;
use EventLog\StreamPosition;
use EventLog\TracksChanges;
use EventLog\UnitOfWork\TrackedObject;
use EventLog\UnitOfWork\TracksEventsAcrossStreams;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PersistInMemorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('EventLog\Persistence\PersistInMemory');
    }

    function it_can_be_observed_by_subscribers()
    {
        $callback = function ($events) {};
        $this->subscribe($callback);
        $this->subscribe($callback);
        $this->subscribers()->shouldHaveCount(2);
    }

    function it_reads_all_events_as_recorded()
    {
        // TODO ensure that the right events are returned
        $this->readAllEventsAsRecorded()->shouldImplement(CollectionOfEvents::class);
    }

    function it_can_stop_events_written_if_stream_revision_is_unexpected()
    {
        $events = $this->getCollectionOfEvents();
        $this->shouldThrow(StreamRevisionShouldBeExpected::class)
            ->duringWriteEventsToAStream(StreamName::using('Test', 'streamId'), $events, 2);
    }

    function it_writes_events_to_a_stream()
    {
        $events = $this->getCollectionOfEvents();
        $this->writeEventsToAStream(StreamName::using('Test', 'streamId'), $events, ExpectedStreamRevision::NONE)
            ->shouldReturn(2);
    }

    function it_can_write_events_to_multiple_streams()
    {
        $events = $this->getCollectionOfEvents();
        $stream1 = StreamName::using('Test', 'streamId1');
        $stream2 = StreamName::using('Test', 'streamId2');
        $this->writeEventsToAStream($stream1, $events, ExpectedStreamRevision::NONE)->shouldReturn(2);
        $this->writeEventsToAStream($stream2, $events, ExpectedStreamRevision::NONE)->shouldReturn(2);
        $this->readAllEventsAsRecorded()->shouldHaveCount(4);
        $this->writeEventsToAStream($stream1, $events, 2);
        $this->readAllEventsAsRecorded()->shouldHaveCount(6);
        $this->shouldThrow(StreamRevisionShouldBeExpected::class)->duringWriteEventsToAStream($stream1, $events, 3);
        $this->shouldNotThrow(StreamRevisionShouldBeExpected::class)->duringWriteEventsToAStream($stream1, $events, 4);
    }

    function it_notifies_subscribers_of_new_events(EventListener $eventListener)
    {
        $this->subscribe([$eventListener, 'fire']);
        $events = $this->getCollectionOfEvents();
        $this->writeEventsToAStream(StreamName::using('Test', 'streamId'), $events, ExpectedStreamRevision::NONE)->shouldReturn(2);
        $eventListener->fire(Argument::type(CollectionOfEvents::class))->shouldBeCalled();
    }

    function it_commits_a_unit_of_work_in_a_single_transaction(
        TracksEventsAcrossStreams $unitOfWork,
        ReadsAndWritesAStream $stream1,
        ReadsAndWritesAStream $stream2,
        EventListener $eventListener
    ) {
        $this->setupUnitOfWork($unitOfWork, $stream1, $stream2);
        $this->subscribe([$eventListener, 'fire']);
        $this->commit($unitOfWork, false)->shouldReturn(10);
        $eventListener->fire(Argument::type(CollectionOfEvents::class))->shouldBeCalledTimes(1);
    }

    function it_commits_a_unit_of_work_with_a_transaction_per_stream(
        TracksEventsAcrossStreams $unitOfWork,
        ReadsAndWritesAStream $stream1,
        ReadsAndWritesAStream $stream2,
        EventListener $eventListener
    ) {
        $this->setupUnitOfWork($unitOfWork, $stream1, $stream2);
        $this->subscribe([$eventListener, 'fire']);
        $this->commit($unitOfWork, true)->shouldReturn(10);
        $eventListener->fire(Argument::type(CollectionOfEvents::class))->shouldBeCalledTimes(3);
    }

    function it_reads_events_from_a_stream()
    {
        $events = $this->getCollectionOfEvents();
        $stream1 = StreamName::using('Test', 'streamId1');
        $stream2 = StreamName::using('Test', 'streamId2');
        $this->writeSomeEvents($events->append($events)->append($events), $stream1); // adding 2 event 3 times = 6
        $this->writeSomeEvents($events, $stream2); // 2
        $this->readEventsFromAStream($stream1, StreamPosition::START)->shouldHaveCount(6);
        $this->readEventsFromAStream($stream2, StreamPosition::START)->shouldHaveCount(2);
    }

    function it_reads_events_from_a_stream_starting_from_a_known_event()
    {
        $events = $this->getCollectionOfEvents();
        $stream1 = StreamName::using('Test', 'streamId1');
        $stream2 = StreamName::using('Test', 'streamId2');
        $this->writeSomeEvents($events->append($events)->append($events), $stream1); // adding 2 event 3 times = 6
        $this->writeSomeEvents($events, $stream2); // 2

        $allRecordedEvents = iterator_to_array($this->readAllEventsAsRecorded()->getWrappedObject()->events());
        $this->readEventsFromAStream($stream1, $allRecordedEvents[2]->eventId)->shouldHaveCount(4);
        $this->readEventsFromAStream($stream1, $allRecordedEvents[4]->eventId)->shouldHaveCount(2);
        $this->readEventsFromAStream($stream2, $allRecordedEvents[7]->eventId)->shouldHaveCount(1);
    }

    function it_reads_events_from_a_stream_in_reverse()
    {
        $events = $this->getCollectionOfEvents();
        $stream1 = StreamName::using('Test', 'streamId1');
        $stream2 = StreamName::using('Test', 'streamId2');
        $this->writeSomeEvents($events->append($events)->append($events), $stream1); // adding 2 event 3 times = 6
        $this->writeSomeEvents($events, $stream2); // 2

        $allRecordedEvents = iterator_to_array($this->readAllEventsAsRecorded()->getWrappedObject()->events());
        $this->readEventsFromAStreamInReverseOrder($stream1, $allRecordedEvents[1]->eventId)->shouldHaveCount(2);
        $this->readEventsFromAStreamInReverseOrder($stream1, $allRecordedEvents[4]->eventId)->shouldHaveCount(5);
        $this->readEventsFromAStreamInReverseOrder($stream2, $allRecordedEvents[6]->eventId)->shouldHaveCount(1);
        $this->readEventsFromAStreamInReverseOrder($stream2, 'unknown id')->shouldHaveCount(0);
    }

    function it_reads_events_from_multiple_streams()
    {
        $events = $this->getCollectionOfEvents();
        $stream1 = StreamName::using('Test1', 'streamId1');
        $stream2 = StreamName::using('Test2', 'streamId2');
        $stream3 = StreamName::using('Test3', 'streamId3');
        $this->writeSomeEvents($events->append($events)->append($events), $stream1); // adding 2 event 3 times = 6
        $this->writeSomeEvents($events, $stream2); // 2
        $this->writeSomeEvents($events->append($events), $stream3); // adding 2 event twice = 4

        $allRecordedEvents = iterator_to_array($this->readAllEventsAsRecorded()->getWrappedObject()->events());
        $this->readEventsFromMultipleStreams([$stream1->streamCategory(), $stream3->streamCategory()], StreamPosition::START)->shouldHaveCount(10);
        $this->readEventsFromMultipleStreams(
            [$stream1->streamCategory(), $stream3->streamCategory()],
            $allRecordedEvents[2]->eventId
        )->shouldHaveCount(8);
        $this->readEventsFromMultipleStreams([],StreamPosition::START)->shouldHaveCount(12);
    }

    function it_reads_events_from_multiple_streams_in_reverse_order()
    {
        $events = $this->getCollectionOfEvents();
        $stream1 = StreamName::using('Test1', 'streamId1');
        $stream2 = StreamName::using('Test2', 'streamId2');
        $stream3 = StreamName::using('Test3', 'streamId3');
        $this->writeSomeEvents($events->append($events)->append($events), $stream1); // adding 2 event 3 times = 6
        $this->writeSomeEvents($events, $stream2); // 2
        $this->writeSomeEvents($events->append($events), $stream3); // adding 2 event twice = 4
        $this->writeSomeEvents($events, $stream1); // 2 more to stream 1

        $allRecordedEvents = iterator_to_array($this->readAllEventsAsRecorded()->getWrappedObject()->events());
        $this->readEventsFromMultipleStreamsInReverseOrder([$stream1->streamCategory(), $stream3->streamCategory()], StreamPosition::START)->shouldHaveCount(12);
        $this->readEventsFromMultipleStreamsInReverseOrder(
            [$stream1->streamCategory(), $stream3->streamCategory()],
            $allRecordedEvents[9]->eventId // missing 2 off
        )->shouldHaveCount(8); // 2 from test 3, 6 from test 1
        $this->readEventsFromMultipleStreamsInReverseOrder(
            [$stream1->streamCategory()],
            $allRecordedEvents[12]->eventId // missing 1 off, which was from stream 1
        )->shouldHaveCount(7); // 6 from test 1 first time around, and 1 from second go
        $this->readEventsFromMultipleStreamsInReverseOrder([],StreamPosition::START)->shouldHaveCount(14);
    }

    private function setupUnitOfWork (TracksEventsAcrossStreams $unitOfWork, ReadsAndWritesAStream $stream1, ReadsAndWritesAStream $stream2)
    {
        $unitOfWork->tracked()->willReturn([
            new TrackedObject(
                StreamName::using('StreamCategory', 'streamId'),
                new AnObjectThatTracksChanges,
                ExpectedStreamRevision::ANY
            )
        ]);
        $stream1->eventsInQueue()->willReturn($this->getCollectionOfEvents()); // 2 events
        $stream1->streamName()->willReturn(StreamName::using('StreamCategory', 'streamId1'));
        $stream1->expectedStreamRevisionForEnqueuedEvents()->willReturn(ExpectedStreamRevision::NONE);
        $stream2->eventsInQueue()->willReturn($this->getCollectionOfEvents()->append($this->getCollectionOfEvents())); // 4 events in here
        $stream2->streamName()->willReturn(StreamName::using('StreamCategory', 'streamId2'));
        $stream2->expectedStreamRevisionForEnqueuedEvents()->willReturn(ExpectedStreamRevision::NONE);
        $unitOfWork->streamsWithChanges()->willReturn([$stream1, $stream2]);
    }

    /**
     * @return CollectionOfEventsInMemory
     */
    private function getCollectionOfEvents()
    {
        $collection = new CollectionOfEventsInMemory([
            new StandaloneEvent('RegistrationEmailSent', ['to'=>'billy@example.com']),
            new StandaloneEvent('PasswordResetEmailSent', ['to'=>'billy@example.com']),
        ]);
        return $collection;
    }

    /**
     * @param CollectionOfEvents $events
     * @return StreamName
     */
    private function writeSomeEvents(CollectionOfEvents $events, StreamName $streamName = null)
    {
        if (is_null($streamName)) {
            $streamName = StreamName::using('Test', 'streamId1');
        }
        $this->writeEventsToAStream($streamName, $events, ExpectedStreamRevision::ANY);
        return $streamName;
    }
}

class EventListener
{
    private $events;

    public function __construct()
    {
        $this->events = new CollectionOfEventsInMemory();
    }

    public function fire (CollectionOfEvents $collectionOfEvents)
    {
        $this->events = $this->events->append($collectionOfEvents);
    }

    /**
     * @return mixed
     */
    public function eventsAsArray()
    {
        //return iterator_to_array($this->events->events());
    }
}

class AnObjectThatTracksChanges implements TracksChanges
{
    public function hasChanges()
    {
        return true;
    }
    public function changes()
    {
        return new CollectionOfEventsInMemory([
            new StandaloneEvent('ObjectAdded'),
            new StandaloneEvent('ObjectUpdated'),
            new StandaloneEvent('ObjectUpdated'),
            new StandaloneEvent('ObjectMadeUnavailable')
        ]);
    }
    public function clearChanges()
    {
    }
}
