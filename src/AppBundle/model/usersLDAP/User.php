<?php
namespace AppBundle\model\usersLDAP;
use AppBundle\model\ldapCon\LDAPService;
use AppBundle\model\StringMethods;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\model\validators\constraints as PBNLAssert;

/**
 * Created by PhpStorm.
 * User: paul
 * Date: 16.08.16
 * Time: 22:58
 */
class User
{
    /**
     * @PBNLAssert\IsCorrectPBNLName
     */
    public $givenName = "";
    /**
     * @PBNLAssert\IsCorrectPBNLName
     */
    public $uid = "";
    /**
     * @PBNLAssert\IsCorrectPBNLName
     */
    public $firstName = "";
    /**
     * @PBNLAssert\IsCorrectPBNLName
     */
    public $secondName = "";
    public $uidNumber = 0;

    /**
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = true
     * )
     */
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

    /**
     * @return string
     */
    public function getGivenName()
    {
        return $this->givenName;
    }

    /**
     * @param string $givenName
     */
    public function setGivenName($givenName)
    {
        $this->givenName = $givenName;
    }

    /**
     * @return int|string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param int|string $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getSecondName()
    {
        return $this->secondName;
    }

    /**
     * @param string $secondName
     */
    public function setSecondName($secondName)
    {
        $this->secondName = $secondName;
    }

    /**
     * @return int
     */
    public function getUidNumber()
    {
        return $this->uidNumber;
    }

    /**
     * @param int $uidNumber
     */
    public function setUidNumber($uidNumber)
    {
        $this->uidNumber = $uidNumber;
    }

    /**
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param string $mail
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    /**
     * @return string
     */
    public function getHomeDirectory()
    {
        return $this->homeDirectory;
    }

    /**
     * @param string $homeDirectory
     */
    public function setHomeDirectory($homeDirectory)
    {
        $this->homeDirectory = $homeDirectory;
    }

    /**
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param string $mobile
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getTelephoneNumber()
    {
        return $this->telephoneNumber;
    }

    /**
     * @param string $telephoneNumber
     */
    public function setTelephoneNumber($telephoneNumber)
    {
        $this->telephoneNumber = $telephoneNumber;
    }

    /**
     * @return string
     */
    public function getL()
    {
        return $this->l;
    }

    /**
     * @param string $l
     */
    public function setL($l)
    {
        $this->l = $l;
    }
}