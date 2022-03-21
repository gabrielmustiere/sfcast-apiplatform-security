<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class IsValidOwner extends Constraint
{
    public string $message = 'Cannot set owner to a different user';
    public string $anonymousMessage = 'Cannot set owner unless you are authenticated';
}
