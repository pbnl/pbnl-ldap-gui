<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 05.08.16
 * Time: 20:52
 */

namespace AppBundle\Controller;

use AppBundle\LoginHandler;
use AppBundle\model\LDAPConnetor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

class LoginController extends Controller
{
    /**
     * @Route("/login", name="Login")
     */
    public function defaultLoginPage(Request $request)
    {
        $errorMassage= "";

        //Creats a loginform
        $loginHandler = new LoginHandler();
        $loginForm = $this->createFormBuilder($loginHandler,['attr' => ['class' => 'form-signin']])
            ->add("name",TextType::class,array("attr"=>["placeholder"=>"Name"],'label' => false))
            ->add("password",PasswordType::class,array("attr"=>["placeholder"=>"Passwort"],'label' => false))
            ->add("rememberme",CheckboxType::class,array("label"=>"Erinnere dich","required"=>false))
            ->add("send",SubmitType::class,array("label"=>"Login","attr"=>["class"=>"btn btn-lg btn-primary btn-block"]))
            ->getForm();

        $loginForm->handleRequest($request);
        //If someone send login data
        if ($loginForm->isSubmitted() && $loginForm->isValid())
        {
            $ldapConnector = new LDAPConnetor();
            $ldapConnector->intiLDAPConnection();

            $loginHandler = $loginForm->getData();
            if ($loginHandler->login() == FALSE)
            {
                $errorMassage .= "Name oder Passwort falsch!";
            }


        }
        //return the default loginpage
        return $this->render("default/login.html.twig", array(
            "loginForm"=>$loginForm->createView(),
            "errorMassage"=>$errorMassage
        ));
    }
}