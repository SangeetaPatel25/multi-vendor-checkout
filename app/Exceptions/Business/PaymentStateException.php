<?php

namespace App\Exceptions\Business;

use Exception;

class PaymentStateException extends Exception
{
    public function __construct(string $message = 'Invalid payment state')
    {
        parent::__construct($message);
    }
}
