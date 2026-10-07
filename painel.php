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
   NOME DO ADMINISTRADOR
===================================================== */

$nomeAdmin = "Administrador";

if (isset($_SESSION["admin_nome"])) {
    $nomeAdmin = $_SESSION["admin_nome"];
}

if (isset($_SESSION["administrador_nome"])) {
    $nomeAdmin = $_SESSION["administrador_nome"];
}


/* =====================================================
   ESTATÍSTICAS - PRODUTOS
===================================================== */

$totalProdutos = 0;

$resultado = $conexao->query(
    "SELECT COUNT(*) AS total FROM produtos"
);

if ($resultado) {

    $dados = $resultado->fetch_assoc();

    $totalProdutos = intval($dados["total"]);
}


/* =====================================================
   ESTATÍSTICAS - ESTOQUE
===================================================== */

$totalEstoque = 0;

$resultado = $conexao->query(
    "SELECT COALESCE(SUM(estoque),0) AS total FROM produtos"
);

if ($resultado) {

    $dados = $resultado->fetch_assoc();

    $totalEstoque = intval($dados["total"]);
}


/* =====================================================
   ESTATÍSTICAS - PEDIDOS
===================================================== */

$totalPedidos = 0;

$resultado = $conexao->query(
    "SELECT COUNT(*) AS total FROM pedidos"
);

if ($resultado) {

    $dados = $resultado->fetch_assoc();

    $totalPedidos = intval($dados["total"]);
}


/* =====================================================
   ESTATÍSTICAS - FATURAMENTO
===================================================== */

$faturamento = 0;

$resultado = $conexao->query(
    "SELECT COALESCE(SUM(total),0) AS total FROM pedidos"
);

if ($resultado) {

    $dados = $resultado->fetch_assoc();

    $faturamento = floatval($dados["total"]);
}


/* =====================================================
   BUSCAR PRODUTOS
===================================================== */

$resultProdutos = $conexao->query(
    "SELECT * FROM produtos ORDER BY id DESC"
);

if (!$resultProdutos) {

    die(
        "Erro ao buscar produtos: " .
        $conexao->error
    );

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

<title>Painel Administrativo | LojaCD</title>


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

    color: white;

    min-height: 100vh;

    background:
        radial-gradient(
            circle at top left,
            #321449,
            #09090d 65%
        );

}


/* =====================================================
   HEADER
===================================================== */

header {

    height: 75px;

    padding: 0 5%;

    display: flex;

    align-items: center;

    justify-content: space-between;

    background: #09090d;

    border-bottom:
        1px solid #292933;

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

    gap: 22px;

}


.menu a {

    color: #aaa;

    text-decoration: none;

    font-size: 14px;

}


.menu a:hover {

    color: white;

}


.admin-nome {

    color: white;

    font-weight: bold;

}


.sair {

    color: #ff7180 !important;

}


/* =====================================================
   CONTAINER
===================================================== */

.container {

    width: 92%;

    max-width: 1250px;

    margin: auto;

    padding: 45px 0 80px;

}


/* =====================================================
   TÍTULO
===================================================== */

.titulo {

    margin-bottom: 30px;

}


.titulo h1 {

    margin: 0;

    font-size: 38px;

}


.titulo p {

    color: #999;

    margin-top: 8px;

}


/* =====================================================
   MENSAGENS
===================================================== */

.mensagem {

    padding: 15px 20px;

    border-radius: 10px;

    margin-bottom: 25px;

    font-size: 14px;

}


.mensagem-sucesso {

    background: #12351f;

    border:
        1px solid #287a45;

    color: #8ff0ad;

}


.mensagem-aviso {

    background: #332914;

    border:
        1px solid #80652a;

    color: #f3d98a;

}


/* =====================================================
   ESTATÍSTICAS
===================================================== */

.estatisticas {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 15px;

    margin-bottom: 30px;

}


.estatistica {

    background: #111117;

    border:
        1px solid #292932;

    border-radius: 16px;

    padding: 23px;

}


.label {

    color: #888;

    font-size: 12px;

    text-transform: uppercase;

    margin-bottom: 12px;

}


.numero {

    font-size: 27px;

    font-weight: bold;

}


.roxo {

    color: #b15cff;

}


/* =====================================================
   ATALHOS
===================================================== */

.atalhos {

    display: flex;

    gap: 12px;

    margin-bottom: 30px;

    flex-wrap: wrap;

}


