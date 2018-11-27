<?php
namespace Expertime\Iclient\Traits;

trait UsersTrait
{

    public function GetUsersByApi($uri)
    {
        $httpHeaders = new \Zend\Http\Headers();
        $httpHeaders->addHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]);

        $request = new \Zend\Http\Request();
        $request->setHeaders($httpHeaders);
        $request->setUri($uri);
        $request->setMethod(\Zend\Http\Request::METHOD_GET);

        $client = new \Zend\Http\Client();
        $options = [
            'adapter'   => 'Zend\Http\Client\Adapter\Curl',
            'curloptions' => [CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_SSL_VERIFYHOST => 0,
            ],
            'maxredirects' => 0,
            'timeout' => 60
        ];
        $client->setOptions($options);

        $response = $client->send($request);
        $users = $response->getBody();
        return $users;
    }
}
