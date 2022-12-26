<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UNAPMailValidator extends ConstraintValidator
{
    public function validate($email, Constraint $constraint)
    {
        /* @var App\Validator\UNAPMail $constraint */

        if (null === $email || '' === $email) {
            return;
        }

        $expEmail = explode("@", $email);
        $domainEmail = array_pop($expEmail);

        if ($domainEmail !== $constraint->domain){
            $this->context->buildViolation($constraint->message)
            ->setParameter('{{email}}', $email)
            ->addViolation();
        }
        // TODO: implement the validation here
    }
}
