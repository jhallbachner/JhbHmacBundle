<?php

namespace Jhb\HmacBundle\Security\User;

use FOS\UserBundle\Security\UserProvider;
use Jhb\HmacBundle\Interfaces\HmacUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class FosUserHmacProvider extends UserProvider implements HmacUserProviderInterface
{
    public function loadUserByPublicKey($pk)
    {
        $user = $this->userManager->findUserBy(array('publicKey' => $pk));

        if(!$user) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return $user;
    }
}