<?php

session_start();

require_once "conexao.php";


/* =====================================================
   INICIALIZAR CARRINHO
===================================================== */

if (!isset($_SESSION["carrinho"])) {
    $_SESSION["carrinho"] = [];
}


/* =====================================================
   AUMENTAR QUANTIDADE
===================================================== */

if (isset($_GET["aumentar"])) {

    $id = intval($_GET["aumentar"]);

    if (isset($_SESSION["carrinho"][$id])) {

        $estoque_disponivel = 0;

        $stmt = $conexao->prepare(
            "SELECT estoque FROM produtos WHERE id = ?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($produto = $resultado->fetch_assoc()) {
            $estoque_disponivel = intval($produto["estoque"]);
        }

        $stmt->close();


        if (
            $_SESSION["carrinho"][$id] <
            $estoque_disponivel
        ) {

            $_SESSION["carrinho"][$id]++;

        }
    }

    header("Location: carrinho.php");
    exit;
}


/* =====================================================
   DIMINUIR QUANTIDADE
===================================================== */

if (isset($_GET["diminuir"])) {

    $id = intval($_GET["diminuir"]);

    if (isset($_SESSION["carrinho"][$id])) {

        $_SESSION["carrinho"][$id]--;

        if ($_SESSION["carrinho"][$id] <= 0) {

            unset($_SESSION["carrinho"][$id]);

        }
    }

    header("Location: carrinho.php");
    exit;
}


/* =====================================================
   EXCLUIR ITEM
===================================================== */

if (isset($_GET["excluir"])) {

    $id = intval($_GET["excluir"]);

    unset($_SESSION["carrinho"][$id]);

    header("Location: carrinho.php");
    exit;
}


/* =====================================================
   BUSCAR PRODUTOS
===================================================== */

$produtos = [];

$total = 0;


if (!empty($_SESSION["carrinho"])) {

    $ids = array_keys($_SESSION["carrinho"]);

    $ids = array_map("intval", $ids);

    $ids_string = implode(",", $ids);


    $sql = "
        SELECT *
        FROM produtos
        WHERE id IN ($ids_string)
        ORDER BY id DESC
    ";

    $resultado = $conexao->query($sql);


    if ($resultado) {

        while ($produto = $resultado->fetch_assoc()) {

            $id = intval($produto["id"]);

            $quantidade =
                intval(
                    $_SESSION["carrinho"][$id]
                );


            /* =========================================
               CORRIGIR QUANTIDADE CASO PASSE DO ESTOQUE
            ========================================= */

            if (
                $quantidade >
                intval($produto["estoque"])
            ) {

                $quantidade =
                    intval($produto["estoque"]);

                $_SESSION["carrinho"][$id] =
                    $quantidade;
            }


            if ($quantidade <= 0) {

                unset(
                    $_SESSION["carrinho"][$id]
                );

                continue;
            }


            $subtotal =
                floatval($produto["preco"])
                * $quantidade;


            $produto["quantidade"] =
                $quantidade;

            $produto["subtotal"] =
                $subtotal;


            $produtos[] =
                $produto;


            $total +=
                $subtotal;
        }
    }
}


/* =====================================================
   QUANTIDADE TOTAL DE ITENS
===================================================== */

$quantidade_total = 0;

foreach ($produtos as $produto) {

    $quantidade_total +=
        intval($produto["quantidade"]);
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
    Carrinho | LojaCD
</title>


<style>

/* =====================================================
   RESET
===================================================== */

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
            #08080c 55%
        );

    color: white;

    min-height: 100vh;
}


/* =====================================================
   HEADER
===================================================== */

header {

    height: 75px;

    padding:
        0 5%;

    display: flex;

    align-items: center;

    justify-content: space-between;

    background:
        rgba(8,8,12,.96);

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

    color: #b56cff;

}


nav {

    display: flex;

    align-items: center;

    gap: 22px;

}


nav a {

    color: #aaaaaa;

    text-decoration: none;

    font-size: 14px;

}


nav a:hover {

    color: white;

}


.carrinho-topo {

    color: white;

}


/* =====================================================
   CONTAINER
===================================================== */

.container {

    width: 90%;

    max-width: 1200px;

    margin: auto;

    padding:
        45px 0 80px;

}


.titulo {

    margin-bottom:
        30px;

}


.titulo h1 {

    margin: 0;

    font-size: 38px;

}


.titulo p {

    color: #777777;

    margin-top: 8px;

}


