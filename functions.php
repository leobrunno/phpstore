<?php

function formatPrice(float $vlprice)
{
    return number_format($vlprice, 2 , ",", ".");
}

function checkLogin($inadmin = true)
{
    return \Hcode\Model\User::checkLogin($inadmin);
}

function getUserName()
{
    $user = \Hcode\Model\User::getFromSession();

    return $user->getdesperson();
}