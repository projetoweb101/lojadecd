<?php
session_start();

require_once "../conexao.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT * FROM produtos ORDER BY id DESC";
$resultado = $conexao->query($sql);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Produtos | LojaCD</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>

        body {
            background: #08080c;
        }

        .admin-layout {
            min-height: 100vh;
            display: flex;
        }

        /* MENU */

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

        /* CONTEÚDO */

        .admin-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 40px 50px;
        }

        .pagina-topo {
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .btn-adicionar {
            background: white;
            color: #111;
            padding: 13px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: bold;
        }

        /* TABELA */

        .produtos-painel {
            background: #111117;
            border: 1px solid #24242c;
            border-radius: 18px;
            overflow: hidden;
        }

        .tabela {
            width: 100%;
            border-collapse: collapse;
        }

        .tabela th {
            padding: 18px 20px;
            text-align: left;
            color: #666;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #24242c;
        }

        .tabela td {
            padding: 16px 20px;
            border-bottom: 1px solid #1f1f26;
            color: #ccc;
            font-size: 13px;
            vertical-align: middle;
        }

        .produto-mini {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .produto-mini img,
        .produto-sem-imagem {
            width: 55px;
            height: 55px;
            border-radius: 9px;
            object-fit: cover;
            background: #1c1c23;
        }

        .produto-sem-imagem {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
        }

        .produto-nome {
            color: white;
            font-weight: bold;
        }

        .produto-artista {
            color: #777;
            font-size: 11px;
            margin-top: 4px;
        }

        .preco {
            color: white;
            font-weight: bold;
        }

        .estoque {
            display: inline-block;
            padding: 6px 9px;
            border-radius: 7px;
            background: #191c20;
            color: #aaa;
            font-size: 11px;
        }

        .estoque.baixo {
            background: #302819;
            color: #e7bd73;
        }

        .estoque.esgotado {
            background: #301919;
            color: #ff8f8f;
        }

        .acoes-produto {
            display: flex;
            gap: 8px;
        }

        .btn-editar,
        .btn-excluir {
            padding: 8px 11px;
            border-radius: 8px;
            font-size: 11px;
        }

        .btn-editar {
            border: 1px solid #34343c;
            color: #bbb;
        }

        .btn-excluir {
            background: #291719;
            color: #ff9696;
        }

        .sem-produtos {
            text-align: center;
            padding: 70px 20px;
            color: #777;
        }

        .sem-produtos span {
            font-size: 50px;
        }

        /* RESPONSIVO */

        @media (max-width: 900px) {

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

            .admin-voltar {
                position: relative;
                bottom: auto;
                left: auto;
                right: auto;
                margin-top: 20px;
            }

            .produtos-painel {
                overflow-x: auto;
            }

            .tabela {
                min-width: 800px;
            }

        }

    </style>

</head>

<body>

<div class="admin-layout">

    <!-- MENU LATERAL -->

    <aside class="sidebar">

        <div class="admin-logo">
            <span>◉</span> LojaCD
        </div>

        <nav class="menu-admin">

            <a href="painel.php">
                📊 Dashboard
            </a>

            <a href="produtos.php" class="ativo">
                💿 Produtos
            </a>

            <a href="adicionar-produto.php">
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

            <div>

                <h1>Produtos</h1>

                <p>
                    Gerencie os CDs disponíveis na loja.
                </p>

            </div>

            <a
                href="adicionar-produto.php"
                class="btn-adicionar"
            >
                + Adicionar produto
            </a>

        </div>


        <div class="produtos-painel">

            <?php if ($resultado && $resultado->num_rows > 0): ?>

                <table class="tabela">

                    <thead>

                        <tr>

                            <th>Produto</th>

                            <th>Gênero</th>

                            <th>Preço</th>

                            <th>Estoque</th>

                            <th>Ações</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php while ($produto = $resultado->fetch_assoc()): ?>

                            <tr>

                                <!-- PRODUTO -->

                                <td>

                                    <div class="produto-mini">

                                        <?php if (!empty($produto["imagem"])): ?>

                                            <img
                                                src="../assets/imagens/<?php echo htmlspecialchars($produto["imagem"]); ?>"
                                                alt="<?php echo htmlspecialchars($produto["nome"]); ?>"
                                            >

                                        <?php else: ?>

                                            <div class="produto-sem-imagem">
                                                💿
                                            </div>

                                        <?php endif; ?>


                                        <div>

                                            <div class="produto-nome">
                                                <?php echo htmlspecialchars($produto["nome"]); ?>
                                            </div>

                                            <div class="produto-artista">
                                                <?php echo htmlspecialchars($produto["artista"]); ?>
                                            </div>

                                        </div>

                                    </div>

                                </td>


                                <!-- GÊNERO -->

                                <td>

                                    <?php echo htmlspecialchars($produto["genero"]); ?>

                                </td>


                                <!-- PREÇO -->

                                <td>

                                    <span class="preco">

                                        R$
                                        <?php
                                        echo number_format(
                                            $produto["preco"],
                                            2,
                                            ",",
                                            "."
                                        );
                                        ?>

                                    </span>

                                </td>


                                <!-- ESTOQUE -->

                                <td>

                                    <?php

                                    $estoque = intval($produto["estoque"]);

                                    if ($estoque === 0) {

                                        echo '<span class="estoque esgotado">
                                                Esgotado
                                              </span>';

                                    } elseif ($estoque <= 3) {

                                        echo '<span class="estoque baixo">
                                                ' . $estoque . ' unidade(s)
                                              </span>';

                                    } else {

                                        echo '<span class="estoque">
                                                ' . $estoque . ' unidade(s)
                                              </span>';
                                    }

                                    ?>

                                </td>


                                <!-- AÇÕES -->

                                <td>

                                    <div class="acoes-produto">

                                        <a
                                            href="editar-produto.php?id=<?php echo $produto["id"]; ?>"
                                            class="btn-editar"
                                        >
                                            ✏️ Editar
                                        </a>

                                        <a
                                            href="excluir-produto.php?id=<?php echo $produto["id"]; ?>"
                                            class="btn-excluir"
                                            onclick="return confirm('Tem certeza que deseja excluir este produto?');"
                                        >
                                            🗑️ Excluir
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <div class="sem-produtos">

                    <span>💿</span>

                    <h3 style="margin-top:15px;">
                        Nenhum produto cadastrado
                    </h3>

                    <p style="margin-top:8px;">
                        Adicione o primeiro CD à sua loja.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </main>

</div>

</body>

</html>