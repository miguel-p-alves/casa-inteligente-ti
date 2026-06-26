<?php
session_start();

if (!isset($_SESSION["username"]) || !isset($_SESSION["role"])) {
  header("refresh:3;url=index.php");
  die("Acesso restrito");
}

$ficheiro_sensor_movimento = "api/files/sensor-movimento/valor.txt";
$valor_sensor_movimento = file_exists($ficheiro_sensor_movimento) ? file_get_contents($ficheiro_sensor_movimento) : "0";

$ficheiro_sensor_temperatura = "api/files/sensor-temperatura/valor.txt";
$valor_sensor_temperatura = file_exists($ficheiro_sensor_temperatura) ? file_get_contents($ficheiro_sensor_temperatura) : "0";

$ficheiro_sensor_chama = "api/files/sensor-chama/valor.txt";
$valor_sensor_chama = file_exists($ficheiro_sensor_chama) ? file_get_contents($ficheiro_sensor_chama) : "0";

$ficheiro_buzzer_fogo = "api/files/buzzer-fogo/valor.txt";
$valor_buzzer_fogo = file_exists($ficheiro_buzzer_fogo) ? file_get_contents($ficheiro_buzzer_fogo) : "0";

$ficheiro_buzzer_alarme = "api/files/buzzer-alarme/valor.txt";
$valor_buzzer_alarme = file_exists($ficheiro_buzzer_alarme) ? file_get_contents($ficheiro_buzzer_alarme) : "0";

$ficheiro_led_camera = "api/files/led-camera/valor.txt";
$valor_led_camera = file_exists($ficheiro_led_camera) ? file_get_contents($ficheiro_led_camera) : "0";

$ficheiro_led_fogo = "api/files/led-fogo/valor.txt";
$valor_led_fogo = file_exists($ficheiro_led_fogo) ? file_get_contents($ficheiro_led_fogo) : "0";

$ficheiro_led_temperatura = "api/files/led-temperatura/valor.txt";
$valor_led_temperatura = file_exists($ficheiro_led_temperatura) ? file_get_contents($ficheiro_led_temperatura) : "0";

$ficheiro_hora_camera = "api/files/camera/hora.txt";
$hora_camera = file_exists($ficheiro_hora_camera) ? file_get_contents($ficheiro_hora_camera) : "Sem registo";
?>

