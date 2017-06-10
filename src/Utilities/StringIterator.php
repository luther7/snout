<?php
namespace Snout\Utilities;

class StringIterator implements \Iterator
{
    /**
     * @var string Subject.
     */
    private $subject;

    /**
     * @var int Position.
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
    public function current()
    {
        return $this->subject[$this->position];
    }

    /**
     * @return int Current position.
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @return void
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @return bool If valid.
     */
    public function valid()
    {
        return isset($this->subject[$this->position]);
    }
}