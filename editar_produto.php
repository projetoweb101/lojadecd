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
   PEGAR ID DO PRODUTO
===================================================== */

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: painel.php");
    exit;
}


/* =====================================================
   BUSCAR PRODUTO
===================================================== */

$stmt = $conexao->prepare(
    "SELECT * FROM produtos WHERE id = ? LIMIT 1"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {

    $stmt->close();

    die("Produto não encontrado.");

}

$produto = $resultado->fetch_assoc();

$stmt->close();


/* =====================================================
   ATUALIZAR PRODUTO
===================================================== */

$mensagem = "";
$erro = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $artista = trim($_POST["artista"] ?? "");
    $genero = trim($_POST["genero"] ?? "");
    $ano = intval($_POST["ano"] ?? 0);
    $preco = floatval($_POST["preco"] ?? 0);
    $estoque = intval($_POST["estoque"] ?? 0);
    $descricao = trim($_POST["descricao"] ?? "");
    $imagem = trim($_POST["imagem"] ?? "");
    $destaque = isset($_POST["destaque"]) ? 1 : 0;


    if (
        $nome === "" ||
        $artista === "" ||
        $preco <= 0
    ) {

        $erro =
            "Preencha o nome, artista e preço corretamente.";

    } elseif ($estoque < 0) {

        $erro =
            "O estoque não pode ser negativo.";

    } else {


        $sql = "
            UPDATE produtos
            SET
                nome = ?,
                artista = ?,
                genero = ?,
                ano = ?,
                preco = ?,
                estoque = ?,
                descricao = ?,
                imagem = ?,
                destaque = ?
            WHERE id = ?
        ";


        $stmt = $conexao->prepare($sql);


        if ($stmt) {

            $stmt->bind_param(
                "sssidiisii",
                $nome,
                $artista,
                $genero,
                $ano,
                $preco,
                $estoque,
                $descricao,
                $imagem,
                $destaque,
                $id
            );


            if ($stmt->execute()) {

                $mensagem =
                    "Disco atualizado com sucesso!";


                /* Atualizar dados exibidos no formulário */

                $produto["nome"] = $nome;
                $produto["artista"] = $artista;
                $produto["genero"] = $genero;
                $produto["ano"] = $ano;
                $produto["preco"] = $preco;
                $produto["estoque"] = $estoque;
                $produto["descricao"] = $descricao;
                $produto["imagem"] = $imagem;
                $produto["destaque"] = $destaque;

            } else {

                $erro =
                    "Não foi possível atualizar o disco.";

            }


            $stmt->close();

        } else {

            $erro =
                "Erro ao preparar a atualização.";

        }
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
    Editar disco | LojaCD
</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    min-height: 100vh;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    color: white;

    background:
        radial-gradient(
            circle at top left,
            #2d1542,
            #09090d 65%
        );

}


/* =====================================================
   CABEÇALHO
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

    gap: 22px;

    align-items: center;

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

    color: #ff7b87 !important;

}


/* =====================================================
   CONTAINER
===================================================== */

.container {

    width: 92%;

    max-width: 900px;

    margin: auto;

    padding:
        45px 0 70px;

}


/* =====================================================
   TÍTULO
===================================================== */

.titulo {

    margin-bottom: 25px;

}


.titulo h1 {

    margin: 0;

    font-size: 36px;

}


.titulo p {

    color: #888;

    margin-top: 8px;

}


/* =====================================================
   CARD
===================================================== */

.card {

    background:
        #111117;

    border:
        1px solid #292932;

    border-radius:
        18px;

    padding:
        30px;

}


/* =====================================================
   MENSAGENS
===================================================== */

.sucesso {

    padding:
        14px 18px;

    margin-bottom:
        20px;

    border-radius:
        10px;

    background:
        rgba(60,200,120,.12);

    border:
        1px solid
        rgba(60,200,120,.3);

    color:
        #9af0bd;

}


.erro {

    padding:
        14px 18px;

    margin-bottom:
        20px;

    border-radius:
        10px;

    background:
        rgba(255,70,90,.12);

    border:
        1px solid
        rgba(255,70,90,.3);

    color:
        #ff9ca8;

}


/* =====================================================
   FORMULÁRIO
===================================================== */

.grid {

    display:
        grid;

    grid-template-columns:
        1fr 1fr;

    gap:
        18px;

}


.campo {

    display:
        flex;

    flex-direction:
        column;

    gap:
        7px;

}


.full {

    grid-column:
        1 / -1;

}


label {

    color:
        #bbb;

    font-size:
        13px;

}


input,
select,
textarea {

    width:
        100%;

    padding:
        14px;

    border:
        1px solid #383842;

    border-radius:
        10px;

    background:
        #0c0c11;

    color:
        white;

    outline:
        none;

    font-size:
        14px;

}


input:focus,
select:focus,
textarea:focus {

    border-color:
        #a95cff;

}


textarea {

    min-height:
        130px;

    resize:
        vertical;

}


/* =====================================================
   DESTAQUE
===================================================== */

.check {

    display:
        flex;

    flex-direction:
        row;

    align-items:
        center;

    gap:
        10px;

}


.check input {

    width:
        auto;

}


/* =====================================================
   BOTÕES
===================================================== */

.botoes {

    display:
        flex;

    gap:
        12px;

    margin-top:
        10px;

}


.btn {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    padding:
        14px 22px;

    border-radius:
        10px;

    text-decoration:
        none;

    font-weight:
        bold;

    cursor:
        pointer;

    border:
        none;

}


.salvar {

    background:
        white;

    color:
        #111;

}


.voltar {

    background:
        #1a1a21;

    color:
        white;

    border:
        1px solid #33333d;

}


/* =====================================================
   PREVISUALIZAÇÃO
===================================================== */

.preview {

    margin-top:
        28px;

    padding-top:
        25px;

    border-top:
        1px solid #292932;

}


.preview h3 {

    margin:
        0 0 15px;

}


.preview-box {

    display:
        flex;

    align-items:
        center;

    gap:
        15px;

}


.capa {

    width:
        80px;

    height:
        80px;

    border-radius:
        12px;

    overflow:
        hidden;

    background:
        #20202a;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    font-size:
        28px;

}


.capa img {

    width:
        100%;

    height:
        100%;

    object-fit:
        cover;

}


.preview-info strong {

    display:
        block;

    margin-bottom:
        5px;

}


.preview-info span {

    color:
        #888;

    font-size:
        13px;

}


/* =====================================================
   RESPONSIVO
===================================================== */

@media (max-width: 650px) {

    .grid {

        grid-template-columns:
            1fr;

    }


    .full {

        grid-column:
            auto;

    }


    .botoes {

        flex-direction:
            column;

    }


    .btn {

        width:
            100%;

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


    <nav class="menu">

        <a href="painel.php">
            Painel
        </a>

        <a href="pedidos.php">
            Pedidos
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

    </nav>


</header>



<main class="container">


    <section class="titulo">

        <h1>
            ✏️ Editar disco
        </h1>

        <p>
            Altere as informações deste produto.
        </p>

    </section>



    <?php if ($mensagem !== ""): ?>

        <div class="sucesso">

            ✅

            <?php

            echo htmlspecialchars(
                $mensagem
            );

            ?>

        </div>

    <?php endif; ?>



    <?php if ($erro !== ""): ?>

        <div class="erro">

            ⚠️

            <?php

            echo htmlspecialchars(
                $erro
            );

            ?>

        </div>

    <?php endif; ?>



    <section class="card">


        <form
            method="POST"
            action="editar_produto.php?id=<?php

            echo $id;

            ?>"
        >


            <div class="grid">


                <!-- NOME -->

                <div class="campo">

                    <label for="nome">
                        Nome do disco *
                    </label>

                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        value="<?php

                        echo htmlspecialchars(
                            $produto["nome"]
                        );

                        ?>"
                        required
                    >

                </div>



                <!-- ARTISTA -->

                <div class="campo">

                    <label for="artista">
                        Artista *
                    </label>

                    <input
                        type="text"
                        id="artista"
                        name="artista"
                        value="<?php

                        echo htmlspecialchars(
                            $produto["artista"]
                        );

                        ?>"
                        required
                    >

                </div>



                <!-- GÊNERO -->

                <div class="campo">

                    <label for="genero">
                        Gênero
                    </label>

                    <select
                        id="genero"
                        name="genero"
                    >

                        <?php

                        $generos = [

                            "Rock",
                            "Pop",
                            "MPB",
                            "Jazz",
                            "Sertanejo",
                            "Rap",
                            "Eletrônica",
                            "Reggae",
                            "Outro"

                        ];

                        ?>

                        <option value="">
                            Selecione
                        </option>


                        <?php foreach (
                            $generos as $g
                        ): ?>

                            <option
                                value="<?php

                                echo htmlspecialchars(
                                    $g
                                );

                                ?>"
                                <?php

                                if (
                                    $produto["genero"]
                                    ===
                                    $g
                                ) {

                                    echo "selected";

                                }

                                ?>
                            >

                                <?php

                                echo htmlspecialchars(
                                    $g
                                );

                                ?>

                            </option>

                        <?php endforeach; ?>


                    </select>

                </div>



                <!-- ANO -->

                <div class="campo">

                    <label for="ano">
                        Ano
                    </label>

                    <input
                        type="number"
                        id="ano"
                        name="ano"
                        min="1900"
                        max="2100"
                        value="<?php

                        echo intval(
                            $produto["ano"]
                        );

                        ?>"
                    >

                </div>



                <!-- PREÇO -->

                <div class="campo">

                    <label for="preco">
                        Preço *
                    </label>

                    <input
                        type="number"
                        id="preco"
                        name="preco"
                        step="0.01"
                        min="0.01"
                        value="<?php

                        echo number_format(
                            $produto["preco"],
                            2,
                            ".",
                            ""
                        );

                        ?>"
                        required
                    >

                </div>



                <!-- ESTOQUE -->

                <div class="campo">

                    <label for="estoque">
                        Estoque
                    </label>

                    <input
                        type="number"
                        id="estoque"
                        name="estoque"
                        min="0"
                        value="<?php

                        echo intval(
                            $produto["estoque"]
                        );

                        ?>"
                    >

                </div>



                <!-- IMAGEM -->

                <div class="campo full">

                    <label for="imagem">
                        Caminho da imagem
                    </label>

                    <input
                        type="text"
                        id="imagem"
                        name="imagem"
                        value="<?php

                        echo htmlspecialchars(
                            $produto["imagem"] ?? ""
                        );

                        ?>"
                        placeholder="assets/discos/disco.jpg"
                    >

                </div>



                <!-- DESCRIÇÃO -->

                <div class="campo full">

                    <label for="descricao">
                        Descrição
                    </label>

                    <textarea
                        id="descricao"
                        name="descricao"
                    ><?php

                    echo htmlspecialchars(
                        $produto["descricao"] ?? ""
                    );

                    ?></textarea>

                </div>



                <!-- DESTAQUE -->

                <div class="campo full">

                    <label class="check">

                        <input
                            type="checkbox"
                            name="destaque"
                            value="1"
                            <?php

                            if (
                                intval(
                                    $produto["destaque"]
                                ) === 1
                            ) {

                                echo "checked";

                            }

                            ?>
                        >

                        ⭐ Mostrar este disco em destaque

                    </label>

                </div>



                <!-- BOTÕES -->

                <div class="campo full">

                    <div class="botoes">


                        <button
                            type="submit"
                            class="btn salvar"
                        >

                            💾 Salvar alterações

                        </button>


                        <a
                            href="painel.php"
                            class="btn voltar"
                        >

                            ← Voltar

                        </a>


                    </div>

                </div>


            </div>


        </form>



        <!-- =================================================
             PREVISUALIZAÇÃO
        ================================================== -->

        <div class="preview">


            <h3>
                Pré-visualização
            </h3>


            <div class="preview-box">


                <div class="capa">


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
                            alt="Capa do disco"
                        >

                    <?php else: ?>

                        💿

                    <?php endif; ?>


                </div>



                <div class="preview-info">

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $produto["nome"]
                        );

                        ?>

                    </strong>


                    <span>

                        <?php

                        echo htmlspecialchars(
                            $produto["artista"]
                        );

                        ?>

                    </span>

                </div>


            </div>


        </div>


    </section>


</main>


</body>

</html>