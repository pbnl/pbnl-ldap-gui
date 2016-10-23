<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 30.09.16
 * Time: 18:48
 */

namespace AppBundle\model\formDataClasses;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\model\validators\constraints as PBNLAssert;

class UserSearchFormDataHolder
{
    /**
     * @PBNLAssert\IsCorrectPBNLName
     */
    public $userFilter = "";
    public $groupFilter = "";
}