.atalho {

    display: inline-flex;

    align-items: center;

    padding: 13px 19px;

    border-radius: 10px;

    text-decoration: none;

    font-size: 13px;

    font-weight: bold;

}


.atalho-principal {

    background: white;

    color: #111;

}


.atalho-secundario {

    background: #17171e;

    color: white;

    border:
        1px solid #35353f;

}


/* =====================================================
   SEÇÃO
===================================================== */

.secao {

    background: #111117;

    border:
        1px solid #292932;

    border-radius: 18px;

    padding: 28px;

    margin-bottom: 30px;

}


.secao-cabecalho {

    margin-bottom: 25px;

}


.secao-cabecalho h2 {

    margin: 0;

    font-size: 23px;

}


.secao-cabecalho p {

    color: #888;

    font-size: 13px;

    margin-top: 7px;

}


/* =====================================================
   PRODUTOS
===================================================== */

.produtos {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 18px;

}


.produto {

    background: #0c0c11;

    border:
        1px solid #292932;

    border-radius: 15px;

    overflow: hidden;

    transition: .2s;

}


.produto:hover {

    transform:
        translateY(-3px);

    border-color:
        #a95cff;

}


.produto-imagem {

    height: 190px;

    background: #19191f;

    display: flex;

    align-items: center;

    justify-content: center;

    overflow: hidden;

}


.produto-imagem img {

    width: 100%;

    height: 100%;

    object-fit: cover;

}


.sem-imagem {

    font-size: 55px;

    opacity: .6;

}


.produto-info {

    padding: 18px;

}


.produto-info h3 {

    margin: 0 0 7px;

    font-size: 17px;

}


.artista {

    color: #888;

    font-size: 13px;

    margin-bottom: 15px;

}


.dados {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 17px;

}


.preco {

    color: #b15cff;

    font-size: 18px;

    font-weight: bold;

}


.estoque {

    color: #999;

    font-size: 12px;

}


/* =====================================================
   BOTÕES DOS PRODUTOS
===================================================== */

.acoes-produto {

    display: grid;

    grid-template-columns:
        1fr 1fr 1fr;

    gap: 7px;

}


.btn {

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 10px 5px;

    border-radius: 8px;

    text-decoration: none;

    font-size: 11px;

    font-weight: bold;

}


.editar {

    background: #21142d;

    color: #c98cff;

    border:
        1px solid #4a2a62;

}


.editar:hover {

    background: #2e1940;

}


.excluir {

    background: #30151a;

    color: #ff8995;

    border:
        1px solid #5a2931;

}


.excluir:hover {

    background: #451c23;

}


.ver {

    background: #17171e;

    color: #ddd;

    border:
        1px solid #33333d;

}


.ver:hover {

    background: #22222a;

}


/* =====================================================
   FORMULÁRIO
===================================================== */

.form-grid {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 17px;

}


.campo {

    display: flex;

    flex-direction: column;

    gap: 7px;

}


.campo-full {

    grid-column:
        1 / -1;

}


label {

    color: #aaa;

    font-size: 13px;

}


input,
select,
textarea {

    width: 100%;

    padding: 13px;

    border-radius: 9px;

    border:
        1px solid #363640;

    background: #0b0b10;

    color: white;

    outline: none;

}


input:focus,
select:focus,
textarea:focus {

    border-color:
        #a95cff;

}


textarea {

    min-height: 110px;

    resize: vertical;

}


.btn-adicionar {

    border: none;

    background: white;

    color: #111;

    padding: 14px 22px;

    border-radius: 9px;

    font-weight: bold;

    cursor: pointer;

}


/* =====================================================
   RESPONSIVO
===================================================== */

