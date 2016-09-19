<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 19.09.16
 * Time: 21:23
 */

namespace AppBundle\model\usersLDAP;


use AppBundle\model\ldapCon\LDAPService;

class ParentGroup
{
    public $LDAPService;
    protected $members = Array(); // An array with all dn of the users
    public $name = "";
    public $dn = "";
    public $gidNumber = "";
    public $type = "";

    public function __construct(LDAPService $LDAPService)
    {
        $this->LDAPService = $LDAPService;
    }

    public function getMembersDN()
    {
        return $this->members;
    }

    public function getMemberCount()
    {
        return count($this->members);
    }
    public function isDNMember($dn)
    {
        if(in_array($dn,$this->members)) return true;
        else return false;
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

    public function addMember($dn)
    {

    }

    public function addMemberToClassArray($dn)
    {
        array_push($this->members,$dn);
    }
    public function delTeam($name)
    {

    }
}