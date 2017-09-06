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
     * @param string $name
     * @param string $type
     * @param mixed  $value
     */
    public function __construct(string $name, string $type, $value)
    {
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
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
     * @param  Parameters
     * @return bool
     */
    public function compare(Parameter $other) : bool
    {
        return $this->name === $other->getName()
               && $this->type === $other->getType()
               //FIXME with parameter casting.
               && $this->value == $other->getValue();
    }
}
