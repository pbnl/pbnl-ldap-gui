<?php
namespace AppBundle\model\usersLDAP;
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


    public function __construct($data = null)
    {
        if($data != null) {
            $this->givenName = $data["givenname"][0];
            $this->dn = $data["dn"];
            $this->uidNumber = intval($data["uidnumber"][0]);
            $this->uid = intval($data["uid"][0]);
            $this->firstName = $data["cn"][0];
            $this->secondName = $data["sn"][0];
            if(isset($data["mail"][0])) $this->mail = $data["mail"][0];
                //TODO: Weiterleitung holen
            else $this->mail = "";
        }
    }

    public function memberOf(Group $group)
    {
        return in_array($this->dn, $group->getMembersDN());
    }

}