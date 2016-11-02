<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace morozco\csv\Exceptions;

/**
 * Description of UnreadableHandleException
 *
 * @author morozco
 */
class UnreadableHandleException extends \Exception {
    protected $message = 'File handle is not readable' . PHP_EOL;
}
