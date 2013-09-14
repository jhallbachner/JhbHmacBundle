<?php

namespace Jhb\HmacBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class ApiRequestToken extends AbstractToken
{
    public $date;
    public $request;
    public $publicKey;
    public $signature;
    public $method;
    public $path;

    public function getCredentials()
    {
        return '';
    }
}