<?php

    namespace Hcode\Model;

    use \Hcode\DB\Sql;
    use \Hcode\Model;
    use \Hcode\Model\Cart;
    use \Hcode\Model\Address;

    class Order extends Model
    {

        const ERROR = "Order-Error";
        const SUCCESS = "Order-Success";
        const SESSION = "OrderSession";

       public function save()
       {
           $sql = new Sql();

           $results = $sql->select("CALL sp_orders_save(:idorder, :idcart, :iduser, :idstatus, :idaddress, :vltotal)", 
           array(
               ":idorder" => $this->getidorder(), 
               ":idcart" => $this->getidcart(), 
               ":iduser" =>  $this->getiduser(), 
               ":idstatus" => $this->getidstatus(), 
               ":idaddress" => $this->getidaddress(), 
               ":vltotal" => $this->getvltotal()
           ));

           if(count($results) > 0){
               $this->setData($results[0]);
            }
       }

       public function get($id_order)
       {
           $sql = new Sql();

           $results = $sql->select(
            "SELECT 
                a.idorder, a.idcart, a.idcart, a.iduser, a.idstatus, a.idaddress, a.vltotal, a.dtregister,
                b.desstatus,
                c.dessessionid, c.deszipcode, c.vlfreight, c.nrdays,
                d.idperson, d.deslogin,
                e.desaddress, e.desnumber, e.descomplement, e.descity, e.desstate, e.descountry, e.deszipcode, e.desdistrict,
                f.desperson, f.desemail, f.nrphone,
                g.descode, g.vlgrossamount, g.vldiscountamount, g.vlfeeamount, g.vlnetamount, g.vlextraamount, g.despaymentlink 
            FROM tb_orders a 
            INNER JOIN tb_ordersstatus b USING(idstatus) 
            INNER JOIN tb_carts c USING(idcart)
            INNER JOIN tb_users d ON d.iduser = a.iduser
            INNER JOIN tb_addresses e USING(idaddress)
            INNER JOIN tb_persons f ON f.idperson = d.idperson
            LEFT JOIN tb_orderspagseguro g ON g.idorder = a.idorder
            WHERE a.idorder = :idorder", array(
                ":idorder" => $id_order
            ));

            if(count($results) > 0){

                $this->setData($results[0]);
            }
       }

       public static function listAll()
       {
            $sql = new Sql();

            return $sql->select(
            "SELECT * 
            FROM tb_orders a 
            INNER JOIN tb_ordersstatus b USING(idstatus) 
            INNER JOIN tb_carts c USING(idcart)
            INNER JOIN tb_users d ON d.iduser = a.iduser
            INNER JOIN tb_addresses e USING(idaddress)
            INNER JOIN tb_persons f ON f.idperson = d.idperson
            ORDER BY a.dtregister DESC");
       }

       public function delete()
       {
           $sql = new Sql();

           $sql->query("DELETE FROM tb_orders WHERE idorder = :idorder", array(
               ":idorder" => $this->getidorder()
           ));
       }

       public function getCart():Cart
       {
           $cart = new Cart();

           $cart->get((int) $this->getidcart());

           return $cart;
       }

       public static function setError($msg)
        {
            $_SESSION[Order::ERROR] = $msg;
        }

        public static function getError()
        {
            $msg = (isset($_SESSION[Order::ERROR]) && ($_SESSION[Order::ERROR])) ? $_SESSION[Order::ERROR] : "";

            Order::clearError();

            return $msg;
        }

        public static function clearError()
        {
            $_SESSION[Order::ERROR] = NULL;
        }

        public static function setSuccess($msg)
        {
            $_SESSION[Order::SUCCESS] = $msg;
        }

        public static function getSuccess()
        {
            $msg = (isset($_SESSION[Order::SUCCESS]) && ($_SESSION[Order::SUCCESS])) ? $_SESSION[Order::SUCCESS] : "";

            Order::clearSuccess();

            return $msg;
        }

        public static function clearSuccess()
        {
            $_SESSION[Order::SUCCESS] = NULL;
        }

        public static function getPage($page = 1, $itemsPerPage = 10)
        {
            $start = ($page-1)*$itemsPerPage;

            $sql = new Sql();

            $results = $sql->select(
                "SELECT SQL_CALC_FOUND_ROWS * 
                FROM tb_orders a 
                INNER JOIN tb_ordersstatus b USING(idstatus) 
                INNER JOIN tb_carts c USING(idcart)
                INNER JOIN tb_users d ON d.iduser = a.iduser
                INNER JOIN tb_addresses e USING(idaddress)
                INNER JOIN tb_persons f ON f.idperson = d.idperson
                ORDER BY a.dtregister DESC
                LIMIT $start, $itemsPerPage");

            $resultTotal = $sql->select("SELECT found_rows() AS nrtotal");

            return array(
                "data" => $results,
                "total" => (int)$resultTotal[0]["nrtotal"],
                "pages" => ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
            );
        }

        public static function getPageSearch($search, $page, $itemsPerPage = 10)
        {
            $start = ($page-1)*$itemsPerPage;

            $sql = new Sql();

            $results = $sql->select(
                "SELECT SQL_CALC_FOUND_ROWS * 
                FROM tb_orders a 
                INNER JOIN tb_ordersstatus b USING(idstatus) 
                INNER JOIN tb_carts c USING(idcart)
                INNER JOIN tb_users d ON d.iduser = a.iduser
                INNER JOIN tb_addresses e USING(idaddress)
                INNER JOIN tb_persons f ON f.idperson = d.idperson
                WHERE a.idorder = :search OR f.desperson LIKE :search_like OR b.desstatus LIKE :search_like
                ORDER BY a.dtregister DESC
                LIMIT $start, $itemsPerPage", array(
                    ":search_like" => "%".$search."%",
                    ":search" => $search
                ));

            $resultTotal = $sql->select("SELECT found_rows() AS nrtotal");

            return array(
                "data" => $results,
                "total" => (int)$resultTotal[0]["nrtotal"],
                "pages" => ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
            );
        }

        public function toSession()
        {
            $_SESSION[Order::SESSION] = $this->getValues();
        }

        public function getFromSession()
        {
            $this->setData($_SESSION[Order::SESSION]);
        }

        public function getAddress():Address
        {
            $address = new Address();

            $address->setData($this->getValues());

            return $address;
        }

        public function setPagSeguroTransactionResponse(string $descode, float $vlgrossamount, float $vldiscountamount, float $vlfeeamount, float $vlnetamount, float $extraamount, string $despaymentlink = "")
        {
            $sql = new Sql();

            $sql->query("CALL sp_orderspagseguro_save(:idorder, :descode, :vlgrossamount, :vldiscountamount, :vlfeeamount, :vlnetamount, :vlextraamount, :despaymentlink)", array(
                ":idorder" => $this->getidorder(),
                ":descode" => $descode,
                ":vlgrossamount" => $vlgrossamount,
                ":vldiscountamount" => $vldiscountamount,
                ":vlfeeamount" => $vlfeeamount,
                ":vlnetamount" => $vlnetamount,
                ":vlextraamount" => $extraamount,
                ":despaymentlink" => $despaymentlink
            ));
        }
    }
?>