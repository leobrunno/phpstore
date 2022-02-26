<?php 

    namespace Hcode\PagSeguro;

    class Config 
    {
        const SANDBOX = true;

        const SANDBOX_EMAIL = "seuemail@email.com";
        const PRODUCTION_EMAIL = "";

        const SANDBOX_TOKEN = "seutokensandbox";
        const PRODUCTION_TOKEN = "";

        const SANDBOX_SESSIONS = "https://ws.sandbox.pagseguro.uol.com.br/v2/sessions";
        const PRODUCTION_SESSIONS = "";

        const SANDBOX_URL_JS = "https://stc.sandbox.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js"; 
        const PRODUCTION_URL_JS = "";

        const MAX_INSTALLMENT_NO_INTEREST = 10;
        const MAX_INSTALLMENT = 12;

        const NOTIFICATION_URL = "http://www.phpstore.com/payment/notification";

        public static function getAuthentication():array
        {
            if(Config::SANDBOX === true){

                return array(
                    "email" => Config::SANDBOX_EMAIL,
                    "token" => Config::SANDBOX_TOKEN
                );
            } else {

                return array(
                    "email" => Config::PRODUCTION_EMAIL,
                    "token" => Config::PRODUCTION_TOKEN
                );
            }
        }

        public static function getUrlSession():string
        {
            return (Config::SANDBOX === true) ? Config::SANDBOX_SESSIONS : Config::PRODUCTION_SESSIONS;
        }

        public static function getUrlJS()
        {
            return (Config::SANDBOX === true) ? Config::SANDBOX_URL_JS : Config::PRODUCTION_URL_JS;
        }
    }