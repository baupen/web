<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class MatchesIssueCraftsman extends Constraint
{
    public string $message = 'property {{property}} must be set to the same craftsman that the issue is attributed to.';
}
