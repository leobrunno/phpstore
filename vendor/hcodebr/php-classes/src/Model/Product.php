<?php
    namespace Hcode\Model;
    use \Hcode\Model;
    use \Hcode\DB\Sql;
    use \Hcode\Mailer;

    class Product extends Model
    {

        public static function listAll()
        {
            $sql = new Sql();

            return $sql->select("SELECT * FROM tb_products ORDER BY tb_products.idproduct DESC");
        }

        public static function checkList($list)
        {
            foreach ($list as &$row) {
                $product = new Product();
                $product->setData($row);
                $row = $product->getValues();
            }

            return $list;
        }

        public function save()
        {
            $sql = new Sql();

            $results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
                ":idproduct" => $this->getidproduct(),
                ":desproduct" => $this->getdesproduct(),
                ":vlprice" => $this->getvlprice(),
                ":vlwidth" => $this->getvlwidth(),
                ":vlheight" => $this->getvlheight(),
                ":vllength" => $this->getvllength(),
                ":vlweight" => $this->getvlweight(),
                ":desurl" => $this->getdesurl()

            ));

            $this->setData($results[0]);

            Category::updateFile();
        }

        public function get($idproduct)
        {
            $sql = new Sql();
            
            $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", array(
                ":idproduct" => $idproduct
            ));

            $this->setData($results[0]);
        }

        public function delete()
        {
            $sql = new Sql();

            $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", array(
                ":idproduct" => $this->getidproduct()
            ));
        }

        public function getValues()
        {
            $this->checkPhoto();
            $values = parent::getValues();


            return $values;
        }

        public function checkPhoto()
        {
            if(file_exists($_SERVER["DOCUMENT_ROOT"]."/phpstore".DIRECTORY_SEPARATOR."res".DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."img".DIRECTORY_SEPARATOR."products".DIRECTORY_SEPARATOR.$this->getidproduct().".jpg")){
                $url = "/phpstore/res/site/img/products/".$this->getidproduct().".jpg";
            } else {
                $url = "/phpstore/res/site/img/Product.jpg";
            }

            return $this->setdesphoto($url);
        }

        public function setPhoto($file)
        {
            $extension = explode(".", $file["name"]);
            $extension = end($extension);

            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $image = imagecreatefromjpeg($file["tmp_name"]);
                break;
                    
                case 'gif':
                    $image = imagecreatefromgif($file["tmp_name"]);
                break;

                case 'png':
                    $image = imagecreatefrompng($file["tmp_name"]);
                break;
            }

            $destiny = $_SERVER["DOCUMENT_ROOT"]."/phpstore".DIRECTORY_SEPARATOR."res".DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."img".DIRECTORY_SEPARATOR."products".DIRECTORY_SEPARATOR.$this->getidproduct().".jpg";
            imagejpeg($image, $destiny);

            imagedestroy($image);

            $this->checkPhoto();
        }

        public function getFromUrl($desurl)
        {
            $sql = new Sql();

            $rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1", array(
                ":desurl" => $desurl
            ));

            $this->setData($rows[0]);
        }

        public function getCategories()
        {
            $sql = new Sql();

            return $sql->select("SELECT * FROM tb_categories a INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory WHERE b.idproduct = :idproduct", array(
                ":idproduct" => $this->getidproduct()
            ));
        }

        public static function getPage($page = 1, $itemsPerPage = 10)
        {
            $start = ($page-1)*$itemsPerPage;

            $sql = new Sql();

            $results = $sql->select(
                "SELECT SQL_CALC_FOUND_ROWS * FROM tb_products ORDER BY tb_products.idproduct DESC
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
                "SELECT SQL_CALC_FOUND_ROWS * FROM tb_products WHERE desproduct LIKE :search_like OR vlprice = :search OR idproduct = :search ORDER BY tb_products.idproduct DESC
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
    }
?>