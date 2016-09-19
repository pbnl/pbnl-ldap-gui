<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 20.08.16
 * Time: 14:22
 */

namespace AppBundle\model\usersLDAP;


use AppBundle\model\ldapCon\LDAPService;

class Group extends ParentGroup
{

    public function addMember($dn)
    {
        array_push($this->members,$dn);
    }
    public function getMembersOfGroupB($group)
    {
        $newGroup = new Group($this->LDAPService);
        foreach ($this->members as $userDN)
        {
            if(in_array($userDN,$group->getMembersDN()))
            {
                $newGroup->addMember($userDN);
            }
        }
        return$newGroup;
    }
}