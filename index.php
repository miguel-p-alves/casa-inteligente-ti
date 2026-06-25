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
      "password_hash" => password_hash("admin", PASSWORD_DEFAULT),
      "role" => "admin"
    ),
    array(
      "username" => "Resident",
      "password_hash" => password_hash("resident", PASSWORD_DEFAULT),
      "role" => "resident"
    ),
    array(
      "username" => "Guest",
      "password_hash" => password_hash("guest", PASSWORD_DEFAULT),
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
        <div class="row g-3 align-items-center">
          <div class="col-lg-7">
            <p class="overline mb-2">Casa Inteligente</p>
            <h1 class="mb-0">Casa Inteligente IoT</h1>
          </div>
        </div>
      </section>

      <section class="row justify-content-center">
        <div class="row g-3 align-items-center">
          <div class="card">
            <div class="card-body p-4">
              <div class="section-heading compact-heading">
                <div>
                  <span class="section-kicker">Autenticação</span>
                  <h2>Iniciar sessão</h2>
                </div>
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

                <button class="btn btn-primary w-100" type="submit">
                  <i class="bi bi-box-arrow-in-right"></i>
                  Entrar
                </button>
              </form>
            </div>
          </div>
        </div>
      </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
