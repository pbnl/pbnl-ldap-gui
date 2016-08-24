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
    public $userFilter = "";
    public $groupFilter = "";

    function __construct(LDAPService $ldapFrontend)
    {
        $this->ldapFrontend = $ldapFrontend;
    }

    public function getAllUsers($group,$user)
    {
        return $this->ldapFrontend->getAllUsers($group,$user);
    }

    /**
     * Adds a new user to the LDAP and adds him to the pbnl and wiki groups
     * @param User $user
     * @return array|bool
     */
    public function addUser(User $user)
    {
        if(!$this->ldapFrontend->getUserByName($user->givenName))
        {
            $user = $this->ldapFrontend->addAUser($user);
            $this->ldapFrontend->addUserDNToGroup($user->dn,"nordlichter");
            $this->ldapFrontend->addUserDNToGroup($user->dn,"wiki");
            return $user;
        }
        else return FALSE;

    }

    public function getOUGroupsNames()
    {
        return $this->ldapFrontend->getOUGroupsNames();
    }

    public function delUser(User $user)
    {

    }

    public function getGroups()
    {
        return $this->ldapFrontend->getAllGroups();
    }
}