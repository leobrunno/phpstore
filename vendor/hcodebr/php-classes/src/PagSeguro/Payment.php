<?php

    namespace Hcode\PagSeguro;

    class Payment
    {

        private $mode = "default";
        private $currency = "BRL";
        private $extraAmount = 0;
        private $reference = "";
        private $items = array();
        private $sender;
        private $shipping;
        private $method;
        private $creditCard;
        private $bank;
        
    }