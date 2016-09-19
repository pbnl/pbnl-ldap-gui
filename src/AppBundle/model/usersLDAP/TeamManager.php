<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 19.09.16
 * Time: 21:02
 */

namespace AppBundle\model\usersLDAP;


use AppBundle\model\ldapCon\LDAPService;

class TeamManager
{
    public $ldapFrontend;

    public function __construct(LDAPService $LDAPService)
    {
        $this->ldapFrontend = $LDAPService;
    }

    public function createNewTeam($name)
    {

    }

    public function getTeamByGid($gid)
    {

    }
    public function getTeamByName($name)
    {

    }

    public function getAllTeams($search)
    {
        return $this->ldapFrontend->getAllTeams($search);
    }
}