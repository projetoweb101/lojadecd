<?php

session_start();

require_once "conexao.php";


/* ==========================================
   VERIFICAR LOGIN
========================================== */

if (!isset($_SESSION["usuario_id"])) {

    header("Location: login.php");
    exit;

}


$usuario_id = intval($_SESSION["usuario_id"]);

$nome_cliente = $_SESSION["usuario_nome"] ?? "Cliente";

$pedidos = [];


/* ==========================================
   BUSCAR PEDIDOS DO CLIENTE
========================================== */

$stmt = $conexao->prepare("
    SELECT id, total, status
    FROM pedidos
    WHERE usuario_id = ?
    ORDER BY id DESC
");


if ($stmt) {

    $stmt->bind_param("i", $usuario_id);

    $stmt->execute();

    $resultado = $stmt->get_result();

    while ($pedido = $resultado->fetch_assoc()) {

        $pedidos[] = $pedido;

    }

    $stmt->close();

}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Meus pedidos | LojaCD</title>


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

        }


        header {

            height: 75px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 6%;

            background: #08080c;

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


        .container {

            width: 90%;

            max-width: 1000px;

            margin: auto;

            padding: 50px 0 80px;

        }


        h1 {

            margin: 0 0 10px;

            font-size: 36px;

        }


        .subtitulo {

            color: #888;

            margin-bottom: 35px;

        }


        .lista {

            display: flex;

            flex-direction: column;

            gap: 15px;

        }


        .pedido {

            background: #111117;

            border: 1px solid #292932;

            border-radius: 16px;

            padding: 22px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

        }


        .pedido-info {

            display: flex;

            flex-direction: column;

            gap: 7px;

        }


        .numero {

            font-size: 18px;

            font-weight: bold;

        }


        .valor {

            color: #aaa;

            font-size: 13px;

        }


        .status {

            display: inline-block;

            width: fit-content;

            padding: 7px 12px;

            border-radius: 20px;

            background: #241733;

            color: #c991ff;

            font-size: 11px;

            font-weight: bold;

        }


        .botao {

            display: inline-block;

            padding: 11px 18px;

            border-radius: 9px;

            background: white;

            color: #111;

            text-decoration: none;

            font-size: 12px;

            font-weight: bold;

        }


        .botao:hover {

            opacity: .9;

        }


        .vazio {

            background: #111117;

            border: 1px solid #292932;

            border-radius: 18px;

            padding: 50px 25px;

            text-align: center;

        }


        .vazio-icon {

            font-size: 45px;

            margin-bottom: 15px;

        }


        .vazio h2 {

            margin: 0 0 10px;

        }


        .vazio p {

            color: #777;

            margin-bottom: 25px;

        }


        .voltar {

            display: inline-block;

            padding: 13px 20px;

            border-radius: 9px;

            background: white;

            color: #111;

            text-decoration: none;

            font-weight: bold;

            font-size: 13px;

        }


        @media (max-width: 650px) {

            header {

                padding: 0 20px;

            }


            .usuario {

                display: none;

            }


            .container {

                width: 92%;

            }


            h1 {

                font-size: 28px;

            }


            .pedido {

                flex-direction: column;

                align-items: flex-start;

            }


            .botao {

                width: 100%;

                text-align: center;

            }

        }

    </style>

</head>


<body>


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
            href="index.php"
        >

            Loja

        </a>


        <a
            href="logout.php"
            class="sair"
        >

            Sair

        </a>

    </nav>


</header>



<main class="container">


    <h1>
        Meus pedidos
    </h1>


    <p class="subtitulo">

        Aqui você pode acompanhar suas compras.

    </p>



    <?php if (empty($pedidos)): ?>


        <div class="vazio">


            <div class="vazio-icon">
                📦
            </div>


            <h2>
                Você ainda não fez nenhum pedido
            </h2>


            <p>
                Explore nossa loja e encontre seu próximo disco.
            </p>


            <a
                href="produtos.php"
                class="voltar"
            >

                🛍️ Ver discos

            </a>


        </div>


    <?php else: ?>


        <div class="lista">


            <?php foreach ($pedidos as $pedido): ?>


                <div class="pedido">


                    <div class="pedido-info">


                        <div class="numero">

                            Pedido
                            #<?php

                            echo intval(
                                $pedido["id"]
                            );

                            ?>

                        </div>


                        <div class="valor">

                            Total:

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


                        <span class="status">

                            <?php

                            echo htmlspecialchars(
                                $pedido["status"]
                            );

                            ?>

                        </span>


                    </div>


                    <a
                        href="pedido_sucesso.php"
                        class="botao"
                    >

                        Ver pedido

                    </a>


                </div>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


</main>


</body>

</html>