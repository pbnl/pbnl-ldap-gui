<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 18.09.16
 * Time: 01:00
 */

namespace AppBundle\model\usersLDAP;


use AppBundle\model\ldapCon\LDAPService;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Dump\Container;
use Symfony\Component\HttpFoundation\Session\Session;

class Organisation
{
    public $ldapFrontend ;
    public $logger;
    private $teamManager;
    private $userManager;
    private $groupManager;
    public $session;

    public function __construct(LDAPService $LDAPService , Logger $logger, Session $session)
    {
        $this->session = $session;
        $this->logger = $logger;
        $this->ldapFrontend = $LDAPService;
    }

    public function getTeamManager()
    {
        if($this->teamManager == null)
        {
            $this->teamManager = new TeamManager($this->ldapFrontend,$this);
            return $this->teamManager;
        }
        else return $this->teamManager;
    }

    public function getUserManager()
    {
        if($this->userManager == null)
        {
            $this->userManager = new UserManager($this->ldapFrontend,$this);
            return $this->userManager;
        }
        else return $this->userManager;
    }

    public function getGroupManager()
    {
        if($this->groupManager == null)
        {
            $this->groupManager = new GroupManager($this->ldapFrontend,$this);
            return $this->groupManager;
        }
        else return $this->groupManager;
    }

    public function getOUGroupsNames()
    {
        return $this->ldapFrontend->getOUGroupsNames();
    }

    public function getStammesNames()
    {
        $groups = $this->ldapFrontend->getAllGroups();
        $names = array();
        foreach ($groups as $group)
        {
            if($group->type == "stamm")
            {
                array_push($names,$group->name);
            }
        };
        return $names;
    }

}