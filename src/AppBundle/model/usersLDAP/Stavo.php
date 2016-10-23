<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 26.08.16
 * Time: 15:24
 */

namespace AppBundle\model\usersLDAP;

use AppBundle\model\ldapCon\LDAPService;

class Stavo extends ParentGroup
{
    protected $stammesName;

    public function __construct(LDAPService $ldap, $name)
    {
        $this->LDAPService = $ldap;
        $this->name = "stavo";
        $this->stammesName = $name;
        $stamm = $ldap->getAllGroups($name)[0];
        $stavos = $ldap->getAllGroups("stavo")[0];
        $stavo = $stamm->getMembersOfGroupB($stavos);

        $this->members = $stavo->getMembersDN();

    }
}