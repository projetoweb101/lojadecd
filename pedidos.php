<?php

session_start();

require_once "../conexao.php";


/* =====================================================
   VERIFICAR ADMINISTRADOR
===================================================== */

if (
    !isset($_SESSION["admin_id"]) &&
    !isset($_SESSION["administrador_id"])
) {
    header("Location: login.php");
    exit;
}


/* =====================================================
   ATUALIZAR STATUS DO PEDIDO
===================================================== */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["pedido_id"]) &&
    isset($_POST["status"])
) {

    $pedido_id = intval($_POST["pedido_id"]);
    $status = trim($_POST["status"]);


    $status_permitidos = [
        "Pedido realizado",
        "Aguardando pagamento",
        "Pagamento aprovado",
        "Em preparação",
        "Enviado",
        "Entregue",
        "Cancelado"
    ];


    if (in_array($status, $status_permitidos, true)) {

        $stmt = $conexao->prepare(
            "UPDATE pedidos SET status = ? WHERE id = ?"
        );

        $stmt->bind_param(
            "si",
            $status,
            $pedido_id
        );

        $stmt->execute();

        $stmt->close();
    }


    header("Location: pedidos.php");
    exit;
}


/* =====================================================
   BUSCAR PEDIDOS
===================================================== */

$sql = "
    SELECT
        p.id,
        p.usuario_id,
        p.total,
        p.forma_pagamento,
        p.endereco_entrega,
        p.status,
        p.data_pedido,
        u.nome,
        u.email
    FROM pedidos p
    LEFT JOIN usuarios u
        ON u.id = p.usuario_id
    ORDER BY p.id DESC
";


$resultado = $conexao->query($sql);

?>


<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Pedidos | LojaCD
</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:
        radial-gradient(
            circle at top left,
            #29143b,
            #08080c 60%
        );

    color: white;

    min-height: 100vh;
}


/* =====================================================
   TOPO
===================================================== */

header {

    height: 75px;

    padding:
        0 5%;

    display: flex;

    align-items: center;

    justify-content: space-between;

    background:
        #08080c;

    border-bottom:
        1px solid #292932;

}


.logo {

    color: white;

    text-decoration: none;

    font-size: 25px;

    font-weight: bold;

}


.logo span {

    color: #a95cff;

}


.menu {

    display: flex;

    align-items: center;

    gap: 25px;

}


.menu a {

    color: #aaa;

    text-decoration: none;

    font-size: 14px;

}


.menu a:hover {

    color: white;

}


.sair {

    color: #ff7d89 !important;

}


/* =====================================================
   CONTAINER
===================================================== */

.container {

    width: 92%;

    max-width: 1250px;

    margin: auto;

    padding:
        45px 0 70px;

}


.titulo {

    margin-bottom: 30px;

}


.titulo h1 {

    margin: 0;

    font-size: 38px;

}


.titulo p {

    margin-top: 8px;

    color: #888;

}


/* =====================================================
   PEDIDO
===================================================== */

.pedido {

    background:
        #111117;

    border:
        1px solid #292932;

    border-radius:
        18px;

    margin-bottom:
        20px;

    overflow: hidden;

}


.pedido-topo {

    padding:
        20px 25px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    border-bottom:
        1px solid #292932;

}


.numero {

    font-size: 20px;

    font-weight: bold;

}


.numero span {

    color: #b56cff;

}


.data {

    color: #777;

    font-size: 13px;

}


/* =====================================================
   INFORMAÇÕES
===================================================== */

.info {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 15px;

    padding:
        20px 25px;

}


.info-box {

    background:
        #0c0c11;

    border:
        1px solid #24242d;

    border-radius:
        12px;

    padding:
        15px;

}


.info-label {

    color: #777;

    font-size: 11px;

    text-transform:
        uppercase;

    margin-bottom:
        7px;

}


.info-value {

    color: white;

    font-weight: bold;

    word-break: break-word;

}


.total {

    color: #c183ff;

    font-size: 18px;

}


/* =====================================================
   DETALHES
===================================================== */

.detalhes {

    padding:
        0 25px 25px;

}


.detalhes h3 {

    margin:
        10px 0 15px;

    font-size: 17px;

}


.tabela {

    width: 100%;

    border-collapse:
        collapse;

}


.tabela th {

    text-align: left;

    color: #777;

    font-size: 11px;

    text-transform:
        uppercase;

    padding:
        10px;

    border-bottom:
        1px solid #292932;

}


.tabela td {

    padding:
        12px 10px;

    border-bottom:
        1px solid #202027;

    color: #ddd;

}


