<?php

namespace App\Exceptions;

use RuntimeException;

class CommerceFulfillmentException extends RuntimeException
{
    // The provider approved the charge, but the purchased benefit still needs fulfillment.
}
