<?php

namespace App\Exceptions\Business;

use Exception;

class EmptyCartException extends Exception
{
    public function __construct(string $message = 'Cart is empty')
    {
        parent::__construct($message);
    }
}
