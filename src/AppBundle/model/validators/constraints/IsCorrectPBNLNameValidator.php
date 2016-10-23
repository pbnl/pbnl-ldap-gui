<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 22.10.16
 * Time: 14:47
 */

namespace AppBundle\model\validators\constraints;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsCorrectPBNLNameValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!preg_match('/^[a-zA-Z0-9]+$/', $value, $matches) && $value != "") {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', $value)
                ->addViolation();
        }
    }
}