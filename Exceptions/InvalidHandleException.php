<?php

namespace morozco\csv\Exceptions;

/**
 * Description of InvalidHandleException
 *
 * @author morozco
 */
class InvalidHandleException extends \Exception {
    protected $message = 'Invalid file handle' . PHP_EOL;
}
