<?php

namespace Jhb\HmacBundle\Security\User;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ApiSecurityUser implements UserInterface
{
    protected $username;
    protected $secretKey;
    protected $publicKey;
    protected $roles = array();

    public function __construct($username, $secretKey, $publicKey, $roles = array())
    {
        $this->username = $username;
        $this->secretKey = $secretKey;
        $this->publicKey = $publicKey;
        $this->roles = $roles;
    }

    public function __toString()
    {
        return $this->username . '/' . $this->publicKey;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return '';
    }

    public function getSalt()
    {
        return '';
    }

    public function getSecretKey()
    {
        return $this->secretKey;
    }

    public function getPublicKey()
    {
        return $this->publicKey;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
        unset($this->secretKey);
    }

    public function equals(UserInterface $account)
    {
        if(!$account instanceof ApiSecurityUser) {
            return false;
        }

        if($account->getUsername() !== $this->username)
            return false;

        if($account->getPublicKey() !== $this->publicKey)
            return false;

        if($account->getSecretKey() !== $this->secretKey)
            return false;

        return true;
    }

}