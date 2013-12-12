<?php

namespace Jhb\HmacBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType;

class FosUserRegistrationForm extends RegistrationFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $defaultKey = openssl_random_pseudo_bytes(16);

        $builder->add('publicKey');
        $builder->add('secretKey', null, array('data' => $defaultKey));
    }

    public function getName()
    {
        return 'jhb_hmac_fos_user_registration';
    }
}