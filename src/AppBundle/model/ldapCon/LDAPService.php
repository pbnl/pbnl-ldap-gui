<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 17.08.16
 * Time: 12:02
 */

namespace AppBundle\model\ldapCon;


use AppBundle\model\usersLDAP\User;

class LDAPService
{

    private $LDAPConnector = null;
    private $ldapCon = null;

    function __construct()
    {
        $this->LDAPConnector = new LDAPConnetor();
        $this->LDAPConnector->intiLDAPConnection();
        $this->ldapCon = $this->LDAPConnector->getCon();
    }

    /**
     * Returns all users of a group or every user
     *
     * @param $groupFilter
     * group we are looking for. All if $groupFilter = ""
     *
     * @return array
     */
    public function getAllUsers($groupFilter = "")
    {
        //Search options
        $ldaptree = "ou=People,dc=pbnl,dc=de";

        //Search filters
        if ($groupFilter != "")
        {
            $filter="(&(objectClass=inetOrgPerson)(memberOf=cn=$groupFilter,ou=Group,dc=pbnl,dc=de))";
        }
        else
        {
            $filter="(&(objectClass=inetOrgPerson))";
        }

        //Search
        $result = ldap_search($this->ldapCon,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($this->ldapCon));
        $data = ldap_get_entries($this->ldapCon, $result);

        $people = Array();

        if($data["count"] != 0)
        {
            for($i = 0; $i < $data["count"]; $i++) {
                $user = new User();
                $user->givenName = $data[$i]["givenname"][0];
                array_push($people,$user);
            }
        }

        return $people;
    }

}