<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 28.03.17
 * Time: 17:58
 */

namespace AppBundle\Security;

use AppBundle\model\ldapCon\LDAPService;
use AppBundle\model\usersLDAP\UserNotUnique;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use AppBundle\model\usersLDAP\User;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Session\Session;

class LDAPUserProvider implements UserProviderInterface
{

    private $session;
    private $logger;
    private $ldapFrontend;

    public function __construct(LDAPService $LDAPService , Logger $logger, Session $session)
    {
        $this->session = $session;
        $this->logger = $logger;
        $this->ldapFrontend = $LDAPService;
    }

    public function loadUserByUsername($username)
    {
        /** @var User $object */
        $user  = null;
        try {
            $user = $this->ldapFrontend->getUserByGivenname($username);
        }
        catch (UserNotUnique $e)
        {
            $user = null;
        }

        if ($user != null) {
            // skip the "{SSHA}"
            $b64 = substr($user->hashedPassword, 6);

            // base64 decoded
            $b64_dec = base64_decode($b64);

            // the salt (given it is a 8byte one)
            $salt = substr($b64_dec, -8);
            // the sha1 part
            $sha = substr($b64_dec, 0,20);

            $roles = array();
            array_push($roles,"ROLE_NORMAL");
            if($user->getStamm() != "") array_push($roles,"ROLE_STMM_".$user->getStamm());
            if($this->ldapFrontend->getAllGroups("stavo")[0]->isDNMember($user->getDN())) array_push($roles,"ROLE_STAVO") ;
            if($this->ldapFrontend->getAllGroups("buvo")[0]->isDNMember($user->getDN())) array_push($roles,"ROLE_BUVO");

            return new AuthUser($username, $sha, $salt, $roles);
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof AuthUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return AuthUser::class === $class;
    }
}