<?php
// Inicia a sessão para poder usar as variáveis $_SESSION
session_start();

// Verifica se o utilizador está autenticado, senão redireciona para o login após 3 segundos
if (!isset($_SESSION["username"]) || !isset($_SESSION["role"])) {
  header("refresh:3;url=index.php");
  die("Acesso restrito");
}

// Lê o valor de cada sensor/atuador a partir de ficheiros .txt
// Se o ficheiro não existir, usa "0" como valor por defeito

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

// Lê a hora da última captura da câmara
$ficheiro_hora_camera = "api/files/camera/hora.txt";
$hora_camera = file_exists($ficheiro_hora_camera) ? file_get_contents($ficheiro_hora_camera) : "Sem registo";
?>

<!doctype html>
<html lang="pt-PT">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Casa Inteligente IoT</title>
  <!-- Bootstrap CSS para estilos e componentes -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons para os ícones usados na navbar -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Estilos personalizados do projeto -->
  <link href="css/style.css" rel="stylesheet">
</head>

<body>
  <!-- Navbar fixa no topo com links para as secções da página -->
  <nav class="navbar navbar-expand-lg navbar-dark app-navbar sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">
        <i class="bi bi-house-heart-fill"></i>
        Casa Inteligente IoT
      </a>

      <!-- Botão hamburger para mobile -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="menuPrincipal">
        <ul class="navbar-nav me-auto">
          <!-- Links de âncora para as secções da página -->
          <li class="nav-item"><a class="nav-link" href="#sensores">Sensores</a></li>
          <li class="nav-item"><a class="nav-link" href="#atuadores">Atuadores</a></li>
          <li class="nav-item"><a class="nav-link" href="#camara">Câmara</a></li>
          <!-- O histórico só aparece para admin e resident -->
          <?php if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "resident"): ?>
            <li class="nav-item"><a class="nav-link" href="historico.php">Histórico</a></li>
          <?php endif; ?>
        </ul>

        <!-- Mostra o nome do utilizador autenticado; -->
        <span class="user-pill me-2">
          <i class="bi bi-person-circle"></i>
          <?php echo($_SESSION["username"]); ?>
        </span>
        <a class="btn btn-outline-light btn-sm" href="logout.php">
          <i class="bi bi-box-arrow-right"></i>
          Terminar sessão
        </a>
      </div>
    </div>
  </nav>

  <main class="dashboard-shell">
    <!-- Cabeçalho do painel com saudação ao utilizador -->
    <section class="overview-band mb-4">
      <p class="overline mb-2">Casa Inteligente</p>
      <h1>Painel principal</h1>
      <p>Bem-vindo, <?php echo($_SESSION["username"]); ?>.</p>
    </section>

    <!-- Secção dos sensores -->
    <section class="dashboard-section" id="sensores">
      <div class="section-heading">
        <span class="section-kicker">Monitorização</span>
        <h2>Sensores</h2>
      </div>

      <div class="row">
        <!-- Cartão do sensor de movimento: muda de classe CSS consoante o estado (ativo/inativo) -->
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

        <!-- Cartão do sensor de temperatura: mostra o valor numérico em °C -->
        <div class="col-12 col-sm-6 col-xl-4 mb-3">
          <div class="card sensor-card sensor-closed h-100" id="cartaoSensorTemperatura">
            <div class="card-body">
              <h3 class="sensor-title">Sensor de Temperatura</h3>
              <p class="sensor-meta">Monitorizada pelo Arduino</p>
              <div class="sensor-value" id="valorSensorTemperatura">
                <?php echo($valor_sensor_temperatura) . ' °C'; ?>
              </div>
              <span class="state-badge state-off" id="badgeSensorTemperatura">Inativo</span>
            </div>
          </div>
        </div>

        <!-- Cartão do detetor de chama -->
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

    <!-- Secção dos atuadores (buzzers e LEDs) -->
    <section class="dashboard-section" id="atuadores">
      <div class="section-heading">
        <span class="section-kicker">Automação</span>
        <h2>Controlos dos atuadores</h2>
      </div>

      <div class="row">
        <!-- Buzzer de alarme: botão de controlo só visível para admin -->
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
              <?php if ($_SESSION["role"] == "admin"): ?>
                <div class="mt-2">
                  <!--
                    alternarAtuador(), chamada com os IDs dos elementos a atualizar
                    e o nome do atuador para a chamada à API
                  -->
                  <button
                    class="btn <?php echo ($valor_buzzer_alarme == '1') ? 'btn-outline-secondary' : 'btn-primary'; ?> control-button"
                    type="button" id="botaoBuzzerAlarme"
                    onclick="alternarAtuador('cartaoBuzzerAlarme', 'estadoBuzzerAlarme', 'botaoBuzzerAlarme', 'Ativo', 'Inativo', 'Desligar', 'Ligar', 'buzzer-alarme')">
                    <?php echo ($valor_buzzer_alarme == '1') ? 'Desligar' : 'Ligar'; ?>
                  </button>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Buzzer de fogo: também só controlável pelo admin -->
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
              <?php if ($_SESSION["role"] == "admin"): ?>
                <div class="mt-2">
                  <button
                    class="btn <?php echo ($valor_buzzer_fogo == '1') ? 'btn-outline-secondary' : 'btn-primary'; ?> control-button"
                    type="button" id="botaoBuzzerFogo"
                    onclick="alternarAtuador('cartaoBuzzerFogo', 'estadoBuzzerFogo', 'botaoBuzzerFogo', 'Ativo', 'Inativo', 'Desligar', 'Ligar', 'buzzer-fogo')">
                    <?php echo ($valor_buzzer_fogo == '1') ? 'Desligar' : 'Ligar'; ?>
                  </button>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- LED da câmara: admin e resident podem controlar -->
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
              <?php if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "resident"): ?>
                <div class="mt-2">
                  <button
                    class="btn <?php echo ($valor_led_camera == '1') ? 'btn-outline-secondary' : 'btn-primary'; ?> control-button"
                    type="button" id="botaoLedCamera"
                    onclick="alternarAtuador('cartaoLedCamera', 'estadoLedCamera', 'botaoLedCamera', 'Ativo', 'Inativo', 'Desligar', 'Ligar', 'led-camera')">
                    <?php echo ($valor_led_camera == '1') ? 'Desligar' : 'Ligar'; ?>
                  </button>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- LED de aviso de fogo -->
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
              <?php if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "resident"): ?>
                <div class="mt-2">
                  <button
                    class="btn <?php echo ($valor_led_fogo == '1') ? 'btn-outline-secondary' : 'btn-primary'; ?> control-button"
                    type="button" id="botaoLedFogo"
                    onclick="alternarAtuador('cartaoLedFogo', 'estadoLedFogo', 'botaoLedFogo', 'Ativo', 'Inativo', 'Desligar', 'Ligar', 'led-fogo')">
                    <?php echo ($valor_led_fogo == '1') ? 'Desligar' : 'Ligar'; ?>
                  </button>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- LED de aviso de temperatura -->
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
              <?php if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "resident"): ?>
                <div class="mt-2">
                  <button
                    class="btn <?php echo ($valor_led_temperatura == '1') ? 'btn-outline-secondary' : 'btn-primary'; ?> control-button"
                    type="button" id="botaoLedTemperatura"
                    onclick="alternarAtuador('cartaoLedTemperatura', 'estadoLedTemperatura', 'botaoLedTemperatura', 'Ativo', 'Inativo', 'Desligar', 'Ligar', 'led-temperatura')">
                    <?php echo ($valor_led_temperatura == '1') ? 'Desligar' : 'Ligar'; ?>
                  </button>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Secção da câmara: mostra a última imagem capturada e a sua hora -->
    <section class="dashboard-section" id="camara">
      <div id="card-camera" class="card">
        <div class="card-body">
          <div class="section-heading">
            <span class="section-kicker">Câmara</span>
            <h2>Última imagem da câmara</h2>
          </div>

          <div class="camera-placeholder" style="background: #000;">
            <!-- A imagem é atualizada dinamicamente pelo dashboard.js -->
            <img id="imagemWebcam" src="api/images/webcam.jpg" class="imagem-capturada"
              alt="Última Captura da DroidCam">
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

    <!-- Secção de acesso ao histórico (só para admin e resident) -->
    <?php if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "resident"): ?>
      <section class="dashboard-section" id="historico">
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
      </section>
    <?php endif; ?>
  </main>

  <!-- Bootstrap JS (inclui Popper) para componentes interativos -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Script do dashboard: trata da atualização automática dos valores e dos controlos dos atuadores -->
  <script src="js/dashboard.js"></script>

  <!-- Selo de validação CSS do W3C -->
  <p>
      <a href="https://jigsaw.w3.org/css-validator/check/referer">
          <img style="border:0;width:88px;height:31px"
              src="https://jigsaw.w3.org/css-validator/images/vcss-blue"
              alt="CSS válido!" />
      </a>
  </p>
</body>

</html>