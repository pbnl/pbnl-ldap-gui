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
    public $ouGroup = "";
    public $mobile = "0";
    public $postalCode = "0";
    public $street = "0";
    public $telephoneNumber = "0";
    public $l = "0";
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
            if(isset($data["mobile"][0])) $this->mobile = $data["mobile"][0];
            if(isset($data["l"][0])) $this->l = $data["l"][0];
            if(isset($data["postalcode"][0])) $this->postalCode = $data["postalcode"][0];
            if(isset($data["street"][0])) $this->street = $data["street"][0];
            if(isset($data["telephonenumber"][0])) $this->telephoneNumber = $data["telephonenumber"][0];
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

    public function getStamm()
    {
        if ($this->stamm != "") return $this->stamm;
        $staemme = $this->ldapService->getStammesNames();
        foreach ($staemme as $stammName) {
            $stammGroup = $this->ldapService->getAllGroups("$stammName")[0];
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

    public function pushNewData()
    {
        $this->ldapService->saveNewUserData($this);
    }

}