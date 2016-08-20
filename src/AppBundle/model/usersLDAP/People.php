<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 16.08.16
 * Time: 23:07
 */

namespace AppBundle\model\usersLDAP;


class People
{
    function __construct($ldapFrontend)
    {
        $this->ldapFrontend = $ldapFrontend;
    }

    public function getAllUsers($group)
    {
        return $this->ldapFrontend->getAllUsers($group);
    }

    public function addUser($user)
    {

    }

    public function delUser($user)
    {

    }
}