.tabela tr:last-child td {

    border-bottom: none;

}


/* =====================================================
   STATUS
===================================================== */

.status-area {

    padding:
        20px 25px;

    border-top:
        1px solid #292932;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

}


.status-atual {

    color: #aaa;

    font-size: 14px;

}


.status-atual strong {

    color: white;

}


.form-status {

    display: flex;

    gap: 10px;

}


select {

    background:
        #0c0c11;

    color: white;

    border:
        1px solid #383842;

    border-radius:
        9px;

    padding:
        11px 13px;

}


button {

    border: none;

    border-radius:
        9px;

    padding:
        11px 17px;

    background:
        white;

    color: #111;

    font-weight: bold;

    cursor: pointer;

}


button:hover {

    opacity: .9;

}


/* =====================================================
   ENDEREÇO
===================================================== */

.endereco {

    margin:
        0 25px 20px;

    padding:
        15px;

    border-radius:
        10px;

    background:
        #0c0c11;

    border:
        1px solid #24242d;

}


.endereco strong {

    display: block;

    margin-bottom:
        6px;

    font-size: 12px;

    color: #777;

    text-transform:
        uppercase;

}


.endereco span {

    color: #ccc;

    font-size: 14px;

}


/* =====================================================
   SEM PEDIDOS
===================================================== */

.vazio {

    text-align: center;

    padding:
        70px 20px;

    background:
        #111117;

    border:
        1px solid #292932;

    border-radius:
        18px;

}


.vazio .icone {

    font-size: 50px;

    margin-bottom:
        15px;

}


.vazio h2 {

    margin: 0 0 8px;

}


.vazio p {

    color: #777;

}


/* =====================================================
   RESPONSIVO
===================================================== */

