<?php

    namespace Hcode\PagSeguro;

    use Exception;
    use \GuzzleHttp\Client;
    use Hcode\Model\Order;

    class Transporter 
    {
        public static function createSession()
        {
            $client = new Client();

            $res = $client->request('POST', Config::getUrlSession()."?".http_build_query(Config::getAuthentication()), array(
                "verify" => false
            ));

            $xml = simplexml_load_string($res->getBody()->getContents());

            return ((string) $xml->id);
        }

        public static function sendTransaction(Payment $payment)
        {
            $client = new Client();

            $res = $client->request('POST', Config::getUrlTransaction()."?".http_build_query(Config::getAuthentication()), array(
                "verify" => false,
                "headers" => array(
                    "Content-Type" => "application/xml"
                ),
                "body" => $payment->getDOMDocument()->saveXml()
            ));

            $xml = simplexml_load_string($res->getBody()->getContents());

            $order = new Order();

            $order->get((int) $xml->reference);

            $order->setPagSeguroTransactionResponse(
                (string) $xml->code,
                (float) $xml->grossAmount,
                (float) $xml->discountAmount,
                (float) $xml->feeAmount,
                (float) $xml->netAmount,
                (float) $xml->extraAmount,
                (string) $xml->paymentLink
            );

            return $xml;
        }

        public static function getNotification(string $notificationCode, string $notificationType)
        {
            $url = "";

            switch ($notificationType) {
                case 'transaction':
                
                    $url = Config::getNotificationTransactionURL();
                break;
                
                default:
                    
                    throw new Exception("Notificação inválida");
                break;
            }

            $client = new Client();

            $res = $client->request('GET', $url.$notificationCode."?".http_build_query(Config::getAuthentication()), array(
                "verify" => false
            ));

            $xml = simplexml_load_string($res->getBody()->getContents());

            $order = new Order();

            $order->get((int) $xml->reference);

            if($order->getidstatus() !== (int) $xml->status){

                $order->setidstatus((int) $xml->status);
                $order->save();
            }

            $s = DIRECTORY_SEPARATOR;
            $filename = $_SERVER['DOCUMENT_ROOT'] . $s . "res" . $s . "logs" . $s . date("YmdHis") . ".json";

            $file = fopen($filename, "a+");
            fwrite($file, json_encode(array(
                "post" => $_POST,
                "xml" => $xml
            )));
            fclose($file);

            return $xml;
        }
    }