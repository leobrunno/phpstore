<?php

$app->get('/', function () {

    $products = Hcode\Model\Product::listAll();

    $page = new Hcode\Page();

    $page->setTpl("index", array(
        "products" => Hcode\Model\Product::checkList($products)
    ));
});

$app->get("/categories/:idcategory", function ($idcategory) {
    
    $category = new Hcode\Model\Category();

    $category->get((int)$idcategory);

    $page = new Hcode\Page();

    $page->setTpl("category", array(
        "category" => $category->getValues(),
        "products" => array()
    ));
});