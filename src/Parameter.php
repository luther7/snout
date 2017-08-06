<?php
namespace Snout;

/**
 * Parameter.
 */
class Parameter
{
    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var mixed $value
     */
    private $value;

    /**
     * @var bool $matching
     */
    private $matching;

    /**
     * @param string $name
     */
    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
        $this->value = '';
        $this->matching = false;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isMatching() : bool
    {
        return $this->matching;
    }

    /**
     * @return void
     */
    public function matched() : void
    {
        $this->matched = true;
    }

    /**
     * @param mixed $value
     * @return void
     */
    public function addValue($value) : void
    {
        if ($this->matched()) {
            // TODO exception.
        }

        $this->value .= $value;
    }
}
