<?php

use \Hcode\Model\User;
use \Hcode\Model\Product;

$app->get("/admin/products", function (){
    User::verifyLogin();

    $products = Product::listAll();

    $page = new Hcode\PageAdmin();

    $page->setTpl("products", array(
        "products" => $products
    ));
});