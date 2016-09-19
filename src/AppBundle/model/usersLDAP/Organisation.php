<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 18.09.16
 * Time: 01:00
 */

namespace AppBundle\model\usersLDAP;


use AppBundle\model\ldapCon\LDAPService;

class Organisation
{
    public $ldapFrontend ;
    public $teamManager;

    public function __construct(LDAPService $LDAPService)
    {
        $this->ldapFrontend = $LDAPService;
    }

    public function getTeamManager()
    {
        if($this->teamManager == null)
        {
            $this->teamManager = new TeamManager($this->ldapFrontend);
            return $this->teamManager;
        }
        else return $this->teamManager;
    }
}