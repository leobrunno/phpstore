<?php

use Hcode\PagSeguro\Config;

$app->get('/payment/pagseguro', function () {

    $client = new \GuzzleHttp\Client();

    $res = $client->request('POST', Config::getUrlSession()."?".http_build_query(Config::getAuthentication()), array(
        "verify" => false
    ));

    echo $res->getBody()->getContents();
});