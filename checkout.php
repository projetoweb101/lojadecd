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


/* ==========================================
   VERIFICAR CARRINHO
========================================== */

if (
    !isset($_SESSION["carrinho"]) ||
    empty($_SESSION["carrinho"])
) {

    header("Location: carrinho.php");

    exit;

}


$usuario_id = intval($_SESSION["usuario_id"]);

$carrinho = $_SESSION["carrinho"];

$produtos = [];

$total = 0;

$erro = "";


/* ==========================================
   BUSCAR PRODUTOS
========================================== */

$ids = [];

foreach ($carrinho as $id => $quantidade) {

    if (is_numeric($id)) {

        $ids[] = intval($id);

    }

}


if (!empty($ids)) {

    $ids_sql = implode(",", $ids);


    $sql = "
        SELECT *
        FROM produtos
        WHERE id IN ($ids_sql)
    ";


    $resultado = $conexao->query($sql);


    if ($resultado) {

        while ($produto = $resultado->fetch_assoc()) {

            $id = intval($produto["id"]);

            $quantidade = intval(
                $carrinho[$id] ?? 0
            );


            if ($quantidade <= 0) {

                continue;

            }


            $preco = floatval(
                $produto["preco"]
            );


            $subtotal = $preco * $quantidade;


            $produto["quantidade"] = $quantidade;

            $produto["subtotal"] = $subtotal;


            $produtos[] = $produto;


            $total += $subtotal;

        }

    }

}


/* ==========================================
   VERIFICAR SE OS PRODUTOS EXISTEM
========================================== */

if (empty($produtos)) {

    header("Location: carrinho.php");

    exit;

}


