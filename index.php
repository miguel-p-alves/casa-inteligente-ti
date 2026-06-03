<?php
  $ficheiro_sensor_movimento = "api/files/sensor-movimento/valor.txt";
  $valor_sensor_movimento = file_exists($ficheiro_sensor_movimento) ? file_get_contents($ficheiro_sensor_movimento) : "0";

  $ficheiro_sensor_temperatura = "api/files/sensor-temperatura/valor.txt";
  $valor_sensor_temperatura = file_exists($ficheiro_sensor_temperatura) ? file_get_contents($ficheiro_sensor_temperatura) : "0";

  $ficheiro_sensor_chama = "api/files/sensor-chama/valor.txt";
  $valor_sensor_chama = file_exists($ficheiro_sensor_chama) ? file_get_contents($ficheiro_sensor_chama) : "0";

  $ficheiro_luz_camera = "api/files/luz-camera/valor.txt";
  $valor_luz_camera = file_exists($ficheiro_luz_camera) ? file_get_contents($ficheiro_luz_camera) : "0";

  $ficheiro_buzzer_fogo = "api/files/buzzer-fogo/valor.txt";
  $valor_buzzer_fogo = file_exists($ficheiro_buzzer_fogo) ? file_get_contents($ficheiro_buzzer_fogo) : "0";

  $ficheiro_led_temperatura = "api/files/led-temperatura/valor.txt";
  $valor_led_temperatura = file_exists($ficheiro_led_temperatura) ? file_get_contents($ficheiro_led_temperatura) : "0";


  // O histórico usa uma linha por registro no formato: data/hora;tipo;nome;valor;origem.
  // Esta função lê o log, separa cada linha nesses 5 campos e prepara os dados para a tabela.
  function lerLogs($ficheiro_log) {
    $historico = array();

    // Se o arquivo ainda não existir, a função retorna a lista vazia.
    if (file_exists($ficheiro_log)) {
      $conteudo = file_get_contents($ficheiro_log);

      // Cada quebra de linha representa um registro diferente do histórico.
      $linhas = explode(PHP_EOL, $conteudo);

      foreach ($linhas as $linha) {
        // O trim remove espaços, tabs e quebras de linha do começo e do fim do texto.
        $linha = trim($linha);

        // Linhas vazias são ignoradas para não criar linhas em branco na tabela.
        if ($linha != "") {
          // O ponto e vírgula separa os campos que depois viram as colunas da tabela.
          $dados = explode(";", $linha);

          // Se não tiver 5 campos, a linha não serve para preencher a tabela inteira.
          if (count($dados) == 5) {
            $data = $dados[0];
            $tipo = $dados[1];
            $nome = $dados[2];
            $valor = $dados[3];
            $origem = $dados[4];
          }
          else {
            continue;
          }

          // No arquivo fica mais simples guardar 1 e 0, mas na tabela é melhor mostrar texto.
          if ($valor == "1") {
            $valor = "Ativo";
          }

          if ($valor == "0") {
            $valor = "Inativo";
          }

          // Cada posição do array corresponde a uma coluna da tabela.
          $historico[] = array($data, $tipo, $nome, $valor, $origem);
        }
      }
    }

    return $historico;
  }

    // 1. Criamos um histórico vazio (uma lista limpa)
  $historico = array();

  // 2. Lista final dos dispositivos do protótipo Casa Inteligente.
  $dispositivos_finais = array(
    "sensor-movimento",
    "luz-camera",
    "sensor-temperatura",
    "led-temperatura",
    "sensor-chama",
    "buzzer-fogo",
    "camera"
  );

  // 3. Fazemos um ciclo simples para ler cada ficheiro que ele encontrou
  foreach ($dispositivos_finais as $dispositivo) {
      $ficheiro = "api/files/" . $dispositivo . "/log.txt";

      if (!file_exists($ficheiro)) {
          continue;
      }

      // Lê o ficheiro atual
      $log_desta_pasta = lerLogs($ficheiro);
      
      // array_merge junta as linhas deste log ao nosso histórico principal
      $historico = array_merge($historico, $log_desta_pasta);
  }

  // 4. Ordenação cronológica (Mais recente primeiro)
  usort($historico, function($a, $b) {
      // strtotime transforma o texto da data em segundos para o PHP conseguir comparar números
      return strtotime($b[0]) - strtotime($a[0]);
  });

  // Se o JavaScript pedir "tabela=sim", o PHP desenha só as linhas e PARA!
  if (isset($_GET['tabela'])) {
      foreach ($historico as $registro) {
          echo "<tr>";
          echo "<td>" . $registro[0] . "</td>";
          echo "<td>" . $registro[1] . "</td>";
          echo "<td>" . $registro[2] . "</td>";
          echo "<td>" . $registro[3] . "</td>";
          echo "<td><span class='origin-label'>" . $registro[4] . "</span></td>";
          echo "</tr>";
      }
      exit(); // Este comando é a magia: faz o PHP parar e não carregar o HTML todo!
  }
  // -----------------------------

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
              Miguel Alves
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
                    <h3 class="sensor-title">Movimento</h3>
                    <p class="sensor-meta mb-0">Detetado pelo Arduino</p>
                  </div>
                  <span class="sensor-icon"><i class="bi bi-broadcast"></i></span>
                </div>
                <div class="sensor-value" id="valorSensorMovimento">
                  <?php echo ($valor_sensor_movimento == '1') ? 'Ativo' : 'Inativo'; ?>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-3">
                  <span class="sensor-meta">Origem: Arduino</span>
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
                    <h3 class="sensor-title">Temperatura</h3>
                    <p class="sensor-meta mb-0">Monitorizada pelo Arduino</p>
                  </div>
                  <span class="sensor-icon"><i class="bi bi-thermometer-half"></i></span>
                </div>
                <div class="sensor-value" id="valorSensorTemperatura">
                  <?php echo trim($valor_sensor_temperatura) . ' °C'; ?>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-3">
                  <span class="sensor-meta">Origem: Arduino</span>
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
                    <h3 class="sensor-title">Chama</h3>
                    <p class="sensor-meta mb-0">Detetada pela Raspberry Pi</p>
                  </div>
                  <span class="sensor-icon"><i class="bi bi-fire"></i></span>
                </div>
                <div class="sensor-value" id="valorSensorChama">
                  <?php echo ($valor_sensor_chama == '1') ? 'Ativo' : 'Inativo'; ?>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-3">
                  <span class="sensor-meta">Origem: Raspberry Pi</span>
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
            <div class="card actuator-card <?php echo ($valor_luz_camera == '1') ? 'actuator-active' : ''; ?> h-100" id="cartaoLuzCamera">
              <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between gap-3">
                  <div>
                    <h3 class="actuator-title">Luz da câmara</h3>
                    <p class="sensor-meta mb-0">LED controlado pelo Arduino</p>
                  </div>
                  <span class="actuator-icon"><i class="bi bi-lightbulb-fill"></i></span>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-auto pt-4">
                  <span class="state-badge <?php echo ($valor_luz_camera == '1') ? 'state-on' : 'state-off'; ?>" id="estadoLuzCamera">
                    <?php echo ($valor_luz_camera == '1') ? 'Ativo' : 'Inativo'; ?>
                  </span>
                  <button class="btn <?php echo ($valor_luz_camera == '1') ? 'btn-outline-secondary' : 'btn-primary'; ?> control-button" type="button" id="botaoLuzCamera" onclick="alternarAtuador('cartaoLuzCamera', 'estadoLuzCamera', 'botaoLuzCamera', 'Ativo', 'Inativo', 'Desligar', 'Ligar', 'luz-camera')">
                    <?php echo ($valor_luz_camera == '1') ? 'Desligar' : 'Ligar'; ?>
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
                    <h3 class="actuator-title">Buzzer de fogo</h3>
                    <p class="sensor-meta mb-0">Controlado pela Raspberry Pi</p>
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
            <div class="card actuator-card <?php echo ($valor_led_temperatura == '1') ? 'actuator-active' : ''; ?> h-100" id="cartaoLedTemperatura">
              <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between gap-3">
                  <div>
                    <h3 class="actuator-title">LED de temperatura</h3>
                    <p class="sensor-meta mb-0">Aviso controlado pelo Arduino</p>
                  </div>
                  <span class="actuator-icon"><i class="bi bi-lightbulb-fill"></i></span>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-auto pt-4">
                  <span class="state-badge <?php echo ($valor_led_temperatura == '1') ? 'state-on' : 'state-off'; ?>" id="estadoLedTemperatura">
                    <?php echo ($valor_led_temperatura == '1') ? 'Ativo' : 'Inativo'; ?>
                  </span>
                  <button class="btn <?php echo ($valor_led_temperatura == '1') ? 'btn-outline-secondary' : 'btn-primary'; ?> control-button" type="button" id="botaoLedTemperatura" onclick="alternarAtuador('cartaoLedTemperatura', 'estadoLedTemperatura', 'botaoLedTemperatura', 'Ativo', 'Inativo', 'Desligar', 'Ligar', 'led-temperatura')">
                    <?php echo ($valor_led_temperatura == '1') ? 'Desligar' : 'Ligar'; ?>
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
                    <tbody id="tabela-historico">
                      <?php if (count($historico) == 0): ?>
                        <!-- Mostra uma mensagem quando ainda não existe histórico. -->
                        <tr>
                          <td colspan="5">Nenhum registro encontrado.</td>
                        </tr>
                      <?php else: ?>
                        <!-- Cria uma linha da tabela para cada registro encontrado no log. -->
                        <?php foreach ($historico as $registro): ?>
                          <tr>
                            <td><?php echo $registro[0]; ?></td>
                            <td><?php echo $registro[1]; ?></td>
                            <td><?php echo $registro[2]; ?></td>
                            <td><?php echo $registro[3]; ?></td>
                            <td><span class="origin-label"><?php echo $registro[4]; ?></span></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
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
