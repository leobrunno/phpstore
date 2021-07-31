<?php

$app->get('/', function () {

    $page = new Hcode\Page();

    $page->setTpl("index");
});
