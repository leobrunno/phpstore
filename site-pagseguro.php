<?php

use \Hcode\PagSeguro\Config;
use \GuzzleHttp\Client;
use \Hcode\Page;
use \Hcode\Model\User;
use \Hcode\Model\Order;

$app->get("/payment", function(){

    User::verifyLogin(false);

    $order = new Order();

    $order->getFromSession();

    $years = array();

    for ($i = date("Y"); $i < date("Y") + 14; $i++) {

        array_push($years, $i);        
    }

    $page = new Page(array(
        "footer" => false
    ));

    $page->setTpl("payment", array(
        "order" => $order->getValues(),
        "msgError" => Order::getError(),
        "years" => $years,
        "pagseguro" => array(
            "urlJS" => Config::getUrlJS()
        )
    ));
});

$app->get('/payment/pagseguro', function () {

    $client = new Client();

    $res = $client->request('POST', Config::getUrlSession()."?".http_build_query(Config::getAuthentication()), array(
        "verify" => false
    ));

    echo $res->getBody()->getContents();
});