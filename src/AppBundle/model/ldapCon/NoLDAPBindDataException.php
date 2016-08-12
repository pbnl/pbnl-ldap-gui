<?php
namespace AppBundle\model\ldapCon;
namespace AppBundle\model\ldapCon;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Created by PhpStorm.
 * User: paul
 * Date: 10.08.16
 * Time: 20:34
 */
class NoLDAPBindDataException extends Exception
{

    /**
     * NoLDAPBindDataException constructor.
     */
    public function __construct($message, $code = 0, Exception $previous = null) {
        // some code

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}