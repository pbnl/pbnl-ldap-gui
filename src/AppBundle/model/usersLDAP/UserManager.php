<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 19.09.16
 * Time: 21:02
 */

namespace AppBundle\model\usersLDAP;


use AppBundle\model\ldapCon\LDAPService;

class UserManager
{
    public $ldapFrontend;

    public function __construct(LDAPService $LDAPService)
    {
        $this->ldapFrontend = $LDAPService;
    }

    public function createNewUser($name)
    {

    }

    public function getUserByUid($uid)
    {
        return $this->ldapFrontend->getUserByUidNumber($uid);
    }
    public function getUserByName($name)
    {
        return $this->ldapFrontend->getUserByName($name);
    }

    /**
     * @param $group if "" then all
     * @param $user if "" then all
     * @return array
     */
    public function getAllUsers($group, $user)
    {
        return $this->ldapFrontend->getAllUsers($group,$user);
    }

    public function getEmptyUser()
    {
        return new User($this->ldapFrontend);
    }
}