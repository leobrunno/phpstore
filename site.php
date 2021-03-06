<?php

$app->get('/', function () {

    $products = Hcode\Model\Product::listAll();

    $page = new Hcode\Page();

    $page->setTpl("index", array(
        "products" => Hcode\Model\Product::checkList($products)
    ));
});

$app->get("/categories/:idcategory", function ($idcategory) {
    
    $page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

    $category = new Hcode\Model\Category();

    $category->get((int)$idcategory);

    $pagination = $category->getProductsPage($page);

    $pages = array();

    for ($i=1; $i <= $pagination["pages"]; $i++) {
        array_push($pages, array(
            "link" => "/phpstore/categories/".$category->getidcategory()."?page=".$i,
            "page" => $i
        ));
    }

    $page = new Hcode\Page();

    $page->setTpl("category", array(
        "category" => $category->getValues(),
        "products" => $pagination["data"],
        "pages" => $pages
    ));
});

$app->get("/products/:desurl", function($desurl){

    $product = new Hcode\Model\Product();

    $product->getFromUrl($desurl);

    $page = new Hcode\Page();

    $page->setTpl("product-detail", array(
        "product" => $product->getValues(),
        "categories" => $product->getCategories()
    ));
});

$app->get("/cart", function(){

    $cart = Hcode\Model\Cart::getFromSession();
    
    $page = new Hcode\Page();

    $page->setTpl("cart", array(
        "cart" => $cart->getValues(),
        "products" => $cart->getProducts(),
        "error" => Hcode\Model\Cart::getMsgError()
    ));
});

$app->get("/cart/:idproduct/add", function($idproduct){
    $product = new Hcode\Model\Product();

    $product->get((int)$idproduct);

    $cart = Hcode\Model\Cart::getFromSession();

    $qtd = (isset($_GET["qtd"])) ? (int)$_GET["qtd"] : 1;

    for ($i=0; $i < $qtd; $i++) { 
        $cart->addProduct($product);
    }

    header("Location: /phpstore/cart");
    exit();
});

$app->get("/cart/:idproduct/minus", function($idproduct){
    $product = new Hcode\Model\Product();

    $product->get((int)$idproduct);

    $cart = Hcode\Model\Cart::getFromSession();

    $cart->removeProduct($product);

    header("Location: /phpstore/cart");
    exit();
});

$app->get("/cart/:idproduct/remove", function($idproduct){
    $product = new Hcode\Model\Product();

    $product->get((int)$idproduct);

    $cart = Hcode\Model\Cart::getFromSession();

    $cart->removeProduct($product, true);

    header("Location: /phpstore/cart");
    exit();
});

$app->post("/cart/freight", function(){

    $cart = Hcode\Model\Cart::getFromSession();

    $cart->setFreight($_POST['zipcode']);

    header("Location: /phpstore/cart");
    exit();
});

$app->get("/checkout", function(){

    Hcode\Model\User::verifyLogin(false);

    $address = new Hcode\Model\Address();

    $cart = Hcode\Model\Cart::getFromSession();

    if(isset($_GET['zipcode'])){

        $_GET['zipcode'] = $cart->getdeszipcode();

        $address->loadFromCEP($_GET['zipcode']);

        $cart->setdeszipcode($_GET['zipcode']);

        $cart->save();

        $cart->getCalculateTotal();
    }

    if(!$address->getdesaddress()) $address->setdesaddress("");
    if(!$address->getdesnumber()) $address->setdesnumber("");
    if(!$address->getdescomplement()) $address->setdescomplement("");
    if(!$address->getdesdistrict()) $address->setdesdistrict("");
    if(!$address->getdescity()) $address->setdescity("");
    if(!$address->getdesstate()) $address->setdesstate("");
    if(!$address->getdescountry()) $address->setdescountry("");
    if(!$address->getdeszipcode()) $address->setdeszipcode("");

    $page = new Hcode\Page();

    $page->setTpl("checkout", array(
        "cart" => $cart->getValues(),
        "address" => $address->getValues(),
        "products" => $cart->getProducts(),
        "error" => Hcode\Model\Address::getMsgError()
    ));
});

