<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 17.08.16
 * Time: 12:02
 */

namespace AppBundle\model\ldapCon;


use AppBundle\model\usersLDAP\Group;
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
     * @param $groupFilterName
     * group we are looking for. All if $groupFilter = ""
     *
     * @return array
     */
    public function getAllUsers($groupFilterName = "")
    {
        //Search options
        $ldaptree = "ou=People,dc=pbnl,dc=de";

        //Search filters
        $filter="(&(objectClass=inetOrgPerson))";

        //Search
        $result = ldap_search($this->ldapCon,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($this->ldapCon));
        $data = ldap_get_entries($this->ldapCon, $result);

        $people = Array();

        if($data["count"] != 0)
        {
            for($i = 0; $i < $data["count"]; $i++) {
                $user = new User();
                $user->givenName = $data[$i]["givenname"][0];
                $user->dn = $data[$i]["dn"];
                array_push($people,$user);
            }
        }

        //if you look for a special group
        if ($groupFilterName != "")
        {
            $filteredPeople = Array();
            //get the group
            $filterGroup = $this->getAllGroups($groupFilterName)[0];
            //who is in the group?
            foreach ($people as $onePerson)
            {
                if($onePerson->memberOF($filterGroup))
                {
                    array_push($filteredPeople,$onePerson);
                }
            }
            return $filteredPeople;
        }
        else
        {
            return $people;
        }
    }

    /**
     * Returns all groups or a group which name is equal to the $groupFilterName string
     *
     * @param string $groupFilterName
     * @return array
     * An array with all groups
     */
    public function getAllGroups($groupFilterName = "")
    {
        //Search options
        $ldaptree = "ou=Group,dc=pbnl,dc=de";

        //Search filters
        if ($groupFilterName != "")
        {
            $filter="(&(objectClass=posixGroup)(cn=$groupFilterName))";
        }
        else
        {
            $filter="(&(objectClass=posixGroup))";
        }

        //Search
        $result = ldap_search($this->ldapCon,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($this->ldapCon));
        $data = ldap_get_entries($this->ldapCon, $result);

        $groups = Array();

        if($data["count"] != 0)
        {
            for($i = 0; $i < $data["count"]; $i++) {
                $group = new Group();
                $group->name = $data[$i]["cn"][0];
                $group->dn = $data[$i]["dn"];
                $member = $data[$i]["memberuid"];
                for ($j = 0;$j < $member["count"];$j++)
                {
                    $group->addMember($member[$j]);
                }
                array_push($groups,$group);
            }
        }

        return $groups;
    }

}