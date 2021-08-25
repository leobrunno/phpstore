<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Model\User;

class Cart extends Model{

    const SESSION = "Cart";
    const SESSION_ERROR = "CartError";

    public static function getFromSession()
    {
        $cart = new Cart();

        if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0){
            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);

        } else {
            $cart->getFromSessionId();

            if(!(int)$cart->getidcart() > 0){
                $data = array(
                    'dessessionid' => session_id()
                );

                if(User::checkLogin(false)){

                    $user = User::getFromSession();

                    $data['iduser'] = $user->getiduser();
                }

                $cart->setData($data);

                $cart->save();

                $cart->setToSession();

            }
        }

        return $cart;
    }

    public function setToSession()
    {

        $_SESSION[Cart::SESSION] = $this->getValues();   

    }

    public function getFromSessionId()
    {
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", array(
            ":dessessionid" => session_id()
        ));

        if(count($results) > 0) {
            $this->setData($results[0]);
        }
    }

    public function get(int $idcart)
    {
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", array(
            ":idcart" => $idcart
        ));

        
        if(count($results) > 0) {
            $this->setData($results[0]);
        }
    }

    public function save()
    {
        $sql = new Sql();

        $results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", array(
            ':idcart' => $this->getidcart(),
            ':dessessionid' => $this->getdessessionid(),
            ':iduser' => $this->getiduser(),
            ':deszipcode' => $this->getdeszipcode(),
            ':vlfreight' => $this->getvlfreight(),
            ':nrdays' => $this->getnrdays(),
        ));

        $this->setData($results[0]);
    }

    public function addProduct(Product $product)
    {
        $sql = new Sql();

        $sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)", array(
            ":idcart" => $this->getidcart(),
            ":idproduct" => $product->getidproduct()
        ));
    }

    public function removeProduct(Product $product, $all = false)
    {
        $sql = new Sql();

        if($all){

            $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved is NULL", array(
                ":idcart" => $this->getidcart(),
                ":idproduct" => $product->getidproduct()
            ));
        } else {

            $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved is NULL LIMIT 1", array(
                ":idcart" => $this->getidcart(),
                ":idproduct" => $product->getidproduct()
            ));

        }
    }

    public function getProducts()
    {
        $sql = new Sql();

        $rowsCart = $sql->select("SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal 
        FROM tb_cartsproducts a
        INNER JOIN tb_products b ON a.idproduct = b.idproduct
        WHERE a.idcart = :idcart AND a.dtremoved IS NULL
        GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
        ORDER BY b.desproduct", array(
            ":idcart" => $this->getidcart()
        ));

        return Product::checkList($rowsCart);
    }

    public function getProductsTotals()
    {
        $sql = new Sql();

        $results = $sql->select("SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight,
        SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd FROM tb_products a
        INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
        WHERE b.idcart = :idcart AND dtremoved IS NULL", array(
            ":idcart" => $this->getidcart(),
        ));

        if(count($results) > 0){
            
            return $results[0];

        } else{

            return array();

        }

    }

    public function setFreight($nrzipcode)
    {
        $nrzipcode = str_replace('-', '', $nrzipcode);

        $totals = $this->getProductsTotals();

        if($totals['nrqtd'] > 0){

            if($totals["vlheight"] < 2) $totals["vlheight"] = 2;
            if($totals["vllength"] < 2) $totals["vllength"] = 16;

            $qs = http_build_query(array(
                "nCdEmpresa" => "",
                "sDsSenha" => "",
                "nCdServico" => "40010",
                "sCepOrigem" => "05859230",
                "sCepDestino" => $nrzipcode,
                "nVlPeso" => $totals["vlweight"],
                "nCdFormato" => "1",
                "nVlComprimento" => $totals["vllength"],
                "nVlAltura" => $totals["vlheight"],
                "nVlLargura" => $totals["vlwidth"],
                "nVlDiametro" => "0",
                "sCdMaoPropria" => "S",
                "nVlValorDeclarado" => $totals["vlprice"],
                "sCdAvisoRecebimento" => "S"
            ));

            $xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);

            $result = $xml->Servicos->cServico;

            if($result->MsgErro != ''){

                Cart::setMsgError($result->MsgErro);
            } else {
                Cart::clearMsgError();
            }

            $this->setnrdays($result->PrazoEntrega);
            $this->setvlfreight(Cart::formatValueToDecimal($result->valor));
            $this->setnrdays($nrzipcode);

            $this->save();

            return $result;
        }
    }

    public static function formatValueToDecimal($value):float
    {
        $value = str_replace('.', '', $value);
        return str_replace(',', '.', $value);
    }

    public static function setMsgError($msg)
    {
        $_SESSION[Cart::SESSION_ERROR] = $msg;
    }

    public static function getMsgError()
    {
        $msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";

        Cart::clearMsgError();

        return $msg;
    }

    public static function clearMsgError()
    {
        $_SESSION[Cart::SESSION_ERROR] = NULL;
    }
}