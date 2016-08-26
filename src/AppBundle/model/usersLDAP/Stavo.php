<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 26.08.16
 * Time: 15:24
 */

namespace AppBundle\model\usersLDAP;

use AppBundle\model\ldapCon\LDAPService;

class Stavo
{
    private $members = Array();
    private $ldap;

    public function __construct(LDAPService $ldap, $name)
    {
        $this->ldap = $ldap;
        $stamm = $ldap->getAllGroups($name)[0];
        $stavos = $ldap->getAllGroups("stavo")[0];
        $stavo = $stamm->getMembersOfGroupB($stavos);

        $this->members = $stavo->getMembersDN();
    }

    public function getMembersDN()
    {
        return $this->members;
    }

    public function getMembersUser()
    {
        $membersUser = Array();
        foreach ($this->members as $memberDN)
        {
            array_push($membersUser,$this->ldap->getUserByDN($memberDN));
        }
        return $membersUser;
    }


}