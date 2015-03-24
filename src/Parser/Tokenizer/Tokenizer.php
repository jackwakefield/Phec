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

    private static $numericLiteralRegex = '/^[0-9]+$/';

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
            if (!$this->nextCharacter()) {
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

    public function getLiteral() {
        return $this->literal;
    }

    private function isLiteralEmpty() {
        return strlen($this->literal) == 0;
    }

    public function getNumericLiteral() {
        return intval($this->literal);
    }

    private function nextCharacter() {
        if ($this->isEndOfFile()) {
            return false;
        }

        $this->character = $this->source[$this->position++];
        return true;
    }

    private function peek() {
        if ($this->isEndOfFile()) {
            return null;
        }

        return $this->source[$this->position + 1];
    }

    private function isWhitespace() {
        return in_array($this->character, Tokenizer::$whitespaceCharacters);
    }

    private function isNextCharacterWhitespace() {
        return in_array($this->peek(), Tokenizer::$whitespaceCharacters);
    }

    private function isSemiColon() {
        return $this->character == ';';
    }

    private function isEndOfFile() {
        return $this->position == strlen($this->source);
    }

    private function isKeyword() {
        return in_array($this->literal, Tokenizer::$keywords);
    }

    private function isPunctuator() {
        return in_array($this->literal, Tokenizer::$punctuators);
    }

    private function isCharacterPunctuator() {
        return in_array($this->character, Tokenizer::$punctuators);
    }

    private function isDivisionPunctuator() {
        return in_array($this->literal, Tokenizer::$divisionPunctuators);
    }

    private function isNumeric() {
        return preg_match(Tokenizer::$numericLiteralRegex, $this->literal);
    }
}
