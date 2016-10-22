<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 19.09.16
 * Time: 21:23
 */

namespace AppBundle\model\usersLDAP;


use AppBundle\model\ldapCon\AllreadyInGroupException;
use AppBundle\model\ldapCon\LDAPService;

class ParentGroup
{
    /**
     * @var LDAPService
     */
    public $LDAPService;

    protected $members = Array(); // An array with all dn of the users
    protected $membersUserData = Array(); // An array with all dn of the users
    public $name = "";
    public $dn = "";
    public $gidNumber = "";
    public $type = "";
    private $fetchedData = false;
    /**
     * @var GroupManager
     */
    private $groupManager = null;

    public function __construct(LDAPService $LDAPService)
    {
        $this->LDAPService = $LDAPService;
    }

    public function getMembersDN()
    {
        return $this->members;
    }

    public function getMembersUser()
    {
        if(!$this->fetchedData) $this->fetchUserData();
        return $this->membersUserData;
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
        try
        {
            $this->LDAPService->addUserDNToGroup($dn, $this->name);
        }
        catch (AllreadyInGroupException $e)
        {
            $this->getGroupManager()->getOrg()->session->getFlashBag()->add("notice","Der benutzer $dn war schon in der Gruppe $this->name");
        }
        $mail = $this->LDAPService->getUserByDN($dn)->mail;
        $name = str_replace("@","",$this->name);
        try
        {
            if (filter_var($mail, FILTER_VALIDATE_EMAIL)) $this->LDAPService->addMailToForward($mail, "$name@pbnl.de");
        }
        catch (AllreadyInGroupException $e)
        {
            $this->getGroupManager()->getOrg()->session->getFlashBag()->add("notice","Der benutzer $dn war schon in der Gruppe $name@pbnl.de");
        }
    }

    public function addMemberToClassArray($dn)
    {
        array_push($this->members,$dn);
        $this->fetchedData = false;
    }
    public function delTeam($name)
    {

    }

    public function fetchUserData()
    {
        $this->membersUserData = Array();
        foreach ($this->members as $member)
        {
            $oneUser = $this->LDAPService->getUserByDN($member);
            array_push($this->membersUserData,$oneUser);
        }
        $this->fetchedData = true;
    }

    public function removeMember($dn)
    {
        $this->LDAPService->removeUserDNFromGroup($dn,$this->name);
        $mail = $this->LDAPService->getUserByDN($dn)->mail;
        $name = str_replace("@","",$this->name);
        if(filter_var($mail, FILTER_VALIDATE_EMAIL)) $this->LDAPService->removeMailFromForward($mail,"$name@pbnl.de");
    }

    /**
     * @return GroupManager
     */
    public function getGroupManager()
    {
        return $this->groupManager;
    }

    /**
     * @param GroupManager $groupManager
     */
    public function setGroupManager($groupManager)
    {
        $this->groupManager = $groupManager;
    }
}