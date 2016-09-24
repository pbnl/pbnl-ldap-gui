<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 19.09.16
 * Time: 21:02
 */

namespace AppBundle\model\usersLDAP;


use AppBundle\model\ldapCon\LDAPService;

class GroupManager
{
    protected $ldapFrontend;
    protected $org;

    public function __construct(LDAPService $LDAPService,$org)
    {
        $this->ldapFrontend = $LDAPService;
        $this->org = $org;
    }

    public function createNewGroup($name)
    {

    }

    public function getGroupByGid($gid)
    {
        return $this->ldapFrontend->getAllGroups($gid)[0];
    }
    public function getGroupByName($name)
    {

    }

    public function getAllGroups($search)
    {
        return $this->ldapFrontend->getAllGroups($search);
    }
}