<!doctype html>
<html lang="pt-PT">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Casa Inteligente IoT</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark app-navbar sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">
        <i class="bi bi-house-heart-fill"></i>
        Casa Inteligente IoT
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="menuPrincipal">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="#sensores">Sensores</a></li>
          <li class="nav-item"><a class="nav-link" href="#atuadores">Atuadores</a></li>
          <li class="nav-item"><a class="nav-link" href="#camara">Câmara</a></li>
                                                    <?php
              if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "resident") {
                $classe = ($valor_led_temperatura == '1') ? 'btn-outline-secondary' : 'btn-primary';
                $texto = ($valor_led_temperatura == '1') ? 'Desligar' : 'Ligar';
                echo '<div class="mt-2">
                        <button class="btn ' . $classe . ' control-button" type="button" id="botaoLedTemperatura"
                          onclick="alternarAtuador(\'cartaoLedTemperatura\', \'estadoLedTemperatura\', \'botaoLedTemperatura\', \'Ativo\', \'Inativo\', \'Desligar\', \'Ligar\', \'led-temperatura\')">
                          ' . $texto . '
                        </button>
                      </div>';
              }
              ?>
        </ul>

        <span class="user-pill me-2">
          <i class="bi bi-person-circle"></i>
          <?php echo htmlspecialchars($_SESSION["username"]); ?>
        </span>
        <a class="btn btn-outline-light btn-sm" href="logout.php">
          <i class="bi bi-box-arrow-right"></i>
          Terminar sessão
        </a>
      </div>
    </div>
  </nav>

  <main class="dashboard-shell">
    <section class="overview-band mb-4">
      <p class="overline mb-2">Casa Inteligente</p>
      <h1>Painel principal</h1>
      <p>Bem-vindo, <?php echo htmlspecialchars($_SESSION["username"]); ?>.</p>
    </section>

    <section class="dashboard-section" id="sensores">
      <div class="section-heading">
        <span class="section-kicker">Monitorização</span>
        <h2>Sensores</h2>
      </div>

      <div class="row">
        <div class="col-12 col-sm-6 col-xl-4 mb-3">
          <div
            class="card sensor-card <?php echo ($valor_sensor_movimento == '1') ? 'sensor-active' : 'sensor-closed'; ?> h-100"
            id="cartaoSensorMovimento">
            <div class="card-body">
              <h3 class="sensor-title">Sensor de Movimento</h3>
              <p class="sensor-meta">Detetado pelo Arduino</p>
              <div class="sensor-value" id="valorSensorMovimento">
                <?php echo ($valor_sensor_movimento == '1') ? 'Ativo' : 'Inativo'; ?>
              </div>
              <span class="state-badge <?php echo ($valor_sensor_movimento == '1') ? 'state-on' : 'state-off'; ?>"
                id="badgeSensorMovimento">
                <?php echo ($valor_sensor_movimento == '1') ? 'Ativo' : 'Inativo'; ?>
              </span>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 mb-3">
          <div class="card sensor-card sensor-closed h-100" id="cartaoSensorTemperatura">
            <div class="card-body">
              <h3 class="sensor-title">Sensor de Temperatura</h3>
              <p class="sensor-meta">Monitorizada pelo Arduino</p>
              <div class="sensor-value" id="valorSensorTemperatura">
                <?php echo trim($valor_sensor_temperatura) . ' °C'; ?>
              </div>
              <span class="state-badge state-off" id="badgeSensorTemperatura">Inativo</span>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 mb-3">
          <div
            class="card sensor-card <?php echo ($valor_sensor_chama == '1') ? 'sensor-active' : 'sensor-closed'; ?> h-100"
            id="cartaoSensorChama">
            <div class="card-body">
              <h3 class="sensor-title">Detetor de Chama</h3>
              <p class="sensor-meta">Detetada pela Raspberry Pi</p>
              <div class="sensor-value" id="valorSensorChama">
                <?php echo ($valor_sensor_chama == '1') ? 'Ativo' : 'Inativo'; ?>
              </div>
              <span class="state-badge <?php echo ($valor_sensor_chama == '1') ? 'state-on' : 'state-off'; ?>"
                id="badgeSensorChama">
                <?php echo ($valor_sensor_chama == '1') ? 'Ativo' : 'Inativo'; ?>
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="dashboard-section" id="atuadores">
      <div class="section-heading">
        <span class="section-kicker">Automação</span>
        <h2>Controlos dos atuadores</h2>
      </div>

      <div class="row">
        <div class="col-12 col-sm-6 col-xl-4 mb-3">
          <div class="card actuator-card <?php echo ($valor_buzzer_alarme == '1') ? 'actuator-active' : ''; ?> h-100"
            id="cartaoBuzzerAlarme">
            <div class="card-body">
              <h3 class="actuator-title">Buzzer de alarme</h3>
              <p class="sensor-meta">Controlado pela Raspberry Pi</p>
              <span class="state-badge <?php echo ($valor_buzzer_alarme == '1') ? 'state-on' : 'state-off'; ?>"
                id="estadoBuzzerAlarme">
                <?php echo ($valor_buzzer_alarme == '1') ? 'Ativo' : 'Inativo'; ?>
              </span>
              <?php
              if ($_SESSION["role"] == "admin") {
                $classe = ($valor_buzzer_alarme == '1') ? 'btn-outline-secondary' : 'btn-primary';
                $texto = ($valor_buzzer_alarme == '1') ? 'Desligar' : 'Ligar';
                echo '<div class="mt-2">
                        <button class="btn ' . $classe . ' control-button" type="button" id="botaoBuzzerAlarme"
                          onclick="alternarAtuador(\'cartaoBuzzerAlarme\', \'estadoBuzzerAlarme\', \'botaoBuzzerAlarme\', \'Ativo\', \'Inativo\', \'Desligar\', \'Ligar\', \'buzzer-alarme\')">
                          ' . $texto . '
                        </button>
                      </div>';
              }
              ?>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 mb-3">
          <div class="card actuator-card <?php echo ($valor_buzzer_fogo == '1') ? 'actuator-active' : ''; ?> h-100"
            id="cartaoBuzzerFogo">
            <div class="card-body">
              <h3 class="actuator-title">Buzzer de fogo</h3>
              <p class="sensor-meta">Controlado pela Raspberry Pi</p>
              <span class="state-badge <?php echo ($valor_buzzer_fogo == '1') ? 'state-on' : 'state-off'; ?>"
                id="estadoBuzzerFogo">
                <?php echo ($valor_buzzer_fogo == '1') ? 'Ativo' : 'Inativo'; ?>
              </span>
              <?php
              if ($_SESSION["role"] == "admin") {
                $classe = ($valor_buzzer_fogo == '1') ? 'btn-outline-secondary' : 'btn-primary';
                $texto = ($valor_buzzer_fogo == '1') ? 'Desligar' : 'Ligar';
                echo '<div class="mt-2">
                        <button class="btn ' . $classe . ' control-button" type="button" id="botaoBuzzerFogo"
                          onclick="alternarAtuador(\'cartaoBuzzerFogo\', \'estadoBuzzerFogo\', \'botaoBuzzerFogo\', \'Ativo\', \'Inativo\', \'Desligar\', \'Ligar\', \'buzzer-fogo\')">
                          ' . $texto . '
                        </button>
                      </div>';
              }
              ?>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 mb-3">
          <div class="card actuator-card <?php echo ($valor_led_camera == '1') ? 'actuator-active' : ''; ?> h-100"
            id="cartaoLedCamera">
            <div class="card-body">
              <h3 class="actuator-title">LED da Câmara</h3>
              <p class="sensor-meta">LED controlado pelo Arduino</p>
              <span class="state-badge <?php echo ($valor_led_camera == '1') ? 'state-on' : 'state-off'; ?>"
                id="estadoLedCamera">
                <?php echo ($valor_led_camera == '1') ? 'Ativo' : 'Inativo'; ?>
              </span>
              <?php
              if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "resident") {
                $classe = ($valor_led_camera == '1') ? 'btn-outline-secondary' : 'btn-primary';
                $texto = ($valor_led_camera == '1') ? 'Desligar' : 'Ligar';
                echo '<div class="mt-2">
                        <button class="btn ' . $classe . ' control-button" type="button" id="botaoLedCamera"
                          onclick="alternarAtuador(\'cartaoLedCamera\', \'estadoLedCamera\', \'botaoLedCamera\', \'Ativo\', \'Inativo\', \'Desligar\', \'Ligar\', \'led-camera\')">
                          ' . $texto . '
                        </button>
                      </div>';
              }
              ?>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 mb-3">
          <div class="card actuator-card <?php echo ($valor_led_fogo == '1') ? 'actuator-active' : ''; ?> h-100"
            id="cartaoLedFogo">
            <div class="card-body">
              <h3 class="actuator-title">LED de Aviso de Fogo</h3>
              <p class="sensor-meta">LED controlado pelo Arduino</p>
              <span class="state-badge <?php echo ($valor_led_fogo == '1') ? 'state-on' : 'state-off'; ?>"
                id="estadoLedFogo">
                <?php echo ($valor_led_fogo == '1') ? 'Ativo' : 'Inativo'; ?>
              </span>
              <?php
              if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "resident") {
                $classe = ($valor_led_fogo == '1') ? 'btn-outline-secondary' : 'btn-primary';
                $texto = ($valor_led_fogo == '1') ? 'Desligar' : 'Ligar';
                echo '<div class="mt-2">
                        <button class="btn ' . $classe . ' control-button" type="button" id="botaoLedFogo"
                          onclick="alternarAtuador(\'cartaoLedFogo\', \'estadoLedFogo\', \'botaoLedFogo\', \'Ativo\', \'Inativo\', \'Desligar\', \'Ligar\', \'led-fogo\')">
                          ' . $texto . '
                        </button>
                      </div>';
              }
              ?>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 mb-3">
          <div class="card actuator-card <?php echo ($valor_led_temperatura == '1') ? 'actuator-active' : ''; ?> h-100"
            id="cartaoLedTemperatura">
            <div class="card-body">
              <h3 class="actuator-title">LED de Aviso de Temperatura</h3>
              <p class="sensor-meta">Aviso controlado pelo Arduino</p>
              <span class="state-badge <?php echo ($valor_led_temperatura == '1') ? 'state-on' : 'state-off'; ?>"
                id="estadoLedTemperatura">
                <?php echo ($valor_led_temperatura == '1') ? 'Ativo' : 'Inativo'; ?>
              </span>
              <?php
              if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "resident") {
                $classe = ($valor_led_temperatura == '1') ? 'btn-outline-secondary' : 'btn-primary';
                $texto = ($valor_led_temperatura == '1') ? 'Desligar' : 'Ligar';
                echo '<div class="mt-2">
                        <button class="btn ' . $classe . ' control-button" type="button" id="botaoLedTemperatura"
                          onclick="alternarAtuador(\'cartaoLedTemperatura\', \'estadoLedTemperatura\', \'botaoLedTemperatura\', \'Ativo\', \'Inativo\', \'Desligar\', \'Ligar\', \'led-temperatura\')">
                          ' . $texto . '
                        </button>
                      </div>';
              }
              ?>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="dashboard-section" id="camara">
      <div class="card">
        <div class="card-body">
          <div class="section-heading">
            <span class="section-kicker">Câmara</span>
            <h2>Última imagem da câmara</h2>
          </div>

          <div class="camera-placeholder" style="background: #000;">
            <img id="imagemWebcam" src="api/images/webcam.jpg" class="imagem-capturada"
              style="max-height: 100%; border-radius: 8px;" alt="Última Captura da DroidCam">
          </div>

          <div class="mt-3">
            <span class="capture-label">Data/Hora</span>
            <div class="text-muted small">
              <strong id="horaWebcam"><?php echo $hora_camera; ?></strong>
            </div>
          </div>
        </div>
      </div>
    </section>

    <?php
    if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "resident") {
      echo '<section class="dashboard-section" id="historico">
        <div class="card">
          <div class="card-body">
            <div class="section-heading">
              <span class="section-kicker">Registos</span>
              <h2>Histórico</h2>
            </div>
            <p class="sensor-meta">Consulta os registos dos sensores e atuadores numa página separada.</p>
            <a class="btn btn-primary" href="historico.php">
              <i class="bi bi-clock-history"></i>
              Ver histórico
            </a>
          </div>
        </div>
      </section>';
    }
    ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/dashboard.js"></script>
</body>

</html>