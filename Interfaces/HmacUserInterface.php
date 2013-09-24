<?php

namespace Jhb\HmacBundle\Interfaces;

interface HmacUserInterface
{
    public function getPublicKey();

    public function getSecretKey();
}