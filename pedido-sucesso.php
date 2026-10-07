<?php

session_start();

require_once "conexao.php";


/* ================================
   PEGAR ID DO PEDIDO
================================ */

$pedido_id = intval(
    $_GET["id"] ?? 0
);


if ($pedido_id <= 0) {

    header("Location: index.php");
    exit;

}


/* ================================
   BUSCAR PEDIDO
================================ */

$stmt = $conexao->prepare(

    "SELECT *
     FROM pedidos
     WHERE id = ?
     LIMIT 1"

);


$stmt->bind_param(
    "i",
    $pedido_id
);


$stmt->execute();


$resultado = $stmt->get_result();


if ($resultado->num_rows === 0) {

    die("Pedido não encontrado.");

}


$pedido = $resultado->fetch_assoc();


$stmt->close();

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
        Pedido realizado | LojaCD
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            min-height: 100vh;

            background:
                radial-gradient(
                    circle at top,
                    #211633,
                    #08080c 55%
                );

            color: white;

            font-family: Arial, sans-serif;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 25px;

        }


        .sucesso {

            width: 100%;

            max-width: 620px;

            background: #111117;

            border: 1px solid #2c2c35;

            border-radius: 24px;

            padding: 50px 35px;

            text-align: center;

            box-shadow:
                0 30px 80px
                rgba(0,0,0,.45);

        }


        .icone {

            width: 85px;

            height: 85px;

            border-radius: 50%;

            margin: 0 auto 25px;

            background: #b56cff;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 40px;

        }


        h1 {

            margin: 0 0 12px;

            font-size: 34px;

        }


        .mensagem {

            color: #999;

            line-height: 1.6;

        }


        .numero {

            margin: 30px 0;

            padding: 20px;

            background: #09090d;

            border: 1px solid #292932;

            border-radius: 14px;

        }


        .numero small {

            display: block;

            color: #777;

            margin-bottom: 8px;

        }


        .numero strong {

            font-size: 28px;

        }


        .detalhes {

            display: flex;

            justify-content: space-between;

            gap: 20px;

            padding: 20px 0;

            border-top: 1px solid #292932;

            border-bottom: 1px solid #292932;

            margin-bottom: 25px;

        }


        .detalhe {

            flex: 1;

        }


        .detalhe small {

            display: block;

            color: #777;

            margin-bottom: 7px;

        }


        .detalhe strong {

            font-size: 15px;

        }


        .botoes {

            display: flex;

            gap: 12px;

        }


        .botao {

            flex: 1;

            padding: 15px;

            border-radius: 10px;

            text-decoration: none;

            font-weight: bold;

            font-size: 13px;

        }


        .principal {

            background: white;

            color: #111;

        }


        .secundario {

            border: 1px solid #33333b;

            color: white;

        }


        @media (max-width: 600px) {

            .sucesso {

                padding: 35px 20px;

            }


            h1 {

                font-size: 27px;

            }


            .detalhes {

                flex-direction: column;

            }


            .botoes {

                flex-direction: column;

            }

        }

    </style>

</head>


<body>


<div class="sucesso">


    <div class="icone">

        ✓

    </div>


    <h1>

        Pedido realizado!

    </h1>


    <p class="mensagem">

        Obrigado pela sua compra.
        Seu pedido foi registrado com sucesso
        em nossa loja.

    </p>


    <div class="numero">

        <small>
            Número do pedido
        </small>

        <strong>

            #<?php echo $pedido["id"]; ?>

        </strong>

    </div>


    <div class="detalhes">


        <div class="detalhe">

            <small>
                Total
            </small>

            <strong>

                R$

                <?php

                echo number_format(
                    $pedido["total"],
                    2,
                    ",",
                    "."
                );

                ?>

            </strong>

        </div>



        <div class="detalhe">

            <small>
                Pagamento
            </small>

            <strong>

                <?php

                echo htmlspecialchars(
                    $pedido["forma_pagamento"]
                );

                ?>

            </strong>

        </div>


        <div class="detalhe">

            <small>
                Status
            </small>

            <strong>

                <?php

                echo htmlspecialchars(
                    $pedido["status"]
                );

                ?>

            </strong>

        </div>


    </div>


    <div class="botoes">


        <a
            href="index.php"
            class="botao principal"
        >

            Continuar comprando

        </a>


        <a
            href="pedidos.php"
            class="botao secundario"
        >

            Meus pedidos

        </a>


    </div>


</div>


</body>

</html>