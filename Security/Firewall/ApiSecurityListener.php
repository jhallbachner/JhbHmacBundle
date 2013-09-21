<?php

namespace Jhb\HmacBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Jhb\HmacBundle\Security\Authentication\Token\ApiRequestToken;
use Jhb\HmacBundle\Services\SignatureEncoder;

use FOS\RestBundle\EventListener\BodyListener; // optional FOSRestBundle integration

class ApiSecurityListener implements ListenerInterface
{
    protected $securityContext;
    protected $authenticationManager;
    protected $encoder;
    protected $bodyListener;

    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, SignatureEncoder $encoder, BodyListener $listener = null)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->encoder = $encoder;

        if(isset($listener)) {
            $this->bodyListener = $listener;
        }
    }

    public function handle(GetResponseEvent $event)
    {
        if(isset($this->bodyListener)) {
            $this->bodyListener->onKernelRequest($event);
        }

        $request = $event->getRequest();

        $data = $this->encoder->prepareRequestData($request, true);

        $token = new ApiRequestToken();
        foreach(array('method', 'path', 'request', 'signature', 'publicKey') as $field) {
            $token->$field = isset($data[$field]) ? $data[$field] : null;
        }

        if(is_null($token->signature) && is_null($token->publicKey)) {
            return $this->securityContext->setToken($token);
        }

        try {
            $returnValue = $this->authenticationManager->authenticate($token);

            if($returnValue instanceof TokenInterface) {
                return $this->securityContext->setToken($returnValue);
            } elseif ($returnValue instanceof Response) {
                return $event->setResponse($returnValue);
            }
        } catch (AuthenticationException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        }

        $response = new Response();
        $response->setStatusCode(403);
        $event->setResponse($response);
    }
}
