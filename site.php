<?php

$app->get('/', function () {

    $products = Hcode\Model\Product::listAll();

    $page = new Hcode\Page();

    $page->setTpl("index", array(
        "products" => Hcode\Model\Product::checkList($products)
    ));
});

$app->get("/categories/:idcategory", function ($idcategory) {
    
    $page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

    $category = new Hcode\Model\Category();

    $category->get((int)$idcategory);

    $pagination = $category->getProductsPage($page);

    $pages = array();

    for ($i=1; $i <= $pagination["pages"]; $i++) {
        array_push($pages, array(
            "link" => "/phpstore/categories/".$category->getidcategory()."?page=".$i,
            "page" => $i
        ));
    }

    $page = new Hcode\Page();

    $page->setTpl("category", array(
        "category" => $category->getValues(),
        "products" => $pagination["data"],
        "pages" => $pages
    ));
});

$app->get("/products/:desurl", function($desurl){

    $product = new Hcode\Model\Product();

    $product->getFromUrl($desurl);

    $page = new Hcode\Page();

    $page->setTpl("product-detail", array(
        "product" => $product->getValues(),
        "categories" => $product->getCategories()
    ));
});

$app->get("/cart", function(){

    $cart = Hcode\Model\Cart::getFromSession();
    
    $page = new Hcode\Page();

    $page->setTpl("cart", array(
        "cart" => $cart->getValues(),
        "products" => $cart->getProducts(),
        "error" => Hcode\Model\Cart::getMsgError()
    ));
});

$app->get("/cart/:idproduct/add", function($idproduct){
    $product = new Hcode\Model\Product();

    $product->get((int)$idproduct);

    $cart = Hcode\Model\Cart::getFromSession();

    $qtd = (isset($_GET["qtd"])) ? (int)$_GET["qtd"] : 1;

    for ($i=0; $i < $qtd; $i++) { 
        $cart->addProduct($product);
    }

    header("Location: /phpstore/cart");
    exit();
});

$app->get("/cart/:idproduct/minus", function($idproduct){
    $product = new Hcode\Model\Product();

    $product->get((int)$idproduct);

    $cart = Hcode\Model\Cart::getFromSession();

    $cart->removeProduct($product);

    header("Location: /phpstore/cart");
    exit();
});

$app->get("/cart/:idproduct/remove", function($idproduct){
    $product = new Hcode\Model\Product();

    $product->get((int)$idproduct);

    $cart = Hcode\Model\Cart::getFromSession();

    $cart->removeProduct($product, true);

    header("Location: /phpstore/cart");
    exit();
});

$app->post("/cart/freight", function(){

    $cart = Hcode\Model\Cart::getFromSession();

    $cart->setFreight($_POST['zipcode']);

    header("Location: /phpstore/cart");
    exit();
});

$app->get("/checkout", function(){

    Hcode\Model\User::verifyLogin(false);

    $cart = Hcode\Model\Cart::getFromSession();

    $address = new Hcode\Model\Address();

    $page = new Hcode\Page();

    $page->setTpl("checkout", array(
        "cart" => $cart->getValues(),
        "address" => $address->getValues()
    ));
});

$app->get("/login", function(){

    $page = new Hcode\Page();

    $page->setTpl("login", array(
        "error" => Hcode\Model\User::getError()
    ));
});

$app->post("/login", function(){

    try{

        Hcode\Model\User::login($_POST['login'], $_POST['password']);

    } catch(Exception $e){

        Hcode\Model\User::setError($e->getMessage());
    }

    header("Location: /phpstore/checkout");
    exit();
});

$app->get("/logout", function(){

    \Hcode\Model\User::logout();

    header("Location: /phpstore/login");
    exit();
});