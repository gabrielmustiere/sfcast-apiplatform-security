<?php

namespace App\Validator;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidOwnerValidator extends ConstraintValidator
{
    public function __construct(private Security $security)
    {
    }

    public function validate($value, Constraint $constraint)
    {
        /* @var IsValidOwner $constraint */
        if (null === $value || '' === $value) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            $this->context->buildViolation($constraint->anonymousMessage)->addViolation();

            return;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        if (!$value instanceof User) {
            throw new \InvalidArgumentException('@IsValidOwner constraint must be put on property conatining a User object');
        }

        if ($value->getId()->toRfc4122() !== $user->getId()->toRfc4122()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
