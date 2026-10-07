<?php

session_start();

require_once "conexao.php";


/* ==========================
   RECEBER DADOS
========================== */

if (
    !isset($_POST["produto_id"]) ||
    !is_numeric($_POST["produto_id"])
) {
    header("Location: produtos.php");
    exit;
}

$produto_id = intval($_POST["produto_id"]);

$quantidade = isset($_POST["quantidade"])
    ? intval($_POST["quantidade"])
    : 1;


/* ==========================
   BUSCAR PRODUTO
========================== */

$stmt = $conexao->prepare(
    "SELECT id, nome, artista, preco, estoque, imagem
     FROM produtos
     WHERE id = ?"
);

$stmt->bind_param("i", $produto_id);

$stmt->execute();

$resultado = $stmt->get_result();


if ($resultado->num_rows === 0) {

    $stmt->close();

    header("Location: produtos.php");
    exit;
}


$produto = $resultado->fetch_assoc();

$stmt->close();


/* ==========================
   VERIFICAR ESTOQUE
========================== */

if ($produto["estoque"] <= 0) {

    header("Location: produto.php?id=" . $produto_id);
    exit;
}


if ($quantidade < 1) {

    $quantidade = 1;
}


if ($quantidade > $produto["estoque"]) {

    $quantidade = $produto["estoque"];
}


/* ==========================
   CRIAR CARRINHO
========================== */

if (!isset($_SESSION["carrinho"])) {

    $_SESSION["carrinho"] = [];
}


/* ==========================
   ADICIONAR AO CARRINHO
========================== */

if (isset($_SESSION["carrinho"][$produto_id])) {

    $_SESSION["carrinho"][$produto_id] += $quantidade;

} else {

    $_SESSION["carrinho"][$produto_id] = $quantidade;
}


/* ==========================
   LIMITAR AO ESTOQUE
========================== */

if (
    $_SESSION["carrinho"][$produto_id]
    > $produto["estoque"]
) {

    $_SESSION["carrinho"][$produto_id]
        = $produto["estoque"];
}


/* ==========================
   VOLTAR PARA O CARRINHO
========================== */

header("Location: carrinho.php");

exit;

?>