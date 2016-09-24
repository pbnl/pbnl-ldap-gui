<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 16.08.16
 * Time: 23:13
 */

namespace AppBundle\Controller;


use AppBundle\model\ArrayMethods;
use AppBundle\model\usersLDAP\Organisation;
use AppBundle\model\usersLDAP\People;
use AppBundle\model\usersLDAP\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Tests\Extension\Core\Type\PasswordTypeTest;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\model\ldapCon\ldapService;
use Symfony\Component\HttpFoundation\Response;

class UserManagerController extends Controller
{
    /**
     * @Route("/user/schowAllUsers", name="Alle Benutzer")
     * @param Request $request
     * @return Response
     */
    public function getAllUsers(Request $request)
    {
        $loginHandler = $this->get("login");
        if (!$loginHandler->checkPermissions("")) return $this->redirectToRoute("PermissionError");

        $errorMessage = Array();
        $successMessage = Array();

        //Get all users and search for name and groupq if wanted
        $people = new People($this->get("ldap.frontend"));

        //Create search form
        $peopleSearchForm = $this->createFormBuilder($people,['attr' => ['class' => 'form-searchUser']])
            ->add("userFilter",TextType::class,array("attr"=>["placeholder"=>"Benutzer suchen"],'label'=>false,'required' => false))
            ->add("groupFilter",TextType::class,array("attr"=>["placeholder"=>"Gruppen suchen"],'label'=>false,'required' => false))
            ->add("send",SubmitType::class,array("label"=>"Suchen","attr"=>["class"=>"btn btn-lg btn-primary btn-block"]))
            ->setMethod("get")
            ->getForm();

        //Handel the form input
        $peopleSearchForm->handleRequest($request);

        //Search users
        $peopleList = $people->getAllUsers($people->groupFilter,$people->userFilter);

        return$this->render(":default:showUsersInOneTabel.html.twig",array(
            "peopleSearchForm" => $peopleSearchForm->createView(),
            "users"=>$peopleList,
            "errorMessage"=>$errorMessage,
            "successMessage"=>$successMessage

        ));
    }

    /**
     * @Route("/user/addAUser", name="Benutzer erstellen")
     * @param Request $request
     * @return Response
     */
    public function addAUser(Request $request)
    {
        $loginHandler = $this->get("login");
        if (!$loginHandler->checkPermissions("")) return $this->redirectToRoute("PermissionError");
        
        $errorMessage = Array();
        $successMessage = Array();

        //Create the form
        $org = new Organisation($this->get("ldap.frontend"));
        $userManager = $org->getUserManager();
        $ouGroups = $org->getOUGroupsNames();
        $staemme = $org->getStammesNames();
        $user = $userManager->getEmptyUser();
        $addUserForm = $this->createFormBuilder($user,['attr' => ['class' => 'form-addAUser']])
            ->add("firstName",TextType::class,array("attr"=>["placeholder"=>"Vorname"],'label' => "Vorname"))
            ->add("secondName",TextType::class,array("attr"=>["placeholder"=>"Nachname"],'label' => "Nachname"))
            ->add("givenName",TextType::class,array("attr"=>["placeholder"=>"Benutzername"],'label' => "Benutzername"))
            ->add("clearPassword",PasswordType::class,array("attr"=>["placeholder"=>"Password"],'label' => "Password"))
            ->add("generatePassword",ButtonType::class,array("attr"=>[],'label' => "Generiere ein Passwort"))
            ->add("generatedPassword",TextType::class,array("attr"=>["readonly"=>"","placeholder"=>"Generiertes Passwort"],"label"=>FALSE))
            ->add('ouGroup', ChoiceType::class, array(
                'choices'  => ArrayMethods::valueToKeyAndValue($ouGroups),
            ))
            ->add('stamm', ChoiceType::class, array(
                'choices'  => ArrayMethods::valueToKeyAndValue($staemme),
            ))
            ->add("send",SubmitType::class,array("label"=>"Erstellen","attr"=>["class"=>"btn btn-lg btn-primary btn-block"]))
            ->getForm();

        $personAddedToLDAP = Array();
        $addedSomeone = FALSE;

        //Handel the form input
        $addUserForm->handleRequest($request);
        if($addUserForm->isSubmitted() && $addUserForm->isValid())
        {
            //Create the new user
            $personAddedToLDAP = $userManager->createNewUser($user);
            //Handel result and errors
            if ($personAddedToLDAP == FALSE) array_push($errorMessage,"Benutzer konnte nicht hinzugefügt werden. Benutzer exestiert bereits");
            else $addedSomeone = TRUE;
        }

        //Render the page
        return $this->render("default/addAUser.html.twig",array(
            "addAUserForm" => $addUserForm->createView(),
            "addedPerson" => $personAddedToLDAP,
            "addedSomeone" => $addedSomeone,
            "errorMessage"=>$errorMessage,
            "successMessage"=>$successMessage

        ));
    }

    /**
     * @Route("/user/loeschen", name="Loeschen")
     * @param Request $request
     * @return Response
     */
    public function delUser(Request $request)
    {
        $loginHandler = $this->get("login");
        if (!$loginHandler->checkPermissions("")) return $this->redirectToRoute("PermissionError");

        $errorMessage = Array();
        $successMessage = Array();

        $uidNumber = $request->get("uidNumber");


        $org = new Organisation($this->get("ldap.frontend"));
        $user = $org->getUserManager()->getUserByUid($uidNumber);
        $user->delUser();

        return $this->redirectToRoute("Alle Benutzer");
    }
}