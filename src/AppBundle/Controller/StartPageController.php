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
        return$this->render("default/startPage.html.twig",array(

        ));
    }
}