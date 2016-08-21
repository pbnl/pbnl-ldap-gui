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


    public function __construct($data = null)
    {
        if($data != null) {
            $this->givenName = $data["givenname"][0];
            $this->dn = $data["dn"];
            $this->uidNumber = intval($data["uidnumber"][0]);
        }
    }

    public function memberOf(Group $group)
    {
        return in_array($this->dn, $group->getMembersDN());
    }

}