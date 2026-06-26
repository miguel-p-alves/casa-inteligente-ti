<?php

  session_start();
  if (!isset($_SESSION["username"]) || !isset($_SESSION["role"])) {
    header("refresh:3;url=index.php");
    die("Acesso restrito");
  }

  $dispositivos = array(
    "sensor-movimento" => "Movimento",
    "luz-camera" => "Luz da câmara",
    "sensor-temperatura" => "Temperatura",
    "led-temperatura" => "LED de temperatura",
    "sensor-chama" => "Chama",
    "buzzer-fogo" => "Buzzer de fogo",
    "camera" => "Câmara"
  );

  $filtro_nome = "";
  $filtro_data_inicio = "";
  $filtro_data_fim = "";

  if (isset($_GET["data_inicio"])) {
      $filtro_data_inicio = $_GET["data_inicio"];
  } 

  if (isset($_GET["data_fim"])) {
      $filtro_data_fim = $_GET["data_fim"];
  } 

  if (isset($_GET["nome"]) && array_key_exists($_GET["nome"], $dispositivos)) {
    $filtro_nome = $_GET["nome"];
  }

  // O histórico usa uma linha por registo no formato: data/hora;tipo;nome;valor;origem.
  function lerLogs($ficheiro_log) {
    $historico = array();

    if (file_exists($ficheiro_log)) {
      $conteudo = file_get_contents($ficheiro_log);
      $linhas = explode(PHP_EOL, $conteudo);

      foreach ($linhas as $linha) {
        $linha = trim($linha);

        if ($linha == "") {
          continue;
        }

        $dados = explode(";", $linha);

        if (count($dados) != 5) {
          continue;
        }

        $data = $dados[0];
        $tipo = $dados[1];
        $nome = $dados[2];
        $valor = $dados[3];
        $origem = $dados[4];

        if ($valor == "1") {
          $valor = "Ativo";
        }

        if ($valor == "0") {
          $valor = "Inativo";
        }

        $historico[] = array($data, $tipo, $nome, $valor, $origem);
      }
    }

    return $historico;
  }

  function carregarHistorico($dispositivos, $filtro_nome) {
    $historico = array();

    foreach ($dispositivos as $nome => $label) {
      if ($filtro_nome != "" && $nome != $filtro_nome) {
        continue;
      }

      $ficheiro = "api/files/" . $nome . "/log.txt";
      $historico = array_merge($historico, lerLogs($ficheiro));
    }

    usort($historico, function($a, $b) {
      return strtotime($b[0]) - strtotime($a[0]);
    });

    return $historico;
  }

  function mostrarLinhasHistorico($historico) {
    if (count($historico) == 0) {
      echo "<tr>";
      echo "<td colspan='5'>Nenhum registo encontrado.</td>";
      echo "</tr>";
      return;
    }

    foreach ($historico as $registo) {
      echo "<tr>";
      echo "<td>" . htmlspecialchars($registo[0]) . "</td>";
      echo "<td>" . htmlspecialchars($registo[1]) . "</td>";
      echo "<td>" . htmlspecialchars($registo[2]) . "</td>";
      echo "<td>" . htmlspecialchars($registo[3]) . "</td>";
      echo "<td><span class='origin-label'>" . htmlspecialchars($registo[4]) . "</span></td>";
      echo "</tr>";
    }
  }

 function carregarDadosGraficoTemperatura($ficheiro_log, $data_inicio, $data_fim) {
    $temperaturas = array();

    if (file_exists($ficheiro_log)) {
      $conteudo = file_get_contents($ficheiro_log);
      $linhas = explode(PHP_EOL, $conteudo);

      foreach ($linhas as $linha) {
        $linha = trim($linha);

        if ($linha != "") {
          $dados = explode(";", $linha);

          if (count($dados) == 5) {
           
            // 1. Isolar apenas os 10 primeiros caracteres (ex: "02-06-2026")
            $data_pt = substr($dados[0], 0, 10);
            
            // 2. Cortar a data pelos traços
            $partes = explode("-", $data_pt);
            
            // 3. Montar no formato do HTML (Ano-Mês-Dia)
            // $partes[2] é o Ano (2026), $partes[1] é o Mês (06), $partes[0] é o Dia (02)
            $data_registo = $partes[2] . "-" . $partes[1] . "-" . $partes[0];
            
            $passou_filtro = true;
            
            if ($data_inicio != "") {
                if ($data_registo < $data_inicio) {
                    $passou_filtro = false;
                }
            }
            
            if ($data_fim != "") {
                if ($data_registo > $data_fim) {
                    $passou_filtro = false;
                }
            }

            // 3. Guarda se passou no teste
            if ($passou_filtro) {
              $temperaturas[] = array($dados[0], $dados[3]);
            }
          }
        }
      }
    }

    return $temperaturas;
  }

  $historico = carregarHistorico($dispositivos, $filtro_nome);
  $temperaturas = carregarDadosGraficoTemperatura("api/files/sensor-temperatura/log.txt", $filtro_data_inicio, $filtro_data_fim);
  $datas_grafico = "";
  $valores_grafico = "";

  // Prepara os valores que vão ser usados pelo Chart.js.
  foreach ($temperaturas as $temperatura) {
    $datas_grafico = $datas_grafico . "'" . $temperatura[0] . "',";
    $valores_grafico = $valores_grafico . $temperatura[1] . ",";
  }

  if (isset($_GET["tabela"])) {
    mostrarLinhasHistorico($historico);
    exit();
  }
