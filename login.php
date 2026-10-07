<?php
session_start();
require_once "../conexao.php";

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];

    $sql = "SELECT * FROM administradores WHERE email = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {

        $admin = $resultado->fetch_assoc();

        if (password_verify($senha, $admin["senha"])) {

            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_nome"] = $admin["nome"];

            header("Location: painel.php");
            exit;

        } else {
            $erro = "E-mail ou senha incorretos.";
        }

    } else {
        $erro = "E-mail ou senha incorretos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | LojaCD</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <main style="
        min-height:100vh;
        display:flex;
        align-items:center;
        justify-content:center;
        padding:30px;
    ">

        <div style="
            width:100%;
            max-width:430px;
            background:#121218;
            border:1px solid #27272e;
            border-radius:20px;
            padding:40px;
        ">

            <div style="text-align:center; margin-bottom:30px;">

                <div class="logo">
                    <span>◉</span> LojaCD
                </div>

                <p style="color:#777; margin-top:10px;">
                    Painel administrativo
                </p>

            </div>

            <?php if ($erro): ?>

                <div style="
                    background:#351b1b;
                    color:#ff9999;
                    padding:12px;
                    border-radius:10px;
                    margin-bottom:20px;
                    text-align:center;
                ">
                    <?php echo htmlspecialchars($erro); ?>
                </div>

            <?php endif; ?>

            <form method="POST">

                <label>E-mail</label>

                <input
                    type="email"
                    name="email"
                    required
                    placeholder="admin@lojacd.com"
                    style="
                        width:100%;
                        padding:14px;
                        margin:8px 0 20px;
                        border-radius:10px;
                        border:1px solid #333;
                        background:#0b0b0f;
                        color:white;
                    "
                >

                <label>Senha</label>

                <input
                    type="password"
                    name="senha"
                    required
                    placeholder="Digite sua senha"
                    style="
                        width:100%;
                        padding:14px;
                        margin:8px 0 25px;
                        border-radius:10px;
                        border:1px solid #333;
                        background:#0b0b0f;
                        color:white;
                    "
                >

                <button
                    type="submit"
                    style="
                        width:100%;
                        padding:15px;
                        border:none;
                        border-radius:10px;
                        background:white;
                        color:#111;
                        font-weight:bold;
                        cursor:pointer;
                    "
                >
                    Entrar no painel
                </button>

            </form>

            <div style="text-align:center; margin-top:25px;">
                <a href="../index.php" style="color:#b56cff;">
                    ← Voltar para a loja
                </a>
            </div>

        </div>

    </main>

</body>

</html>