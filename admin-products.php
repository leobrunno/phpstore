<?php

use \Hcode\Model\User;
use \Hcode\Model\Product;

$app->get("/admin/products", function (){
    User::verifyLogin();

    $search = (isset($_GET['search'])) ? $_GET['search'] : "";
    $page = (isset($_GET['page'])) ? (int) $_GET['page'] : 1;

    if(!empty($search)){

        $pagination = Product::getPageSearch($search, $page);
    } else {

        $pagination = Product::getPage($page);
    }

    $pages = array();

    for ($i=0; $i < $pagination['pages']; $i++) { 
        
        array_push($pages, array(
            "href" => "/phpstore/admin/products?".http_build_query(array(
                "page" => $i + 1,
                "search" => $search
            )),
            "text" => $i + 1
        ));
    }

    $page = new Hcode\PageAdmin();

    $page->setTpl("products", array(
        "products" => $pagination['data'],
        "search" => $search,
        "pages" => $pages
    ));
});

$app->get("/admin/products/create", function (){
    User::verifyLogin();

    $page = new Hcode\PageAdmin();

    $page->setTpl("products-create");
});

$app->post("/admin/products/create", function (){
    User::verifyLogin();

    $product = new Product();

    $product->setData($_POST);

    $product->save();

    header("Location: /phpstore/admin/products");
    exit();
});

$app->get("/admin/products/:idproduct", function ($idproduct){
    User::verifyLogin();

    $product = new Product();
    
    $product->get((int)$idproduct);

    $page = new Hcode\PageAdmin();

    $page->setTpl("products-update", array(
        "product" => $product->getValues()
    ));
});

$app->post("/admin/products/:idproduct", function ($idproduct){
    User::verifyLogin();

    $product = new Product();
    
    $product->get((int)$idproduct);

    $product->setData($_POST);

    $product->save();

    $product->setPhoto($_FILES["file"]);

    header("Location: /phpstore/admin/products");
    exit();
});

$app->get("/admin/products/:idproduct/delete", function ($idproduct){
    User::verifyLogin();

    $product = new Product();
    
    $product->get((int)$idproduct);

    $product->delete();

    header("Location: /phpstore/admin/products");
    exit();
});