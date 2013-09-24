<?php

namespace Jhb\HmacBundle\Interfaces;

interface HmacUserProviderInterface
{
    public function loadUserByUsername($username);

    public function loadUserByPublicKey($publicKey);
}