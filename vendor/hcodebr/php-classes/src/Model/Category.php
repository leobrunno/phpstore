<?php
    namespace Hcode\Model;
    use \Hcode\Model;
    use \Hcode\DB\Sql;
    use \Hcode\Mailer;

    class Category extends Model
    {

        public static function listAll()
        {
            $sql = new Sql();

            return $sql->select("SELECT * FROM tb_categories ORDER BY tb_categories.idcategory DESC");
        }

        public function save()
        {
            $sql = new Sql();

            $results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
                ":idcategory" => $this->getidcategory(),
                ":descategory" => $this->getdescategory(),
            ));

            $this->setData($results[0]);

            Category::updateFile();
        }

        public function get($idcategory)
        {
            $sql = new Sql();
            
            $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
                ":idcategory" => $idcategory
            ));

            $this->setData($results[0]);
        }

        public function delete()
        {
            $sql = new Sql();

            $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", array(
                ":idcategory" => $this->getidcategory()
            ));

            Category::updateFile();
        }

        public static function updateFile()
        {
            $categories = Category::listAll();

            $html = array();

            foreach ($categories as $row) {
                array_push($html, '<li><a href="/phpstore/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
            }

            $s = DIRECTORY_SEPARATOR;
            file_put_contents($_SERVER['DOCUMENT_ROOT'].$s."ecommerce".$s."views".$s."categories-menu.html", implode('', $html));
        }
    }
?>