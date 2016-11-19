<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 12.08.16
 * Time: 20:08
 */

namespace AppBundle\Controller;


use AppBundle\model\ArrayMethods;
use AppBundle\model\usersLDAP\People;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class StartPageController extends Controller
{
    /**
     * @Route("/startPage", name="Startpage")
     */
    public function getStartPage(Request $request)
    {
        $loginHandler = $this->get("login");
        if (!$loginHandler->checkPermissions("")) return $this->redirectToRoute("PermissionError");


        $people = new People($this->get("ldap.frontend"));
        $groups = $people->getGroups();

        $memberCount = 0;
        $groupNames = Array();
        $groubCount = Array();
        foreach ($groups as $group)
        {
            if ($group->type == "stamm") {
                array_push($groupNames, $group->name);
                array_push($groubCount, $group->getMemberCount());
            }
        }

        return$this->render("default/startPage.html.twig",array(
            "groupNames" => $groupNames,
            "groupCounts" => $groubCount,

        ));
    }

    /**
     * @Route("/permissionError", name="PermissionError")
     */
    public function permissionErrorPage(Request $request)
    {
        $this->addFlash("error","PermissionError");

        return$this->render("/default/permissionError.html.twig",array(
        ));
    }
}