$app->post("/checkout", function(){

    Hcode\Model\User::verifyLogin(false);

    if(!isset($_POST['zipcode']) || empty($_POST['zipcode'])){

        Hcode\Model\Address::setMsgError("Informe o cep");

        header("Location: /phpstore/checkout");
        exit();
    }

    if(!isset($_POST['desaddress']) || empty($_POST['desaddress'])){

        Hcode\Model\Address::setMsgError("Informe o endere??o");

        header("Location: /phpstore/checkout");
        exit();
    }

    if(!isset($_POST['desdistrict']) || empty($_POST['desdistrict'])){

        Hcode\Model\Address::setMsgError("Informe o bairro");

        header("Location: /phpstore/checkout");
        exit();
    }

    if(!isset($_POST['descity']) || empty($_POST['descity'])){

        Hcode\Model\Address::setMsgError("Informe a cidade");

        header("Location: /phpstore/checkout");
        exit();
    }

    if(!isset($_POST['desstate']) || empty($_POST['desstate'])){

        Hcode\Model\Address::setMsgError("Informe o estado");

        header("Location: /phpstore/checkout");
        exit();
    }

    if(!isset($_POST['descountry']) || empty($_POST['descountry'])){

        Hcode\Model\Address::setMsgError("Informe o pa??s");

        header("Location: /phpstore/checkout");
        exit();
    }

    $user = Hcode\Model\User::getFromSession();

    $address = new Hcode\Model\Address();

    $_POST['deszipcode'] = $_POST['zipcode'];
    $_POST['idperson'] = $user->getidperson();

    $address->setData($_POST);

    $address->save();

    $cart = Hcode\Model\Cart::getFromSession();

    $cart->getCalculateTotal();

    $order = new \Hcode\Model\Order();

    $order->setData(array(
        "idcart" => $cart->getidcart(),
        "idaddress" => $address->getidaddress(),
        "iduser" => $user->getiduser(),
        "idstatus" => Hcode\Model\OrderStatus::EM_ABERTO,
        "vltotal" => $cart->getvltotal()
    ));

    $order->save();

    $order->toSession();

    header("Location: /phpstore/payment");
    exit();
});

