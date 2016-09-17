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
use AppBundle\model\usersLDAP\People;
use NoLDAPBindDataException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

class LoginHandler
{

    private $ldapFrontend;

    public function __construct(LDAPService $ldapFrontend)
    {
        $this->ldapFrontend = $ldapFrontend;
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

        $people = new People($this->ldapFrontend);
        $person = $people->getUserByName($this->data->name);
        $session->set("stamm",$person->getStamm($this->ldapFrontend));
        $session->set("dn",$person->dn);
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
        $con = new LDAPService();

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
                    return $con->getAllGroups($session->get("stamm"))[0]->isDNMember($session->get("dn"));
                    break;
                case "inStamm" :
                    $value = explode(":",$requierment)[1];
                    return $con->getAllGroups($value)[0]->isDNMember($session->get("dn"));
                    break;
            }
        }

        return $session->get("loggedIn");
    }
}