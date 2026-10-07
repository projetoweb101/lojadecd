<?php

session_start();

require_once "../conexao.php";


/* =====================================================
   PROTEGER ÁREA ADMINISTRATIVA
===================================================== */

if (!isset($_SESSION["admin_id"])) {

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
    "SELECT *
     FROM produtos
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {

    $stmt->close();

    header("Location: painel.php");

    exit;
}

$produto = $resultado->fetch_assoc();

$stmt->close();


$erro = "";


/* =====================================================
   ATUALIZAR PRODUTO
===================================================== */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {

    $nome =
        trim($_POST["nome"] ?? "");

    $artista =
        trim($_POST["artista"] ?? "");

    $genero =
        trim($_POST["genero"] ?? "");

    $ano =
        intval($_POST["ano"] ?? 0);

    $preco =
        floatval($_POST["preco"] ?? 0);

    $estoque =
        intval($_POST["estoque"] ?? 0);

    $descricao =
        trim($_POST["descricao"] ?? "");

    $destaque =
        isset($_POST["destaque"]) ? 1 : 0;


    /* =================================================
       VALIDAR
    ================================================= */

    if (
        $nome === ""
        || $artista === ""
        || $preco <= 0
        || $estoque < 0
    ) {

        $erro =
            "Preencha corretamente os campos obrigatórios.";

    } else {


        /* =============================================
           MANTER IMAGEM ATUAL
        ============================================= */

        $imagem =
            $produto["imagem"] ?? "";


        /* =============================================
           NOVA IMAGEM
        ============================================= */

        if (
            isset($_FILES["imagem"])
            && $_FILES["imagem"]["error"]
            === UPLOAD_ERR_OK
        ) {

            $pasta =
                "../assets/produtos/";


            if (!is_dir($pasta)) {

                mkdir(
                    $pasta,
                    0777,
                    true
                );

            }


            $extensao =
                strtolower(
                    pathinfo(
                        $_FILES["imagem"]["name"],
                        PATHINFO_EXTENSION
                    )
                );


            $permitidas = [

                "jpg",
                "jpeg",
                "png",
                "webp"

            ];


            if (
                in_array(
                    $extensao,
                    $permitidas
                )
            ) {

                $novo_nome =
                    uniqid(
                        "disco_",
                        true
                    )
                    . "."
                    . $extensao;


                $destino =
                    $pasta
                    . $novo_nome;


                if (
                    move_uploaded_file(
                        $_FILES["imagem"]["tmp_name"],
                        $destino
                    )
                ) {

                    $imagem =
                        "assets/produtos/"
                        . $novo_nome;

                }

            } else {

                $erro =
                    "Formato de imagem inválido. "
                    . "Use JPG, JPEG, PNG ou WEBP.";

            }

        }


        /* =============================================
           ATUALIZAR BANCO
        ============================================= */

        if ($erro === "") {

            $stmt = $conexao->prepare(
                "UPDATE produtos
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
                 WHERE id = ?"
            );


            if (!$stmt) {

                $erro =
                    "Erro ao preparar atualização: "
                    . $conexao->error;

            } else {

                $stmt->bind_param(
                    "sssdiissii",
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

                    $stmt->close();


                    header(
                        "Location: painel.php?editado=1"
                    );

                    exit;

                } else {

                    $erro =
                        "Erro ao atualizar produto: "
                        . $stmt->error;

                    $stmt->close();

                }

            }

        }

    }


    /* =============================================
       ATUALIZAR VALORES NA TELA
    ============================================= */

    $produto["nome"] =
        $nome;

    $produto["artista"] =
        $artista;

    $produto["genero"] =
        $genero;

    $produto["ano"] =
        $ano;

    $produto["preco"] =
        $preco;

    $produto["estoque"] =
        $estoque;

    $produto["descricao"] =
        $descricao;

    $produto["imagem"] =
        $imagem;

    $produto["destaque"] =
        $destaque;

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
    Editar Disco | LojaCD
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

    color: white;

    min-height: 100vh;

    background:
        radial-gradient(
            circle at top left,
            #28143b,
            #08080c 55%
        );

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

    text-decoration: none;

    color: white;

    font-size: 25px;

    font-weight: bold;

}


.logo span {

    color: #b56cff;

}


.voltar {

    color: #aaaaaa;

    text-decoration: none;

    font-size: 13px;

}


.voltar:hover {

    color: white;

}


/* =====================================================
   CONTAINER
===================================================== */

.container {

    width: 90%;

    max-width: 900px;

    margin:
        auto;

    padding:
        45px 0 80px;

}


.titulo {

    margin-bottom:
        25px;

}


.titulo h1 {

    margin: 0;

    font-size: 35px;

}


.titulo p {

    color: #777777;

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
        20px;

    padding:
        30px;

}


/* =====================================================
   IMAGEM ATUAL
===================================================== */

.imagem-atual {

    margin-bottom:
        30px;

}


.imagem-atual h3 {

    margin-top: 0;

    font-size: 15px;

    color: #aaaaaa;

}


.imagem-atual img {

    width: 150px;

    height: 150px;

    object-fit: cover;

    border-radius: 12px;

    border:
        1px solid #33333d;

}


.sem-imagem {

    width: 150px;

    height: 150px;

    border-radius: 12px;

    background:
        #20202a;

    display: flex;

    align-items: center;

    justify-content: center;

    color: #777777;

    font-size: 12px;

}


/* =====================================================
   FORMULÁRIO
===================================================== */

.form-grid {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 18px;

}


.campo {

    display: flex;

    flex-direction: column;

    gap: 8px;

}


.completo {

    grid-column:
        1 / -1;

}


label {

    color:
        #aaaaaa;

    font-size:
        12px;

}


input,
select,
textarea {

    width: 100%;

    padding:
        14px;

    border:
        1px solid #35353f;

    border-radius:
        9px;

    background:
        #0b0b10;

    color:
        white;

    outline:
        none;

}


input:focus,
select:focus,
textarea:focus {

    border-color:
        #b56cff;

}


textarea {

    min-height:
        120px;

    resize:
        vertical;

}


input[type="file"] {

    padding:
        10px;

}


.checkbox {

    flex-direction:
        row;

    align-items:
        center;

    margin-top:
        15px;

}


.checkbox input {

    width:
        auto;

}


/* =====================================================
   BOTÕES
===================================================== */

.botoes {

    display: flex;

    gap: 12px;

    margin-top:
        25px;

}


.salvar {

    border: none;

    background:
        white;

    color:
        #111111;

    padding:
        14px 25px;

    border-radius:
        9px;

    font-weight:
        bold;

    cursor:
        pointer;

}


.cancelar {

    text-decoration:
        none;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    padding:
        14px 25px;

    border-radius:
        9px;

    border:
        1px solid #35353f;

    color:
        #aaaaaa;

}


.cancelar:hover {

    color:
        white;

}


/* =====================================================
   ERRO
===================================================== */

.erro {

    background:
        #3a191e;

    border:
        1px solid #73323b;

    color:
        #ffb7bf;

    padding:
        14px;

    border-radius:
        10px;

    margin-bottom:
        25px;

}


/* =====================================================
   RESPONSIVO
===================================================== */

@media (max-width: 650px) {

    .form-grid {

        grid-template-columns:
            1fr;

    }


    .completo {

        grid-column:
            auto;

    }


    .botoes {

        flex-direction:
            column;

    }

}

</style>

</head>


<body>


<header>


    <a
        href="../index.php"
        class="logo"
    >

        <span>◉</span>
        LojaCD

    </a>


    <a
        href="painel.php"
        class="voltar"
    >

        ← Voltar ao painel

    </a>


</header>



<main class="container">


    <div class="titulo">

        <h1>
            Editar disco
        </h1>

        <p>
            Altere as informações do produto abaixo.
        </p>

    </div>



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


        <!-- =============================================
             IMAGEM ATUAL
        ============================================== -->

        <div class="imagem-atual">

            <h3>
                Imagem atual
            </h3>


            <?php if (
                !empty(
                    $produto["imagem"]
                )
            ): ?>

                <img
                    src="../<?php

                    echo htmlspecialchars(
                        $produto["imagem"]
                    );

                    ?>"
                    alt="Imagem do disco"
                >

            <?php else: ?>

                <div class="sem-imagem">

                    SEM IMAGEM

                </div>

            <?php endif; ?>


        </div>



        <!-- =============================================
             FORMULÁRIO
        ============================================== -->

        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <div class="form-grid">


                <div class="campo">

                    <label>
                        Nome do disco *
                    </label>

                    <input
                        type="text"
                        name="nome"
                        value="<?php

                        echo htmlspecialchars(
                            $produto["nome"]
                        );

                        ?>"
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
                        value="<?php

                        echo htmlspecialchars(
                            $produto["artista"]
                        );

                        ?>"
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


                        <?php

                        $generos = [

                            "Rock",
                            "Pop",
                            "MPB",
                            "Jazz",
                            "Sertanejo",
                            "Rap",
                            "Eletrônica",
                            "R&B"

                        ];

                        ?>


                        <?php foreach (
                            $generos
                            as $genero
                        ): ?>


                            <option
                                value="<?php

                                echo htmlspecialchars(
                                    $genero
                                );

                                ?>"
                                <?php

                                if (
                                    $produto["genero"]
                                    ===
                                    $genero
                                ) {

                                    echo "selected";

                                }

                                ?>
                            >

                                <?php

                                echo htmlspecialchars(
                                    $genero
                                );

                                ?>

                            </option>


                        <?php endforeach; ?>


                    </select>

                </div>



                <div class="campo">

                    <label>
                        Ano
                    </label>

                    <input
                        type="number"
                        name="ano"
                        value="<?php

                        echo intval(
                            $produto["ano"]
                        );

                        ?>"
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
                        min="0"
                        value="<?php

                        echo htmlspecialchars(
                            $produto["preco"]
                        );

                        ?>"
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
                        value="<?php

                        echo intval(
                            $produto["estoque"]
                        );

                        ?>"
                        required
                    >

                </div>



                <div class="campo completo">

                    <label>
                        Descrição
                    </label>

                    <textarea
                        name="descricao"
                    ><?php

                    echo htmlspecialchars(
                        $produto["descricao"] ?? ""
                    );

                    ?></textarea>

                </div>



                <div class="campo completo">

                    <label>
                        Trocar imagem
                    </label>

                    <input
                        type="file"
                        name="imagem"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                </div>


            </div>



            <div class="checkbox">

                <input
                    type="checkbox"
                    name="destaque"
                    value="1"
                    id="destaque"
                    <?php

                    if (
                        !empty(
                            $produto["destaque"]
                        )
                    ) {

                        echo "checked";

                    }

                    ?>
                >

                <label for="destaque">

                    ⭐ Colocar este disco em destaque

                </label>

            </div>



            <div class="botoes">


                <button
                    type="submit"
                    class="salvar"
                >

                    💾 Salvar alterações

                </button>


                <a
                    href="painel.php"
                    class="cancelar"
                >

                    Cancelar

                </a>


            </div>


        </form>


    </section>


</main>


</body>

</html>