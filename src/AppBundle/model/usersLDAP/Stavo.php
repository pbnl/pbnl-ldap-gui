<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 26.08.16
 * Time: 15:24
 */

namespace AppBundle\model\usersLDAP;

use AppBundle\model\ldapCon\LDAPService;

class Stavo
{
    private $members = Array();

    public function __construct(LDAPService $ldap, $name)
    {
        $stamm = $ldap->getAllGroups($name);
        $stavos = $ldap->getAllGroups("stavo");
        $stavo = $stamm->getMembersOfGroupB($stavos);

        $this->members = $stavo->getMembersDN();
    }

    public function getMembersDN()
    {
        return $this->members;
    }


}