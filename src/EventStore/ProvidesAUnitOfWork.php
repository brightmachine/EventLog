<?php namespace EventLog\EventStore;

use EventLog\UnitOfWork;

interface ProvidesAUnitOfWork
{
    /**
     * EventLog allows only one Unit of Work at any point of time. Calling this multiple times will simply return the
     * same Unit of Work.
     * @return UnitOfWork
     */
    public function unitOfWork();
}
