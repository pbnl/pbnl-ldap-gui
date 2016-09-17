<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 20.08.16
 * Time: 14:22
 */

namespace AppBundle\model\usersLDAP;


use AppBundle\model\ldapCon\LDAPService;

class Group
{
    private $members = Array(); // An array with all dn of the users
    public $name = "";
    public $dn = "";
    public $gidNumber = "";
    public $type = "";

    public function __construct(LDAPService $LDAPService)
    {
        $this->LDAPService = $LDAPService;
    }

    public function addMember($dn)
    {
        array_push($this->members,$dn);
    }

    public function getMembersDN()
    {
        return $this->members;
    }
    public function getMemberCount()
    {
        return count($this->members);
    }

    public function getListWithDNAndName()
    {
        $return = Array();

        foreach ($this->members as $member)
        {
            $return[$this->LDAPService->getUserByDN($member)->givenName] = $member;
        }
        return $return;
    }
    public function getMembersOfGroupB($group)
    {
        $newGroup = new Group($this->LDAPService);
        foreach ($this->members as $userDN)
        {
            if(in_array($userDN,$group->getMembersDN()))
            {
                $newGroup->addMember($userDN);
            }
        }
        return$newGroup;
    }

    public function isDNMember($dn)
    {
        if(in_array($dn,$this->members)) return true;
        else return false;
    }
}