<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once "conexao.php";


/* ==========================================
   VERIFICAR FORMULÁRIO
========================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: carrinho.php");
    exit;
}


/* ==========================================
   VERIFICAR CARRINHO
========================================== */

if (!isset($_SESSION["carrinho"]) || empty($_SESSION["carrinho"])) {
    die("Seu carrinho está vazio.");
}

$carrinho = $_SESSION["carrinho"];


/* ==========================================
   RECEBER DADOS
========================================== */

$nome = trim($_POST["nome"] ?? "");
$cpf = trim($_POST["cpf"] ?? "");
$email = trim($_POST["email"] ?? "");
$telefone = trim($_POST["telefone"] ?? "");

$cep = trim($_POST["cep"] ?? "");
$estado = trim($_POST["estado"] ?? "");
$cidade = trim($_POST["cidade"] ?? "");
$bairro = trim($_POST["bairro"] ?? "");
$rua = trim($_POST["rua"] ?? "");
$numero = trim($_POST["numero"] ?? "");
$complemento = trim($_POST["complemento"] ?? "");

$pagamento = trim($_POST["pagamento"] ?? "");


/* ==========================================
   VALIDAR CAMPOS
========================================== */

if (
    $nome === "" ||
    $cpf === "" ||
    $email === "" ||
    $telefone === "" ||
    $cep === "" ||
    $estado === "" ||
    $cidade === "" ||
    $bairro === "" ||
    $rua === "" ||
    $numero === "" ||
    $pagamento === ""
) {
    die("Preencha todos os campos obrigatórios.");
}


$formas_pagamento = [
    "PIX",
    "Cartão",
    "Boleto"
];

if (!in_array($pagamento, $formas_pagamento)) {
    die("Forma de pagamento inválida.");
}


/* ==========================================
   IDENTIFICAR USUÁRIO LOGADO
========================================== */

$usuario_id = 0;


/*
   Tentativa 1:
   sessão usuario_id
*/

if (isset($_SESSION["usuario_id"])) {
    $usuario_id = intval($_SESSION["usuario_id"]);
}


/*
   Tentativa 2:
   sessão user_id
*/

if ($usuario_id <= 0 && isset($_SESSION["user_id"])) {
    $usuario_id = intval($_SESSION["user_id"]);
}


/*
   Tentativa 3:
   sessão id_usuario
*/

if ($usuario_id <= 0 && isset($_SESSION["id_usuario"])) {
    $usuario_id = intval($_SESSION["id_usuario"]);
}


/*
   Tentativa 4:
   sessão id
*/

if ($usuario_id <= 0 && isset($_SESSION["id"])) {
    $usuario_id = intval($_SESSION["id"]);
}


/*
   Tentativa 5:
   sessão usuario como array
*/

if (
    $usuario_id <= 0 &&
    isset($_SESSION["usuario"]) &&
    is_array($_SESSION["usuario"])
) {

    if (isset($_SESSION["usuario"]["id"])) {

        $usuario_id =
            intval($_SESSION["usuario"]["id"]);

    }

}


/*
   Tentativa 6:
   procurar pelo e-mail digitado
*/

if ($usuario_id <= 0) {

    $stmt_usuario = $conexao->prepare(
        "SELECT id FROM usuarios WHERE email = ? LIMIT 1"
    );

    if (!$stmt_usuario) {
        die(
            "Erro ao preparar busca do usuário: " .
            $conexao->error
        );
    }

    $stmt_usuario->bind_param(
        "s",
        $email
    );

    $stmt_usuario->execute();

    $resultado_usuario =
        $stmt_usuario->get_result();

    if ($resultado_usuario->num_rows > 0) {

        $usuario =
            $resultado_usuario->fetch_assoc();

        $usuario_id =
            intval($usuario["id"]);

    }

    $stmt_usuario->close();
}


/* ==========================================
   VERIFICAR USUÁRIO
========================================== */

if ($usuario_id <= 0) {

    die(
        "<h2>Usuário não identificado</h2>" .
        "<p>O e-mail informado no checkout não foi encontrado no cadastro.</p>" .
        "<p>Volte ao site, entre na sua conta e utilize o mesmo e-mail cadastrado.</p>" .
        "<br>" .
        "<a href='checkout.php'>Voltar para o checkout</a>"
    );

}


/* ==========================================
   MONTAR ENDEREÇO
========================================== */

$endereco =
    "Rua: " . $rua .
    ", Nº " . $numero .
    ", Bairro: " . $bairro .
    ", Cidade: " . $cidade .
    " - " . $estado .
    ", CEP: " . $cep;

if ($complemento !== "") {

    $endereco .=
        ", Complemento: " .
        $complemento;

}


/* ==========================================
   INICIAR TRANSAÇÃO
========================================== */

$conexao->begin_transaction();


