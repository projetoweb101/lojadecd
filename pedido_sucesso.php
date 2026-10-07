<?php

session_start();

require_once "conexao.php";

/* ==========================================
   VERIFICAR SE EXISTE PEDIDO FINALIZADO
========================================== */

if (!isset($_SESSION["pedido_finalizado"])) {

    header("Location: index.php");
    exit;

}

$pedido_id = intval($_SESSION["pedido_finalizado"]);


/* ==========================================
   BUSCAR PEDIDO NO BANCO
========================================== */

$sql = "
    SELECT *
    FROM pedidos
    WHERE id = ?
";

$stmt = $conexao->prepare($sql);

$stmt->bind_param("i", $pedido_id);

$stmt->execute();

$resultado = $stmt->get_result();

$pedido = $resultado->fetch_assoc();

$stmt->close();


/* ==========================================
   SE NÃO ENCONTRAR O PEDIDO
========================================== */

if (!$pedido) {

    header("Location: index.php");
    exit;

}


/* ==========================================
   NOME DO CLIENTE
========================================== */

$nome_cliente = $_SESSION["usuario_nome"] ?? "Cliente";

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Pedido confirmado | LojaCD</title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            min-height: 100vh;

            font-family: Arial, sans-serif;

            background:
                radial-gradient(
                    circle at top,
                    #29173c,
                    #08080c 60%
                );

            color: white;

            display: flex;

            flex-direction: column;

        }


        /* ==================================
           HEADER
        ================================== */

        header {

            height: 75px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 6%;

            background: rgba(8,8,12,.95);

            border-bottom: 1px solid #292932;

        }


        .logo {

            color: white;

            text-decoration: none;

            font-size: 25px;

            font-weight: bold;

        }


        .logo span {

            color: #b56cff;

        }


        nav {

            display: flex;

            align-items: center;

            gap: 20px;

        }


        nav a {

            color: #aaa;

            text-decoration: none;

            font-size: 13px;

        }


        nav a:hover {

            color: white;

        }


        .usuario {

            font-size: 13px;

            font-weight: bold;

        }


        .sair {

            color: #ff8995 !important;

        }


        /* ==================================
           CONTEÚDO
        ================================== */

        main {

            flex: 1;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 60px 20px;

        }


        .confirmacao {

            width: 100%;

            max-width: 650px;

            background: #111117;

            border: 1px solid #292932;

            border-radius: 22px;

            padding: 50px 40px;

            text-align: center;

            box-shadow:
                0 25px 80px rgba(0,0,0,.4);

        }


        /* ==================================
           ÍCONE
        ================================== */

        .icone {

            width: 85px;

            height: 85px;

            margin: 0 auto 25px;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #1b3527;

            border: 1px solid #3f8057;

            font-size: 42px;

        }


        h1 {

            margin: 0 0 12px;

            font-size: 32px;

        }


        .mensagem {

            color: #999;

            line-height: 1.6;

            margin: 0 auto 30px;

            max-width: 500px;

        }


        /* ==================================
           NÚMERO DO PEDIDO
        ================================== */

        .pedido {

            background: #0b0b10;

            border: 1px solid #30303a;

            border-radius: 14px;

            padding: 20px;

            margin-bottom: 25px;

        }


        .pedido-label {

            color: #777;

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: 1px;

            margin-bottom: 8px;

        }


        .pedido-numero {

            font-size: 25px;

            font-weight: bold;

            color: #b56cff;

        }


        /* ==================================
           INFORMAÇÕES
        ================================== */

        .informacoes {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 12px;

            margin-bottom: 30px;

        }


        .info {

            background: #17171e;

            border-radius: 10px;

            padding: 15px;

        }


        .info span {

            display: block;

            color: #777;

            font-size: 11px;

            margin-bottom: 6px;

        }


        .info strong {

            font-size: 14px;

        }


        /* ==================================
           BOTÕES
        ================================== */

        .botoes {

            display: flex;

            gap: 12px;

            justify-content: center;

        }


        .botao {

            padding: 14px 22px;

            border-radius: 10px;

            text-decoration: none;

            font-size: 13px;

            font-weight: bold;

            transition: .2s;

        }


        .principal {

            background: white;

            color: #111;

        }


        .principal:hover {

            opacity: .9;

        }


        .secundario {

            background: transparent;

            border: 1px solid #383842;

            color: white;

        }


        .secundario:hover {

            background: #1b1b22;

        }


        /* ==================================
           RESPONSIVO
        ================================== */

        @media (max-width: 600px) {

            header {

                padding: 0 20px;

            }


            nav a:not(.sair) {

                display: none;

            }


            .confirmacao {

                padding: 40px 20px;

            }


            h1 {

                font-size: 26px;

            }


            .informacoes {

                grid-template-columns: 1fr;

            }


            .botoes {

                flex-direction: column;

            }


            .botao {

                width: 100%;

            }

        }

    </style>

</head>


<body>


<!-- ==================================
     CABEÇALHO
================================== -->

<header>


    <a
        href="index.php"
        class="logo"
    >

        <span>◉</span> LojaCD

    </a>


    <nav>


        <span class="usuario">

            👤 Olá,
            <?php

            echo htmlspecialchars(
                $nome_cliente
            );

            ?>

        </span>


        <a
            href="logout.php"
            class="sair"
        >

            Sair

        </a>


    </nav>


</header>



<!-- ==================================
     CONFIRMAÇÃO
================================== -->

<main>


    <section class="confirmacao">


        <div class="icone">

            ✓

        </div>


        <h1>

            Pedido realizado com sucesso!

        </h1>


        <p class="mensagem">

            Obrigado pela sua compra,
            <strong>
                <?php

                echo htmlspecialchars(
                    $nome_cliente
                );

                ?>
            </strong>!

            <br>

            Seu pedido foi registrado
            e já está sendo processado.

        </p>



        <!-- NÚMERO DO PEDIDO -->

        <div class="pedido">


            <div class="pedido-label">

                Número do pedido

            </div>


            <div class="pedido-numero">

                #<?php

                echo $pedido_id;

                ?>

            </div>


        </div>



        <!-- INFORMAÇÕES -->

        <div class="informacoes">


            <div class="info">


                <span>
                    Status
                </span>


                <strong>

                    <?php

                    echo htmlspecialchars(
                        $pedido["status"]
                    );

                    ?>

                </strong>


            </div>



            <div class="info">


                <span>
                    Total
                </span>


                <strong>

                    R$

                    <?php

                    echo number_format(
                        floatval(
                            $pedido["total"]
                        ),
                        2,
                        ",",
                        "."
                    );

                    ?>

                </strong>


            </div>


        </div>



        <!-- BOTÕES -->

        <div class="botoes">


            <a
                href="index.php"
                class="botao principal"
            >

                ← Voltar para a loja

            </a>


            <a
                href="produtos.php"
                class="botao secundario"
            >

                Continuar comprando

            </a>


        </div>


    </section>


</main>


</body>

</html>