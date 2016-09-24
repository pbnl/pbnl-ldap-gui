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
    private $ldapFrontend;
    private $org;

    public function __construct(LDAPService $LDAPService, Organisation $org)
    {
        $this->ldapFrontend = $LDAPService;
        $this->org = $org;
    }
    /**
     * Adds a new user to the LDAP and adds him to the pbnl and wiki groups
     * @param User $user
     * @return array|bool
     */
    public function createNewUser($user)
    {
        if(!$this->ldapFrontend->getUserByName($user->givenName))
        {
            $stamm = $user->getStamm($this->ldapFrontend);
            $user = $this->ldapFrontend->addAUser($user);
            $this->org->getGroupManager()->getAllGroups("nordlichter")[0]->addMember($user->dn);
            $this->org->getGroupManager()->getAllGroups("wiki")[0]->addMember($user->dn);
            $this->org->getGroupManager()->getAllGroups($stamm)[0]->addMember($user->dn);
            return $user;
        }
        else return FALSE;

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