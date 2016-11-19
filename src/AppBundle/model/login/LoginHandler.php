<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 06.08.16
 * Time: 18:42
 */

namespace AppBundle\model\login;


use AppBundle\model\ldapCon\LDAPConnetor;
use AppBundle\model\ldapCon\LDAPService;
use AppBundle\model\SSHA;
use AppBundle\model\usersLDAP\Organisation;
use Monolog\Logger;
use NoLDAPBindDataException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

class LoginHandler
{

    private $ldapFrontend;
    protected $org;

    public function __construct(LDAPService $ldapFrontend,Logger $logger,Organisation $organisationg)
    {
        $this->ldapFrontend = $ldapFrontend;
        $this->org = $organisationg;
    }

    /**
     * Tries to find a user in the LDAP with a password and a name stored in the object fields
     * If there is a correct user -> login
     *
     * @return bool
     */
    public function login(LoginDataHolder $loginData)
    {
        $this->data = $loginData;

        $data = $this->ldapFrontend->makeLoginRequest($loginData);

        //There can be only one user with the name we are looking for
        if ($data["count"] != 1) return FALSE;
        //Correct password an name?
        if ($data[0]["givenname"][0] == $loginData->name && SSHA::ssha_password_verify($data[0]["userpassword"][0],$loginData->password))
        {
            $this->loginSuccess();
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Sets some variables in the session to see if someone is logged in
     */
    private function loginSuccess()
    {
        $session = new Session();
        $session->set("loggedIn",TRUE);
        $session->set("name",$this->data->name);



        $userManager = $this->org->getUserManager();
        $person = $userManager->getUserByName($this->data->name);
        $session->set("stamm",$person->getStamm($this->ldapFrontend));
        $session->set("dn",$person->dn);
        $session->set("uidNumber",$person->getUidNumber());
    }

    public function logout()
    {
        $session = new Session();
        $session->set("loggedIn",FALSE);
        $session->set("name","");
    }

    public function checkPermissions($requierments = "")
    {
        $session = new Session();
        if($requierments == "")
        {
            return $session->get("loggedIn");
        }
        else
        {
            if(!$session->get("loggedIn")) return false;
            if($this->ldapFrontend->getAllGroups("buvo")[0]->isDNMember($session->get("dn"))) return true;
        }

        //Splits up the string into an array
        $requierments = explode(",",$requierments);
        foreach ($requierments as $requierment)
        {
            //Splits up the key
            $key = explode(":",$requierment)[0];
            switch ($key)
            {
                case "ownStamm" :
                    $value = explode(":",$requierment)[1];
                    return $this->ldapFrontend->getAllGroups($session->get("stamm"))[0]->isDNMember($session->get("dn"));
                    break;
                case "inStamm" :
                    $value = explode(":",$requierment)[1];
                    return $this->ldapFrontend->getAllGroups($value)[0]->isDNMember($session->get("dn"));
                    break;
                case "inGroup" :
                    $value = explode(":",$requierment)[1];
                    return $this->ldapFrontend->getAllGroups($value)[0]->isDNMember($session->get("dn"));
                    break;
                case "inTeam" :
                    $value = explode(":",$requierment)[1];
                    return $this->ldapFrontend->getAllTeams($value)[0]->isDNMember($session->get("dn"));
                    break;
                case "isUser":
                    $value = explode(":",$requierment)[1];
                    $user = $this->ldapFrontend->getUserByUidNumber($value);
                    if($user->getUidNumber() == $session->get("uidNumber")) return true;
                    return false;
                    break;
            }
        }

        return $session->get("loggedIn");
    }
}