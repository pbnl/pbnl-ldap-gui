<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 16.08.16
 * Time: 23:13
 */

namespace AppBundle\Controller;


use AppBundle\model\ArrayMethods;
use AppBundle\model\formDataClasses\UserSearchFormDataHolder;
use AppBundle\model\ldapCon\AllreadyInGroupException;
use AppBundle\model\ldapCon\GroupNotFoundException;
use AppBundle\model\usersLDAP\Organisation;
use AppBundle\model\usersLDAP\People;
use AppBundle\model\usersLDAP\User;
use AppBundle\model\usersLDAP\UserAlreadyExistException;
use AppBundle\model\usersLDAP\UserNotUnique;
use AppBundle\model\xlsxImport\XlsxImport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
        //Get all users and search for name and groupq if wanted
        $org = $this->get("organisation");
        $userManager = $org->getUserManager();

        //Create search form
        $userSearchFormDataHolder = new UserSearchFormDataHolder();
        $peopleSearchForm = $this->createFormBuilder($userSearchFormDataHolder,['attr' => ['class' => 'form-searchUser']])
            ->add("userFilter",TextType::class,array("attr"=>["placeholder"=>"Benutzer suchen"],'label'=>false,'required' => false))
            ->add("groupFilter",TextType::class,array("attr"=>["placeholder"=>"Gruppen suchen"],'label'=>false,'required' => false))
            ->add("send",SubmitType::class,array("label"=>"Suchen","attr"=>["class"=>"btn btn-lg btn-primary btn-block"]))
            ->setMethod("get")
            ->getForm();

        //Handel the form input
        $peopleSearchForm->handleRequest($request);

        //Search users
        $userList = [];
        try {
            $userList = $userManager->getAllUsers($userSearchFormDataHolder->groupFilter, $userSearchFormDataHolder->userFilter);
        }
        catch (GroupNotFoundException $e)
        {
            $this->addFlash("error",$e->getMessage());
        }

        return$this->render(":default:showUsersInOneTabel.html.twig",array(
            "peopleSearchForm" => $peopleSearchForm->createView(),
            "users"=>$userList,

        ));
    }

    /**
     * @Route("/user/addAUser", name="Benutzer erstellen")
     * @param Request $request
     * @return Response
     */
    public function addAUser(Request $request)
    {
        //Create the form
        $org = $this->get("organisation");
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
            try
            {
                $personAddedToLDAP = $userManager->createNewUser($user);
                $addedSomeone = TRUE;
                $this->addFlash("succsess","Benutzer hinzugefügt");
            }
            catch (UserNotUnique $e)
            {
                $this->addFlash("error",$e->getMessage());
            }
            catch (AllreadyInGroupException $e)
            {
                $this->addFlash("error",$e->getMessage());
            }
            catch (UserAlreadyExistException $e)
            {
                $this->addFlash("error",$e->getMessage());
            }
        }

        //Render the page
        return $this->render("default/addAUser.html.twig",array(
            "addAUserForm" => $addUserForm->createView(),
            "addedPerson" => $personAddedToLDAP,
            "addedSomeone" => $addedSomeone
        ));
    }

    /**
     * @Route("/user/loeschen", name="Loeschen")
     * @param Request $request
     * @return Response
     */
    public function delUser(Request $request)
    {
        $uidNumber = $request->get("uidNumber");
        $uidNumbers =  explode(";",$uidNumber);

        $org = $this->get("organisation");
        foreach ($uidNumbers as $oneNumber)
        {
            try {
                $user = $org->getUserManager()->getUserByUid($oneNumber);
                $user->delUser();
            }
            catch (UserNotUnique $e)
            {
                $this->addFlash("error",$e->getMessage());
            }
        }

        return $this->redirectToRoute("Alle Benutzer");
    }

    /**
     * @Route("/user/userDetail", name="Userdetails")
     * @param Request $request
     * @return Response
     */
    public function showDetailsUser(Request $request)
    {
        $uidNumber = $request->get("uidNumber");


        $org = $this->get("organisation");
        $userManager = $org->getUserManager();
        $editUserForm = false;
        $user = false;
        try {
            $user = $userManager->getUserByUid($uidNumber);

            //Create the form
            //is the logged in user in the same stamm as this user and a stavo member?
            //than he can edit him
            //or if the session user is the same as the one who gets edited
            $stamm = $user->getStamm();
            //Security stuff
            $authUser = $this->get('security.token_storage')->getToken()->getUser();
            if ($stamm == $authUser->getStamm() ||
                $authUser->getUidNumber() == $uidNumber ||
                $this->get('security.authorization_checker')->isGranted('ROLE_BUVO')) {
                $editUserForm = $this->createFormBuilder($user, ['attr' => ['class' => 'form-addAUser']])
                    ->add("firstName", TextType::class, array("attr" => ["placeholder" => "Vorname"], 'label' => "Vorname"))
                    ->add("secondName", TextType::class, array("attr" => ["placeholder" => "Nachname"], 'label' => "Nachname"))
                    ->add("l",TextType::class,array("attr" => ["placeholder" => "Stadt"], 'label' => "Stadt","required" => false))
                    ->add("postalCode",TextType::class,array("attr" => ["placeholder" => "PLZ"], 'label' => "PLZ","required" => false))
                    ->add("street",TextType::class,array("attr" => ["placeholder" => "Straße"], 'label' => "Straße","required" => false))
                    ->add("telephoneNumber",TextType::class,array("attr" => ["placeholder" => "Telefonnummer"], 'label' => "Telefonnummer","required" => false))
                    ->add("mobile",TextType::class,array("attr" => ["placeholder" => "Mobil"], 'label' => "Mobil","required" => false))
                    ->add("send", SubmitType::class, array("label" => "Änderungen speichern", "attr" => ["class" => "btn btn-lg btn-primary btn-block"]))
                    ->getForm();
                //Handel the form input
                $editUserForm->handleRequest($request);
                if($editUserForm->isSubmitted() && $editUserForm->isValid())
                {
                    $user->pushNewData();
                    $this->addFlash("success","Änderungen gespeichert");
                }

                $editUserForm = $editUserForm->createView();
            }
        }
        catch (UserNotUnique $e)
        {
            $this->addFlash("error",$e->getMessage());
        }




        //Render the page
        return $this->render(":default:userDetail.html.twig",array(
            "editUserForm" => $editUserForm,
            "user" => $user,
        ));
    }

    /**
     * @Route("/user/importUserFromXlsx", name="Importieren von xlsx")
     * @param Request $request
     * @return Response
     */
    public function importUserFromXlsx(Request $request)
    {
        $org = $this->get("organisation");
        $ouGroups = $org->getOUGroupsNames();
        $staemme = $org->getStammesNames();

        $xlsxImporter = new XlsxImport();
        $xlsxImporterFileForm = $this->createFormBuilder($xlsxImporter,['attr' => ['class' => 'form-xlsxFileUploadForm']])
            ->add('xlsxFile', FileType::class, array('label' => 'xlsx Datei'))
            ->add('ouGroup', ChoiceType::class, array(
                'choices'  => ArrayMethods::valueToKeyAndValue($ouGroups),
            ))
            ->add('stamm', ChoiceType::class, array(
                'choices'  => ArrayMethods::valueToKeyAndValue($staemme),
            ))
            ->add("send", SubmitType::class, array("label" => "Hochladen", "attr" => ["class" => "btn btn-lg btn-primary btn-block"]))
            ->getForm();

        //Handel the request
        $xlsxImporterFileForm->handleRequest($request);
        if ($xlsxImporterFileForm->isSubmitted() && $xlsxImporterFileForm->isValid()) {
            // $file stores the uploaded xlsx file
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $xlsxImporter->xlsxFile;

            // Generate a unique name for the file before saving it
            $fileName = $this->get("session")->getId().md5(uniqid()).'.'.$file->guessExtension();

            // Move the file to the directory where brochures are stored
            $file->move($this->getParameter('xlsxFile_directory'),$fileName);

            // Update the 'xlsxFile' property to store the xlsx file name
            // instead of its contents
            $xlsxImporter->xlsxFile = $fileName;
            $xlsxImporter->xlsxFilePath = $this->getParameter('xlsxFile_directory')."/".$fileName;

            $xlsxImporter->parse($this->get('phpexcel'),$org);
            unlink($xlsxImporter->xlsxFilePath);
        }



        //Render the page
        return $this->render(":default:xlsxImporter.html.twig",array(
            "xlsxImporterFileForm" => $xlsxImporterFileForm->createView(),
        ));
    }
}