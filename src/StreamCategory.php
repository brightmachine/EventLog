<?php namespace EventLog;

final class StreamCategory
{
    /** @var string */
    private $category;

    public function __construct($category)
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function category()
    {
        return $this->category;
    }

    public function __toString()
    {
        return $this->category();
    }

    public static function using ($category)
    {
        return new StreamCategory($category);
    }
}
