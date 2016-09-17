<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 05.08.16
 * Time: 20:52
 */

namespace AppBundle\Controller;

use AppBundle\model\login\LoginDataHolder;
use AppBundle\model\login\LoginHandler;
use AppBundle\model\LDAPConnetor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class LoginController extends Controller
{
    /**
     * @Route("/login", name="Login")
     */
    public function defaultLoginPage(Request $request)
    {
        //If your are logged in you get redirected to the startPage
        if($request->getSession()->get("loggedIn",TRUE))
        {
            return $this->redirectToRoute("Startpage");
        }

        $errorMassage = Array();
        $successMessage = Array();

        //Creats a loginform
        $loginDataHolder= new LoginDataHolder();
        $loginForm = $this->createFormBuilder($loginDataHolder,['attr' => ['class' => 'form-signin']])
            ->add("name",TextType::class,array("attr"=>["placeholder"=>"Name"],'label' => false))
            ->add("password",PasswordType::class,array("attr"=>["placeholder"=>"Passwort"],'label' => false))
            ->add("rememberme",CheckboxType::class,array("label"=>"Erinnere dich","required"=>false))
            ->add("send",SubmitType::class,array("label"=>"Login","attr"=>["class"=>"btn btn-lg btn-primary btn-block"]))
            ->getForm();

        $loginForm->handleRequest($request);
        //If someone send login data
        if ($loginForm->isSubmitted() && $loginForm->isValid())
        {
            $loginDataHolder = $loginForm->getData();
            $loginHandler = $this->get("login");
            if ($loginHandler->login($loginDataHolder) == FALSE)
            {
                array_push($errorMassage, "Name oder Passwort falsch!");
            }
            else
            {
                return $this->redirectToRoute("Startpage");
            }


        }
        //return the default loginpage
        return $this->render("default/login.html.twig", array(
            "loginForm"=>$loginForm->createView(),
            "errorMessage"=>$errorMassage,
            "successMessage"=>$successMessage
        ));
    }

    /**
     * @Route("/logout",name="Logout")
     */
    public function logout()
    {
        $loginHandler = $this->get("login");
        $loginHandler->logout();
        return $this->redirectToRoute("Login");
    }
}