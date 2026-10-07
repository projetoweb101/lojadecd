<?php

session_start();

require_once "conexao.php";

$mensagem = "";
$tipo = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";
    $confirmar = $_POST["confirmar"] ?? "";

    if ($nome === "" || $email === "" || $senha === "" || $confirmar === "") {

        $mensagem = "Preencha todos os campos.";
        $tipo = "erro";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $mensagem = "Digite um e-mail válido.";
        $tipo = "erro";

    } elseif (strlen($senha) < 6) {

        $mensagem = "A senha precisa ter pelo menos 6 caracteres.";
        $tipo = "erro";

    } elseif ($senha !== $confirmar) {

        $mensagem = "As senhas não são iguais.";
        $tipo = "erro";

    } else {

        /* Verificar se o e-mail já existe */

        $verificar = $conexao->prepare(
            "SELECT id FROM usuarios WHERE email = ? LIMIT 1"
        );

        $verificar->bind_param("s", $email);
        $verificar->execute();

        $resultado = $verificar->get_result();

        if ($resultado->num_rows > 0) {

            $mensagem = "Este e-mail já está cadastrado.";
            $tipo = "erro";

        } else {

            /* Criptografar senha */

            $senha_hash = password_hash(
                $senha,
                PASSWORD_DEFAULT
            );


            /* Cadastrar usuário */

            $stmt = $conexao->prepare(
                "INSERT INTO usuarios (nome, email, senha)
                 VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $nome,
                $email,
                $senha_hash
            );


            if ($stmt->execute()) {

                $novo_usuario_id = $conexao->insert_id;


                /* Criar sessão automaticamente */

                $_SESSION["usuario_id"] = $novo_usuario_id;
                $_SESSION["usuario_nome"] = $nome;
                $_SESSION["usuario_email"] = $email;


                header("Location: index.php");
                exit;

            } else {

                $mensagem =
                    "Não foi possível realizar o cadastro: " .
                    $stmt->error;

                $tipo = "erro";
            }

            $stmt->close();
        }

        $verificar->close();
    }
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Criar conta | LojaCD</title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            font-family: Arial, sans-serif;

            color: white;

            background:
                radial-gradient(
                    circle at top,
                    #29163d,
                    #08080c 60%
                );

            padding: 30px;

        }


        .container {

            width: 100%;

            max-width: 470px;

        }


        .logo {

            text-align: center;

            margin-bottom: 25px;

        }


        .logo a {

            color: white;

            text-decoration: none;

            font-size: 30px;

            font-weight: bold;

        }


        .logo span {

            color: #b56cff;

        }


        .card {

            background: rgba(18, 18, 25, .96);

            border: 1px solid #30303a;

            border-radius: 20px;

            padding: 35px;

            box-shadow:
                0 20px 60px rgba(0,0,0,.45);

        }


        h1 {

            margin: 0 0 8px;

            font-size: 30px;

        }


        .subtitulo {

            color: #858590;

            font-size: 13px;

            margin-bottom: 28px;

        }


        .campo {

            margin-bottom: 18px;

        }


        label {

            display: block;

            margin-bottom: 8px;

            color: #bbb;

            font-size: 13px;

        }


        input {

            width: 100%;

            padding: 15px;

            border-radius: 10px;

            border: 1px solid #363640;

            background: #0c0c11;

            color: white;

            outline: none;

            font-size: 14px;

        }


        input:focus {

            border-color: #b56cff;

        }


        button {

            width: 100%;

            padding: 16px;

            border: 0;

            border-radius: 10px;

            background: white;

            color: #111;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;

            margin-top: 5px;

        }


        button:hover {

            opacity: .9;

            transform: translateY(-1px);

        }


        .mensagem {

            padding: 13px;

            border-radius: 9px;

            margin-bottom: 20px;

            font-size: 13px;

        }


        .erro {

            background: #351719;

            border: 1px solid #703137;

            color: #ffb8bd;

        }


        .login {

            text-align: center;

            margin-top: 23px;

            color: #777;

            font-size: 13px;

        }


        .login a {

            color: #c58cff;

            text-decoration: none;

            font-weight: bold;

        }


        .login a:hover {

            text-decoration: underline;

        }


        .seguranca {

            text-align: center;

            color: #555;

            font-size: 10px;

            margin-top: 18px;

        }

    </style>

</head>


<body>


<div class="container">


    <div class="logo">

        <a href="index.php">

            <span>◉</span> LojaCD

        </a>

    </div>


    <div class="card">


        <h1>
            Criar sua conta
        </h1>


        <div class="subtitulo">

            Crie sua conta para comprar e acompanhar seus pedidos.

        </div>



        <?php if ($mensagem !== ""): ?>

            <div class="mensagem <?php echo $tipo; ?>">

                <?php echo htmlspecialchars($mensagem); ?>

            </div>

        <?php endif; ?>



        <form method="POST" action="cadastro.php">


            <div class="campo">

                <label>
                    Nome completo
                </label>

                <input
                    type="text"
                    name="nome"
                    placeholder="Digite seu nome"
                    required
                >

            </div>



            <div class="campo">

                <label>
                    E-mail
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="seuemail@email.com"
                    required
                >

            </div>



            <div class="campo">

                <label>
                    Senha
                </label>

                <input
                    type="password"
                    name="senha"
                    placeholder="Mínimo de 6 caracteres"
                    minlength="6"
                    required
                >

            </div>



            <div class="campo">

                <label>
                    Confirmar senha
                </label>

                <input
                    type="password"
                    name="confirmar"
                    placeholder="Digite a senha novamente"
                    minlength="6"
                    required
                >

            </div>



            <button type="submit">

                Criar minha conta

            </button>


        </form>



        <div class="seguranca">

            🔒 Seus dados são protegidos com senha criptografada.

        </div>



        <div class="login">

            Já possui uma conta?

            <a href="login.php">
                Entrar
            </a>

        </div>


    </div>


</div>


</body>

</html>