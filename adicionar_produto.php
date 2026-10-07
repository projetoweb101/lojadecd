<?php
session_start();

require_once "../conexao.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$mensagem = "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"]);
    $artista = trim($_POST["artista"]);
    $genero = trim($_POST["genero"]);
    $ano = intval($_POST["ano"]);
    $preco = floatval($_POST["preco"]);
    $estoque = intval($_POST["estoque"]);
    $descricao = trim($_POST["descricao"]);

    $destaque = isset($_POST["destaque"]) ? 1 : 0;

    $imagem = "";

    /* ==========================
       VALIDAÇÕES
    ========================== */

    if ($nome === "" || $artista === "") {

        $erro = "Preencha o nome do CD e o artista.";

    } elseif ($preco < 0) {

        $erro = "O preço não pode ser negativo.";

    } elseif ($estoque < 0) {

        $erro = "O estoque não pode ser negativo.";

    } else {

        /* ==========================
           UPLOAD DA IMAGEM
        ========================== */

        if (isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] === 0) {

            $arquivo = $_FILES["imagem"];

            $nome_original = $arquivo["name"];
            $tamanho = $arquivo["size"];
            $temporario = $arquivo["tmp_name"];

            $extensao = strtolower(
                pathinfo($nome_original, PATHINFO_EXTENSION)
            );

            $extensoes_permitidas = [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ];

            if (!in_array($extensao, $extensoes_permitidas)) {

                $erro = "Formato de imagem não permitido. Use JPG, JPEG, PNG ou WEBP.";

            } elseif ($tamanho > 5 * 1024 * 1024) {

                $erro = "A imagem deve ter no máximo 5 MB.";

            } else {

                $nome_imagem = uniqid("cd_", true) . "." . $extensao;

                $destino = "../assets/imagens/" . $nome_imagem;

                if (move_uploaded_file($temporario, $destino)) {

                    $imagem = $nome_imagem;

                } else {

                    $erro = "Não foi possível salvar a imagem.";
                }
            }
        }


        /* ==========================
           SALVAR PRODUTO
        ========================== */

        if ($erro === "") {

            $sql = "INSERT INTO produtos 
                    (nome, artista, genero, ano, preco, estoque, descricao, imagem, destaque)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conexao->prepare($sql);

            $stmt->bind_param(
                "sssidiisi",
                $nome,
                $artista,
                $genero,
                $ano,
                $preco,
                $estoque,
                $descricao,
                $imagem,
                $destaque
            );

            if ($stmt->execute()) {

                $mensagem = "Produto cadastrado com sucesso!";

                $_POST = [];

            } else {

                $erro = "Erro ao cadastrar o produto: " . $stmt->error;
            }

            $stmt->close();
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

    <title>Adicionar Produto | LojaCD</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>

        body {
            background: #08080c;
        }

        .admin-layout {
            min-height: 100vh;
            display: flex;
        }

        /* ==========================
           SIDEBAR
        ========================== */

        .sidebar {
            width: 250px;
            min-height: 100vh;
            background: #101015;
            border-right: 1px solid #24242c;
            padding: 28px 18px;
            position: fixed;
            left: 0;
            top: 0;
        }

        .admin-logo {
            font-size: 24px;
            font-weight: 800;
            padding: 10px 15px 35px;
        }

        .admin-logo span {
            color: #b56cff;
        }

        .menu-admin {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .menu-admin a {
            padding: 13px 15px;
            border-radius: 10px;
            color: #999;
            font-size: 14px;
            transition: .3s;
        }

        .menu-admin a:hover,
        .menu-admin a.ativo {
            background: #1b1820;
            color: white;
        }

        .menu-admin a.ativo {
            border-left: 3px solid #b56cff;
        }

        .admin-voltar {
            position: absolute;
            bottom: 25px;
            left: 18px;
            right: 18px;
        }

        .admin-voltar a {
            display: block;
            padding: 12px;
            border: 1px solid #292930;
            border-radius: 10px;
            text-align: center;
            color: #aaa;
            font-size: 13px;
        }

        /* ==========================
           CONTEÚDO
        ========================== */

        .admin-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 40px 50px;
        }

        .pagina-topo {
            margin-bottom: 30px;
        }

        .pagina-topo h1 {
            font-size: 32px;
            margin-bottom: 8px;
        }

        .pagina-topo p {
            color: #777;
            font-size: 14px;
        }

        /* ==========================
           FORMULÁRIO
        ========================== */

        .formulario {
            max-width: 900px;
            background: #111117;
            border: 1px solid #24242c;
            border-radius: 18px;
            padding: 32px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 22px;
        }

        .campo {
            display: flex;
            flex-direction: column;
        }

        .campo.full {
            grid-column: 1 / -1;
        }

        .campo label {
            color: #ddd;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .campo input,
        .campo select,
        .campo textarea {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            border: 1px solid #303038;
            background: #0b0b0f;
            color: white;
            outline: none;
            font-family: inherit;
        }

        .campo input:focus,
        .campo select:focus,
        .campo textarea:focus {
            border-color: #b56cff;
        }

        .campo textarea {
            min-height: 130px;
            resize: vertical;
        }

        .campo small {
            margin-top: 7px;
            color: #666;
            font-size: 11px;
        }

        /* ==========================
           IMAGEM
        ========================== */

        .upload-area {
            border: 1px dashed #393943;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            background: #0d0d12;
        }

        .upload-area input {
            border: none;
            padding: 10px 0;
        }

        .upload-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }

        /* ==========================
           DESTAQUE
        ========================== */

        .checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 5px;
        }

        .checkbox input {
            width: 18px;
            height: 18px;
        }

        .checkbox label {
            margin: 0;
            color: #ccc;
        }

        /* ==========================
           MENSAGENS
        ========================== */

        .mensagem {
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 22px;
            background: #15281d;
            border: 1px solid #244a31;
            color: #91e5aa;
        }

        .erro {
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 22px;
            background: #301919;
            border: 1px solid #542525;
            color: #ff9999;
        }

        /* ==========================
           BOTÕES
        ========================== */

        .form-acoes {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #24242c;
        }

        .btn-cancelar {
            padding: 14px 22px;
            border-radius: 10px;
            border: 1px solid #33333b;
            color: #aaa;
        }

        .btn-salvar {
            padding: 14px 25px;
            border-radius: 10px;
            border: none;
            background: white;
            color: #111;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-salvar:hover {
            opacity: .9;
        }

        /* ==========================
           RESPONSIVO
        ========================== */

        @media (max-width: 800px) {

            .sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
            }

            .admin-layout {
                display: block;
            }

            .admin-content {
                margin-left: 0;
                width: 100%;
                padding: 25px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .campo.full {
                grid-column: auto;
            }

            .admin-voltar {
                position: relative;
                bottom: auto;
                left: auto;
                right: auto;
                margin-top: 20px;
            }

        }

    </style>

