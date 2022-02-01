<?php

function formatPrice($vlprice)
{
    if(!$vlprice > 0) $vlprice = 0;
    
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

function getCartNrQtd()
{
    $cart = Hcode\Model\Cart::getFromSession();

    $totals = $cart->getProductsTotals();

    return $totals['nrqtd'];
}

function getCartVlSubTotal()
{
    $cart = Hcode\Model\Cart::getFromSession();

    $totals = $cart->getProductsTotals();

    return formatPrice($totals['vlprice']);
}

function formatDate($date)
{
    return date('d/m/Y', strtotime($date));
}