<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 04.10.16
 * Time: 18:42
 */

namespace AppBundle\model;


class StringMethods
{
    public static function replaceUmlaute($string)
    {
        $ersetzen = array( 'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss', ' ' => '_');
        return strtr( $string, $ersetzen );
    }

}