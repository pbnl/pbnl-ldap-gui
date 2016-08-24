<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 20.08.16
 * Time: 14:22
 */

namespace AppBundle\model\usersLDAP;


class Group
{
    private $members = Array(); // An array with all dn of the users
    public $name = "";
    public $dn = "";
    public $gidNumber = "";

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
}