# PHPStore

E-commerce desenvolvido durante o curso da Hcode com as tecnologias: PHP, HTML, CSS, JavaScript, Jquery e os frameworks Slim (para rotas e requisições) e RainTpl (para manipulação dos templates utilizados).

Com funcionalidades extras de recuperação de senha via e-mail e também integração com os meios de pagamento de checkout transparente do [PagSeguro](https://dev.pagseguro.uol.com.br/v1.0/reference/como-obter-token-de-autenticacao), sendo necessário a substituição do token pessoal vinculado a conta que receberá os valores pelos pagamentos.

## 🚀 Começando

Para executar o projeto, será necessário ter o PHP 7.X, servidor apache instalados (recomenda-se o [Xampp](https://www.apachefriends.org/pt_br/index.html)) e o gerenciador de dependencias do PHP o composer, juntamente com o MySQL 8.X ou 5.6. 

Importar o arquivo dentro da pasta database na raiz do projeto, nele contem todas as tabelas necessárias e o usuário padrão para área administrativa (/phpstore/admin) e comercial do site (/phpstore)

- login: administrador@sandbox.pagseguro.com.br
- senha: 123456

### 📋 Pré-requisitos

Será necessário utilizar o seguinte comando para que as dependencias presentes no arquivo composer.json sejam instalados em sua maquina

```
$ composer install
```

### 🔧 Instalação

Para que seja possivel utilizar a recuperação de senha via email, na classe [Mailer.php](https://github.com/leobrunno/phpstore/blob/main/vendor/hcodebr/php-classes/src/Mailer.php), esse trecho de código deverá ser modificado para as suas credenciais de servidor de email (a classe está pré configurada para utilizar o servidor do gmail, porém você poderá utilizar outro modificando o host, porta e certificado de segurança utilizado)

```
const USERNAME = "email@example.com";
const PASSWORD = "<?senha?>";
const NAME_FROM = "Dev Store";
```

Fazendo uso da integração com o [PagSeguro](https://acesso.pagseguro.uol.com.br/sandbox), deve-se primeiramente criar uma conta no site e gerar um token para ser utilizado no ambiente sandbox.

Será necessário editar o arquivo da classe [Config.php](https://github.com/leobrunno/phpstore/blob/main/vendor/hcodebr/php-classes/src/PagSeguro/Config.php) com as informações pessoais de e-mail e o token gerado:

```
const SANDBOX_EMAIL = "seuemail@email.com";
```

```
const SANDBOX_TOKEN = "seutokensandbox";
```


Ainda no mesmo arquivo é possível configurar as parcelas máximas em seu e-commerce:

Quantidade máxima permitida
```
const MAX_INSTALLMENT = 12;
```

Quantidade que poderá ser oferecida sem juros para o cliente
```
const MAX_INSTALLMENT_NO_INTEREST = 10;
```

## 🛠️ Construído com

Para a construção do projeto foi utilizado as seguintes ferramentas:

* [PHP 7.X](https://https://www.php.net/) - Linguagem base para o back-end
* [Composer](https://getcomposer.org/) - Gerenciamento de Dependências
* [Slim 2.X](https://www.slimframework.com/) - Direcionamento de requisições e rotas
* [RainTpl 3.X](https://github.com/feulf/raintpl3) - Estilização e modularização dos templates utilizados

## 📄 Licença

Este projeto está sob a licença  MIT [LICENSE.md](https://github.com/leobrunno/phpstore/blob/main/LICENSE).