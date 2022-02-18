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
    }