/* =====================================================
   LAYOUT
===================================================== */

.layout {

    display: grid;

    grid-template-columns:
        1fr 350px;

    gap: 25px;

}


/* =====================================================
   ITENS
===================================================== */

.itens {

    display: flex;

    flex-direction: column;

    gap: 15px;

}


.item {

    display: flex;

    gap: 20px;

    padding: 20px;

    background:
        #111117;

    border:
        1px solid #292932;

    border-radius:
        16px;

}


.imagem {

    width: 120px;

    height: 120px;

    flex-shrink: 0;

    border-radius:
        12px;

    object-fit: cover;

    background:
        #20202a;

}


.sem-imagem {

    width: 120px;

    height: 120px;

    flex-shrink: 0;

    border-radius:
        12px;

    background:
        #20202a;

    display: flex;

    align-items: center;

    justify-content: center;

    color: #777777;

    font-size: 11px;

}


.informacoes {

    flex: 1;

}


.artista {

    color: #999999;

    font-size: 13px;

    margin-bottom: 7px;

}


.nome {

    font-size: 20px;

    font-weight: bold;

    margin-bottom: 10px;

}


.genero {

    color: #777777;

    font-size: 12px;

}


.preco {

    margin-top: 15px;

    font-weight: bold;

}


/* =====================================================
   CONTROLES
===================================================== */

.controles {

    display: flex;

    align-items: center;

    gap: 8px;

    margin-top: 15px;

}


.controle {

    width: 32px;

    height: 32px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        #20202a;

    border:
        1px solid #3a3a45;

    border-radius:
        8px;

    color: white;

    text-decoration: none;

    font-weight: bold;

}


.controle:hover {

    background:
        #30303a;

}


.quantidade {

    min-width: 35px;

    text-align: center;

    font-weight: bold;

}


.excluir {

    margin-top: 15px;

    display: inline-block;

    color: #ff7d89;

    text-decoration: none;

    font-size: 12px;

}


.excluir:hover {

    text-decoration: underline;

}


/* =====================================================
   RESUMO
===================================================== */

.resumo {

    height: fit-content;

    position: sticky;

    top: 20px;

    background:
        #111117;

    border:
        1px solid #292932;

    border-radius:
        18px;

    padding: 25px;

}


.resumo h2 {

    margin-top: 0;

}


.linha {

    display: flex;

    justify-content: space-between;

    padding:
        13px 0;

    border-bottom:
        1px solid #25252d;

    color: #aaaaaa;

}


.total {

    display: flex;

    justify-content: space-between;

    margin-top: 20px;

    font-size: 21px;

    font-weight: bold;

}


.botao {

    display: block;

    width: 100%;

    margin-top: 22px;

    padding: 15px;

    text-align: center;

    text-decoration: none;

    border-radius: 10px;

    background: white;

    color: #111111;

    font-weight: bold;

}


.botao:hover {

    opacity: .9;

}


.continuar {

    display: block;

    text-align: center;

    margin-top: 15px;

    color: #aaaaaa;

    text-decoration: none;

    font-size: 13px;

}


.continuar:hover {

    color: white;

}


/* =====================================================
   CARRINHO VAZIO
===================================================== */

.vazio {

    text-align: center;

    background:
        #111117;

    border:
        1px solid #292932;

    border-radius:
        20px;

    padding:
        70px 30px;

}


.vazio .icone {

    font-size: 55px;

    margin-bottom: 15px;

}


.vazio h2 {

    margin: 0;

}


.vazio p {

    color: #777777;

}


.vazio a {

    display: inline-block;

    margin-top: 15px;

    background: white;

    color: #111111;

    text-decoration: none;

    padding:
        13px 22px;

    border-radius: 9px;

    font-weight: bold;

}


/* =====================================================
   RESPONSIVO
===================================================== */

@media (max-width: 850px) {

    .layout {

        grid-template-columns:
            1fr;

    }


    .resumo {

        position:
            static;

    }

}


