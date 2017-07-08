<?php
namespace Snout;

use \Snout\Exceptions\ParserException;
use \Snout\Token;
use \Snout\Config;
use \Snout\Lexer;

/**
 * Parser.
 */
class Parser
{
    /**
     * @var Lexer $lexer
     */
    private $lexer;

    /**
     * @var array $invalid Invalid tokens.
     */
    private $invalid;

    /**
     * @param Config $config
     * @param Lexer  $lexer
     */
    public function __construct(Config $config, Lexer $lexer)
    {
        $this->configure($config);
        $this->lexer = $lexer;
    }

    /**
     * @param Config $config
     *
     * @return void
     **/
    public function configure(config $config)
    {
        $parser_config = $config->get('parser', true);

        $this->invalid = array_map(
            function ($t) {
                return constant("\Snout\Token::{$t}");
            },
            $parser_config['invalid']
        );
    }

    /**
     * Accept token and scan.
     *
     * @param string $valid Valid next token.
     *
     * @return void
     */
    public function accept(string $valid)
    {
        $token = $this->lexer->getToken();
        $current_char = $this->lexer->getCharCount();

        if (in_array($token, $this->invalid)) {
            throw new ParserException(
                "Invalid token '{$token}'. At char {$current_char}."
            );
        }

        if ($token !== $valid) {
            throw new ParserException(
                "Unexpected token '{$token}'. Expecting token '{$valid}'. "
                . "At char {$current_char}."
            );
        }

        $this->lexer->next();
    }
}