@media (max-width: 950px) {

    .estatisticas {

        grid-template-columns:
            repeat(2, 1fr);

    }


    .produtos {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media (max-width: 650px) {

    .admin-nome {

        display: none;

    }


    .estatisticas {

        grid-template-columns:
            1fr;

    }


    .produtos {

        grid-template-columns:
            1fr;

    }


    .form-grid {

        grid-template-columns:
            1fr;

    }


    .campo-full {

        grid-column:
            auto;

    }


    .acoes-produto {

        grid-template-columns:
            1fr;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     CABEÇALHO
===================================================== -->

<header>


    <a
        href="painel.php"
        class="logo"
    >

        <span>◉</span>

        LojaCD

    </a>


    <nav class="menu">


        <span class="admin-nome">

            👤

            <?php

            echo htmlspecialchars(
                $nomeAdmin
            );

            ?>

        </span>


        <a href="../index.php">
            Ver loja
        </a>


        <a
            href="pedidos.php"
            >
            Pedidos
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


<!-- =====================================================
     TÍTULO
===================================================== -->

<section class="titulo">

    <h1>
        Painel Administrativo
    </h1>

    <p>
        Gerencie os discos, estoque e pedidos da LojaCD.
    </p>

</section>



<!-- =====================================================
     MENSAGEM DE EXCLUSÃO
===================================================== -->

<?php if (
    isset($_GET["produto_excluido"])
): ?>

    <div class="mensagem mensagem-sucesso">

        ✅ O disco

        <strong>

            <?php

            echo htmlspecialchars(
                $_GET["produto_excluido"]
            );

            ?>

        </strong>

        foi excluído com sucesso!

    </div>

<?php endif; ?>



<!-- =====================================================
     MENSAGEM DE DESATIVAÇÃO
===================================================== -->

<?php if (
    isset($_GET["produto_desativado"])
): ?>

    <div class="mensagem mensagem-aviso">

        ⚠️ O disco

        <strong>

            <?php

            echo htmlspecialchars(
                $_GET["produto_desativado"]
            );

            ?>

        </strong>

        já possui pedidos e foi retirado da venda.

    </div>

<?php endif; ?>



<!-- =====================================================
     ESTATÍSTICAS
===================================================== -->

<section class="estatisticas">


    <div class="estatistica">

        <div class="label">
            Produtos
        </div>

        <div class="numero">

            <?php

            echo $totalProdutos;

            ?>

        </div>

    </div>



    <div class="estatistica">

        <div class="label">
            Pedidos
        </div>

        <div class="numero">

            <?php

            echo $totalPedidos;

            ?>

        </div>

    </div>



    <div class="estatistica">

        <div class="label">
            Estoque
        </div>

        <div class="numero">

            <?php

            echo $totalEstoque;

            ?>

        </div>

    </div>



    <div class="estatistica">

        <div class="label">
            Faturamento
        </div>

        <div class="numero roxo">

            R$

            <?php

            echo number_format(
                $faturamento,
                2,
                ",",
                "."
            );

            ?>

        </div>

    </div>


</section>



<!-- =====================================================
     ATALHOS
===================================================== -->

<div class="atalhos">


    <a
        href="pedidos.php"
        class="atalho atalho-principal"
    >

        📦 Ver pedidos

    </a>


    <a
        href="../index.php"
        class="atalho atalho-secundario"
    >

        🛍️ Ver loja

    </a>


</div>



<!-- =====================================================
     LISTA DE PRODUTOS
===================================================== -->

<section class="secao">


    <div class="secao-cabecalho">

        <h2>
            💿 Discos cadastrados
        </h2>

        <p>
            Edite, exclua ou visualize os produtos.
        </p>

    </div>



    <div class="produtos">


        <?php if (
            $resultProdutos->num_rows > 0
        ): ?>


            <?php while (
                $produto =
                $resultProdutos->fetch_assoc()
            ): ?>


                <article class="produto">


                    <!-- IMAGEM -->

                    <div class="produto-imagem">


                        <?php

                        $imagem =
                            trim(
                                $produto["imagem"] ?? ""
                            );

                        ?>


                        <?php if (
                            $imagem !== ""
                        ): ?>

                            <img
                                src="<?php

                                echo htmlspecialchars(
                                    $imagem
                                );

                                ?>"
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


                    </div>



                    <!-- DADOS -->

                    <div class="produto-info">


                        <h3>

                            <?php

                            echo htmlspecialchars(
                                $produto["nome"]
                            );

                            ?>

                        </h3>


                        <div class="artista">

                            <?php

                            echo htmlspecialchars(
                                $produto["artista"]
                            );

                            ?>

                        </div>



                        <div class="dados">


                            <span class="preco">

                                R$

                                <?php

                                echo number_format(
                                    floatval(
                                        $produto["preco"]
                                    ),
                                    2,
                                    ",",
                                    "."
                                );

                                ?>

                            </span>


                            <span class="estoque">

                                Estoque:

                                <?php

                                echo intval(
                                    $produto["estoque"]
                                );

                                ?>

                            </span>


                        </div>



                        <!-- =================================================
                             BOTÕES
                        ================================================== -->

                        <div class="acoes-produto">


                            <!-- EDITAR -->

                            <a
                                href="editar_produto.php?id=<?php

                                echo intval(
                                    $produto["id"]
                                );

                                ?>"
                                class="btn editar"
                            >

                                ✏️ Editar

                            </a>



                            <!-- EXCLUIR -->

                            <a
                                href="excluir_produto.php?id=<?php

                                echo intval(
                                    $produto["id"]
                                );

                                ?>"
                                class="btn excluir"

                                onclick="return confirm('Tem certeza que deseja excluir este disco?');"
                            >

                                🗑️ Excluir

                            </a>



                            <!-- VER -->

                            <a
                                href="../produto.php?id=<?php

                                echo intval(
                                    $produto["id"]
                                );

                                ?>"
                                class="btn ver"
                            >

                                👁️ Ver

                            </a>


                        </div>


                    </div>


                </article>


            <?php endwhile; ?>


        <?php else: ?>


            <div
                style="
                    color:#777;
                    padding:30px;
                "
            >

                💿 Nenhum disco cadastrado.

            </div>


        <?php endif; ?>


    </div>


</section>



<!-- =====================================================
     ADICIONAR NOVO DISCO
===================================================== -->

<section class="secao">


    <div class="secao-cabecalho">

        <h2>
            ➕ Adicionar novo disco
        </h2>

        <p>
            Cadastre um novo produto na LojaCD.
        </p>

    </div>



    <form
        action="adicionar_produto.php"
        method="POST"
    >


        <div class="form-grid">


            <div class="campo">

                <label>
                    Nome do disco *
                </label>

                <input
                    type="text"
                    name="nome"
                    placeholder="Nome do disco"
                    required
                >

            </div>



            <div class="campo">

                <label>
                    Artista *
                </label>

                <input
                    type="text"
                    name="artista"
                    placeholder="Nome do artista"
                    required
                >

            </div>



            <div class="campo">

                <label>
                    Gênero
                </label>

                <select name="genero">

                    <option value="">
                        Selecione
                    </option>

                    <option value="Rock">
                        Rock
                    </option>

                    <option value="Pop">
                        Pop
                    </option>

                    <option value="MPB">
                        MPB
                    </option>

                    <option value="Jazz">
                        Jazz
                    </option>

                    <option value="Sertanejo">
                        Sertanejo
                    </option>

                    <option value="Rap">
                        Rap
                    </option>

                    <option value="Eletrônica">
                        Eletrônica
                    </option>

                    <option value="Reggae">
                        Reggae
                    </option>

                    <option value="Outro">
                        Outro
                    </option>

                </select>

            </div>



            <div class="campo">

                <label>
                    Ano
                </label>

                <input
                    type="number"
                    name="ano"
                    placeholder="2026"
                    min="1900"
                    max="2100"
                >

            </div>



            <div class="campo">

                <label>
                    Preço *
                </label>

                <input
                    type="number"
                    name="preco"
                    step="0.01"
                    min="0.01"
                    placeholder="29.90"
                    required
                >

            </div>



            <div class="campo">

                <label>
                    Estoque *
                </label>

                <input
                    type="number"
                    name="estoque"
                    min="0"
                    placeholder="10"
                    required
                >

            </div>



            <div class="campo campo-full">

                <label>
                    Caminho da imagem
                </label>

                <input
                    type="text"
                    name="imagem"
                    placeholder="assets/discos/disco.jpg"
                >

            </div>



            <div class="campo campo-full">

                <label>
                    Descrição
                </label>

                <textarea
                    name="descricao"
                    placeholder="Descrição do disco..."
                ></textarea>

            </div>



            <div class="campo campo-full">

                <button
                    type="submit"
                    class="btn-adicionar"
                >

                    ➕ Cadastrar disco

                </button>

            </div>


        </div>


    </form>


</section>



<!-- =====================================================
     PEDIDOS
===================================================== -->

<section class="secao">


    <div class="secao-cabecalho">

        <h2>
            📦 Gerenciamento de pedidos
        </h2>

        <p>
            Consulte as compras realizadas pelos clientes.
        </p>

    </div>


    <a
        href="pedidos.php"
        class="atalho atalho-principal"
    >

        📦 Abrir gerenciamento de pedidos →

    </a>


</section>


</main>


</body>

</html>