@media (max-width: 600px) {

    nav a:not(.carrinho-topo) {

        display: none;

    }


    .item {

        flex-direction:
            column;

    }


    .imagem,
    .sem-imagem {

        width: 100%;

        height: 220px;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     HEADER
===================================================== -->

<header>


    <a
        href="index.php"
        class="logo"
    >

        <span>◉</span>
        LojaCD

    </a>


    <nav>

        <a href="index.php">
            Início
        </a>

        <a href="produtos.php">
            Discos
        </a>

        <a
            href="carrinho.php"
            class="carrinho-topo"
        >

            🛒 Carrinho
            (<?php echo $quantidade_total; ?>)

        </a>

    </nav>


</header>



<!-- =====================================================
     CONTEÚDO
===================================================== -->

<main class="container">


    <div class="titulo">

        <h1>
            Seu carrinho
        </h1>

        <p>
            Confira seus discos antes de continuar.
        </p>

    </div>



    <?php if (empty($produtos)): ?>


        <!-- =============================================
             CARRINHO VAZIO
        ============================================== -->

        <div class="vazio">

            <div class="icone">
                🛒
            </div>

            <h2>
                Seu carrinho está vazio
            </h2>

            <p>
                Adicione alguns discos para começar sua compra.
            </p>

            <a href="produtos.php">
                Explorar discos
            </a>

        </div>


    <?php else: ?>


        <div class="layout">


            <!-- =========================================
                 PRODUTOS
            ========================================== -->

            <section class="itens">


                <?php foreach (
                    $produtos
                    as $produto
                ): ?>


                    <article class="item">


                        <!-- IMAGEM -->

                        <?php if (
                            !empty(
                                $produto["imagem"]
                            )
                        ): ?>

                            <img
                                src="<?php

                                echo htmlspecialchars(
                                    $produto["imagem"]
                                );

                                ?>"
                                class="imagem"
                                alt="<?php

                                echo htmlspecialchars(
                                    $produto["nome"]
                                );

                                ?>"
                            >

                        <?php else: ?>

                            <div class="sem-imagem">

                                💿

                            </div>

                        <?php endif; ?>



                        <!-- INFORMAÇÕES -->

                        <div class="informacoes">


                            <div class="artista">

                                <?php

                                echo htmlspecialchars(
                                    $produto["artista"]
                                );

                                ?>

                            </div>


                            <div class="nome">

                                <?php

                                echo htmlspecialchars(
                                    $produto["nome"]
                                );

                                ?>

                            </div>


                            <div class="genero">

                                <?php

                                echo htmlspecialchars(
                                    $produto["genero"] ?? ""
                                );

                                ?>

                            </div>


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

                                cada

                            </div>



                            <!-- QUANTIDADE -->

                            <div class="controles">


                                <a
                                    href="carrinho.php?diminuir=<?php

                                    echo intval(
                                        $produto["id"]
                                    );

                                    ?>"
                                    class="controle"
                                >

                                    −

                                </a>


                                <span class="quantidade">

                                    <?php

                                    echo intval(
                                        $produto["quantidade"]
                                    );

                                    ?>

                                </span>


                                <a
                                    href="carrinho.php?aumentar=<?php

                                    echo intval(
                                        $produto["id"]
                                    );

                                    ?>"
                                    class="controle"
                                >

                                    +

                                </a>


                            </div>



                            <!-- EXCLUIR -->

                            <a
                                href="carrinho.php?excluir=<?php

                                echo intval(
                                    $produto["id"]
                                );

                                ?>"
                                class="excluir"
                                onclick="return confirm('Deseja remover este disco do carrinho?');"
                            >

                                🗑 Remover do carrinho

                            </a>


                        </div>


                    </article>


                <?php endforeach; ?>


            </section>



            <!-- =========================================
                 RESUMO
            ========================================== -->

            <aside class="resumo">


                <h2>
                    Resumo da compra
                </h2>


                <div class="linha">

                    <span>
                        Itens
                    </span>

                    <span>

                        <?php

                        echo $quantidade_total;

                        ?>

                    </span>

                </div>


                <div class="linha">

                    <span>
                        Subtotal
                    </span>

                    <span>

                        R$

                        <?php

                        echo number_format(
                            $total,
                            2,
                            ",",
                            "."
                        );

                        ?>

                    </span>

                </div>


                <div class="linha">

                    <span>
                        Frete
                    </span>

                    <span>
                        A calcular
                    </span>

                </div>


                <div class="total">

                    <span>
                        Total
                    </span>

                    <span>

                        R$

                        <?php

                        echo number_format(
                            $total,
                            2,
                            ",",
                            "."
                        );

                        ?>

                    </span>

                </div>


                <a
                    href="checkout.php"
                    class="botao"
                >

                    Continuar para compra →

                </a>


                <a
                    href="produtos.php"
                    class="continuar"
                >

                    ← Continuar comprando

                </a>


            </aside>


        </div>


    <?php endif; ?>


</main>


</body>

</html>