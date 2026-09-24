<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a request violates an institutional business rule (unit cap,
 * missing prerequisite, scheduling conflict, locked grade, etc.) rather than
 * a technical failure. Controllers catch this and surface the message as a
 * validation error.
 */
class BusinessRuleException extends RuntimeException
{
}
