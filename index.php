<?php
  // inicia a sessão para conseguirmos usar o $_SESSION
  session_start();

  // se o utilizador ja está logado não faz sentido mostrar o login
  // então mandamos logo para o dashboard
  if (isset($_SESSION["username"])) {
    header("Location: dashboard.php");
    die(); // pára o codigo aqui para nao continuar
  }

  // variável para guardar o erro caso o login falhe
  // começa vazia porque ainda não houve nenhum erro
  $mensagem_erro = "";

  // lê o ficheiro e devolve um array com os utilizadores
  function carregarUtilizadores($ficheiro) {
      $lista = [];

      if (!file_exists($ficheiro)){ 
        return $lista;
      }

      $linhas = file($ficheiro,FILE_IGNORE_NEW_LINES);

      foreach ($linhas as $linha) {
          $dados = explode(':', $linha); // divide cada linha pelo separador :
          $lista[] = [
              "username"      => $dados[0],
              "password_hash" => $dados[1],
              "role"          => $dados[2]
          ];
      }

      return $lista;
  }

  $utilizadores = carregarUtilizadores('utilizadores.txt');

  // só entramos aqui se o formulario foi enviado
  // e se os dois campos vieram preenchidos
  if ($_POST && isset($_POST["username"]) && isset($_POST["password"])) {

    // guardamos o que o utilizador escreveu
    $username_formulario = $_POST["username"];
    $password_formulario = $_POST["password"];

    // variavel para saber se o login foi correto ou nao
    $login_correto = false;

    // percorremos a lista de utilizadores para ver se algum dá certo
    foreach ($utilizadores as $utilizador) {

      // verificamos o username e a password ao mesmo tempo
      // usamos password_verify porque a password está em hash, não em texto normal
      // ou seja não dá para comparar diretamente com o ==
      if ($username_formulario == $utilizador["username"] && password_verify($password_formulario, $utilizador["password_hash"])) {

        // encontrou! guardamos os dados na sessão para as outras paginas saberem quem está logado
        $_SESSION["username"] = $utilizador["username"];
        $_SESSION["role"] = $utilizador["role"];
        $login_correto = true;
        break;
      }
    }

    // se o login foi correto mandamos para o dashboard
    if ($login_correto) {
      header("Location: dashboard.php");
      die();
    } else {
      // se chegou aqui é porque não encontrou nenhum utilizador que correspondesse
      $mensagem_erro = "Utilizador ou palavra-passe incorretos.";
    }
  }
?>

<!doctype html>
<html lang="pt-PT">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Casa Inteligente IoT</title>
    <!-- css do bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- icones do bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- o nosso css -->
    <link href="css/style.css" rel="stylesheet">
  </head>
  <body>
    <main class="dashboard-shell">
      <section class="overview-band mb-4">
        <p class="overline mb-2">Casa Inteligente</p>
        <h1>Casa Inteligente IoT</h1>
      </section>

      <section>
        <div class="card">
          <div class="card-body p-4">
            <div class="section-heading">
              <span class="section-kicker">Autenticação</span>
              <h2>Iniciar sessão</h2>
            </div>

            <!-- só aparece a mensagem de erro se houver algum erro -->
            <?php if ($mensagem_erro != ""): ?>
              <div class="alert alert-danger" role="alert">
                <?php echo $mensagem_erro; ?>
              </div>
            <?php endif; ?>

            <!-- o formulario envia para esta mesma pagina -->
            <form method="POST" action="index.php">
              <div class="mb-3">
                <label class="form-label" for="username">Utilizador</label>
                <!-- o required faz a validação antes de enviar -->
                <input class="form-control" type="text" id="username" name="username" required>
              </div>

              <div class="mb-3">
                <label class="form-label" for="password">Palavra-passe</label>
                <!-- type password esconde o texto que se escreve -->
                <input class="form-control" type="password" id="password" name="password" required>
              </div>

              <button class="btn btn-primary" type="submit">
                <i class="bi bi-box-arrow-in-right"></i>
                Entrar
              </button>
            </form>
          </div>
        </div>
      </section>
    </main>

    <!-- js do bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>