<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 21.08.16
 * Time: 21:41
 */

namespace AppBundle\model;


class ArrayMethods
{
    public static function valueToKeyAndValue($array)
    {
        $out = Array();
        foreach ($array as $value)
        {
            $out[$value] = $value;
        }
        return $out;
    }
}