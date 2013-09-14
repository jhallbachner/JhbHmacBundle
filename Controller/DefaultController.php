<?php

namespace Jhb\HmacBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/signature")
     * @Template()
     */
    public function indexAction()
    {
        $request = $this->container->get('request');
        $encoder = $this->container->get('jhb_hmac.encoder');
        $userp = $this->container->get('jhb_hmac.user_provider');

        if($request->headers->has('key')) {
            $request->headers->set('signature', ' ');
        } elseif($request->request->has('key')) {
            $request->request->set('signature', ' ');
        } else {
            $request->query->set('signature', ' ');
        }

        $data = $encoder->prepareRequestData($request, false);
        if(!$data) {
            return array('body' => 'Your request was malformed.');
        }

        $path = $request->getPathInfo();
        unset($data['request']['signature']);
        unset($data['signature']);
        $data['path'] = $data['request']['path'];
        unset($data['request']['path']);
        $data['method'] = (isset($data['request']['method']) ? $data['request']['method'] : 'GET');
        unset($data['request']['method']);

        ob_start(); var_dump(time()); var_dump($data); $body = ob_get_clean();

        $user = $userp->loadUserByPublicKey($data['publicKey']);
        $signature = $encoder->encode($data['method'], $data['path'], $data['request'], $user->getSecretKey());

        $body .= '<br><br><b>Signature:</b> ' . $signature;

        return array('body' => $body);
    }
}
