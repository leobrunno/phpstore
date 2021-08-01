<?php

$app->get('/', function () {

    $products = Hcode\Model\Product::listAll();

    $page = new Hcode\Page();

    $page->setTpl("index", array(
        "products" => Hcode\Model\Product::checkList($products)
    ));
});
