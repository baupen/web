<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class CloseToNowWhenPreviouslyNull extends Constraint
{
    public string $message = 'property {{ property}} must be close to the current date and time when set (tolerance: {{ tolerance }}s).';

    /**
     * Maximum allowed difference from now, in seconds.
     */
    public int $tolerance = 60;
}