try {


    /* ======================================
       PEGAR PRODUTOS
    ====================================== */

    $ids = array_keys($carrinho);

    $ids_validos = [];

    foreach ($ids as $id) {

        if (is_numeric($id)) {

            $ids_validos[] =
                intval($id);

        }

    }


    if (empty($ids_validos)) {

        throw new Exception(
            "Nenhum produto válido foi encontrado."
        );

    }


    $ids_sql =
        implode(",", $ids_validos);


    $sql_produtos = "
        SELECT *
        FROM produtos
        WHERE id IN ($ids_sql)
        FOR UPDATE
    ";


    $resultado_produtos =
        $conexao->query($sql_produtos);


    if (!$resultado_produtos) {

        throw new Exception(
            "Erro ao buscar produtos: " .
            $conexao->error
        );

    }


    $produtos = [];

    $total = 0;


    while (
        $produto =
        $resultado_produtos->fetch_assoc()
    ) {

        $produto_id =
            intval($produto["id"]);


        $quantidade =
            intval(
                $carrinho[$produto_id] ?? 0
            );


        if ($quantidade <= 0) {
            continue;
        }


        $estoque =
            intval($produto["estoque"]);


        if ($estoque < $quantidade) {

            throw new Exception(
                "O produto '" .
                $produto["nome"] .
                "' não possui estoque suficiente."
            );

        }


        $preco =
            floatval($produto["preco"]);


        $subtotal =
            $preco * $quantidade;


        $total += $subtotal;


        $produtos[] = [

            "id" => $produto_id,

            "quantidade" => $quantidade,

            "preco" => $preco

        ];

    }


    if (empty($produtos)) {

        throw new Exception(
            "Nenhum produto foi encontrado."
        );

    }


    /* ======================================
       CRIAR PEDIDO
    ====================================== */

    $status =
        "Pedido realizado";


    $stmt_pedido =
        $conexao->prepare(

            "INSERT INTO pedidos
            (
                usuario_id,
                total,
                forma_pagamento,
                endereco_entrega,
                status
            )
            VALUES (?, ?, ?, ?, ?)"

        );


    if (!$stmt_pedido) {

        throw new Exception(
            "Erro ao preparar pedido: " .
            $conexao->error
        );

    }


    $stmt_pedido->bind_param(
        "idsss",
        $usuario_id,
        $total,
        $pagamento,
        $endereco,
        $status
    );


    if (!$stmt_pedido->execute()) {

        throw new Exception(
            "Erro ao salvar pedido: " .
            $stmt_pedido->error
        );

    }


    $pedido_id =
        $conexao->insert_id;


    $stmt_pedido->close();


    /* ======================================
       INSERIR ITENS DO PEDIDO
    ====================================== */

    $stmt_item =
        $conexao->prepare(

            "INSERT INTO itens_pedido
            (
                pedido_id,
                produto_id,
                quantidade,
                preco
            )
            VALUES (?, ?, ?, ?)"

        );


    if (!$stmt_item) {

        throw new Exception(
            "Erro ao preparar itens: " .
            $conexao->error
        );

    }


    /* ======================================
       ATUALIZAR ESTOQUE
    ====================================== */

    $stmt_estoque =
        $conexao->prepare(

            "UPDATE produtos
             SET estoque = estoque - ?
             WHERE id = ?"

        );


    if (!$stmt_estoque) {

        throw new Exception(
            "Erro ao preparar estoque: " .
            $conexao->error
        );

    }


    foreach ($produtos as $item) {

        $produto_id =
            intval($item["id"]);

        $quantidade =
            intval($item["quantidade"]);

        $preco =
            floatval($item["preco"]);


        /* Registrar item */

        $stmt_item->bind_param(
            "iiid",
            $pedido_id,
            $produto_id,
            $quantidade,
            $preco
        );


        if (!$stmt_item->execute()) {

            throw new Exception(
                "Erro ao registrar item do pedido: " .
                $stmt_item->error
            );

        }


        /* Diminuir estoque */

        $stmt_estoque->bind_param(
            "ii",
            $quantidade,
            $produto_id
        );


        if (!$stmt_estoque->execute()) {

            throw new Exception(
                "Erro ao atualizar estoque: " .
                $stmt_estoque->error
            );

        }

    }


    $stmt_item->close();

    $stmt_estoque->close();


    /* ======================================
       CONFIRMAR
    ====================================== */

    $conexao->commit();


    /* ======================================
       LIMPAR CARRINHO
    ====================================== */

    unset($_SESSION["carrinho"]);


    $_SESSION["ultimo_pedido"] =
        $pedido_id;


    /* ======================================
       IR PARA SUCESSO
    ====================================== */

    header(
        "Location: pedido-sucesso.php?id=" .
        $pedido_id
    );

    exit;


} catch (Exception $e) {


    /* Desfazer tudo se houver erro */

    $conexao->rollback();


    die(

        "<h2>Não foi possível finalizar o pedido.</h2>" .

        "<p>" .
        htmlspecialchars(
            $e->getMessage()
        ) .
        "</p>" .

        "<br>" .

        "<a href='checkout.php'>
            Voltar para o checkout
        </a>"

    );

}

?>