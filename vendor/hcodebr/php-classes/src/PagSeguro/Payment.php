<?php

    namespace Hcode\PagSeguro;

    use DOMDocument;
    use Hcode\PagSeguro\Payment\Method;

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
        
        public function __construct(string $reference, Sender $sender, Shipping $shipping, float $extraAmount = 0)
        {
            
            $this->sender = $sender;
            $this->shipping = $shipping;
            $this->reference = $reference;
            $this->extraAmount = number_format($extraAmount, 2, ".", "");
        }

        public function addItem(Item $item)
        {
            array_push($this->items, $item);
        }

        public function setCreditCard(CreditCard $creditCard)
        {
            $this->creditCard = $creditCard;
            $this->method = Method::CREDIT_CARD;
        }

        public function setBank(Bank $bank)
        {
            $this->bank = $bank;
            $this->method = Method::DEBIT;
        }

        public function setBoleto()
        {
            $this->method = Method::BOLETO;
        }

        public function getDOMDocument():DOMDocument
        {
            $dom = new DOMDocument("1.0", "ISO-8859-1");

            $payment = $dom->createElement("payment");
            $payment = $dom->appendChild($payment);

            $mode = $dom->createElement("mode", $this->mode);
            $mode = $payment->appendChild($mode);

            $currency = $dom->createElement("currency", $this->currency);
            $currency = $payment->appendChild($currency);

            $notificationUrl = $dom->createElement("notificationURL", Config::NOTIFICATION_URL);
            $notificationUrl = $payment->appendChild($notificationUrl);

            $receiverEmail = $dom->createElement("receiverEmail", Config::SANDBOX_EMAIL);
            $receiverEmail = $payment->appendChild($receiverEmail);

            $sender = $this->sender->getDOMElement();
            $sender = $dom->importNode($sender, true);
            $sender = $payment->appendChild($sender);

            $items = $dom->createElement("items");
            $items = $payment->appendChild($items);

            foreach ($this->items as $item_) {
                
                $item = $item_->getDOMElement();
                $item = $dom->importNode($item, true);
                $item = $items->appendChild($item);
            }

            $reference = $dom->createElement("reference", $this->reference);
            $reference = $payment->appendChild($reference);

            $shipping = $this->shipping->getDOMElement();
            $shipping = $dom->importNode($shipping, true);
            $shipping = $payment->appendChild($shipping);

            $extraAmount = $dom->createElement("extraAmount", $this->extraAmount);
            $extraAmount = $payment->appendChild($extraAmount);

            $method = $dom->createElement("method", $this->method);
            $method = $payment->appendChild($method);

            switch ($this->method) {
                case Method::CREDIT_CARD:
                    
                    $creditCard = $this->creditCard->getDOMElement();
                    $creditCard = $dom->importNode($creditCard, true);
                    $creditCard = $payment->appendChild($creditCard);

                break;

                case Method::DEBIT:

                break;
            }

            return $dom;
        }
    }