@media (max-width: 900px) {

    .info {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media (max-width: 650px) {

    .pedido-topo {

        flex-direction:
            column;

        align-items:
            flex-start;

        gap: 8px;

    }


    .info {

        grid-template-columns:
            1fr;

    }


    .status-area {

        flex-direction:
            column;

        align-items:
            stretch;

    }


    .form-status {

        flex-direction:
            column;

    }


    select,
    button {

        width: 100%;

    }

}

</style>

</head>


<body>


<header>

    <a
        href="painel.php"
        class="logo"
    >

        <span>◉</span>
        LojaCD

    </a>


    <div class="menu">

        <a href="painel.php">
            Painel
        </a>

        <a href="../index.php">
            Ver loja
        </a>

        <a
            href="logout.php"
            class="sair"
        >
            Sair
        </a>

    </div>

</header>



<main class="container">


    <div class="titulo">

        <h1>
            Pedidos
        </h1>

        <p>
            Acompanhe e gerencie os pedidos realizados pelos clientes.
        </p>

    </div>



    <?php if (
        !$resultado ||
        $resultado->num_rows === 0
    ): ?>


        <div class="vazio">

            <div class="icone">
                📦
            </div>

            <h2>
                Nenhum pedido encontrado
            </h2>

            <p>
                Quando um cliente realizar uma compra,
                ela aparecerá aqui.
            </p>

        </div>


    <?php else: ?>


        <?php while (
            $pedido = $resultado->fetch_assoc()
        ): ?>


            <article class="pedido">


                <!-- =====================================
                     CABEÇALHO DO PEDIDO
                ====================================== -->

                <div class="pedido-topo">

                    <div class="numero">

                        Pedido
                        <span>
                            #<?php
                            echo intval(
                                $pedido["id"]
                            );
                            ?>
                        </span>

                    </div>


                    <div class="data">

                        <?php

                        echo date(
                            "d/m/Y H:i",
                            strtotime(
                                $pedido["data_pedido"]
                            )
                        );

                        ?>

                    </div>

                </div>



                <!-- =====================================
                     INFORMAÇÕES
                ====================================== -->

                <div class="info">


                    <div class="info-box">

                        <div class="info-label">
                            Cliente
                        </div>

                        <div class="info-value">

                            <?php

                            echo htmlspecialchars(
                                $pedido["nome"]
                                ?? "Cliente não encontrado"
                            );

                            ?>

                        </div>

                    </div>



                    <div class="info-box">

                        <div class="info-label">
                            E-mail
                        </div>

                        <div class="info-value">

                            <?php

                            echo htmlspecialchars(
                                $pedido["email"]
                                ?? "-"
                            );

                            ?>

                        </div>

                    </div>



                    <div class="info-box">

                        <div class="info-label">
                            Pagamento
                        </div>

                        <div class="info-value">

                            <?php

                            echo htmlspecialchars(
                                $pedido["forma_pagamento"]
                            );

                            ?>

                        </div>

                    </div>



                    <div class="info-box">

                        <div class="info-label">
                            Total
                        </div>

                        <div class="info-value total">

                            R$

                            <?php

                            echo number_format(
                                $pedido["total"],
                                2,
                                ",",
                                "."
                            );

                            ?>

                        </div>

                    </div>


                </div>



                <!-- =====================================
                     ENDEREÇO
                ====================================== -->

                <div class="endereco">

                    <strong>
                        Endereço de entrega
                    </strong>

                    <span>

                        <?php

                        echo nl2br(
                            htmlspecialchars(
                                $pedido["endereco_entrega"]
                            )
                        );

                        ?>

                    </span>

                </div>



                <!-- =====================================
                     PRODUTOS DO PEDIDO
                ====================================== -->

                <div class="detalhes">

                    <h3>
                        💿 Discos do pedido
                    </h3>


                    <table class="tabela">

                        <thead>

                            <tr>

                                <th>
                                    Disco
                                </th>

                                <th>
                                    Artista
                                </th>

                                <th>
                                    Quantidade
                                </th>

                                <th>
                                    Preço
                                </th>

                                <th>
                                    Subtotal
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php

                        $pedido_id =
                            intval(
                                $pedido["id"]
                            );


                        $stmt_itens =
                            $conexao->prepare("

                                SELECT
                                    ip.quantidade,
                                    ip.preco,
                                    pr.nome,
                                    pr.artista

                                FROM itens_pedido ip

                                INNER JOIN produtos pr
                                    ON pr.id = ip.produto_id

                                WHERE ip.pedido_id = ?

                            ");


                        $stmt_itens->bind_param(
                            "i",
                            $pedido_id
                        );


                        $stmt_itens->execute();


                        $itens =
                            $stmt_itens->get_result();


                        while (
                            $item =
                            $itens->fetch_assoc()
                        ):

                            $subtotal_item =
                                floatval(
                                    $item["preco"]
                                )
                                *
                                intval(
                                    $item["quantidade"]
                                );

                        ?>


                            <tr>

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $item["nome"]
                                    );

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $item["artista"]
                                    );

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo intval(
                                        $item["quantidade"]
                                    );

                                    ?>

                                </td>


                                <td>

                                    R$

                                    <?php

                                    echo number_format(
                                        $item["preco"],
                                        2,
                                        ",",
                                        "."
                                    );

                                    ?>

                                </td>


                                <td>

                                    R$

                                    <?php

                                    echo number_format(
                                        $subtotal_item,
                                        2,
                                        ",",
                                        "."
                                    );

                                    ?>

                                </td>

                            </tr>


                        <?php endwhile; ?>


                        </tbody>

                    </table>


                    <?php

                    $stmt_itens->close();

                    ?>

                </div>



                <!-- =====================================
                     STATUS
                ====================================== -->

                <div class="status-area">


                    <div class="status-atual">

                        Status atual:

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $pedido["status"]
                                ?? "Pedido realizado"
                            );

                            ?>

                        </strong>

                    </div>


                    <form
                        method="POST"
                        class="form-status"
                    >

                        <input
                            type="hidden"
                            name="pedido_id"
                            value="<?php

                            echo intval(
                                $pedido["id"]
                            );

                            ?>"
                        >


                        <select name="status">

                            <?php

                            $status_atual =
                                $pedido["status"]
                                ?? "Pedido realizado";

                            $opcoes_status = [

                                "Pedido realizado",

                                "Aguardando pagamento",

                                "Pagamento aprovado",

                                "Em preparação",

                                "Enviado",

                                "Entregue",

                                "Cancelado"

                            ];


                            foreach (
                                $opcoes_status
                                as $opcao
                            ):

                            ?>

                                <option
                                    value="<?php

                                    echo htmlspecialchars(
                                        $opcao
                                    );

                                    ?>"
                                    <?php

                                    if (
                                        $status_atual
                                        ===
                                        $opcao
                                    ) {

                                        echo "selected";

                                    }

                                    ?>
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $opcao
                                    );

                                    ?>

                                </option>

                            <?php endforeach; ?>

                        </select>


                        <button
                            type="submit"
                        >

                            Atualizar status

                        </button>

                    </form>


                </div>


            </article>


        <?php endwhile; ?>


    <?php endif; ?>


</main>


</body>

</html>