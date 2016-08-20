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
    public $uidNumber = "";
    public $mail = "";
    public $hashedPassword = "";
    public $homeDirectory = "";
    public $dn = "";


    public function memberOf(Group $group)
    {
        return in_array($this->dn, $group->getMembersDN());
    }

}