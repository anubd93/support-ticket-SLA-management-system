<?php

namespace App\Exceptions\V1;

use Exception;

/**
 * A ticket business rule was broken (e.g. an invalid status change).
 * The message is safe to show to the user.
 */
class TicketException extends Exception
{
}
