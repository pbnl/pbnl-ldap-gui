<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 16.08.16
 * Time: 23:13
 */

namespace AppBundle\Controller;


use AppBundle\model\usersLDAP\People;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\model\ldapCon\ldapService;
use Symfony\Component\HttpFoundation\Response;

class UserManagerController extends Controller
{
    /**
     * @Route("/user/schowAllUsers/{group}", name="Alle Benutzer")
     * @param Request $request
     * @return Response
     */
    public function getStartPage(Request $request,$group="")
    {
        $people = new People($this->get("ldap.frontend"));
        $peopleList = $people->getAllUsers($group);

        return$this->render("default/allUsers.html.twig",array(
            "users"=>$peopleList
        ));
    }
}