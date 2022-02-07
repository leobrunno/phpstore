<?php


use \Hcode\Model\User;
use \Hcode\Model\Category;

$app->get("/admin/categories", function () {
    User::verifyLogin();

    $search = (isset($_GET['search'])) ? $_GET['search'] : "";
    $page = (isset($_GET['page'])) ? (int) $_GET['page'] : 1;

    if(!empty($search)){

        $pagination = Category::getPageSearch($search, $page);
    } else {

        $pagination = Category::getPage($page);
    }

    $pages = array();

    for ($i=0; $i < $pagination['pages']; $i++) { 
        
        array_push($pages, array(
            "href" => "/phpstore/admin/users?".http_build_query(array(
                "page" => $i + 1,
                "search" => $search
            )),
            "text" => $i + 1
        ));
    }

    $page = new Hcode\PageAdmin();

    $page->setTpl("categories", array(
        "categories" => $pagination['data'],
        "search" => $search,
        "pages" => $pages
    ));
});

$app->get("/admin/categories/create", function () {
    User::verifyLogin();

    $page = new Hcode\PageAdmin();

    $page->setTpl("categories-create");
});

$app->post("/admin/categories/create", function () {
    User::verifyLogin();

    $category = new Category();
    $category->setData($_POST);
    $category->save();

    header("Location: /phpstore/admin/categories");
    exit();
});

$app->get("/admin/categories/:idcategory/delete", function ($idcategory) {
    User::verifyLogin();

    $category = new Category();

    $category->get((int)$idcategory);

    $category->delete();

    header("Location: /phpstore/admin/categories");
    exit();
});

$app->get("/admin/categories/:idcategory", function ($idcategory) {
    User::verifyLogin();

    $category = new Category();
    $category->get((int)$idcategory);

    $page = new Hcode\PageAdmin();

    $page->setTpl("categories-update", array(
        "category" => $category->getValues()
    ));
});

$app->post("/admin/categories/:idcategory", function ($idcategory) {
    User::verifyLogin();

    $category = new Category();
    $category->get((int)$idcategory);

    $category->setData($_POST);
    $category->save();

    header("Location: /phpstore/admin/categories");
    exit();
});

$app->get("/admin/categories/:idcategory/products", function ($idcategory){
    User::verifyLogin();

    $category = new Category();

    $category->get((int)$idcategory);

    $page = new Hcode\PageAdmin();

    $page->setTpl("categories-products", array(
        "category" => $category->getValues(),
        "productsRelated" => $category->getProducts(),
        "productsNotRelated" => $category->getProducts(false)
    ));
});

$app->get("/admin/categories/:idcategory/products/:idproduct/add", function ($idcategory, $idproduct){
    User::verifyLogin();

    $category = new Category();

    $category->get((int)$idcategory);

    $product = new Hcode\Model\Product();

    $product->get((int)$idproduct);

    $category->addproduct($product);

    header("Location: /phpstore/admin/categories/".$idcategory."/products");
    exit();
});

$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function ($idcategory, $idproduct){
    User::verifyLogin();

    $category = new Category();

    $category->get((int)$idcategory);

    $product = new Hcode\Model\Product();

    $product->get((int)$idproduct);

    $category->removeproduct($product);

    header("Location: /phpstore/admin/categories/".$idcategory."/products");
    exit();
});
