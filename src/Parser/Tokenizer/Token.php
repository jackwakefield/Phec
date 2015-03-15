<?php

namespace JackWakefield\Phec\Parser\Tokenizer;

abstract class Token {
    const EOF = 0;
    const KEYWORD = 1;
    const PUNCTUATOR = 2;
    const DIV_PUNCTUATOR = 3;
    const IDENTIFIER_NAME = 4;
}