</head>

<body>

<div class="admin-layout">

    <!-- MENU -->

    <aside class="sidebar">

        <div class="admin-logo">
            <span>◉</span> LojaCD
        </div>

        <nav class="menu-admin">

            <a href="painel.php">
                📊 Dashboard
            </a>

            <a href="produtos.php">
                💿 Produtos
            </a>

            <a href="adicionar-produto.php" class="ativo">
                ➕ Adicionar produto
            </a>

            <a href="pedidos.php">
                🛒 Pedidos
            </a>

            <a href="clientes.php">
                👥 Clientes
            </a>

            <a href="reservas.php">
                📋 Reservas
            </a>

        </nav>

        <div class="admin-voltar">

            <a href="logout.php">
                Sair do painel
            </a>

        </div>

    </aside>


    <!-- CONTEÚDO -->

    <main class="admin-content">

        <div class="pagina-topo">

            <h1>Adicionar produto</h1>

            <p>
                Cadastre um novo CD para disponibilizá-lo na loja.
            </p>

        </div>


        <?php if ($mensagem): ?>

            <div class="mensagem">
                ✅ <?php echo htmlspecialchars($mensagem); ?>
            </div>

        <?php endif; ?>


        <?php if ($erro): ?>

            <div class="erro">
                ⚠️ <?php echo htmlspecialchars($erro); ?>
            </div>

        <?php endif; ?>


        <form
            class="formulario"
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="form-grid">


                <!-- NOME -->

                <div class="campo">

                    <label for="nome">
                        Nome do CD *
                    </label>

                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        required
                        placeholder="Ex.: The Dark Side of the Moon"
                        value="<?php echo htmlspecialchars($_POST["nome"] ?? ""); ?>"
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
                        required
                        placeholder="Ex.: Pink Floyd"
                        value="<?php echo htmlspecialchars($_POST["artista"] ?? ""); ?>"
                    >

                </div>


                <!-- GÊNERO -->

                <div class="campo">

                    <label for="genero">
                        Gênero musical
                    </label>

                    <select id="genero" name="genero">

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

                        <option value="Clássica">
                            Clássica
                        </option>

                        <option value="Outros">
                            Outros
                        </option>

                    </select>

                </div>


                <!-- ANO -->

                <div class="campo">

                    <label for="ano">
                        Ano de lançamento
                    </label>

                    <input
                        type="number"
                        id="ano"
                        name="ano"
                        min="1900"
                        max="2100"
                        placeholder="Ex.: 1973"
                        value="<?php echo htmlspecialchars($_POST["ano"] ?? ""); ?>"
                    >

                </div>


                <!-- PREÇO -->

                <div class="campo">

                    <label for="preco">
                        Preço (R$) *
                    </label>

                    <input
                        type="number"
                        id="preco"
                        name="preco"
                        step="0.01"
                        min="0"
                        required
                        placeholder="49.90"
                        value="<?php echo htmlspecialchars($_POST["preco"] ?? ""); ?>"
                    >

                </div>


                <!-- ESTOQUE -->

                <div class="campo">

                    <label for="estoque">
                        Quantidade em estoque *
                    </label>

                    <input
                        type="number"
                        id="estoque"
                        name="estoque"
                        min="0"
                        required
                        placeholder="10"
                        value="<?php echo htmlspecialchars($_POST["estoque"] ?? ""); ?>"
                    >

                </div>


                <!-- IMAGEM -->

                <div class="campo full">

                    <label for="imagem">
                        Imagem da capa
                    </label>

                    <div class="upload-area">

                        <div class="upload-icon">
                            🖼️
                        </div>

                        <p style="color:#aaa; font-size:13px;">
                            Selecione a imagem da capa do CD
                        </p>

                        <input
                            type="file"
                            id="imagem"
                            name="imagem"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <small>
                            JPG, JPEG, PNG ou WEBP — máximo 5 MB.
                        </small>

                    </div>

                </div>


                <!-- DESCRIÇÃO -->

                <div class="campo full">

                    <label for="descricao">
                        Descrição
                    </label>

                    <textarea
                        id="descricao"
                        name="descricao"
                        placeholder="Escreva uma descrição do álbum..."
                    ><?php echo htmlspecialchars($_POST["descricao"] ?? ""); ?></textarea>

                </div>


                <!-- DESTAQUE -->

                <div class="campo full">

                    <div class="checkbox">

                        <input
                            type="checkbox"
                            id="destaque"
                            name="destaque"
                            value="1"
                        >

                        <label for="destaque">
                            ⭐ Mostrar este produto nos destaques da página inicial
                        </label>

                    </div>

                </div>

            </div>


            <!-- AÇÕES -->

            <div class="form-acoes">

                <a
                    href="painel.php"
                    class="btn-cancelar"
                >
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="btn-salvar"
                >
                    Adicionar produto
                </button>

            </div>

        </form>

    </main>

</div>

</body>

</html>