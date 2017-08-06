<?php
namespace Snout;

use Snout\Exceptions\LexerException;

/**
 * Token.
 */
class Token
{
    /**
     * @const string END Ending type.
     */
    public const END = 'END';

    /**
     * @const string DIGIT Digit type.
     */
    public const DIGIT = 'DIGIT';

    /**
     * @const string ALPHA Alpha type.
     */
    public const ALPHA = 'ALPHA';

    /**
     * @const string FORWARD_SLASH Forward slash type.
     */
    public const FORWARD_SLASH = 'FORWARD_SLASH';

    /**
     * @const string UNDERSCORE Underscore type.
     */
    public const UNDERSCORE = 'UNDERSCORE';

    /**
     * @const string HYPHEN Hyphen type.
     */
    public const HYPHEN = 'HYPHEN';

    /**
     * @const string PERIOD Period type.
     */
    public const PERIOD = 'PERIOD';

    /**
     * @const string COLON Colon type.
     */
    public const COLON = 'COLON';

    /**
     * @const string OPEN_BRACE type.
     */
    public const OPEN_BRACE = 'OPEN_BRACE';

    /**
     * @const string CLOSE_BRACE type.
     */
    public const CLOSE_BRACE = 'CLOSE_BRACE';

    /**
     * @const string BACK_SLASH Back slash type.
     */
    public const BACK_SLASH = 'BACK_SLASH';

    /**
     * @const string SPACE Space type.
     */
    public const SPACE = 'SPACE';

    /**
     * @const string TAB Tab type.
     */
    public const TAB = 'TAB';

    /**
     * @const string NEW_LINE New line type.
     */
    public const NEW_LINE = 'NEW_LINE';

    /**
     * @const string CARRIAGE_RETURN Carriage return type.
     */
    public const CARRIAGE_RETURN = 'CARRIAGE_RETURN';

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var string $lexeme
     */
    private $lexeme;

    /**
     * @var mixed $value
     */
    private $value;

    /**
     * @param  string $type
     * @return string
     */
    public static function typeConstant(string $type) : string
    {
        return constant("self::{$type}");
    }

    /**
     * @param string $type
     * @param string ?$lexeme
     * @param mixed  ?$value
     */
    public function __construct(
        string $type,
        ?string $lexeme = null,
        $value = null
    ) {
        $this->lexeme = $lexeme;
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     * @throws LexerException If there is no value.
     */
    public function getValue()
    {
        if ($this->value === null) {
            throw new LexerException('Token has no value.');
        }

        return $this->value;
    }

    /**
     * @return bool
     */
    public function hasValue()
    {
        return $this->value !== null;
    }
}
