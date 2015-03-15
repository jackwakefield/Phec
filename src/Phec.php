<?php

namespace JackWakefield\Phec;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;

class Phec implements LoggerAwareInterface {
    private $logger;

    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }
}