?>

<!doctype html>
<html lang="pt-PT">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Histórico - Casa Inteligente IoT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-dark app-navbar sticky-top">
      <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
          <i class="bi bi-house-heart-fill"></i>
          Casa Inteligente IoT
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
          <ul class="navbar-nav me-auto">
            <li class="nav-item"><a class="nav-link" href="index.php#sensores">Sensores</a></li>
            <li class="nav-item"><a class="nav-link" href="index.php#atuadores">Atuadores</a></li>
            <li class="nav-item"><a class="nav-link" href="index.php#camara">Câmara</a></li>
            <li class="nav-item"><a class="nav-link active" href="historico.php">Histórico</a></li>
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
        <h1>Histórico</h1>
      </section>
       <div class="card mb-3">
          <div class="card-body">
            <div class="section-heading">
              <div>
                <span class="section-kicker">Temperatura</span>
                <h2>Histórico de temperatura</h2>
              </div>
            </div>

            <div class="chart-container">
              <canvas id="graficoTemperatura"></canvas>
            </div> <hr class="mt-4 mb-3">

            <form method="GET" action="historico.php">
              <?php if(isset($_GET['nome']) && $_GET['nome'] != ""): ?>
                <input type="hidden" name="nome" value="<?php echo htmlspecialchars($_GET['nome']); ?>">
              <?php endif; ?>

              <div class="mb-3">
                <label class="capture-label" for="data_inicio">Data Início</label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo htmlspecialchars($filtro_data_inicio); ?>">
              </div>

              <div class="mb-3">
                <label class="capture-label" for="data_fim">Data Fim</label>
                <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo htmlspecialchars($filtro_data_fim); ?>">
              </div>

              <button class="btn btn-primary" type="submit">Filtrar Gráfico</button>
              <a class="btn btn-outline-secondary" href="historico.php<?php echo isset($_GET['nome']) && $_GET['nome'] != "" ? '?nome='.htmlspecialchars($_GET['nome']) : ''; ?>">Limpar Datas</a>
            </form>
            
          </div>
        </div>
      <section class="dashboard-section" id="historico">
        <div class="card mb-3">
          <div class="card-body">
            <form method="GET" action="historico.php">
              <div class="mb-3">
                <label class="capture-label" for="nome">Dispositivo</label>
                <select class="form-select" id="nome" name="nome">
                  <option value="">Todos</option>
                  <?php foreach ($dispositivos as $nome => $label): ?>
                    <option value="<?php echo htmlspecialchars($nome); ?>" <?php echo ($filtro_nome == $nome) ? "selected" : ""; ?>>
                      <?php echo htmlspecialchars($label); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <button class="btn btn-primary" type="submit">Filtrar</button>
              <a class="btn btn-outline-secondary" href="historico.php">Limpar filtro</a>
            </form>
          </div>
        </div>
        
        <div class="card">
          <div class="card-body">
            <div class="section-heading">
              <div>
                <span class="section-kicker">Amostras</span>
                <h2>Registos</h2>
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
                  <?php mostrarLinhasHistorico($historico); ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      // Cria o gráfico de temperatura com os dados preparados em PHP.
      var graficoTemperatura = document.getElementById("graficoTemperatura");

     new Chart(graficoTemperatura, {
        type: "line",
        data: {
          labels: [<?php echo $datas_grafico; ?>],
          datasets: [{
            label: "Temperatura (°C)",
            data: [<?php echo $valores_grafico; ?>],
            borderColor: "#0f766e",
            backgroundColor: "rgba(15, 118, 110, 0.15)",
            borderWidth: 2,
            fill: true,
            pointRadius: 0,
            pointHitRadius: 10
          }]
        },
        options: {
          maintainAspectRatio: false, // Diz ao gráfico para respeitar os 400px de altura que pusemos no HTML
          scales: {
            x: {
              ticks: {
                maxTicksLimit: 10, // Limita as datas em baixo a um máximo de 10 espaçadas!
                maxRotation: 45,   // Inclina ligeiramente o texto para caber melhor
                minRotation: 45
              }
            }
          }
        }
      });
    </script>
    <script src="js/dashboard.js"></script>
  </body>
</html>
