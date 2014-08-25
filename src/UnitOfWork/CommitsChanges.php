<?php namespace EventLog\UnitOfWork;


interface CommitsChanges
{
    /**
     * @param bool $commitPerStream whether to break the commit apart across each stream
     * @return mixed
     */
    public function commit($commitPerStream = false);
}