$app->get("/login", function(){

    $page = new Hcode\Page();

    $page->setTpl("login", array(
        "error" => Hcode\Model\User::getError(),
        "errorRegister" => Hcode\Model\User::getErrorRegister(),
        "registerValues" => (isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : array("name" => "", "email" => "", "phone" => "")
    ));
});

$app->post("/login", function(){

    try{

        Hcode\Model\User::login($_POST['login'], $_POST['password']);

    } catch(Exception $e){

        Hcode\Model\User::setError($e->getMessage());
    }

    header("Location: /phpstore/checkout");
    exit();
});

$app->get("/logout", function(){

    \Hcode\Model\User::logout();

    header("Location: /phpstore/login");
    exit();
});

$app->post("/register", function(){

    $user = new \Hcode\Model\User();

    $_SESSION['registerValues'] = $_POST;

    if(!isset($_POST['name']) || (empty($_POST['name']))){

        \Hcode\Model\User::setErrorRegister("Preencha seu nome!");
        header("Location: /phpstore/login");
        exit();
    }

    if(!isset($_POST['email']) || (empty($_POST['email']))){

        \Hcode\Model\User::setErrorRegister("Preencha seu email!");
        header("Location: /phpstore/login");
        exit();
    }

    if(!isset($_POST['password']) || (empty($_POST['password']))){

        \Hcode\Model\User::setErrorRegister("Preencha a senha!");
        header("Location: /phpstore/login");
        exit();
    }

    if(\Hcode\Model\User::checkLoginExists($_POST['email']) === true){

        \Hcode\Model\User::setErrorRegister("Este endere??o de email j?? foi cadastrado!");
        header("Location: /phpstore/login");
        exit();
    }

    $user->setData(array(
        "inadmin" => 0,
        "deslogin" => $_POST['email'],
        "desperson" => $_POST['name'],
        "desemail" => $_POST['email'],
        "despassword" => $_POST['password'],
        "nrphone" => $_POST['phone']
    ));

    $user->save();

    \Hcode\Model\User::login($_POST['email'], $_POST['password']);

    header("Location: /phpstore/checkout");
    exit();
});

$app->get("/forgot", function () {
    $page = new Hcode\Page();

    $page->setTpl("forgot");
});

$app->post("/forgot", function () {

    $user = \Hcode\Model\User::getForgot($_POST["email"], false);

    header("Location: /phpstore/forgot/sent");
    exit();
});

$app->get("/forgot/sent", function () {
    $page = new Hcode\Page();

    $page->setTpl("forgot-sent");
});

$app->get("/forgot/reset", function () {
    $user = \Hcode\Model\User::validForgotDecrypt($_GET["code"]);

    $page = new Hcode\Page();

    $page->setTpl("forgot-reset", array(
        "name" => $user["desperson"],
        "code" => $_GET["code"]
    ));
});

$app->post("/forgot/reset", function () {
    
    $forgot = \Hcode\Model\User::validForgotDecrypt($_POST["code"]);

    \Hcode\Model\User::setForgotUsed($forgot["idrecovery"]);

    $user = new \Hcode\Model\User();
    $user->get((int)$forgot["iduser"]);

    $password = password_hash($_POST["password"], PASSWORD_DEFAULT, ["cost" => 12]);

    $user->setPassword($password);

    $page = new Hcode\Page();

    $page->setTpl("forgot-reset-success");
});

$app->get("/profile", function(){

    Hcode\Model\User::verifyLogin(false);

    $user = Hcode\Model\User::getFromSession();

    $page = new Hcode\Page();

    $page->setTpl("profile", array(
        "user" => $user->getValues(),
        "profileMsg" => Hcode\Model\User::getSuccess(),
        "profileError" => Hcode\Model\User::getError()
    ));
});

$app->post("/profile", function(){

    Hcode\Model\User::verifyLogin(false);

    if(!isset($_POST['desperson']) || empty($_POST['desperson'])){

        \Hcode\Model\User::setError("Preencha seu nome!");

        header("Location: /phpstore/profile");
        exit();

    }

    if(!isset($_POST['desemail']) || empty($_POST['desemail'])){

        \Hcode\Model\User::setError("Preencha seu email!");

        header("Location: /phpstore/profile");
        exit();
    }
    
    $user = Hcode\Model\User::getFromSession();

    if($_POST['desemail'] !== $user->getdesemail()){

        if(\Hcode\Model\User::checkLoginExists($_POST['email']) === true){

            \Hcode\Model\User::setError("Este endere??o de email j?? est?? cadastrado");
            header("Location: /phpstore/profile");
            exit();
        }
    }


    $_POST['indamin'] = $user->getinadmin();
    $_POST['despassword'] = $user->getdespassword();
    $_POST['deslogin'] = $_POST['desemail'];

    $user->setData($_POST);

    $user->update();

    Hcode\Model\User::setSuccess("Dados atualizados com sucesso!");

    header("Location: /phpstore/profile");
    exit();
});

$app->get("/order/:idorder", function($id_order){

    Hcode\Model\User::verifyLogin(false);

    $order = new Hcode\Model\Order();

    $order->get((int)$id_order);

    $page = new Hcode\Page();

    $page->setTpl("payment", array(
        "order" => $order->getValues()
    ));
});

$app->get("/boleto/:idorder", function($id_order){

    Hcode\Model\User::verifyLogin(false);

    $order = new Hcode\Model\Order();

    $order->get((int)$id_order);

    // DADOS DO BOLETO PARA O SEU CLIENTE
    $dias_de_prazo_para_pagamento = 10;
    $taxa_boleto = 5.00;
    $data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
    $valor_cobrado = (float) formatPrice($order->getvltotal()); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
    $valor_cobrado = str_replace(",", ".",$valor_cobrado);
    $valor_boleto = number_format($valor_cobrado + $taxa_boleto, 2, ',', '');

    $dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: M??ximo de 8 caracteres!
    $dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
    $dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
    $dadosboleto["data_documento"] = date("d/m/Y"); // Data de emiss??o do Boleto
    $dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
    $dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com v??rgula e sempre com duas casas depois da virgula

    // DADOS DO SEU CLIENTE
    $dadosboleto["sacado"] = $order->getdesperson();
    $dadosboleto["endereco1"] = $order->getdesaddress() . " " . $order->getdesdistrict();
    $dadosboleto["endereco2"] = $order->getdescity() . " " . $order->getdesstate() . " " . $order->getdescountry() . " - CEP: " . $order->getdeszipcode();

    // INFORMACOES PARA O CLIENTE
    $dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja PHP STORE";
    $dadosboleto["demonstrativo2"] = "Taxa banc??ria - R$ 0,00";
    $dadosboleto["demonstrativo3"] = "";
    $dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% ap??s o vencimento";
    $dadosboleto["instrucoes2"] = "- Receber at?? 10 dias ap??s o vencimento";
    $dadosboleto["instrucoes3"] = "- Em caso de d??vidas entre em contato conosco: suporte@phpstore.com.br";
    $dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja PHP STORE - www.phpstore.com.br";

    // DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
    $dadosboleto["quantidade"] = "";
    $dadosboleto["valor_unitario"] = "";
    $dadosboleto["aceite"] = "";		
    $dadosboleto["especie"] = "R$";
    $dadosboleto["especie_doc"] = "";


    // ---------------------- DADOS FIXOS DE CONFIGURA????O DO SEU BOLETO --------------- //


    // DADOS DA SUA CONTA - ITA??
    $dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
    $dadosboleto["conta"] = "48781";	// Num da conta, sem digito
    $dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

    // DADOS PERSONALIZADOS - ITA??
    $dadosboleto["carteira"] = "175";  // C??digo da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

    // SEUS DADOS
    $dadosboleto["identificacao"] = "PHP STORE";
    $dadosboleto["cpf_cnpj"] = "00.000.000/0001-91";
    $dadosboleto["endereco"] = "Rua Rasmus Lerdorf, 777 - Qeqertarsuaq, 00000-001";
    $dadosboleto["cidade_uf"] = "Qeqertarsuaq - Disko";
    $dadosboleto["cedente"] = "PHP STORE LTDA";

    // N??O ALTERAR!
    $path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "phpstore" . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "boletophp" . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR;
    require_once($path . "funcoes_itau.php");
    require_once($path . "layout_itau.php");
});

$app->get("/profile/orders", function(){

    Hcode\Model\User::verifyLogin(false);

    $user = Hcode\Model\User::getFromSession();

    $page = new Hcode\Page();

    $page->setTpl("profile-orders", array(
        "orders" => $user->getOrders()
    ));
});

$app->get("/profile/orders/:idorder", function($id_order){

    Hcode\Model\User::verifyLogin(false);

    $order = new Hcode\Model\Order();

    $order->get((int) $id_order);

    $cart = new \Hcode\Model\Cart();

    $cart->get((int) $order->getidcart());

    $cart->getCalculateTotal();

    $page = new Hcode\Page();

    $page->setTpl("profile-orders-detail", array(
        "order" => $order->getValues(),
        "cart" => $cart->getValues(),
        "products" => $cart->getProducts()
    ));
});

$app->get("/profile/change-password", function(){

    Hcode\Model\User::verifyLogin(false);

    $page = new Hcode\Page();

    $page->setTpl("profile-change-password", array(
        "changePassError" => Hcode\Model\User::getError(),
        "changePassSuccess" => Hcode\Model\User::getSuccess()
    ));
});

$app->post("/profile/change-password", function(){

    Hcode\Model\User::verifyLogin(false);

    if(!isset($_POST['current_pass']) || empty($_POST['current_pass'])){

        Hcode\Model\User::setError("Digite a senha atual");
        header("Location: /phpstore/profile/change-password");
        exit();
    }

    if(!isset($_POST['new_pass']) || empty($_POST['new_pass'])){

        Hcode\Model\User::setError("Digite a nova senha");
        header("Location: /phpstore/profile/change-password");
        exit();
    }

    if(!isset($_POST['new_pass_confirm']) || empty($_POST['new_pass_confirm'])){

        Hcode\Model\User::setError("Confirme a nova senha");
        header("Location: /phpstore/profile/change-password");
        exit();
    }

    if($_POST['current_pass'] === $_POST['new_pass_confirm']){

        Hcode\Model\User::setError("Para mudar, escolha uma senha diferente da atual");
        header("Location: /phpstore/profile/change-password");
        exit();
    }

    $user = Hcode\Model\User::getFromSession();

    if(!password_verify($_POST['current_pass'], $user->getdespassword())){

        Hcode\Model\User::setError("Senha inv??lida");
        header("Location: /phpstore/profile/change-password");
        exit();
    }

    $user->setdespassword($_POST['new_pass']);

    $user->update();

    Hcode\Model\User::setSuccess("Senha alterada com sucesso");
    
    header("Location: /phpstore/profile/change-password");
    exit();
});