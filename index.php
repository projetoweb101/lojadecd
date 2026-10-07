<?php

session_start();

require_once "conexao.php";


// Buscar produtos cadastrados
$produtos = [];

$sql = "SELECT * FROM produtos ORDER BY id DESC LIMIT 8";

$resultado = $conexao->query($sql);

if ($resultado) {

    while ($produto = $resultado->fetch_assoc()) {

        $produtos[] = $produto;

    }

}


// Quantidade de itens no carrinho
$total_itens = 0;

if (isset($_SESSION["carrinho"])) {

    foreach ($_SESSION["carrinho"] as $quantidade) {

        $total_itens += intval($quantidade);

    }

}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LojaCD | Sua música, sua coleção</title>


    <style>

        * {
            box-sizing: border-box;
        }

        body {

            margin: 0;

            background:
                radial-gradient(
                    circle at top,
                    #261536,
                    #08080c 55%
                );

            color: white;

            font-family: Arial, sans-serif;

        }


        /* =========================
           TOPO
        ========================= */

        header {

            height: 75px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 6%;

            background: rgba(8,8,12,.95);

            border-bottom: 1px solid #292932;

            position: sticky;

            top: 0;

            z-index: 100;

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

            font-size: 14px;

        }


        nav a:hover {

            color: white;

        }


        .usuario {

            color: white;

            font-size: 13px;

            font-weight: bold;

        }


        .sair {

            color: #ff9ba4 !important;

        }


        .carrinho {

            color: white !important;

            border: 1px solid #363640;

            padding: 10px 14px;

            border-radius: 9px;

        }


        /* =========================
           HERO
        ========================= */

        .hero {

            min-height: 560px;

            display: flex;

            align-items: center;

            padding: 70px 8%;

            position: relative;

            overflow: hidden;

        }


        .hero::before {

            content: "";

            position: absolute;

            width: 450px;

            height: 450px;

            background: #7c3aed;

            filter: blur(180px);

            opacity: .20;

            right: -100px;

            top: 50px;

        }


        .hero-conteudo {

            max-width: 720px;

            position: relative;

            z-index: 2;

        }


        .etiqueta {

            color: #c58cff;

            font-size: 13px;

            font-weight: bold;

            letter-spacing: 2px;

            margin-bottom: 20px;

        }


        .hero h1 {

            font-size: clamp(48px, 7vw, 86px);

            line-height: .95;

            margin: 0 0 25px;

            letter-spacing: -4px;

        }


        .hero h1 span {

            color: #b56cff;

        }


        .hero p {

            color: #92929c;

            font-size: 17px;

            line-height: 1.7;

            max-width: 600px;

        }


        .botoes {

            display: flex;

            gap: 12px;

            margin-top: 30px;

            flex-wrap: wrap;

        }


        .botao {

            display: inline-block;

            padding: 15px 23px;

            border-radius: 10px;

            text-decoration: none;

            font-weight: bold;

        }


        .botao-principal {

            background: white;

            color: #111;

        }


        .botao-secundario {

            border: 1px solid #363640;

            color: white;

        }


        /* =========================
           PRODUTOS
        ========================= */

        .secao {

            width: 88%;

            max-width: 1250px;

            margin: auto;

            padding: 65px 0;

        }


        .cabecalho-secao {

            display: flex;

            justify-content: space-between;

            align-items: end;

            margin-bottom: 25px;

        }


        .cabecalho-secao h2 {

            margin: 0 0 7px;

            font-size: 28px;

        }


        .cabecalho-secao p {

            margin: 0;

            color: #777;

            font-size: 13px;

        }


        .ver-todos {

            color: #c58cff;

            text-decoration: none;

            font-size: 13px;

        }


        .grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

        }


        .produto {

            background: #111117;

            border: 1px solid #292932;

            border-radius: 15px;

            overflow: hidden;

            transition: .25s;

        }


        .produto:hover {

            transform: translateY(-5px);

            border-color: #55505e;

        }


        .imagem {

            width: 100%;

            aspect-ratio: 1 / 1;

            background: #1a1a21;

            overflow: hidden;

        }


        .imagem img {

            width: 100%;

            height: 100%;

            object-fit: cover;

        }


        .sem-imagem {

            height: 100%;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 65px;

        }


        .info {

            padding: 18px;

        }


        .info h3 {

            margin: 0 0 8px;

            font-size: 16px;

        }


        .preco {

            color: white;

            font-size: 18px;

            font-weight: bold;

            margin-bottom: 15px;

        }


        .detalhes {

            display: block;

            text-align: center;

            padding: 11px;

            border: 1px solid #383842;

            border-radius: 8px;

            color: white;

            text-decoration: none;

            font-size: 12px;

        }


        .detalhes:hover {

            background: white;

            color: #111;

        }


        /* =========================
           ESTILOS
        ========================= */

        .estilos {

            display: grid;

            grid-template-columns:
                repeat(6, 1fr);

            gap: 12px;

        }


        .estilo {

            padding: 22px 10px;

            text-align: center;

            background: #111117;

            border: 1px solid #292932;

            border-radius: 12px;

            color: #aaa;

            text-decoration: none;

            font-size: 13px;

        }


        .estilo:hover {

            color: white;

            border-color: #b56cff;

        }


        /* =========================
           RODAPÉ
        ========================= */

        footer {

            border-top: 1px solid #292932;

            padding: 30px 6%;

            color: #666;

            font-size: 12px;

            text-align: center;

        }


        /* =========================
           RESPONSIVO
        ========================= */

        @media (max-width: 1100px) {

            nav {

                gap: 12px;

            }

        }


        @media (max-width: 1000px) {

            .grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

            .estilos {

                grid-template-columns:
                    repeat(3, 1fr);

            }

        }


        @media (max-width: 650px) {

            header {

                padding: 0 20px;

            }

            nav a:not(.carrinho) {

                display: none;

            }

            .hero {

                padding: 60px 6%;

            }

            .grid {

                grid-template-columns: 1fr;

            }

            .estilos {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }

    </style>

</head>


<body>


<header>


    <a href="index.php" class="logo">

        <span>◉</span> LojaCD

    </a>


    <nav>


        <?php if (isset($_SESSION["usuario_nome"])): ?>

            <!-- NOME DO CLIENTE -->

            <span class="usuario">

                👤 Olá,
                <?php

                echo htmlspecialchars(
                    $_SESSION["usuario_nome"]
                );

                ?>

            </span>


            <!-- MEUS PEDIDOS -->

            <a href="meus_pedidos.php">

                📦 Meus pedidos

            </a>


            <!-- SAIR -->

            <a href="logout.php" class="sair">

                Sair

            </a>


        <?php else: ?>

            <!-- ENTRAR -->

            <a href="login.php">

                Entrar

            </a>


            <!-- CRIAR CONTA -->

            <a href="cadastro.php">

                Criar conta

            </a>

        <?php endif; ?>


        <!-- CARRINHO -->

        <a href="carrinho.php" class="carrinho">

            🛒 Carrinho

            <?php if ($total_itens > 0): ?>

                (<?php echo $total_itens; ?>)

            <?php endif; ?>

        </a>


    </nav>


</header>



<section class="hero">


    <div class="hero-conteudo">


        <div class="etiqueta">

            ✦ COLEÇÃO MUSICAL

        </div>


        <h1>

            Sua música.<br>

            <span>Sua coleção.</span>

        </h1>


        <p>

            Encontre discos dos seus artistas favoritos,
            descubra novos sons e monte uma coleção
            que tenha a sua identidade.

        </p>


        <div class="botoes">


            <a
                href="produtos.php"
                class="botao botao-principal"
            >

                Explorar discos →

            </a>


            <a
                href="#destaques"
                class="botao botao-secundario"
            >

                Ver destaques

            </a>


        </div>


    </div>


</section>



<section
    class="secao"
    id="destaques"
>


    <div class="cabecalho-secao">


        <div>

            <h2>
                Discos em destaque
            </h2>

            <p>
                Produtos disponíveis na LojaCD.
            </p>

        </div>


        <a
            href="produtos.php"
            class="ver-todos"
        >

            Ver todos →

        </a>


    </div>



    <?php if (!empty($produtos)): ?>


        <div class="grid">


            <?php foreach ($produtos as $produto): ?>


                <article class="produto">


                    <div class="imagem">


                        <?php if (!empty($produto["imagem"])): ?>

                            <img
                                src="assets/imagens/<?php echo htmlspecialchars($produto["imagem"]); ?>"
                                alt="<?php echo htmlspecialchars($produto["nome"]); ?>"
                            >

                        <?php else: ?>

                            <div class="sem-imagem">

                                💿

                            </div>

                        <?php endif; ?>


                    </div>


                    <div class="info">


                        <h3>

                            <?php

                            echo htmlspecialchars(
                                $produto["nome"]
                            );

                            ?>

                        </h3>


                        <div class="preco">

                            R$

                            <?php

                            echo number_format(
                                floatval($produto["preco"]),
                                2,
                                ",",
                                "."
                            );

                            ?>

                        </div>


                        <a
                            href="produto.php?id=<?php echo intval($produto["id"]); ?>"
                            class="detalhes"
                        >

                            Ver detalhes

                        </a>


                    </div>


                </article>


            <?php endforeach; ?>


        </div>


    <?php else: ?>


        <div
            style="
                padding:50px;
                text-align:center;
                background:#111117;
                border:1px solid #292932;
                border-radius:15px;
                color:#777;
            "
        >

            Ainda não existem discos cadastrados.

        </div>


    <?php endif; ?>


</section>



<section class="secao">


    <div class="cabecalho-secao">


        <div>

            <h2>
                Encontre pelo seu estilo
            </h2>

            <p>
                Explore diferentes gêneros musicais.
            </p>

        </div>


    </div>



    <div class="estilos">


        <a href="produtos.php?genero=Rock" class="estilo">
            🎸 Rock
        </a>

        <a href="produtos.php?genero=Pop" class="estilo">
            🎤 Pop
        </a>

        <a href="produtos.php?genero=MPB" class="estilo">
            🎵 MPB
        </a>

        <a href="produtos.php?genero=Jazz" class="estilo">
            🎷 Jazz
        </a>

        <a href="produtos.php?genero=Sertanejo" class="estilo">
            🤠 Sertanejo
        </a>

        <a href="produtos.php?genero=Rap" class="estilo">
            🎧 Rap
        </a>


    </div>


</section>



<footer>

    © <?php echo date("Y"); ?> LojaCD —
    Sua música, sua coleção.

</footer>


</body>

</html>vvvv