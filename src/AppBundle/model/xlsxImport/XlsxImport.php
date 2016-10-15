<?php

/**
 * Created by PhpStorm.
 * User: paul
 * Date: 04.10.16
 * Time: 01:32
 */

namespace AppBundle\model\xlsxImport;

use AppBundle\model\usersLDAP\Organisation;
use Symfony\Component\Validator\Constraints as Assert;

class XlsxImport
{

    /**
     * @Assert\File(mimeTypes={ "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" })
     */

    public $xlsxFile;
    public $xlsxFilePath;
    public $ouGroup;
    public $stamm;


    public function parse($phpExel, Organisation $org)
    {
        $exelObject = $phpExel->createPHPExcelObject($this->xlsxFilePath);
        $sheetData = $exelObject->getSheet(0)->toArray(null,true,true,true);

        $userManager = $org->getUserManager();

        foreach($sheetData as $rowName => $rowValue)
        {
            //Rows 1 2 and 3 are rows with titels
            if($rowName >= 4 && $rowValue["B"] != "") {
                $user = $userManager->getEmptyUser();
                $user->setFirstName($rowValue["B"]);
                $user->setSecondName($rowValue["A"]);
                if($rowValue["C"] != "") $user->setGivenName($rowValue["C"]);
                else $user->setGivenName($rowValue["B"]);
                if($rowValue["K"] != "") $user->street = $rowValue["K"];
                if($rowValue["L"] != "") $user->postalCode = $rowValue["L"];
                if($rowValue["M"] != "") $user->l = $rowValue["M"];
                if($rowValue["N"] != "") $user->telephoneNumber = $rowValue["N"];
                if($rowValue["O"] != "") $user->mobile = $rowValue["O"];
                $user->ouGroup = $this->ouGroup;
                $user->setStamm($this->stamm);

                //Create user
                $userManager->createNewUser($user);
            }
        }

    }
}