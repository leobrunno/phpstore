<?php

$app->get("/admin/orders/:idorder/delete", function($id_order){

    Hcode\Model\User::verifyLogin();

    $order = new Hcode\Model\Order();

    $order->get((int) $id_order);

    $order->delete();

    header("Location: /phpstore/admin/orders");
    exit();
});

$app->get("/admin/orders/:idorder/status", function($id_order){

    Hcode\Model\User::verifyLogin();

    $order = new Hcode\Model\Order();

    $order->get((int) $id_order);

    $page = new Hcode\PageAdmin();

    $page->setTpl("order-status", array(
        "order" => $order->getValues(),
        "status" => Hcode\Model\OrderStatus::listAll(),
        "msgError" => Hcode\Model\Order::getError(),
        "msgSuccess" => Hcode\Model\Order::getSuccess()
    ));
});

$app->post("/admin/orders/:idorder/status", function($id_order){

    Hcode\Model\User::verifyLogin();

    if(!isset($_POST['idstatus']) || !(int) $_POST['idstatus'] > 0){

        Hcode\Model\Order::setError("Informe um status valido");
        
        header("Location: /phpstore/admin/orders/".$id_order."/status");
        exit();
    }

    $order = new Hcode\Model\Order();

    $order->get((int) $id_order);

    $order->setidstatus((int) $_POST['idstatus']);

    $order->save();

    Hcode\Model\Order::setSuccess("Status atualizado com sucesso");

    header("Location: /phpstore/admin/orders/".$id_order."/status");
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

    $search = (isset($_GET['search'])) ? $_GET['search'] : "";
    $page = (isset($_GET['page'])) ? (int) $_GET['page'] : 1;

    if(!empty($search)){

        $pagination = Hcode\Model\Order::getPageSearch($search, $page);
    } else {

        $pagination = Hcode\Model\Order::getPage($page);
    }

    $pages = array();

    for ($i=0; $i < $pagination['pages']; $i++) { 
        
        array_push($pages, array(
            "href" => "/phpstore/admin/orders?".http_build_query(array(
                "page" => $i + 1,
                "search" => $search
            )),
            "text" => $i + 1
        ));
    }

    $page = new Hcode\PageAdmin();

    $page->setTpl("orders", array(
        "orders" => $pagination['data'],
        "search" => $search,
        "pages" => $pages
    ));
});