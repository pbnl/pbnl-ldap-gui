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
        $group = $this->ldapFrontend->getAllGroups($gid)[0];
        $group->setGroupManager($this);
        return $group;
    }
    public function getGroupByName($name)
    {
        $group = $this->ldapFrontend->getAllGroups($name)[0];
        $group->setGroupManager($this);
        return $group;
    }

    public function getStavo($stamm)
    {
        $stavo = new Stavo($this->ldapFrontend,$stamm);
        $stavo->setGroupManager($this);
        return $stavo;
    }

    public function getAllGroups($search)
    {
        $goups = $this->ldapFrontend->getAllGroups($search);
        foreach ($goups as $goup)
        {
            $goup->setGroupManager($this);
        }
        return $goups;
    }

    /**
     * @return Organisation
     */
    public function getOrg()
    {
        return $this->org;
    }
}