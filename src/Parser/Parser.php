<?php

namespace JackWakefield\Phec\Parser;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;

use JackWakefield\Phec\Parser\Tokenizer\Token;
use JackWakefield\Phec\Parser\Tokenizer\Tokenizer;

class Parser implements LoggerAwareInterface {
    private $logger;
    private $tokenizer;

    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    public function parse($source) {
        $this->tokenizer = new Tokenizer($source);

        do {
            $token = $this->tokenizer->getNextToken();

            switch ($token) {
                case Token::EOF:
                    if (!is_null($this->logger)) {
                        $this->logger->debug('EOF');
                    }
                    break;
                case Token::KEYWORD:
                    if (!is_null($this->logger)) {
                        $this->logger->debug('KEYWORD: '.$this->tokenizer->getLiteral());
                    }
                    break;
                case Token::PUNCTUATOR:
                    if (!is_null($this->logger)) {
                        $this->logger->debug('PUNCTUATOR: '.$this->tokenizer->getLiteral());
                    }
                    break;
                case Token::DIV_PUNCTUATOR:
                    if (!is_null($this->logger)) {
                        $this->logger->debug('DIV_PUNCTUATOR: '.$this->tokenizer->getLiteral());
                    }
                    break;
                case Token::IDENTIFIER_NAME:
                    if (!is_null($this->logger)) {
                        $this->logger->debug('IDENTIFIER_NAME: '.$this->tokenizer->getLiteral());
                    }
                    break;
            }
        } while ($token != Token::EOF);
    }
}
