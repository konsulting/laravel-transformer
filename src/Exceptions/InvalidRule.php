<?php

namespace Konsulting\Transformer\Exceptions;

use Exception;

class InvalidRule extends Exception
{
    public function __construct($rule, $code = 0, \Exception $previous = null)
    {
        parent::__construct("Rule {$rule} not found", $code, $previous);
    }
}
