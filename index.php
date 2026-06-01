<?php
  // Tenta ler o estado atual da campainha do ficheiro
  $ficheiro_campainha = "api/files/campainha/valor.txt";
  $valor_campainha = file_exists($ficheiro_campainha) ? file_get_contents($ficheiro_campainha) : "0";

  $ficheiro_led = "api/files/led/valor.txt";
  $valor_led = file_exists($ficheiro_led) ? file_get_contents($ficheiro_led) : "0";

  $ficheiro_botao_campainha = "api/files/botao-campainha/valor.txt";
  $valor_botao_campainha = file_exists($ficheiro_botao_campainha) ? file_get_contents($ficheiro_botao_campainha) : "0";

  $ficheiro_sensor_movimento = "api/files/sensor-movimento/valor.txt";
  $valor_sensor_movimento = file_exists($ficheiro_sensor_movimento) ? file_get_contents($ficheiro_sensor_movimento) : "0";

  $ficheiro_botao_alarme = "api/files/botao-alarme/valor.txt";
  $valor_botao_alarme = file_exists($ficheiro_botao_alarme) ? file_get_contents($ficheiro_botao_alarme) : "0";

  $ficheiro_sensor_temperatura = "api/files/sensor-temperatura/valor.txt";
  $valor_sensor_temperatura = file_exists($ficheiro_sensor_temperatura) ? file_get_contents($ficheiro_sensor_temperatura) : "0";

  $ficheiro_sensor_chama = "api/files/sensor-chama/valor.txt";
  $valor_sensor_chama = file_exists($ficheiro_sensor_chama) ? file_get_contents($ficheiro_sensor_chama) : "0";

  $ficheiro_buzzer_alarme = "api/files/buzzer-alarme/valor.txt";
  $valor_buzzer_alarme = file_exists($ficheiro_buzzer_alarme) ? file_get_contents($ficheiro_buzzer_alarme) : "0";

  $ficheiro_buzzer_fogo = "api/files/buzzer-fogo/valor.txt";
  $valor_buzzer_fogo = file_exists($ficheiro_buzzer_fogo) ? file_get_contents($ficheiro_buzzer_fogo) : "0";

  $ficheiro_led_fogo = "api/files/led-fogo/valor.txt";
  $valor_led_fogo = file_exists($ficheiro_led_fogo) ? file_get_contents($ficheiro_led_fogo) : "0";

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
            <li class="nav-item">
              <a class="nav-link" href="#historico">Histórico</a>
            </li>
          </ul>

          <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2">
            <span class="user-pill">
              <i class="bi bi-person-circle"></i>
              Miguel Santos
            </span>
            <button class="btn btn-outline-light btn-sm logout-button" type="button">
              <i class="bi bi-box-arrow-right"></i>
              Terminar sessão
            </button>
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
          </div>
          <div class="col-lg-5">
            <div class="row g-3">
              <div class="col-4">
                <div class="status-tile">
                  <i class="bi bi-shield-check"></i>
                  <span>Segurança</span>
                  <strong>Ativa</strong>
                </div>
              </div>
              <div class="col-4">
                <div class="status-tile">
                  <i class="bi bi-house-gear"></i>
                  <span>Divisão</span>
                  <strong>Sala</strong>
                </div>
              </div>
              <div class="col-4">
                <div class="status-tile">
                  <i class="bi bi-wifi"></i>
                  <span>Rede</span>
                  <strong>Estável</strong>
                </div>
              </div>
            </div>
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
                    <p class="sensor-meta mb-0">Botão de Pressão</p>
                  </div>
                  <span class="sensor-icon"><i class="bi bi-broadcast"></i></span>
                </div>
                <div class="sensor-value" id="valorSensorMovimento">
                  <?php echo ($valor_sensor_movimento == '1') ? 'Ativo' : 'Inativo'; ?>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-3">
                  <span class="sensor-meta">Origem: ?</span>
                  <span class="state-badge <?php echo ($valor_sensor_movimento == '1') ? 'state-on' : 'state-off'; ?>" id="badgeSensorMovimento">
                    <?php echo ($valor_sensor_movimento == '1') ? 'Ativo' : 'Inativo'; ?>
                  </span>
                </div>
              </div>
            </div>
          </article>

          <article class="col-12 col-sm-6 col-xl-4">
            <div class="card sensor-card <?php echo ($valor_botao_alarme == '1') ? 'sensor-active' : 'sensor-closed'; ?> h-100" id="cartaoBotaoAlarme">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                  <div>
                    <h3 class="sensor-title">Alarme</h3>
                    <p class="sensor-meta mb-0">Botão de Pressão</p>
                  </div>
                  <span class="sensor-icon"><i class="bi-record-circle-fill"></i></span>
                </div>
                <div class="sensor-value" id="valorBotaoAlarme">
                  <?php echo ($valor_botao_alarme == '1') ? 'Ativo' : 'Inativo'; ?>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-3">
                  <span class="sensor-meta">Origem: ?</span>
                  <span class="state-badge <?php echo ($valor_botao_alarme == '1') ? 'state-on' : 'state-off'; ?>" id="badgeBotaoAlarme">
                    <?php echo ($valor_botao_alarme == '1') ? 'Ativo' : 'Inativo'; ?>
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
                    <h3 class="sensor-title">Medição de Temperatura</h3>
                    <p class="sensor-meta mb-0">Sensor de Temperatura</p>
                  </div>
                  <span class="sensor-icon"><i class="bi-thermometer-half"></i></span>
                </div>
                <div class="sensor-value" id="valorSensorTemperatura">Inativo</div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-3">
                  <span class="sensor-meta">Origem: MCU</span>
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
                    <h3 class="sensor-title">Detetor de Fogo</h3>
                    <p class="sensor-meta mb-0">Sensor de Chama / Mostrar o que ele envia?</p>
                  </div>
                  <span class="sensor-icon"><i class="bi bi-fire"></i></span>
                </div>
                <div class="sensor-value" id="valorSensorChama">
                  <?php echo ($valor_sensor_chama == '1') ? 'Ativo' : 'Inativo'; ?>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-3">
                  <span class="sensor-meta">Origem: ?</span>
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
                    <h3 class="actuator-title">Alarme Sonoro</h3>
                    <p class="sensor-meta mb-0">Buzzer</p>
                  </div>
                  <span class="actuator-icon"><i class="bi bi-bell-fill"></i></span>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-auto pt-4">
                  <span class="state-badge <?php echo ($valor_buzzer_alarme == '1') ? 'state-on' : 'state-off'; ?>" id="estadoBuzzerAlarme">
                    <?php echo ($valor_buzzer_alarme == '1') ? 'Ativo' : 'Inativo'; ?>
                  </span>
                  <button class="btn <?php echo ($valor_buzzer_alarme == '1') ? 'btn-outline-secondary' : 'btn-primary'; ?> control-button" type="button" id="botaoBuzzerAlarme" onclick="alternarAtuador('cartaoBuzzerAlarme', 'estadoBuzzerAlarme', 'botaoBuzzerAlarme', 'Ativo', 'Inativo', 'Desligar', 'Ligar', 'buzzer-alarme')">
                    <?php echo ($valor_buzzer_alarme == '1') ? 'Desligar' : 'Ligar'; ?>
                  </button>
                </div>
              </div>
            </div>
          </article>

          <article class="col-12 col-sm-6 col-xl-4">
            <div class="card actuator-card <?php echo ($valor_buzzer_fogo == '1') ? 'actuator-active' : ''; ?> h-100" id="cartaoBuzzerFogo">
              <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between gap-3">
                  <div>
                    <h3 class="actuator-title">Alarme de Aviso de Fogo</h3>
                    <p class="sensor-meta mb-0">Buzzer</p>
                  </div>
                  <span class="actuator-icon"><i class="bi bi-bell-fill"></i></span>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-auto pt-4">
                  <span class="state-badge <?php echo ($valor_buzzer_fogo == '1') ? 'state-on' : 'state-off'; ?>" id="estadoBuzzerFogo">
                    <?php echo ($valor_buzzer_fogo == '1') ? 'Ativo' : 'Inativo'; ?>
                  </span>
                  <button class="btn <?php echo ($valor_buzzer_fogo == '1') ? 'btn-outline-secondary' : 'btn-primary'; ?> control-button" type="button" id="botaoBuzzerFogo" onclick="alternarAtuador('cartaoBuzzerFogo', 'estadoBuzzerFogo', 'botaoBuzzerFogo', 'Ativo', 'Inativo', 'Desligar', 'Ligar', 'buzzer-fogo')">
                    <?php echo ($valor_buzzer_fogo == '1') ? 'Desligar' : 'Ligar'; ?>
                  </button>
                </div>
              </div>
            </div>
          </article>

          <article class="col-12 col-sm-6 col-xl-4">
            <div class="card actuator-card <?php echo ($valor_led_fogo == '1') ? 'actuator-active' : ''; ?> h-100" id="cartaoLedFogo">
              <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between gap-3">
                  <div>
                    <h3 class="actuator-title">Led de Aviso de Fogo</h3>
                    <p class="sensor-meta mb-0">Led</p>
                  </div>
                  <span class="actuator-icon"><i class="bi bi-lightbulb-fill"></i></span>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-auto pt-4">
                  <span class="state-badge <?php echo ($valor_led_fogo == '1') ? 'state-on' : 'state-off'; ?>" id="estadoLedFogo">
                    <?php echo ($valor_led_fogo == '1') ? 'Ativo' : 'Inativo'; ?>
                  </span>
                  <button class="btn <?php echo ($valor_led_fogo == '1') ? 'btn-outline-secondary' : 'btn-primary'; ?> control-button" type="button" id="botaoLedFogo" onclick="alternarAtuador('cartaoLedFogo', 'estadoLedFogo', 'botaoLedFogo', 'Ativo', 'Inativo', 'Desligar', 'Ligar', 'led-fogo')">
                    <?php echo ($valor_led_fogo == '1') ? 'Desligar' : 'Ligar'; ?>
                  </button>
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
                    <h2>Última captura</h2>
                  </div>
                  <i class="bi bi-camera-video camera-icon"></i>
                </div>

                <div class="camera-placeholder" role="img" aria-label="Imagem simulada da última captura">
                  <div class="camera-overlay">
                    <span>Sala principal</span>
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
                  <button class="btn btn-primary" type="button" onclick="capturarImagem()">
                    <i class="bi bi-camera"></i>
                    Capturar imagem
                  </button>
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
                    <h2>Eventos recentes</h2>
                  </div>
                  <i class="bi bi-activity camera-icon"></i>
                </div>

                <!-- Exemplos estáticos. No projeto final, estes registos podem vir de ficheiros .txt via PHP. -->
                <ul class="list-group event-list">
                  <li class="list-group-item">
                    <span class="event-dot"></span>
                    <span class="event-text">
                      <strong>Movimento detetado na sala</strong>
                      <small>27/05/2026, 14:10:00 · SBC</small>
                    </span>
                  </li>
                  <li class="list-group-item">
                    <span class="event-dot"></span>
                    <span class="event-text">
                      <strong>Luz principal ligada</strong>
                      <small>27/05/2026, 14:05:00 · Dashboard</small>
                    </span>
                  </li>
                  <li class="list-group-item">
                    <span class="event-dot"></span>
                    <span class="event-text">
                      <strong>Porta aberta</strong>
                      <small>27/05/2026, 13:58:00 · SBC</small>
                    </span>
                  </li>
                  <li class="list-group-item">
                    <span class="event-dot"></span>
                    <span class="event-text">
                      <strong>Temperatura atualizada</strong>
                      <small>27/05/2026, 13:52:00 · MCU</small>
                    </span>
                  </li>
                  <li class="list-group-item">
                    <span class="event-dot"></span>
                    <span class="event-text">
                      <strong>Alarme ativado</strong>
                      <small>27/05/2026, 13:45:00 · Dashboard</small>
                    </span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="dashboard-section" id="historico">
        <div class="row g-3">
          <div class="col-xl-8">
            <div class="card h-100">
              <div class="card-body">
                <div class="section-heading compact-heading">
                  <div>
                    <span class="section-kicker">Amostras</span>
                    <h2>Histórico</h2>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="table table-hover align-middle history-table mb-0">
                    <thead>
                      <tr>
                        <th scope="col">Data/Hora</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Nome</th>
                        <th scope="col">Valor/Estado</th>
                        <th scope="col">Origem</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>27/05/2026, 14:12:00</td>
                        <td>Sensor</td>
                        <td>Temperatura</td>
                        <td>21,6 °C</td>
                        <td><span class="origin-label">MCU</span></td>
                      </tr>
                      <tr>
                        <td>27/05/2026, 14:05:00</td>
                        <td>Atuador</td>
                        <td>Luz principal</td>
                        <td>Ligado</td>
                        <td><span class="origin-label">Dashboard</span></td>
                      </tr>
                      <tr>
                        <td>27/05/2026, 13:58:00</td>
                        <td>Sensor</td>
                        <td>Movimento</td>
                        <td>Detetado</td>
                        <td><span class="origin-label">SBC</span></td>
                      </tr>
                      <tr>
                        <td>27/05/2026, 13:54:00</td>
                        <td>Sensor</td>
                        <td>Gás/Fumo</td>
                        <td>Normal</td>
                        <td><span class="origin-label">MCU</span></td>
                      </tr>
                      <tr>
                        <td>27/05/2026, 13:45:00</td>
                        <td>Atuador</td>
                        <td>Alarme</td>
                        <td>Ativo</td>
                        <td><span class="origin-label">Dashboard</span></td>
                      </tr>
                      <tr>
                        <td>27/05/2026, 13:40:00</td>
                        <td>Sensor</td>
                        <td>Estado da Porta</td>
                        <td>Aberto</td>
                        <td><span class="origin-label">SBC</span></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-4">
            <div class="card h-100 temperature-card">
              <div class="card-body">
                <div class="section-heading compact-heading">
                  <div>
                    <span class="section-kicker">Tendência</span>
                    <h2>Histórico de Temperatura</h2>
                  </div>
                </div>

                <div class="temperature-chart" aria-label="Histórico visual de temperatura">
                  <div class="chart-item">
                    <div class="chart-track">
                      <div class="chart-bar" style="height: 42%"></div>
                    </div>
                    <span class="chart-label">20,9 °C</span>
                  </div>
                  <div class="chart-item">
                    <div class="chart-track">
                      <div class="chart-bar" style="height: 48%"></div>
                    </div>
                    <span class="chart-label">21,1 °C</span>
                  </div>
                  <div class="chart-item">
                    <div class="chart-track">
                      <div class="chart-bar" style="height: 54%"></div>
                    </div>
                    <span class="chart-label">21,4 °C</span>
                  </div>
                  <div class="chart-item">
                    <div class="chart-track">
                      <div class="chart-bar" style="height: 50%"></div>
                    </div>
                    <span class="chart-label">21,2 °C</span>
                  </div>
                  <div class="chart-item">
                    <div class="chart-track">
                      <div class="chart-bar" style="height: 62%"></div>
                    </div>
                    <span class="chart-label">21,7 °C</span>
                  </div>
                  <div class="chart-item">
                    <div class="chart-track">
                      <div class="chart-bar" style="height: 60%"></div>
                    </div>
                    <span class="chart-label">21,6 °C</span>
                  </div>
                  <div class="chart-item">
                    <div class="chart-track">
                      <div class="chart-bar" style="height: 64%"></div>
                    </div>
                    <span class="chart-label">21,8 °C</span>
                  </div>
                  <div class="chart-item">
                    <div class="chart-track">
                      <div class="chart-bar" style="height: 60%"></div>
                    </div>
                    <span class="chart-label">21,6 °C</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
  </body>
</html>
