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
        <a class="navbar-brand d-flex align-items-center gap-2" href="#">
          <span class="brand-mark"><i class="bi bi-house-heart-fill"></i></span>
          <span>Casa Inteligente IoT</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal" aria-controls="menuPrincipal" aria-expanded="false" aria-label="Alternar navegação">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
          <ul class="navbar-nav ms-lg-4 me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link" href="#sensores">Sensores</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#atuadores">Atuadores</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#camara">Câmara</a>
            </li>
            <?php if($_SESSION["role"] == "admin" || $_SESSION["role"] == "resident"): ?>
              <li class="nav-item">
                <a class="nav-link" href="historico.php">Histórico</a>
              </li>
            <?php endif; ?>
          </ul>

          <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2">
            <span class="user-pill">
              <i class="bi bi-person-circle"></i>
              <?php echo htmlspecialchars($_SESSION["username"]); ?>
            </span>
            <a class="btn btn-outline-light btn-sm logout-button" href="logout.php">
              <i class="bi bi-box-arrow-right"></i>
              Terminar sessão
            </a>
          </div>
        </div>
      </div>
    </nav>

    <main class="dashboard-shell">
      <section class="overview-band mb-4">
        <div class="row g-3 align-items-center">
          <div class="col-lg-7">
            <p class="overline mb-2">Casa Inteligente</p>
            <h1 class="mb-0">Painel principal</h1>
            <p class="mb-0 mt-2">Bem-vindo, <?php echo htmlspecialchars($_SESSION["username"]); ?>. Perfil: <?php echo htmlspecialchars($_SESSION["role"]); ?>.</p>
          </div>
        </div>
      </section>

      <section class="dashboard-section" id="sensores">
        <div class="section-heading">
          <div>
            <span class="section-kicker">Monitorização</span>
            <h2>Sensores</h2>
          </div>
        </div>

        <!-- Futuramente, estes cartões podem ser gerados por PHP lendo ficheiros .txt. -->
        <div class="row g-3">

          <article class="col-12 col-sm-6 col-xl-4">
            <div class="card sensor-card <?php echo ($valor_sensor_movimento == '1') ? 'sensor-active' : 'sensor-closed'; ?> h-100" id="cartaoSensorMovimento">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                  <div>
                    <h3 class="sensor-title">Sensor de Movimento</h3>
                    <p class="sensor-meta mb-0">Detetado pelo Arduino</p>
                  </div>
                  <span class="sensor-icon"><i class="bi bi-broadcast"></i></span>
                </div>
                <div class="sensor-value" id="valorSensorMovimento">
                  <?php echo ($valor_sensor_movimento == '1') ? 'Ativo' : 'Inativo'; ?>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-3">
                  <span class="state-badge <?php echo ($valor_sensor_movimento == '1') ? 'state-on' : 'state-off'; ?>" id="badgeSensorMovimento">
                    <?php echo ($valor_sensor_movimento == '1') ? 'Ativo' : 'Inativo'; ?>
                  </span>
                </div>
              </div>
            </div>
          </article>

          <article class="col-12 col-sm-6 col-xl-4">
            <div class="card sensor-card sensor-closed h-100" id="cartaoSensorTemperatura">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                  <div>
                    <h3 class="sensor-title">Sensor deTemperatura</h3>
                    <p class="sensor-meta mb-0">Monitorizada pelo Arduino</p>
                  </div>
                  <span class="sensor-icon"><i class="bi bi-thermometer-half"></i></span>
                </div>
                <div class="sensor-value" id="valorSensorTemperatura">
                  <?php echo trim($valor_sensor_temperatura) . ' °C'; ?>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-3">
                  <span class="state-badge state-off" id="badgeSensorTemperatura">
                    Inativo
                  </span>
                </div>
              </div>
            </div>
          </article>

          <article class="col-12 col-sm-6 col-xl-4">
            <div class="card sensor-card <?php echo ($valor_sensor_chama == '1') ? 'sensor-active' : 'sensor-closed'; ?> h-100" id="cartaoSensorChama">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                  <div>
                    <h3 class="sensor-title">Detetor de Chama</h3>
                    <p class="sensor-meta mb-0">Detetada pela Raspberry Pi</p>
                  </div>
                  <span class="sensor-icon"><i class="bi bi-fire"></i></span>
                </div>
                <div class="sensor-value" id="valorSensorChama">
                  <?php echo ($valor_sensor_chama == '1') ? 'Ativo' : 'Inativo'; ?>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-3">
                  <span class="state-badge <?php echo ($valor_sensor_chama == '1') ? 'state-on' : 'state-off'; ?>" id="badgeSensorChama">
                    <?php echo ($valor_sensor_chama == '1') ? 'Ativo' : 'Inativo'; ?>
                  </span>
                </div>
              </div>
            </div>
          </article>

        </div>
      </section>

      <section class="dashboard-section" id="atuadores">
        <div class="section-heading">
          <div>
            <span class="section-kicker">Automação</span>
            <h2>Controlos dos atuadores</h2>
          </div>
        </div>

        <div class="row g-3">

          <article class="col-12 col-sm-6 col-xl-4">
            <div class="card actuator-card <?php echo ($valor_buzzer_alarme == '1') ? 'actuator-active' : ''; ?> h-100" id="cartaoBuzzerAlarme">
              <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between gap-3">
                  <div>
                    <h3 class="actuator-title">Buzzer de alarme</h3>
                    <p class="sensor-meta mb-0">Controlado pela Raspberry Pi</p>
                  </div>
                  <span class="actuator-icon"><i class="bi bi-lightbulb-fill"></i></span>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-auto pt-4">
                  <span class="state-badge <?php echo ($valor_buzzer_alarme == '1') ? 'state-on' : 'state-off'; ?>" id="estadoBuzzerAlarme">
                    <?php echo ($valor_buzzer_alarme == '1') ? 'Ativo' : 'Inativo'; ?>
                  </span>
                  <?php if($_SESSION["role"] == "admin"): ?>
                    <button class="btn <?php echo ($valor_buzzer_alarme == '1') ? 'btn-outline-secondary' : 'btn-primary'; ?> control-button" type="button" id="botaoBuzzerAlarme" onclick="alternarAtuador('cartaoBuzzerAlarme', 'estadoBuzzerAlarme', 'botaoBuzzerAlarme', 'Ativo', 'Inativo', 'Desligar', 'Ligar', 'buzzer-alarme')">
                      <?php echo ($valor_buzzer_alarme == '1') ? 'Desligar' : 'Ligar'; ?>
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </article>

          <article class="col-12 col-sm-6 col-xl-4">
            <div class="card actuator-card <?php echo ($valor_buzzer_fogo == '1') ? 'actuator-active' : ''; ?> h-100" id="cartaoBuzzerFogo">
              <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between gap-3">
                  <div>
                    <h3 class="actuator-title">Buzzer de fogo</h3>
                    <p class="sensor-meta mb-0">Controlado pela Raspberry Pi</p>
                  </div>
                  <span class="actuator-icon"><i class="bi bi-bell-fill"></i></span>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-auto pt-4">
                  <span class="state-badge <?php echo ($valor_buzzer_fogo == '1') ? 'state-on' : 'state-off'; ?>" id="estadoBuzzerFogo">
                    <?php echo ($valor_buzzer_fogo == '1') ? 'Ativo' : 'Inativo'; ?>
                  </span>
                  <?php if($_SESSION["role"] == "admin"): ?>
                    <button class="btn <?php echo ($valor_buzzer_fogo == '1') ? 'btn-outline-secondary' : 'btn-primary'; ?> control-button" type="button" id="botaoBuzzerFogo" onclick="alternarAtuador('cartaoBuzzerFogo', 'estadoBuzzerFogo', 'botaoBuzzerFogo', 'Ativo', 'Inativo', 'Desligar', 'Ligar', 'buzzer-fogo')">
                      <?php echo ($valor_buzzer_fogo == '1') ? 'Desligar' : 'Ligar'; ?>
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </article>

          <article class="col-12 col-sm-6 col-xl-4">
            <div class="card actuator-card <?php echo ($valor_led_camera == '1') ? 'actuator-active' : ''; ?> h-100" id="cartaoLedCamera">
              <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between gap-3">
                  <div>
                    <h3 class="actuator-title">LED da Câmara</h3>
                    <p class="sensor-meta mb-0">LED controlado pelo Arduino</p>
                  </div>
                  <span class="actuator-icon"><i class="bi bi-lightbulb-fill"></i></span>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-auto pt-4">
                  <span class="state-badge <?php echo ($valor_led_camera == '1') ? 'state-on' : 'state-off'; ?>" id="estadoLedCamera">
                    <?php echo ($valor_led_camera == '1') ? 'Ativo' : 'Inativo'; ?>
                  </span>
                  <?php if($_SESSION["role"] == "admin" || $_SESSION["role"] == "resident"): ?>
                    <button class="btn <?php echo ($valor_led_camera == '1') ? 'btn-outline-secondary' : 'btn-primary'; ?> control-button" type="button" id="botaoLedCamera" onclick="alternarAtuador('cartaoLedCamera', 'estadoLedCamera', 'botaoLedCamera', 'Ativo', 'Inativo', 'Desligar', 'Ligar', 'led-camera')">
                      <?php echo ($valor_led_camera == '1') ? 'Desligar' : 'Ligar'; ?>
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </article>

          <article class="col-12 col-sm-6 col-xl-4">
            <div class="card actuator-card <?php echo ($valor_led_fogo == '1') ? 'actuator-active' : ''; ?> h-100" id="cartaoLedFogo">
              <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between gap-3">
                  <div>
                    <h3 class="actuator-title">LED de Aviso de Fogo</h3>
                    <p class="sensor-meta mb-0">LED controlado pelo Arduino</p>
                  </div>
                  <span class="actuator-icon"><i class="bi bi-lightbulb-fill"></i></span>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-auto pt-4">
                  <span class="state-badge <?php echo ($valor_led_fogo == '1') ? 'state-on' : 'state-off'; ?>" id="estadoLedFogo">
                    <?php echo ($valor_led_fogo == '1') ? 'Ativo' : 'Inativo'; ?>
                  </span>
                  <?php if($_SESSION["role"] == "admin" || $_SESSION["role"] == "resident"): ?>
                    <button class="btn <?php echo ($valor_led_fogo == '1') ? 'btn-outline-secondary' : 'btn-primary'; ?> control-button" type="button" id="botaoLedFogo" onclick="alternarAtuador('cartaoLedFogo', 'estadoLedFogo', 'botaoLedFogo', 'Ativo', 'Inativo', 'Desligar', 'Ligar', 'led-fogo')">
                      <?php echo ($valor_led_fogo == '1') ? 'Desligar' : 'Ligar'; ?>
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </article>

          <article class="col-12 col-sm-6 col-xl-4">
            <div class="card actuator-card <?php echo ($valor_led_temperatura == '1') ? 'actuator-active' : ''; ?> h-100" id="cartaoLedTemperatura">
              <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between gap-3">
                  <div>
                    <h3 class="actuator-title">LED de Aviso de Temperatura</h3>
                    <p class="sensor-meta mb-0">Aviso controlado pelo Arduino</p>
                  </div>
                  <span class="actuator-icon"><i class="bi bi-lightbulb-fill"></i></span>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-auto pt-4">
                  <span class="state-badge <?php echo ($valor_led_temperatura == '1') ? 'state-on' : 'state-off'; ?>" id="estadoLedTemperatura">
                    <?php echo ($valor_led_temperatura == '1') ? 'Ativo' : 'Inativo'; ?>
                  </span>
                  <?php if($_SESSION["role"] == "admin" || $_SESSION["role"] == "resident"): ?>
                    <button class="btn <?php echo ($valor_led_temperatura == '1') ? 'btn-outline-secondary' : 'btn-primary'; ?> control-button" type="button" id="botaoLedTemperatura" onclick="alternarAtuador('cartaoLedTemperatura', 'estadoLedTemperatura', 'botaoLedTemperatura', 'Ativo', 'Inativo', 'Desligar', 'Ligar', 'led-temperatura')">
                      <?php echo ($valor_led_temperatura == '1') ? 'Desligar' : 'Ligar'; ?>
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </article>
          
        </div>
      </section>

      <section class="dashboard-section" id="camara">
        <div class="row g-3">
          <div class="col-xl-5">
            <div class="card camera-card h-100">
              <div class="card-body">
                <div class="section-heading compact-heading">
                  <div>
                    <span class="section-kicker">Câmara</span>
                    <h2>Última imagem da câmara</h2>
                  </div>
                  <i class="bi bi-camera-video camera-icon"></i>
                </div>

                <div class="camera-placeholder" role="img" aria-label="Imagem simulada da última captura">
                  <div class="camera-overlay">
                    <span>Raspberry Pi</span>
                    <span>Imagem de exemplo</span>
                  </div>
                  <div class="room-wall">
                    <span class="room-window"></span>
                    <span class="room-lamp"></span>
                    <span class="room-sofa"></span>
                  </div>
                </div>

                <div class="capture-row">
                  <div>
                    <span class="capture-label">Data/Hora</span>
                    <strong id="ultimaCaptura">27/05/2026, 14:15:00</strong>
                  </div>
                  <?php if($_SESSION["role"] == "admin" || $_SESSION["role"] == "resident"): ?>
                    <button class="btn btn-primary" type="button" onclick="capturarImagem()">
                      <i class="bi bi-camera"></i>
                      Capturar imagem
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-7">
            <div class="card events-card h-100">
              <div class="card-body">
                <div class="section-heading compact-heading">
                  <div>
                    <span class="section-kicker">Registos</span>
                    <h2>Último evento</h2>
                  </div>
                  <i class="bi bi-activity camera-icon"></i>
                </div>

                <!-- Exemplos estáticos. No projeto final, estes registos podem vir de ficheiros .txt via PHP. -->
                <ul class="list-group event-list">
                  <li class="list-group-item">
                    <span class="event-dot"></span>
                    <span class="event-text">
                      <strong>Movimento detetado pelo Arduino</strong>
                      <small>27/05/2026, 14:10:00 · Arduino</small>
                    </span>
                  </li>
                  <li class="list-group-item">
                    <span class="event-dot"></span>
                    <span class="event-text">
                      <strong>Luz da câmara ligada</strong>
                      <small>27/05/2026, 14:05:00 · Arduino</small>
                    </span>
                  </li>
                  <li class="list-group-item">
                    <span class="event-dot"></span>
                    <span class="event-text">
                      <strong>Chama detetada pela Raspberry Pi</strong>
                      <small>27/05/2026, 13:58:00 · Raspberry Pi</small>
                    </span>
                  </li>
                  <li class="list-group-item">
                    <span class="event-dot"></span>
                    <span class="event-text">
                      <strong>Temperatura monitorizada pelo Arduino</strong>
                      <small>27/05/2026, 13:52:00 · Arduino</small>
                    </span>
                  </li>
                  <li class="list-group-item">
                    <span class="event-dot"></span>
                    <span class="event-text">
                      <strong>Buzzer de fogo controlado pela Raspberry Pi</strong>
                      <small>27/05/2026, 13:45:00 · Raspberry Pi</small>
                    </span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </section>

      <?php if($_SESSION["role"] == "admin" || $_SESSION["role"] == "resident"): ?>
        <section class="dashboard-section" id="historico">
          <div class="card">
            <div class="card-body">
              <div class="section-heading compact-heading">
                <div>
                  <span class="section-kicker">Registos</span>
                  <h2>Histórico</h2>
                </div>
              </div>

              <p class="sensor-meta">Consulta os registos dos sensores e atuadores numa página separada.</p>

              <a class="btn btn-primary" href="historico.php">
                <i class="bi bi-clock-history"></i>
                Ver histórico
              </a>
            </div>
          </div>
        </section>
      <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
  </body>
</html>
