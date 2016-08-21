<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 16.08.16
 * Time: 23:07
 */

namespace AppBundle\model\usersLDAP;


use AppBundle\model\ldapCon\LDAPService;

class People
{
    function __construct(LDAPService $ldapFrontend)
    {
        $this->ldapFrontend = $ldapFrontend;
    }

    public function getAllUsers($group)
    {
        return $this->ldapFrontend->getAllUsers($group);
    }

    public function addUser(User $user)
    {
        if(!$this->ldapFrontend->getUserByName($user->givenName)) return $this->ldapFrontend->addAUser($user);
        else return FALSE;

    }

    public function getOUGroupsNames()
    {
        return $this->ldapFrontend->getOUGroupsNames();
    }

    public function delUser(User $user)
    {

    }
}