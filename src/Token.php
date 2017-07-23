<?php
namespace Snout;

/**
 * Token.
 */
class Token
{
    /**
     * @const string Ending token.
     */
    public const END = 'END';

    /**
     * @const string Digit token.
     */
    public const DIGIT = 'DIGIT';

    /**
     * @const string Alpha token.
     */
    public const ALPHA = 'ALPHA';

    /**
     * @const string Forward slash token.
     */
    public const FORWARD_SLASH = 'FORWARD_SLASH';

    /**
     * @const string Underscore token.
     */
    public const UNDERSCORE = 'UNDERSCORE';

    /**
     * @const string Hyphen token.
     */
    public const HYPHEN = 'HYPHEN';

    /**
     * @const string Period token.
     */
    public const PERIOD = 'PERIOD';

    /**
     * @const string Colon token.
     */
    public const COLON = 'COLON';

    /**
     * @const string OPEN_BRACE token.
     */
    public const OPEN_BRACE = 'OPEN_BRACE';

    /**
     * @const string CLOSE_BRACE token.
     */
    public const CLOSE_BRACE = 'CLOSE_BRACE';

    /**
     * @const string Back slash token.
     */
    public const BACK_SLASH = 'BACK_SLASH';

    /**
     * @const string Space token.
     */
    public const SPACE = 'SPACE';

    /**
     * @const string Tab token.
     */
    public const TAB = 'TAB';

    /**
     * @const string New line token.
     */
    public const NEW_LINE = 'NEW_LINE';

    /**
     * @const string Carriage return token.
     */
    public const CARRIAGE_RETURN = 'CARRIAGE_RETURN';

    /**
     * @param  string $token
     * @return string
     */
    public static function tokenize(string $token) : string
    {
        return constant("self::{$token}");
    }
}
