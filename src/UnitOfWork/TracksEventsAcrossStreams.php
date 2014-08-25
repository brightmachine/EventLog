<?php namespace EventLog\UnitOfWork;

interface TracksEventsAcrossStreams extends TracksObjectsThatTrackEvents, EnqueuesEventsOnAStream, CommitsChanges
{
}
