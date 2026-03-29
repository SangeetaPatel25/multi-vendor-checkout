<?php

namespace App\Exceptions\Business;

use Exception;

class OrderNotFoundForCustomerException extends Exception
{
    public function __construct(string $message = 'Order not found for this customer')
    {
        parent::__construct($message);
    }
}
