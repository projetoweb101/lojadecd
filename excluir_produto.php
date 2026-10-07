<?php

session_start();

require_once "../conexao.php";


/* ==========================================
   VERIFICAR SE O ADMIN ESTÁ LOGADO
========================================== */

if (
    !isset($_SESSION["admin_id"]) &&
    !isset($_SESSION["administrador_id"])
) {
    header("Location: login.php");
    exit;
}


/* ==========================================
   PEGAR O ID DO PRODUTO
========================================== */

$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;


if ($id <= 0) {
    die("ID do produto inválido.");
}


/* ==========================================
   BUSCAR O PRODUTO
========================================== */

$stmt = $conexao->prepare(
    "SELECT id, nome FROM produtos WHERE id = ?"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$resultado = $stmt->get_result();


if ($resultado->num_rows == 0) {

    $stmt->close();

    die("Produto não encontrado.");

}


$produto = $resultado->fetch_assoc();

$stmt->close();


/* ==========================================
   VERIFICAR SE ESTÁ EM ALGUM PEDIDO
========================================== */

$stmt = $conexao->prepare(
    "SELECT COUNT(*) AS total
     FROM itens_pedido
     WHERE produto_id = ?"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$resultado = $stmt->get_result();

$dados = $resultado->fetch_assoc();

$total = intval($dados["total"]);

$stmt->close();


/* ==========================================
   SE JÁ FOI USADO EM PEDIDO
========================================== */

if ($total > 0) {

    /*
       Não podemos apagar porque o produto
       está ligado ao histórico de pedidos.

       Então colocamos o estoque em zero.
    */

    $stmt = $conexao->prepare(
        "UPDATE produtos
         SET estoque = 0
         WHERE id = ?"
    );

    $stmt->bind_param("i", $id);


    if ($stmt->execute()) {

        $stmt->close();

        header(
            "Location: painel.php?produto_desativado=" .
            urlencode($produto["nome"])
        );

        exit;

    }


    $erro = $stmt->error;

    $stmt->close();

    die(
        "Erro ao desativar o produto: " .
        htmlspecialchars($erro)
    );
}


/* ==========================================
   SE NÃO ESTÁ EM NENHUM PEDIDO
========================================== */

$stmt = $conexao->prepare(
    "DELETE FROM produtos WHERE id = ?"
);

$stmt->bind_param("i", $id);


if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: painel.php?produto_excluido=" .
        urlencode($produto["nome"])
    );

    exit;

}


/* ==========================================
   ERRO AO EXCLUIR
========================================== */

$erro = $stmt->error;

$stmt->close();


die(
    "Erro ao excluir o produto: " .
    htmlspecialchars($erro)
);

?>