<?php
  session_start();

  if (isset($_SESSION["username"])) {
    header("Location: dashboard.php");
    die();
  }

  $mensagem_erro = "";

  // Utilizadores do protótipo.
  $utilizadores = array(
    array(
      "username" => "Admin",
      "password_hash" => '$2y$10$nxdv/hNitznenc7cBY5GIOXnJE0QEmLxwPVQoxh6vsy2mYpOGHDt.',
      "role" => "admin"
    ),
    array(
      "username" => "Resident",
      "password_hash" => '$2y$10$R03glQP5bAaKZEhJgnH6rOvLOst30nhfa9USBwm4gyWR6lph2opIS',
      "role" => "resident"
    ),
    array(
      "username" => "Guest",
      "password_hash" => '$2y$10$Eehj43gNt3jvYGkuHQHEN.un8Q3tLaGYdxrf4cLmgay7rPc7XbVZe',
      "role" => "guest"
    )
  );

  if ($_POST && isset($_POST["username"]) && isset($_POST["password"])) {
    $username_formulario = $_POST["username"];
    $password_formulario = $_POST["password"];
    $login_correto = false;

    foreach ($utilizadores as $utilizador) {
      if ($username_formulario == $utilizador["username"] && password_verify($password_formulario, $utilizador["password_hash"])) {
        $_SESSION["username"] = $utilizador["username"];
        $_SESSION["role"] = $utilizador["role"];
        $login_correto = true;
      }
    }

    if ($login_correto) {
      header("Location: dashboard.php");
      die();
    } else {
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
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

            <?php if ($mensagem_erro != ""): ?>
              <div class="alert alert-danger" role="alert">
                <?php echo $mensagem_erro; ?>
              </div>
            <?php endif; ?>

            <form method="POST" action="index.php">
              <div class="mb-3">
                <label class="form-label" for="username">Utilizador</label>
                <input class="form-control" type="text" id="username" name="username" required>
              </div>

              <div class="mb-3">
                <label class="form-label" for="password">Palavra-passe</label>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
