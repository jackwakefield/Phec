<?php

namespace JackWakefield\Phec\Parser\Tokenizer;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;

class Tokenizer implements LoggerAwareInterface {
    private static $keywords = array('break', 'do', 'instanceof', 'typeof',
        'case', 'else', 'new', 'var', 'catch', 'finally', 'return', 'void',
        'continue', 'for', 'switch', 'while', 'debugger', 'function', 'this',
        'with', 'default', 'if', 'throw', 'delete', 'in', 'try');

    private static $futureReservedWords = array('class', 'enum', 'extends',
        'super', 'const', 'export', 'import');

    private static $futureReservedStrictWords = array('implements', 'let',
        'private', 'public', 'interface', 'package', 'protected', 'static',
        'yield');

    private static $whitespaceCharacters = array('\t', '\v', '\f', '\s', ' ',
        '\xEF');

    private static $punctuators = array('{', '}', '(', ')', '[', ']', '.', ';',
        ',', '<', '>', '<=', '>=', '==', '!=', '===', '!==', '+', '-', '*', '%',
        '++', '--', '<<', '>>', '>>>', '&', '|', '^', '!', '~', '&&', '||', '?',
        ':', '=', '+=', '-=', '*=', '%=', '<<=', '>>=', '>>>=', '&=', '|=',
        '^=');

    private static $divisionPunctuators = array('/', '/=');

    private static $numericLiteralRegex = '/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$/';

    private $source;
    private $position;
    private $character;
    private $literal;
    private $logger;

    public function __construct($source) {
        $this->source = $source;
        $this->position = 0;
    }

    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    public function getNextToken() {
        $this->literal = '';
        $break = false;

        while (true) {
            if (!$this->setNextCharacter()) {
                return Token::EOF;
            }

            if ($this->isSemiColon()) {
                $break = true;
            }

            if ($this->isCharacterPunctuator() && !$this->isLiteralEmpty()) {
                $break = true;
                $this->position--;
            }

            if ($this->isWhitespace() || $break) {
                if (strlen($this->literal) > 0) {
                    if ($this->isNumeric()) {
                        return Token::NUMERIC_LITERAL;
                    }

                    return Token::IDENTIFIER_NAME;
                }

                continue;
            }

            $this->literal .= $this->character;

            if ($this->isKeyword() && $this->isNextCharacterWhitespace()) {
                return Token::KEYWORD;
            }

            if ($this->isPunctuator()) {
                return Token::PUNCTUATOR;
            }

            if ($this->isDivisionPunctuator()) {
                return Token::DIV_PUNCTUATOR;
            }
        }

        return null;
    }

    /**
     * Retrieves the literal value.
     * @return string The literal value.
     */
    public function getLiteral() {
        return $this->literal;
    }

    /**
     * Determines whether the literal value is empty.
     * @return boolean Whether the literal value is empty.
     */
    private function isLiteralEmpty() {
        return strlen($this->literal) == 0;
    }

    /**
     * Retrieves the literal value as a double.
     * @return double The literal value as a double.
     */
    public function getNumericLiteral() {
        return doubleval($this->literal);
    }

    /**
     * Sets the character from the next position in the source and increments
     *   the source position.
     * @return boolean Whether the next character exceeds the length of the
     *   source.
     */
    private function setNextCharacter() {
        if ($this->isEndOfFile()) {
            return false;
        }

        $this->character = $this->source[$this->position++];
        return true;
    }

    /**
     * Retrieves the next available character in the source without incrementing
     *   the source position.
     * @return string The next available character in the source, or null if the
     *   next position exceeds the length of the source.
     */
    private function peek() {
        if ($this->isEndOfFile()) {
            return null;
        }

        return $this->source[$this->position + 1];
    }

    /**
     * Determines whether the current character is deemed to be whitespace.
     * @return boolean Whether the current character is a whitespace character.
     */
    private function isWhitespace() {
        return in_array($this->character, Tokenizer::$whitespaceCharacters);
    }

    /**
     * Determines whether the next character is deemed to be whitespace.
     * @return boolean Whethe the next character is a whitespace character.
     */
    private function isNextCharacterWhitespace() {
        return in_array($this->peek(), Tokenizer::$whitespaceCharacters);
    }

    /**
     * Determines whether the current character is a semi-colon.
     * @return boolean Whether the current character is a semi-colon.
     */
    private function isSemiColon() {
        return $this->character == ';';
    }

    /**
     * Determines whether the current source position exceeds the length of the
     *   source.
     * @return boolean Whether the current source position exceeds the length
     *   of the source.
     */
    private function isEndOfFile() {
        return $this->position == strlen($this->source);
    }

    /**
     * Determines whether the current literal is a keyword.
     * @return boolean Whether the current literal is a keyword.
     */
    private function isKeyword() {
        return in_array($this->literal, Tokenizer::$keywords);
    }

    /**
     * Determines whether the current literal is a punctuator.
     * @return boolean Whether the current literal is a punctuator.
     */
    private function isPunctuator() {
        return in_array($this->literal, Tokenizer::$punctuators);
    }

    /**
     * Determines whether the current character is a punctuator.
     * @return boolean Whether the current character is a punctuator.
     */
    private function isCharacterPunctuator() {
        return in_array($this->character, Tokenizer::$punctuators);
    }

    /**
     * Determines whether the current literal is a division punctuator.
     * @return boolean Whether the current literal is a division punctuator.
     */
    private function isDivisionPunctuator() {
        return in_array($this->literal, Tokenizer::$divisionPunctuators);
    }

    /**
     * Determines whether the current literal is a numeric value.
     * @return boolean Whether the current literal is a numeric value.
     */
    private function isNumeric() {
        return preg_match(Tokenizer::$numericLiteralRegex, $this->literal);
    }
}
