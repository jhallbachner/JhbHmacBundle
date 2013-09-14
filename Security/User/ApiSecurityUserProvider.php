<?php

namespace Jhb\HmacBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class ApiSecurityUserProvider implements UserProviderInterface
{
    protected $users;
    protected $usersByKey = array();

    public function __construct(array $users = array())
    {
        foreach ($users as $username => $attributes) {
            if(!isset($attributes['publicKey']) || !isset($attributes['secretKey']) ) {
                continue;
            }

            $roles = isset($attributes['roles']) ? $attributes['roles'] : array();
            $user = new ApiSecurityUser($username, $attributes['secretKey'], $attributes['publicKey'], $roles);

            $this->createUser($user);
        }
    }

    public function createUser($user)
    {
        if (isset($this->users[strtolower($user->getUsername())])) {
            throw new \LogicException('Another user with the same username already exists.');
        }

        if (isset($this->usersByKey[$user->getPublicKey()])) {
            throw new \LogicException('Another user with the same public key already exists.');
        }

        $this->users[strtolower($user->getUsername())] = $user;

        $this->usersByKey[$user->getPublicKey()] = $user->getUsername();
    }

    public function loadUserByUsername($username)
    {
        if (!isset($this->users[strtolower($username)])) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        $user = $this->users[strtolower($username)];

        return new ApiSecurityUser($user->getUsername(), $user->getSecretKey(), $user->getPublicKey(), $user->getRoles());
    }

    public function loadUserByPublicKey($key)
    {
        if(!isset($this->usersByKey[$key])) {
            throw new UsernameNotFoundException(sprintf('Public key "%s" does not exist.', $key));
        }

        return $this->loadUserByUsername($this->usersByKey[$key]);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof ApiSecurityUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Jhb\HmacBundle\Security\User\ApiSecurityUser';
    }


}