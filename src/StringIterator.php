<?php
namespace Snout;

/**
 * StringIterator.
 */
class StringIterator implements \Iterator
{
    /**
     * @var string $subject Subject.
     */
    private $subject;

    /**
     * @var int $position Position.
     */
    private $position;

    /**
     * @param string $subject Subject.
     */
    public function __construct(string $subject)
    {
        $this->subject = $subject;
        $this->position = 0;
    }

    /**
     * @return string Current char.
     */
    public function current() : string
    {
        return $this->subject[$this->position];
    }

    /**
     * @return int Current position.
     */
    public function key() : int
    {
        return $this->position;
    }

    /**
     * @return void
     */
    public function next() : void
    {
        ++$this->position;
    }

    /**
     * @return void
     */
    public function rewind() : void
    {
        $this->position = 0;
    }

    /**
     * @return bool If valid.
     */
    public function valid() : bool
    {
        return isset($this->subject[$this->position]);
    }
}
