<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 19.09.16
 * Time: 23:19
 */

namespace AppBundle\Controller;

use AppBundle\Entity\material\MaterialOffer;
use AppBundle\model\usersLDAP\Organisation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class AjaxController extends Controller
{

    /**
     * @Route("/ajax/usersNotInGroup", name="Benutzer die nicht in einer Gruppe")
     * @param Request $request
     * @return Response
     */
    public function getUsersNotInGroup(Request $request)
    {


        $gid = $request->query->get('gid');
        $searchedUserName = $request->query->get('searchedUserName');

        $org = $this->get("organisation");

        //Security stuff
        $teamManager = $org->getTeamManager();
        $team = $teamManager->getAllTeams($request->get("gid", ""))[0];


        $authUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$team->isDNMember($authUser->getDN())) throw $this->createAccessDeniedException();

        $userManager = $org->getUserManager();
        $users = $userManager->getAllUsers("", $searchedUserName);
        $usersNotInGroup = array();
        $team = $teamManager->getAllTeams($gid)[0];
        foreach ($users as $user) {
            if (!$team->isDNMember($user->dn)) {
                array_push($usersNotInGroup,$user->givenName);
            }
        }

        $response = array("code" => 100, "success" => true, "users"=>$usersNotInGroup);

        return new Response(json_encode($response));
    }

    /**
     * @Route("/ajax/addMaterialOffer", name="addMaterialOffer")
     * @param Request $request
     * @return Response
     */
    public function addMaterialOffer(Request $request){


        $name = $request->query->get('offerName');
        $description = $request->query->get('offerDescription');
        $url = $request->query->get('offerURL');
        $price = $request->query->get('offerPrice');
        $pieceId = $request->query->get("associatedMaterialPieceID",0);

        $offer = new MaterialOffer();
        $offer->setDescription($description);
        $offer->setPrice($price);
        $offer->setUrl($url);
        $offer->setName($name);

        $authUser = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $em->persist($offer);
        $em->flush();

        $materialPiece = $em->getRepository('AppBundle:material\MaterialPiece')
            ->find($pieceId);
        if ($materialPiece->getOffersIds() == "") {
            $materialPiece->setOffersIds($offer->getId());
        } else {
            $materialPiece->setOffersIds($materialPiece->getOffersIds() . " ; " . $offer->getId());
        }

        $em->flush();


        $response = array("code" => 100,
            "success" => true,
            "materialOfferId"=>$offer->getId(),
            "materialOfferName"=>$offer->getName(),
            "materialOfferDescription"=>$offer->getDescription(),
            "materialOfferPrice"=>$offer->getPrice(),
            "materialOfferURL"=>$offer->getUrl()
        );

        return new Response(json_encode($response));
    }

    /**
     * @Route("/ajax/delMaterialOffer", name="ajaxDelOffer")
     * @param Request $request
     * @return Response
     */
    public function delMaterialOffer(Request $request){

        try {
            $id = $request->get("id");
            $pieceId = $request->get("pieceId");
        } catch (Exception $e) {
            $response = array("code" => 404);
            return new Response(json_encode($response));
        }

        $em = $this->getDoctrine()->getManager();
        $materialOffer = $em->getRepository('AppBundle:material\MaterialOffer')
            ->find($id);

        $materialPiece = $em->getRepository('AppBundle:material\MaterialPiece')
            ->find($pieceId);

        if (!$materialOffer && !$materialPiece) {
            $response = array("code" => 404);
            return new Response(json_encode($response));
        }

        $em->remove($materialOffer);
        $em->flush();

        $offerIds = $materialPiece->getOffersIds();
        if (strpos($offerIds, ' ; '.$id) !== false) {
            $offerIds=str_replace(" ; ".$id,"",$offerIds);
        } else if (strpos($offerIds, $id.' ; ') !== false) {
            $offerIds=str_replace($id." ; ","",$offerIds);
        } else if (strpos($offerIds, $id) !== false) {
            $offerIds=str_replace($id,"",$offerIds);
        }
        $materialPiece->setOffersIds($offerIds);
        $em->flush();

        $response = array("code" => 100,
            "success" => true,
        );

        return new Response(json_encode($response));
    }
}