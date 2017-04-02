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
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\model\validators\constraints as PBNLAssert;

class ParentGroup
{
    /**
     * @var LDAPService
     */
    public $LDAPService;

    protected $members = Array(); // An array with all dn of the users
    protected $membersUserData = Array();
    /**
     * @Assert\NotBlank
     * @PBNLAssert\IsCorrectPBNLName
     */
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
            try
            {
                $return[$this->LDAPService->getUserByDN($member)->givenName] = $member;
            }
            catch (UserNotUnique $e)
            {
                $this->getGroupManager()->getOrg()->session->getFlashBag()->add("error","Der user mit der DN $member konnte nicht geladen werden!");
            }
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
            $this->LDAPService->addMailToForward($mail, "$name@pbnl.de");
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
            try {
                $oneUser = $this->LDAPService->getUserByDN($member);
                array_push($this->membersUserData,$oneUser);
            }
            catch (UserNotUnique $e)
            {
                $this->getGroupManager()->getOrg()->session->getFlashBag()->add("error","Der user mit der DN $member konnte nicht geladen werden!");
            }
        }
        $this->fetchedData = true;
    }

    public function removeMember($dn)
    {
        $this->LDAPService->removeUserDNFromGroup($dn,$this->name);
        $mail = $this->LDAPService->getUserByDN($dn)->mail;
        $name = str_replace("@","",$this->name);
        try
        {
            $this->LDAPService->removeMailFromForward($mail, "$name@pbnl.de");
        }
        catch (Exception $e)
        {
            $this->getGroupManager()->getOrg()->session->getFlashBag()->add("notice",$e->getMessage());
        }
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