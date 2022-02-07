<?php


use \Hcode\Model\User;

$app->get('/admin/users', function () {

    User::verifyLogin();

    $search = (isset($_GET['search'])) ? $_GET['search'] : "";
    $page = (isset($_GET['page'])) ? (int) $_GET['page'] : 1;

    if(!empty($search)){

        $pagination = User::getPageSearch($search, $page);
    } else {

        $pagination = User::getPage($page);
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

    $page->setTpl("users", array(
        "users" => $pagination['data'],
        "search" => $search,
        "pages" => $pages
    ));
});

$app->get('/admin/users/create', function () {

    User::verifyLogin();

    $page = new Hcode\PageAdmin();

    $page->setTpl("users-create");
});

$app->get('/admin/users/:iduser/delete', function ($iduser) {

    User::verifyLogin();

    $user = new User();

    $user->get((int)$iduser);

    $user->delete();

    header("Location: /phpstore/admin/users");
    exit();
});

$app->get('/admin/users/:iduser', function ($iduser) {

    User::verifyLogin();

    $user = new User();
    $user->get((int)$iduser);

    $page = new Hcode\PageAdmin();

    $page->setTpl("users-update", array(
        "user" => $user->getValues()
    ));
});

$app->post('/admin/users/create', function () {

    User::verifyLogin();

    $user = new User();

    $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

    $user->setData($_POST);

    $user->save();

    header("Location: /phpstore/admin/users");
    exit();
});

$app->post('/admin/users/:iduser', function ($iduser) {

    User::verifyLogin();

    $user = new User();

    $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

    $user->get((int)$iduser);
    $user->setData($_POST);
    $user->update();

    header("Location: /phpstore/admin/users");
    exit();
});
