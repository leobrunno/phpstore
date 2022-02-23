<?php

    namespace Hcode\PagSeguro;

    use DateTime;
    use DOMDocument;
    use DOMElement;
    use Exception;

    class Sender
    {

        private $name;
        private $cpf;
        private $bornDate;
        private $phone;
        private $email;
        private $hash;
        private $ip;

        public function __construct(string $name, Document $cpf, DateTime $bornDate, Phone $phone, string $email, string $hash)
        {
            
            if(!$name){

                throw new Exception("Informe o nome do comprador!");
            }

            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){

                throw new Exception("O email informado não é válido!");
            }

            if(!$hash){

                throw new Exception("Informe a identificação do comprador!");
            }

            $this->name = $name;
            $this->cpf = $cpf;
            $this->bornDate = $bornDate;
            $this->phone = $phone;
            $this->email = $email;
            $this->hash = $hash;
            $this->ip = $_SERVER["REMOTE_ADDR"];
        }

        public function getDOMElement():DOMElement
        {
            $dom = new DOMDocument();

            $sender = $dom->createElement("sender");
            $sender = $dom->appendChild($sender);

            $name = $dom->createElement("name", $this->name);
            $name = $sender->appendChild($name);

            $email = $dom->createElement("email", $this->email);
            $email = $sender->appendChild($email);

            $bornDate = $dom->createElement("bornDate", $this->bornDate->format("d/m/Y"));
            $bornDate = $sender->appendChild($bornDate);

            $documents = $dom->createElement("documents");
            $documents = $sender->appendChild($documents);

            $cpf = $this->cpf->getDOMElement();
            $cpf = $dom->importNode($cpf, true);
            $cpf = $documents->appendChild($cpf);

            $phone = $this->phone->getDOMElement();
            $phone = $dom->importNode($phone, true);
            $phone = $documents->appendChild($phone);

            $hash = $dom->createElement("hash", $this->hash);
            $hash = $sender->appendChild($hash);

            $ip = $dom->createElement("ip", $this->ip);
            $ip = $sender->appendChild($ip);
            
            return $sender;
        }
        
    }