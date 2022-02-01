<?php

$app->get("/admin/orders", function(){

    Hcode\Model\User::verifyLogin();

    $page = new Hcode\PageAdmin();

    $page->setTpl("orders", array(
        "orders" => Hcode\Model\Order::listAll()
    ));
});

$app->get("/admin/orders/:idorder/delete", function($id_order){

    Hcode\Model\User::verifyLogin();

    $order = new Hcode\Model\Order();

    $order->get((int) $id_order);

    $order->delete();

    header("Location: /phpstore/admin/orders");
    exit();
});