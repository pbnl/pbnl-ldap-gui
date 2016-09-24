<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 19.09.16
 * Time: 21:02
 */

namespace AppBundle\model\usersLDAP;


use AppBundle\model\ldapCon\LDAPService;

class TeamManager extends GroupManager
{

    public function createNewTeam($name)
    {

    }

    public function getTeamByGid($gid)
    {
        return $this->ldapFrontend->getAllTeams($gid)[0];
    }
    public function getTeamByName($name)
    {

    }

    public function getAllTeams($search)
    {
        return $this->ldapFrontend->getAllTeams($search);
    }
}