<?php
namespace AppBundle\model\usersLDAP;
use AppBundle\model\ldapCon\LDAPService;

/**
 * Created by PhpStorm.
 * User: paul
 * Date: 16.08.16
 * Time: 22:58
 */
class User
{
    public $givenName = "";
    public $uid = "";
    public $firstName = "";
    public $secondName = "";
    public $uidNumber = 0;
    public $mail = "";
    public $hashedPassword = "";
    public $homeDirectory = "";
    public $dn = "";
    public $clearPassword = "";
    public $generatedPassword;
    public $ouGroup;
    private $stamm = "";
    private $ldapService;


    public function __construct(LDAPService $LDAPService,$data = null)
    {
        $this->ldapService = $LDAPService;
        if ($data != null) {
            $this->givenName = $data["givenname"][0];
            $this->dn = str_replace(", ", ",", $data["dn"]);
            $this->uidNumber = intval($data["uidnumber"][0]);
            $this->uid = intval($data["uid"][0]);
            $this->firstName = $data["cn"][0];
            $this->secondName = $data["sn"][0];
            if (isset($data["mail"][0])) $this->mail = $data["mail"][0];
            else {
                if ($this->ldapService->getForwardForMail($data["givenname"][0] . "@pbnl.de") != false) {
                    $this->mail = $this->ldapService->getForwardForMail($data["givenname"][0] . "@pbnl.de")[0];
                }
            }
        }
        $this->stamm = $this->getStamm($LDAPService);
    }

    public function memberOf(ParentGroup $group)
    {
        return in_array($this->dn, $group->getMembersDN());
    }

    public function getStamm(LDAPService $ldapFrontend = null)
    {
        if ($this->stamm != "") return $this->stamm;
        if($ldapFrontend == null) return "";
        $staemme = $ldapFrontend->getStammesNames();
        foreach ($staemme as $stammName) {
            $stammGroup = $ldapFrontend->getAllGroups("$stammName")[0];
            if ($this->memberOf($stammGroup)) {
                $this->stamm = $stammName;
                return $stammName;
            }
        }
    }

    public function setStamm($stamm)
    {
        $this->stamm = $stamm;
    }

    public function delUser()
    {
        $groups = $this->ldapService->getAllGroups();
        foreach ($groups as $group)
        {
            if($group->isDNMember($this->dn)) $group->removeMember($this->dn);
        }
        $this->ldapService->removeUserWithDN($this->dn);
    }

}