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
use AppBundle\model\usersLDAP\Stavo;
use AppBundle\model\usersLDAP\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class StammController extends Controller
{
    /**
     * @Route("/stamm/showAll", name="Stamm")
     */
    public function getStamm(Request $request)
    {
        $loginHandler = $this->get("login");
        if (!$loginHandler->checkPermissions("")) return $this->redirectToRoute("PermissionError");

        $errorMessage = Array();
        $successMessage = Array();

        //Get all users and search for name and groupq if wanted
        $people = new People($this->get("ldap.frontend"));

        //Search users
        $session = new Session();
        $peopleList = $people->getAllUsers($request->get("stamm",$session->get("stamm")),"");

        return$this->render(":default:showUsersInOneTabel.html.twig",array(
            "users"=>$peopleList,
            "errorMessage"=>$errorMessage,
            "successMessage"=>$successMessage

        ));
    }

    /**
     * @Route("/stamm/showStavo", name="Zeige Stavo")
     */
    public function showStavo(Request $request)
    {
        $loginHandler = $this->get("login");
        if (!$loginHandler->checkPermissions("")) return $this->redirectToRoute("PermissionError");

        $errorMessage = Array();
        $successMessage = Array();

        $people = new People($this->get("ldap.frontend"));
        $session = new Session();
        $stamm = $session->get("stamm");
        $group = $people->getStavo($request->get("stamm",$stamm))->getMembersUser();

        $stammesMember = $people->getGroups($request->get("stamm",$stamm))[0]->getListWithDNAndName();

        //Create add stavo member form
        $user = new User($this->get("ldap.frontend"));
        $addstavoMemberForm = $this->createFormBuilder($user,['attr' => ['class' => 'form-addStavoMemberForm']])
            ->add('dn', ChoiceType::class, array(
                'choices'  => $stammesMember,
            ))
            ->add('stamm',HiddenType::class, array(
            ))
            ->add("send",SubmitType::class,array("label"=>"Hinzufügen","attr"=>["class"=>"btn btn-lg btn-primary btn-block"]))
            ->setMethod("get")
            ->setAction($this->generateUrl("Stavo Mitglied hinzufügen"))
            ->getForm();


        return$this->render(":default:showStavo.html.twig",array(
            "addStavoMemberForm"=> $addstavoMemberForm->createView(),
            "errorMessage"=>$errorMessage,
            "successMessage"=>$successMessage,
            "users" => $group
        ));
    }

    /**
     * @Route("/stamm/stavo/delMember", name="Loesche Stavo Mitglied")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function delStavoMember(Request $request)
    {
        $errorMessage = Array();
        $successMessage = Array();

        //The stavo the user will get removed from
        $stammName = $request->get("stamm");

        $loginHandler = $this->get("login");
        if (!$loginHandler->checkPermissions("inStamm:$stammName")) return $this->redirectToRoute("PermissionError");

        //Get the stamm
        $people = new People($this->get("ldap.frontend"));
        $stamm = $people->getStavo($stammName);

        //The uid of the user who should get deleted
        $uidNumber = $request->get("uidNumber");

        $stamm->delMemberByDN($people->getUserByUidNumber($uidNumber)->dn);

        return$this->redirectToRoute("Zeige Stavo");
    }

    /**
     * @Route("/stamm/stavo/addMember", name="Stavo Mitglied hinzufügen")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addStavoMember(Request $request)
    {
        $errorMessage = Array();
        $successMessage = Array();

        $people = new People($this->get("ldap.frontend"));
        $stammesMember = $people->getGroups($request->get("form[stamm]"))[0]->getListWithDNAndName();

        //Create add stavo member form
        $user = new User($this->get("ldap.frontend"));
        $addstavoMemberForm = $this->createFormBuilder($user,['attr' => ['class' => 'form-addStavoMemberForm']])
            ->add('dn', ChoiceType::class, array(
                'choices'  => $stammesMember,
            ))
            ->add('stamm',HiddenType::class, array(
            ))
            ->add("send",SubmitType::class,array("label"=>"Hinzufügen","attr"=>["class"=>"btn btn-lg btn-primary btn-block"]))
            ->setMethod("get")
            ->setAction($this->generateUrl("Stavo Mitglied hinzufügen"))
            ->getForm();

        //Handel the form input
        $addstavoMemberForm->handleRequest($request);
        if($addstavoMemberForm->isSubmitted() && $addstavoMemberForm->isValid())
        {
            //Create the new user
            $stavo = $people->getStavo($request->get("form[stamm]"));
            $stavo->addMemberByDN($user->dn);
        }
        return$this->redirectToRoute("Zeige Stavo");
    }
}