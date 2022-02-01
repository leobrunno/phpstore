<?php

$app->get("/admin/orders/:idorder/delete", function($id_order){

    Hcode\Model\User::verifyLogin();

    $order = new Hcode\Model\Order();

    $order->get((int) $id_order);

    $order->delete();

    header("Location: /phpstore/admin/orders");
    exit();
});

$app->get("/admin/orders/:idorder", function($id_order){

    Hcode\Model\User::verifyLogin();

    $order = new Hcode\Model\Order();

    $order->get((int) $id_order);

    $cart = $order->getCart();

    $page = new Hcode\PageAdmin();

    $page->setTpl("order", array(
        "order" => $order->getValues(),
        "cart" => $cart->getValues(),
        "products" => $cart->getProducts()
    ));
});

$app->get("/admin/orders", function(){

    Hcode\Model\User::verifyLogin();

    $page = new Hcode\PageAdmin();

    $page->setTpl("orders", array(
        "orders" => Hcode\Model\Order::listAll()
    ));
});