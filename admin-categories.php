<?php


use \Hcode\Model\User;
use \Hcode\Model\Category;

$app->get("/admin/categories", function () {
    User::verifyLogin();

    $page = new Hcode\PageAdmin();

    $categories = Category::listAll();

    $page->setTpl("categories", array(
        "categories" => $categories
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

$app->get("/categories/:idcategory", function ($idcategory) {
    $category = new Category();

    $category->get((int)$idcategory);

    $page = new Hcode\Page();

    $page->setTpl("category", array(
        "category" => $category->getValues(),
        "products" => array()
    ));
});
