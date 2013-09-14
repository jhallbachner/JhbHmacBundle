<?php

namespace Jhb\HmacBundle\Services;

class SignatureEncoder
{
    protected $hashMethod;
    protected $requireDate;
    protected $timeframe;
    protected $dateField;
    protected $keyField;
    protected $signatureField;
    protected $requireHeader;

    public function __construct($hashMethod, $requireDate, $timeframe, $dateField, $keyField, $signatureField, $requireHeader)
    {
        $this->hashMethod = $hashMethod;
        $this->requireDate = $requireDate;
        $this->timeframe = $timeframe;
        $this->dateField = $dateField;
        $this->keyField = $keyField;
        $this->signatureField = $signatureField;
        $this->requireHeader = $requireHeader;
    }

    public function encode($method, $resource, $request, $secretKey)
    {
        uksort($request, 'strnatcasecmp');
        $stringToSign = $method . "\n" . $resource . "\n";
        foreach($request as $key => $value) {
            if(is_array($value)) {
                $list = '';
                foreach($value as $subvalue) {
                    $list .= (string) $subvalue;
                }
                $stringToSign .= "$key: $list\n";
            } else {
                $stringToSign .= "$key: $value\n";
            }
        }

        $signature = urlencode(base64_encode(hash_hmac($this->hashMethod, $stringToSign, $secretKey, true)));

        return $signature;
    }

    public function prepareRequestData($request, $restrictions = true)
    {
        $header = ($restrictions && $this->requireHeader);
        $datecheck = ($restrictions && $this->requireDate);

        $data = array();

        if($request->headers->has($this->signatureField) && $request->headers->has($this->keyField)) {
            $signature = urldecode($request->headers->get($this->signatureField));
            $key = $request->headers->get($this->keyField);
        } elseif(!$header && $request->getMethod() == 'GET' && $request->query->has($this->signatureField) && $request->query->has($this->keyField)) {
            $signature = $request->query->get($this->signatureField);
            $key = $request->query->get($this->keyField);
        } elseif(!$header && $request->request->has($this->signatureField) && $request->request->has($this->keyField)) {
            $signature = $request->request->get($this->signatureField);
            $key = $request->request->get($this->keyField);
        } else {
            return false;
        }

        $data['publicKey'] = $key;
        $data['signature'] = $signature;
        $data['method'] = $request->getMethod();
        $data['path'] = $request->getPathInfo();

        if($data['method'] == 'GET') {
            $data['request'] = $request->query->all();
        } else {
            $data['request'] = $request->request->all();
        }

        if($datecheck && !isset($data['request'][$this->dateField]))
            return false;

        if(!isset($data['request'][$this->keyField])) {
            $data['request'][$this->keyField] = $key;
        }
        unset($data['request'][$this->signatureField]);

        return $data;
    }

    public function checkDate($request)
    {
        if(!$this->requireDate)
            return true;

        if(!isset($request[$this->dateField]))
            return false;

        $time = strtotime($request[$this->dateField]);

        return ( abs($time - time()) < $this->timeframe );
    }
}