/* ==========================================
   PROCESSAR PEDIDO
========================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $nome = trim(
        $_POST["nome"] ?? ""
    );


    $telefone = trim(
        $_POST["telefone"] ?? ""
    );


    $cep = trim(
        $_POST["cep"] ?? ""
    );


    $endereco = trim(
        $_POST["endereco"] ?? ""
    );


    $numero = trim(
        $_POST["numero"] ?? ""
    );


    $cidade = trim(
        $_POST["cidade"] ?? ""
    );


    $estado = trim(
        $_POST["estado"] ?? ""
    );


    $pagamento = trim(
        $_POST["pagamento"] ?? ""
    );


    /* ==================================
       VALIDAR CAMPOS
    ================================== */

    if (
        $nome === "" ||
        $telefone === "" ||
        $cep === "" ||
        $endereco === "" ||
        $numero === "" ||
        $cidade === "" ||
        $estado === "" ||
        $pagamento === ""
    ) {

        $erro = "Preencha todos os campos.";

    }


    /* ==================================
       VERIFICAR ESTOQUE NOVAMENTE
    ================================== */

    if ($erro === "") {


        foreach ($produtos as $produto) {


            $produto_id = intval(
                $produto["id"]
            );


            $quantidade = intval(
                $produto["quantidade"]
            );


            $stmt_estoque = $conexao->prepare(
                "
                SELECT estoque, nome
                FROM produtos
                WHERE id = ?
                FOR UPDATE
                "
            );


            if (!$stmt_estoque) {

                $erro =
                    "Erro ao verificar estoque: " .
                    $conexao->error;

                break;

            }


            $stmt_estoque->bind_param(
                "i",
                $produto_id
            );


            $stmt_estoque->execute();


            $resultado_estoque =
                $stmt_estoque->get_result();


            if (
                $resultado_estoque->num_rows === 0
            ) {

                $erro =
                    "Um dos discos do carrinho não existe mais.";

                $stmt_estoque->close();

                break;

            }


            $dados_estoque =
                $resultado_estoque->fetch_assoc();


            $estoque_atual =
                intval(
                    $dados_estoque["estoque"]
                );


            $nome_produto =
                $dados_estoque["nome"];


            $stmt_estoque->close();


            if ($quantidade > $estoque_atual) {

                $erro =
                    "O disco \"" .
                    $nome_produto .
                    "\" possui apenas " .
                    $estoque_atual .
                    " unidade(s) disponível(is).";

                break;

            }

        }

    }


    /* ==================================
       CRIAR PEDIDO
    ================================== */

    if ($erro === "") {


        /*
         * Inicia uma transação.
         *
         * Assim o pedido e o estoque
         * serão alterados juntos.
         */

        $conexao->begin_transaction();


        try {


            /* ==============================
               CRIAR PEDIDO
            ============================== */


            $sql_pedido = "
                INSERT INTO pedidos
                (
                    usuario_id,
                    total,
                    status
                )
                VALUES
                (?, ?, ?)
            ";


            $status =
                "Aguardando pagamento";


            $stmt = $conexao->prepare(
                $sql_pedido
            );


            if (!$stmt) {

                throw new Exception(
                    "Erro ao preparar o pedido: " .
                    $conexao->error
                );

            }


            $stmt->bind_param(
                "ids",
                $usuario_id,
                $total,
                $status
            );


            if (!$stmt->execute()) {

                throw new Exception(
                    "Não foi possível criar o pedido: " .
                    $stmt->error
                );

            }


            $pedido_id =
                $conexao->insert_id;


            $stmt->close();


            /* ==============================
               PREPARAR ITENS
            ============================== */


            $stmt_item = $conexao->prepare(
                "
                INSERT INTO itens_pedido
                (
                    pedido_id,
                    produto_id,
                    quantidade,
                    preco
                )
                VALUES
                (?, ?, ?, ?)
                "
            );


            if (!$stmt_item) {

                throw new Exception(
                    "Erro ao preparar os itens: " .
                    $conexao->error
                );

            }


            /* ==============================
               PREPARAR ATUALIZAÇÃO ESTOQUE
            ============================== */


            $stmt_estoque = $conexao->prepare(
                "
                UPDATE produtos
                SET estoque = estoque - ?
                WHERE id = ?
                AND estoque >= ?
                "
            );


            if (!$stmt_estoque) {

                $stmt_item->close();

                throw new Exception(
                    "Erro ao preparar atualização do estoque: " .
                    $conexao->error
                );

            }


            /* ==============================
               SALVAR ITENS E DIMINUIR ESTOQUE
            ============================== */


            foreach ($produtos as $produto) {


                $produto_id =
                    intval(
                        $produto["id"]
                    );


                $quantidade =
                    intval(
                        $produto["quantidade"]
                    );


                $preco =
                    floatval(
                        $produto["preco"]
                    );


                /* ==========================
                   SALVAR ITEM DO PEDIDO
                ========================== */


                $stmt_item->bind_param(
                    "iiid",
                    $pedido_id,
                    $produto_id,
                    $quantidade,
                    $preco
                );


                if (!$stmt_item->execute()) {

                    throw new Exception(
                        "Erro ao salvar item do pedido: " .
                        $stmt_item->error
                    );

                }


                /* ==========================
                   DIMINUIR ESTOQUE
                ========================== */


                $stmt_estoque->bind_param(
                    "iii",
                    $quantidade,
                    $produto_id,
                    $quantidade
                );


                if (!$stmt_estoque->execute()) {

                    throw new Exception(
                        "Erro ao atualizar estoque: " .
                        $stmt_estoque->error
                    );

                }


                /*
                 * Se nenhuma linha foi alterada,
                 * significa que o estoque mudou
                 * ou não era suficiente.
                 */

                if ($stmt_estoque->affected_rows !== 1) {

                    throw new Exception(
                        "O estoque de um dos discos não é mais suficiente."
                    );

                }

            }


            $stmt_item->close();

            $stmt_estoque->close();


            /* ==============================
               CONFIRMAR TRANSAÇÃO
            ============================== */

            $conexao->commit();


            /* ==============================
               LIMPAR CARRINHO
            ============================== */

            $_SESSION["carrinho"] = [];


            /* ==============================
               GUARDAR PEDIDO
            ============================== */

            $_SESSION["pedido_finalizado"] =
                $pedido_id;


            /* ==============================
               IR PARA CONFIRMAÇÃO
            ============================== */

            header(
                "Location: pedido_sucesso.php"
            );

            exit;


        } catch (Exception $e) {


            /*
             * Se alguma coisa der errado,
             * desfaz tudo.
             */

            $conexao->rollback();


            $erro =
                $e->getMessage();

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

    <title>Finalizar compra | LojaCD</title>


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
                    #241633,
                    #08080c 55%
                );

            color: white;

            font-family: Arial, sans-serif;

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

            max-width: 1150px;

            margin: auto;

            padding: 50px 0 80px;

        }


        h1 {

            font-size: 38px;

            margin: 0 0 8px;

        }


        .subtitulo {

            color: #777;

            margin-bottom: 35px;

        }


        .layout {

            display: grid;

            grid-template-columns: 1fr 360px;

            gap: 25px;

            align-items: start;

        }


        .card {

            background: #111117;

            border: 1px solid #292932;

            border-radius: 18px;

            padding: 28px;

            margin-bottom: 20px;

        }


        .card h2 {

            margin: 0 0 25px;

            font-size: 20px;

        }


        .grid-form {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 18px;

        }


        .campo {

            display: flex;

            flex-direction: column;

            gap: 8px;

        }


        .campo-completo {

            grid-column: 1 / -1;

        }


        label {

            color: #aaa;

            font-size: 12px;

        }


        input,
        select {

            width: 100%;

            padding: 14px;

            background: #0b0b10;

            border: 1px solid #35353f;

            border-radius: 9px;

            color: white;

            outline: none;

            font-size: 13px;

        }


        input:focus,
        select:focus {

            border-color: #b56cff;

        }


        select option {

            background: #111117;

            color: white;

        }


        .pagamentos {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 12px;

        }


        .pagamento {

            position: relative;

        }


        .pagamento input {

            position: absolute;

            opacity: 0;

        }


        .pagamento label {

            display: block;

            padding: 18px 10px;

            border: 1px solid #35353f;

            border-radius: 10px;

            text-align: center;

            cursor: pointer;

            color: #aaa;

            transition: .2s;

        }


        .pagamento input:checked + label {

            border-color: #b56cff;

            background: #20132d;

            color: white;

        }


        .pagamento label:hover {

            border-color: #666;

        }


        .erro {

            background: #35181d;

            border: 1px solid #6d3038;

            color: #ffb2ba;

            padding: 14px;

            border-radius: 9px;

            margin-bottom: 20px;

            font-size: 13px;

        }


        .resumo {

            background: #111117;

            border: 1px solid #292932;

            border-radius: 18px;

            padding: 25px;

            position: sticky;

            top: 20px;

        }


        .resumo h2 {

            margin: 0 0 22px;

        }


        .item {

            display: flex;

            justify-content: space-between;

            gap: 15px;

            padding: 13px 0;

            border-bottom: 1px solid #25252d;

            font-size: 13px;

        }


        .item-nome {

            color: #aaa;

        }


        .item-preco {

            font-weight: bold;

            white-space: nowrap;

        }


        .total {

            display: flex;

            justify-content: space-between;

            margin-top: 20px;

            padding-top: 20px;

            border-top: 1px solid #303039;

            font-size: 21px;

            font-weight: bold;

        }


        .finalizar {

            width: 100%;

            margin-top: 25px;

            padding: 16px;

            border: none;

            border-radius: 10px;

            background: white;

            color: #111;

            font-weight: bold;

            font-size: 14px;

            cursor: pointer;

        }


        .finalizar:hover {

            opacity: .9;

        }


        .voltar {

            display: block;

            text-align: center;

            margin-top: 14px;

            color: #777;

            text-decoration: none;

            font-size: 12px;

        }


        .voltar:hover {

            color: white;

        }


        @media (max-width: 850px) {

            .layout {

                grid-template-columns: 1fr;

            }


            .resumo {

                position: static;

            }

        }


        @media (max-width: 600px) {

            .grid-form {

                grid-template-columns: 1fr;

            }


            .campo-completo {

                grid-column: auto;

            }


            .pagamentos {

                grid-template-columns: 1fr;

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
                $_SESSION["usuario_nome"] ?? "Cliente"
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



<main class="container">


    <h1>
        Finalizar compra
    </h1>


    <p class="subtitulo">

        Preencha seus dados para concluir seu pedido.

    </p>



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



    <div class="layout">


        <!-- ==============================
             FORMULÁRIO
        ============================== -->

        <section>


            <form
                method="POST"
                action="checkout.php"
            >


                <div class="card">


                    <h2>
                        📋 Dados para entrega
                    </h2>


                    <div class="grid-form">


                        <div class="campo campo-completo">

                            <label>
                                Nome completo
                            </label>

                            <input
                                type="text"
                                name="nome"
                                placeholder="Digite seu nome completo"
                                value="<?php

                                echo htmlspecialchars(
                                    $_POST["nome"]
                                    ??
                                    $_SESSION["usuario_nome"]
                                    ??
                                    ""
                                );

                                ?>"
                                required
                            >

                        </div>



                        <div class="campo">

                            <label>
                                Telefone
                            </label>

                            <input
                                type="tel"
                                name="telefone"
                                placeholder="(00) 00000-0000"
                                value="<?php

                                echo htmlspecialchars(
                                    $_POST["telefone"] ?? ""
                                );

                                ?>"
                                required
                            >

                        </div>



                        <div class="campo">

                            <label>
                                CEP
                            </label>

                            <input
                                type="text"
                                name="cep"
                                placeholder="00000-000"
                                value="<?php

                                echo htmlspecialchars(
                                    $_POST["cep"] ?? ""
                                );

                                ?>"
                                required
                            >

                        </div>



                        <div class="campo campo-completo">

                            <label>
                                Endereço
                            </label>

                            <input
                                type="text"
                                name="endereco"
                                placeholder="Rua, avenida..."
                                value="<?php

                                echo htmlspecialchars(
                                    $_POST["endereco"] ?? ""
                                );

                                ?>"
                                required
                            >

                        </div>



                        <div class="campo">

                            <label>
                                Número
                            </label>

                            <input
                                type="text"
                                name="numero"
                                placeholder="Número"
                                value="<?php

                                echo htmlspecialchars(
                                    $_POST["numero"] ?? ""
                                );

                                ?>"
                                required
                            >

                        </div>



                        <div class="campo">

                            <label>
                                Cidade
                            </label>

                            <input
                                type="text"
                                name="cidade"
                                placeholder="Sua cidade"
                                value="<?php

                                echo htmlspecialchars(
                                    $_POST["cidade"] ?? ""
                                );

                                ?>"
                                required
                            >

                        </div>



                        <div class="campo">

                            <label>
                                Estado
                            </label>

                            <select
                                name="estado"
                                required
                            >

                                <option value="">
                                    Selecione
                                </option>

                                <?php

                                $estados = [

                                    "AC",
                                    "AL",
                                    "AP",
                                    "AM",
                                    "BA",
                                    "CE",
                                    "DF",
                                    "ES",
                                    "GO",
                                    "MA",
                                    "MT",
                                    "MS",
                                    "MG",
                                    "PA",
                                    "PB",
                                    "PR",
                                    "PE",
                                    "PI",
                                    "RJ",
                                    "RN",
                                    "RS",
                                    "RO",
                                    "RR",
                                    "SC",
                                    "SP",
                                    "SE",
                                    "TO"

                                ];


                                foreach ($estados as $uf):

                                ?>

                                    <option
                                        value="<?php echo $uf; ?>"
                                        <?php

                                        if (
                                            ($_POST["estado"] ?? "")
                                            ===
                                            $uf
                                        ) {

                                            echo "selected";

                                        }

                                        ?>
                                    >

                                        <?php echo $uf; ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                    </div>


                </div>



                <!-- ==============================
                     PAGAMENTO
                ============================== -->

                <div class="card">


                    <h2>
                        💳 Forma de pagamento
                    </h2>


                    <div class="pagamentos">


                        <div class="pagamento">

                            <input
                                type="radio"
                                id="pix"
                                name="pagamento"
                                value="PIX"
                                required
                                <?php

                                if (
                                    ($_POST["pagamento"] ?? "")
                                    ===
                                    "PIX"
                                ) {

                                    echo "checked";

                                }

                                ?>
                            >

                            <label for="pix">

                                💠<br>
                                PIX

                            </label>

                        </div>



                        <div class="pagamento">

                            <input
                                type="radio"
                                id="cartao"
                                name="pagamento"
                                value="Cartão"
                                <?php

                                if (
                                    ($_POST["pagamento"] ?? "")
                                    ===
                                    "Cartão"
                                ) {

                                    echo "checked";

                                }

                                ?>
                            >

                            <label for="cartao">

                                💳<br>
                                Cartão

                            </label>

                        </div>



                        <div class="pagamento">

                            <input
                                type="radio"
                                id="boleto"
                                name="pagamento"
                                value="Boleto"
                                <?php

                                if (
                                    ($_POST["pagamento"] ?? "")
                                    ===
                                    "Boleto"
                                ) {

                                    echo "checked";

                                }

                                ?>
                            >

                            <label for="boleto">

                                🧾<br>
                                Boleto

                            </label>

                        </div>


                    </div>


                </div>



                <button
                    type="submit"
                    class="finalizar"
                >

                    🔒 Finalizar pedido

                </button>


            </form>


        </section>



        <!-- ==============================
             RESUMO
        ============================== -->

        <aside class="resumo">


            <h2>
                Seu pedido
            </h2>


            <?php foreach ($produtos as $produto): ?>


                <div class="item">


                    <span class="item-nome">

                        <?php

                        echo htmlspecialchars(
                            $produto["nome"]
                        );

                        ?>

                        ×

                        <?php

                        echo intval(
                            $produto["quantidade"]
                        );

                        ?>

                    </span>


                    <span class="item-preco">

                        R$

                        <?php

                        echo number_format(
                            $produto["subtotal"],
                            2,
                            ",",
                            "."
                        );

                        ?>

                    </span>


                </div>


            <?php endforeach; ?>



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
                href="carrinho.php"
                class="voltar"
            >

                ← Voltar para o carrinho

            </a>


        </aside>


    </div>


</main>


</body>

</html>