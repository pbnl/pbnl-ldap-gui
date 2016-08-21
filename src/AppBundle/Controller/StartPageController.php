<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 12.08.16
 * Time: 20:08
 */

namespace AppBundle\Controller;


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

        $errorMessage = Array();
        $successMessage = Array();

        return$this->render("default/startPage.html.twig",array(
            "errorMessage"=>$errorMessage,
            "successMessage"=>$successMessage

        ));
    }

    /**
     * @Route("/permissionError", name="PermissionError")
     */
    public function permissionErrorPage(Request $request)
    {
        $errorMessage = Array();
        $successMessage = Array();

        array_push($errorMessage,"PermissionError");

        return$this->render("/default/permissionError.html.twig",array(
            "errorMessage"=>$errorMessage,
            "successMessage"=>$successMessage
        ));
    }
}