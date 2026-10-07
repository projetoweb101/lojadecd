<?php

session_start();

require_once "conexao.php";


/* ==========================
   VERIFICAR ID DO PRODUTO
========================== */

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: produtos.php");
    exit;
}

$id = intval($_GET["id"]);


/* ==========================
   BUSCAR PRODUTO
========================== */

$stmt = $conexao->prepare(
    "SELECT * FROM produtos WHERE id = ?"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$resultado = $stmt->get_result();


if ($resultado->num_rows === 0) {

    header("Location: produtos.php");
    exit;
}


$produto = $resultado->fetch_assoc();

$stmt->close();


/* ==========================
   CORRIGIR CAMINHO DA IMAGEM
========================== */

$imagem = $produto["imagem"] ?? "";

if (!empty($imagem)) {

    // Se o banco já tiver "assets/imagens/",
    // não adiciona novamente.
    if (strpos($imagem, "assets/") === 0) {

        $caminhoImagem = $imagem;

    } else {

        // Se tiver somente "thriller.jpg",
        // adiciona o caminho da pasta.
        $caminhoImagem = "assets/imagens/" . $imagem;
    }
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

    <title>
        <?php echo htmlspecialchars($produto["nome"]); ?> | LojaCD
    </title>

    <link rel="stylesheet" href="css/style.css">


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            background: #08080c;

            color: white;

            font-family: Arial, sans-serif;

        }


        /* ==========================
           CABEÇALHO
        ========================== */

        .topo {

            height: 75px;

            border-bottom: 1px solid #24242c;

            background: rgba(8, 8, 12, .96);

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 6%;

        }


        .logo {

            color: white;

            text-decoration: none;

            font-size: 25px;

            font-weight: 800;

        }


        .logo span {

            color: #b56cff;

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

            transition: .3s;

        }


        .menu a:hover {

            color: white;

        }


        .carrinho {

            border: 1px solid #33333b;

            padding: 10px 15px;

            border-radius: 9px;

        }


        /* ==========================
           CONTEÚDO
        ========================== */

        .container {

            width: 88%;

            max-width: 1200px;

            margin: auto;

            padding: 60px 0;

        }


        .voltar {

            display: inline-block;

            color: #888;

            text-decoration: none;

            font-size: 13px;

            margin-bottom: 35px;

        }


        .voltar:hover {

            color: white;

        }


        /* ==========================
           PRODUTO
        ========================== */

        .produto-detalhes {

            display: grid;

            grid-template-columns: 480px 1fr;

            gap: 70px;

            align-items: center;

        }


        /* IMAGEM */

        .imagem-produto {

            width: 100%;

            aspect-ratio: 1 / 1;

            background: #111117;

            border: 1px solid #282830;

            border-radius: 20px;

            overflow: hidden;

        }


        .imagem-produto img {

            width: 100%;

            height: 100%;

            object-fit: cover;

            display: block;

        }


        .sem-imagem {

            width: 100%;

            height: 100%;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 100px;

        }


        /* INFORMAÇÕES */

        .genero {

            color: #b56cff;

            text-transform: uppercase;

            letter-spacing: 2px;

            font-size: 11px;

            margin-bottom: 12px;

        }


        .produto-info h1 {

            font-size: 46px;

            line-height: 1.05;

            margin: 0 0 10px;

        }


        .artista {

            color: #888;

            font-size: 18px;

            margin-bottom: 25px;

        }


        .ano {

            color: #666;

            font-size: 13px;

            margin-bottom: 30px;

        }


        .descricao {

            color: #aaa;

            font-size: 14px;

            line-height: 1.7;

            max-width: 600px;

            margin-bottom: 35px;

        }


        /* PREÇO */

        .preco {

            font-size: 32px;

            font-weight: bold;

            margin-bottom: 10px;

        }


        .estoque {

            font-size: 12px;

            color: #72d394;

            margin-bottom: 25px;

        }


        .esgotado {

            color: #ff7777;

        }


        /* COMPRA */

        .compra {

            display: flex;

            gap: 12px;

            align-items: center;

        }


        .quantidade {

            width: 70px;

            padding: 14px;

            background: #111117;

            border: 1px solid #303038;

            color: white;

            border-radius: 10px;

            text-align: center;

        }


        .btn-carrinho {

            border: none;

            background: white;

            color: #111;

            padding: 15px 25px;

            border-radius: 10px;

            font-weight: bold;

            cursor: pointer;

            transition: .3s;

        }


        .btn-carrinho:hover {

            transform: translateY(-2px);

            opacity: .9;

        }


        .btn-esgotado {

            background: #29292f;

            color: #777;

            cursor: not-allowed;

        }


        /* DESTAQUE */

        .badge {

            display: inline-block;

            background: #21182a;

            color: #c995ff;

            border: 1px solid #422b55;

            padding: 7px 11px;

            border-radius: 7px;

            font-size: 10px;

            margin-bottom: 18px;

        }


        /* ==========================
           RESPONSIVO
        ========================== */

        @media (max-width: 900px) {

            .produto-detalhes {

                grid-template-columns: 1fr;

                gap: 40px;

            }


            .imagem-produto {

                max-width: 500px;

                margin: auto;

            }


            .produto-info {

                text-align: center;

            }


            .descricao {

                margin-left: auto;

                margin-right: auto;

            }


            .compra {

                justify-content: center;

            }

        }


        @media (max-width: 600px) {

            .topo {

                padding: 0 20px;

            }


            .menu {

                gap: 10px;

            }


            .menu a:not(.carrinho) {

                display: none;

            }


            .produto-info h1 {

                font-size: 34px;

            }


            .container {

                width: 92%;

            }

        }

    </style>

</head>


<body>


<!-- ==========================
     CABEÇALHO
========================== -->

<header class="topo">


    <a href="index.php" class="logo">

        <span>◉</span> LojaCD

    </a>


    <nav class="menu">

        <a href="index.php">

            Início

        </a>


        <a href="produtos.php">

            Produtos

        </a>


        <a href="login.php">

            Entrar

        </a>


        <a href="carrinho.php" class="carrinho">

            🛒 Carrinho

        </a>

    </nav>


</header>



<!-- ==========================
     CONTEÚDO
========================== -->

<main class="container">


    <a href="produtos.php" class="voltar">

        ← Voltar para produtos

    </a>



    <div class="produto-detalhes">


        <!-- ==========================
             IMAGEM
        ========================== -->

        <div class="imagem-produto">


            <?php if (!empty($imagem)): ?>

                <img
                    src="<?php echo htmlspecialchars($caminhoImagem); ?>"
                    alt="<?php echo htmlspecialchars($produto["nome"]); ?>"
                >

            <?php else: ?>

                <div class="sem-imagem">

                    💿

                </div>

            <?php endif; ?>


        </div>



        <!-- ==========================
             INFORMAÇÕES
        ========================== -->

        <div class="produto-info">


            <?php if (!empty($produto["destaque"])): ?>

                <div class="badge">

                    ⭐ PRODUTO EM DESTAQUE

                </div>

            <?php endif; ?>


            <div class="genero">

                <?php

                echo htmlspecialchars(
                    $produto["genero"] ?: "Música"
                );

                ?>

            </div>


            <h1>

                <?php

                echo htmlspecialchars(
                    $produto["nome"]
                );

                ?>

            </h1>


            <div class="artista">

                <?php

                echo htmlspecialchars(
                    $produto["artista"]
                );

                ?>

            </div>


            <?php if (!empty($produto["ano"])): ?>

                <div class="ano">

                    Lançamento:
                    <?php echo htmlspecialchars($produto["ano"]); ?>

                </div>

            <?php endif; ?>


            <?php if (!empty($produto["descricao"])): ?>

                <div class="descricao">

                    <?php

                    echo nl2br(
                        htmlspecialchars(
                            $produto["descricao"]
                        )
                    );

                    ?>

                </div>

            <?php else: ?>

                <div class="descricao">

                    Um álbum especial para fazer parte
                    da sua coleção musical.

                </div>

            <?php endif; ?>


            <div class="preco">

                R$

                <?php

                echo number_format(
                    $produto["preco"],
                    2,
                    ",",
                    "."
                );

                ?>

            </div>


            <?php if ($produto["estoque"] > 0): ?>


                <div class="estoque">

                    ✓
                    <?php echo $produto["estoque"]; ?>
                    unidade(s) disponível(is)

                </div>


                <form
                    action="adicionar-carrinho.php"
                    method="POST"
                    class="compra"
                >


                    <input
                        type="hidden"
                        name="produto_id"
                        value="<?php echo $produto["id"]; ?>"
                    >


                    <input
                        type="number"
                        name="quantidade"
                        class="quantidade"
                        value="1"
                        min="1"
                        max="<?php echo $produto["estoque"]; ?>"
                        required
                    >


                    <button
                        type="submit"
                        class="btn-carrinho"
                    >

                        🛒 Adicionar ao carrinho

                    </button>


                </form>


            <?php else: ?>


                <div class="estoque esgotado">

                    ✕ Produto esgotado

                </div>


                <button
                    class="btn-carrinho btn-esgotado"
                    disabled
                >

                    Produto indisponível

                </button>


            <?php endif; ?>


        </div>


    </div>


</main>


</body>

</html>