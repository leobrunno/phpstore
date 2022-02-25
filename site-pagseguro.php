<?php

use \Hcode\PagSeguro\Config;
use \Hcode\Page;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use Hcode\PagSeguro\Address;
use Hcode\PagSeguro\CreditCard\Holder;
use Hcode\PagSeguro\CreditCard\Installment;
use Hcode\PagSeguro\Document;
use Hcode\PagSeguro\Phone;
use Hcode\PagSeguro\Sender;
use Hcode\PagSeguro\Shipping;
use \Hcode\PagSeguro\Transporter;

$app->post("/payment/credit", function(){

    User::verifyLogin(false);

    $order = new Order();

    $order->getFromSession();

    $order->get((int) $order->getidorder());

    $address = $order->getAdress();

    $cart = $order->getCart();

    $cpf = new Document(Document::CPF, $_POST['cpf']);

    $phone = new Phone($_POST['ddd'], $_POST['phone']);

    $address = new Address(
        $address->getdesaddress(),
        $address->getdesnumber(),
        $address->getdescomplement(),
        $address->getdesdistrict(),
        $address->getdeszipcode(),
        $address->getdescity(),
        $address->getdesstate(),
        $address->getdescountry()
    );

    $birthDate = new DateTime($_POST['birth']);
    
    $sender = new Sender(
        $order->getdesperson(), 
        $cpf,
        $birthDate, 
        $phone, 
        $order->getdesemail(), 
        $_POST['hash']
    );

    $holder = new Holder($order->getdesperson(), $cpf, $birthDate, $phone);

    $shipping = new Shipping($address, (float) $cart->getvlfreight(), Shipping::PAC);

    $installment = new Installment((int) $_POST['installments_qtd'], (float) $_POST['installments_value']);
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
            "maxInstallmentNoInterest" => Config::MAX_INSTALLMENT_NO_INTEREST,
            "maxInstallment" => Config::MAX_INSTALLMENT
        )
    ));
});