<?php

namespace Jhb\HmacBundle\Security\Authentication\Provider;

use Jhb\HmacBundle\Exception\InvalidProviderException;
use Jhb\HmacBundle\Interfaces\HmacUserInterface;
use Jhb\HmacBundle\Interfaces\HmacUserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Jhb\HmacBundle\Security\Authentication\Token\ApiRequestToken;
use Jhb\HmacBundle\Services\SignatureEncoder;

class ApiSecurityProvider implements AuthenticationProviderInterface
{

    private $userProvider;
    private $cacheDir;

    protected $encoder;

    public function __construct(UserProviderInterface $userProvider, SignatureEncoder $encoder)
    {
        $this->userProvider = $userProvider;
        $this->encoder = $encoder;

        if(!($userProvider instanceof HmacUserProviderInterface)) {
            throw new InvalidProviderException('User provider must implement HmacUserProviderInterface.');
        }
    }

    public function authenticate(TokenInterface $token)
    {
        if(!isset($token->publicKey) || !isset($token->signature)) {
            return $token;
        }

        $user = $this->userProvider->loadUserByPublicKey($token->publicKey);

        if($user && $this->checkRequestSignature($token->method, $token->path, $token->request, $user->getSecretKey(), $token->signature)) {
            $authenticatedToken = new ApiRequestToken($user->getRoles());
            $authenticatedToken->setUser($user);
            $authenticatedToken->setAuthenticated(true);

            return $authenticatedToken;
        }

        throw new AuthenticationException('The key-based API authentication failed due to inauthentic credentials.');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof ApiRequestToken;
    }

    protected function checkRequestSignature($method, $path, $request, $secretKey, $signature)
    {
        if(!$this->encoder->checkDate($request))
            throw new AuthenticationException('The key-based API authentication failed due to an out-of-bounds date.');

        $testsignature = $this->encoder->encode($method, $path, $request, $secretKey);

        return ($testsignature == urlencode($signature));
    }
}