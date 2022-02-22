<?php

use \Hcode\PagSeguro\Config;
use \Hcode\Page;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\PagSeguro\Transporter;

$app->post("/payment/credit", function(){

    User::verifyLogin(false);

    $order = new Order();

    $order->getFromSession();

    $address = $order->getAdress();

    $cart = $order->getCart();
});

$app->get("/payment", function(){

    User::verifyLogin(false);

    $order = new Order();

    $order->getFromSession();

    $years = array();

    for ($i = date("Y"); $i < date("Y") + 14; $i++) {

        array_push($years, $i);        
    }

    $page = new Page();

    $page->setTpl("payment", array(
        "order" => $order->getValues(),
        "msgError" => Order::getError(),
        "years" => $years,
        "pagseguro" => array(
            "urlJS" => Config::getUrlJS(),
            "id" => Transporter::createSession(),
            "maxInstallmentNoInterest" => Config::MAX_INTALLMENT_NO_INTEREST,
            "maxInstallment" => Config::MAX_INTALLMENT
        )
    ));
});