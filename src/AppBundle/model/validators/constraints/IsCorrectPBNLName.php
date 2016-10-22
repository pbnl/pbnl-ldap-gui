<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 22.10.16
 * Time: 14:45
 */

namespace AppBundle\model\validators\constraints;
use Symfony\Component\Validator\Constraint;


/**
 * @Annotation
 */
class IsCorrectPBNLName extends Constraint
{
    public $message = 'The string "%string%" contains an illegal character: it can only contain letters or numbers.';

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}
