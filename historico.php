<?php

  // inicia a sessão para conseguirmos ler os dados do utilizador logado
  session_start();

  // se não tiver sessão ativa não deixa entrar e manda de volta para o login ao fim de 3 segundos
  if (!isset($_SESSION["username"]) || !isset($_SESSION["role"])) {
    header("refresh:3;url=index.php");
    die("Acesso restrito");
  }

  // se for guest não deixa entrar e manda de volta para o dashboard ao fim de 3 segundos
  if ($_SESSION["role"] == "guest") {
    header("refresh:3;url=dashboard.php");
    die("Acesso restrito");
  }

  // array associativo com todos os dispositivos do sistema
  // usamos => porque precisamos de associar um nome "tecnico" a um nome "bonito"
  // a chave (lado esquerdo do =>) é o nome real usado nos ficheiros e na API
  // o valor (lado direito do =>) é o texto que aparece na pagina para o utilizador
  // se fosse um array normal so tinhamos os nomes bonitos e perdiamos o nome dos ficheiros
  $dispositivos = array(
    "sensor-movimento" => "Movimento",
    "led-camera"       => "Luz da câmara",
    "sensor-temperatura" => "Temperatura",
    "led-temperatura"  => "LED de temperatura",
    "sensor-chama"     => "Chama",
    "buzzer-fogo"      => "Buzzer de fogo",
    "camera"           => "Câmara"
  );

  // variaveis dos filtros, começam vazias e so recebem valor se o utilizador filtrar
  $filtro_nome = "";
  $filtro_data_inicio = "";
  $filtro_data_fim = "";

  // verifica se vieram filtros de data no URL (ex: data_inicio=2026-01-01)
  if (isset($_GET["data_inicio"])) {
      $filtro_data_inicio = $_GET["data_inicio"];
  } 

  if (isset($_GET["data_fim"])) {
      $filtro_data_fim = $_GET["data_fim"];
  } 

  // só aceita o filtro de nome se o dispositivo existir no nosso array
  // para evitar que alguem invente nomes no URL
  if (isset($_GET["nome"]) && array_key_exists($_GET["nome"], $dispositivos)) {
    $filtro_nome = $_GET["nome"];
  }

  // lê o ficheiro de log de um dispositivo e devolve um array com todos os registos
  // cada linha do ficheiro tem o formato: data/hora;tipo;nome;valor;origem
  function lerLogs($ficheiro_log) {
    $historico = array();

    if (file_exists($ficheiro_log)) {
      $conteudo = file_get_contents($ficheiro_log);

      // separa o conteudo em linhas
      $linhas = explode(PHP_EOL, $conteudo);

      foreach ($linhas as $linha) {
        // limpa espacos em branco à volta
        $linha = trim($linha);

        // ignora linhas vazias
        if ($linha == "") {
          continue;
        }

        // separa cada linha pelos ponto e virgula
        $dados = explode(";", $linha);

        // se não tiver exatamente 5 partes é porque a linha está mal formada, por isso pulamos esse
        if (count($dados) != 5) {
          continue;
        }

        // guardamos cada parte numa variavel para ficar mais legivel
        $data   = $dados[0];
        $tipo   = $dados[1];
        $nome   = $dados[2];
        $valor  = $dados[3];
        $origem = $dados[4];

        // convertemos o 1 e o 0 para texto mais percetivel para o utilizador
        if ($valor == "1") {
          $valor = "Ativo";
        }

        if ($valor == "0") {
          $valor = "Inativo";
        }

        // adicionamos o registo ao array do historico
        $historico[] = array($data, $tipo, $nome, $valor, $origem);
      }
    }

    return $historico;
  }

  // junta os logs de todos os dispositivos num array só
  // se houver filtro de nome só carrega o log desse dispositivo
  function carregarHistorico($dispositivos, $filtro_nome) {
    $historico = array();

    // o foreach em arrays associativos permite aceder à chave e ao valor ao mesmo tempo
    // $nome fica com a chave (ex: "sensor-movimento") e $label fica com o valor (ex: "Movimento")
    // precisamos dos dois porque $nome é usado para montar o caminho do ficheiro
    // e sem $label não conseguimos mostrar o nome "bonito" mais para a frente
    foreach ($dispositivos as $nome => $label) {

      // se o filtro estiver ativo e não for este dispositivo, salta
      if ($filtro_nome != "" && $nome != $filtro_nome) {
        continue;
      }

      // usa o $nome (a chave) para construir o caminho do ficheiro
      $ficheiro = "api/files/" . $nome . "/log.txt";

      // junta os registos deste dispositivo com o array principal
      $historico = array_merge($historico, lerLogs($ficheiro));
    }

    // ordena do mais recente para o mais antigo
    // o strtotime converte a data em numero para conseguirmos comparar
    usort($historico, function($a, $b) {
      return strtotime($b[0]) - strtotime($a[0]);
    });

    return $historico;
  }

  // desenha as linhas da tabela de historico no HTML
  function mostrarLinhasHistorico($historico) {

    // se nao houver registos mostra uma mensagem em vez de uma tabela vazia
    if (count($historico) == 0) {
      echo "<tr>";
      echo "<td colspan='5'>Nenhum registo encontrado.</td>";
      echo "</tr>";
      return;
    }

    foreach ($historico as $registo) {
      echo "<tr>";
      echo "<td>" . $registo[0] . "</td>";
      echo "<td>" . $registo[1] . "</td>";
      echo "<td>" . $registo[2] . "</td>";
      echo "<td>" . $registo[3] . "</td>";
      echo "<td><span class='origin-label'>" . $registo[4] . "</span></td>";
      echo "</tr>";
    }
  }

  // lê o log de temperatura e devolve só os registos dentro do intervalo de datas escolhido
  function carregarDadosGraficoTemperatura($ficheiro_log, $data_inicio, $data_fim) {
    $temperaturas = array();

    if (file_exists($ficheiro_log)) {
      $conteudo = file_get_contents($ficheiro_log);

      // separa o conteudo em linhas
      $linhas = explode(PHP_EOL, $conteudo);

      foreach ($linhas as $linha) {
        // limpa espacos em branco à volta       
        $linha = trim($linha);

        if ($linha != "") {
          $dados = explode(";", $linha);

          if (count($dados) == 5) {

            // os primeiros 10 caracteres da data são sempre "dd-mm-aaaa"
            $data_pt = substr($dados[0], 0, 10);
            
            // separamos pelos traços para reorganizar a data
            $partes = explode("-", $data_pt);
            
            // o input type=date do HTML usa o formato aaaa-mm-dd
            // entao temos de reorganizar as partes da data para comparar corretamente
            // $partes[0] = dia, $partes[1] = mes, $partes[2] = ano
            $data_registo = $partes[2] . "-" . $partes[1] . "-" . $partes[0];
            
            // assume que o registo passa no filtro ate provar o contrario
            $passou_filtro = true;
            // se a data do registo for anterior ao inicio do filtro, descarta
            if ($data_inicio != "") {
                if ($data_registo < $data_inicio) {
                    $passou_filtro = false;
                }
            }
            
            // se a data do registo for depois do fim do filtro, descarta
            if ($data_fim != "") {
                if ($data_registo > $data_fim) {
                    $passou_filtro = false;
                }
            }

            // só guarda se passou nos dois filtros de data
            if ($passou_filtro) {
              $temperaturas[] = array($dados[0], $dados[3]);
            }
          }
        }
      }
    }

    return $temperaturas;
  }

  // carrega os dados com os filtros ativos
  $historico    = carregarHistorico($dispositivos, $filtro_nome);
  $temperaturas = carregarDadosGraficoTemperatura("api/files/sensor-temperatura/log.txt", $filtro_data_inicio, $filtro_data_fim);

  // estas strings vão ser injetadas no javascript lá em baixo para o Chart.js usar
  $datas_grafico   = "";
  $valores_grafico = "";

  // monta as strings com os valores separados por virgula para o grafico
  foreach ($temperaturas as $temperatura) {
    $datas_grafico   = $datas_grafico   . "'" . $temperatura[0] . "',";
    $valores_grafico = $valores_grafico . $temperatura[1] . ",";
  }

  // se o pedido vier com ?tabela na URL só devolve as linhas da tabela e pára
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
    <!-- bootstrap para o estilo geral -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- icones do bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- css do nosso projeto -->
    <link href="css/style.css" rel="stylesheet">
  </head>
  <body>
    <!-- barra de navegação que aparece em todas as paginas -->
    <nav class="navbar navbar-expand-lg navbar-dark app-navbar sticky-top">
      <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
          <i class="bi bi-house-heart-fill"></i>
          Casa Inteligente IoT
        </a>

        <!-- botão que aparece no telemovel para abrir o menu -->
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

          <!-- mostra o nome do utilizador logado que vem da sessão -->
          <span class="user-pill me-2">
            <i class="bi bi-person-circle"></i>
            <?php echo ($_SESSION["username"]); ?>
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

      <!-- card do grafico de temperatura -->
      <div class="card mb-3">
          <div class="card-body">
            <div class="section-heading">
              <div>
                <span class="section-kicker">Temperatura</span>
                <h2>Histórico de temperatura</h2>
              </div>
            </div>

            <!-- o canvas é onde o Chart.js vai desenhar o grafico -->
            <div class="chart-container">
              <canvas id="graficoTemperatura"></canvas>
            </div>

            <hr class="mt-4 mb-3">

            <!-- formulario para filtrar o grafico por datas -->
            <form method="GET" action="historico.php">
              <?php
              // se já estiver um filtro de nome ativo mantemo-lo quando filtramos por data
              // se não o filtro de nome perdia-se ao submeter este formulario
              if (isset($_GET['nome']) && $_GET['nome'] != "") {
                echo '<input type="hidden" name="nome" value="' . ($_GET['nome']) . '">';
              }
              ?>

              <div class="mb-3">
                <label class="capture-label" for="data_inicio">Data Início</label>
                <!-- value mostra a data que ja estava selecionada -->
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo($filtro_data_inicio); ?>">
              </div>

              <div class="mb-3">
                <label class="capture-label" for="data_fim">Data Fim</label>
                <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo ($filtro_data_fim); ?>">
              </div>

              <button class="btn btn-primary" type="submit">Filtrar Gráfico</button>
              <!-- o link de limpar mantem o filtro de nome mas remove as datas -->
              <a class="btn btn-outline-secondary" href="historico.php<?php echo isset($_GET['nome']) && $_GET['nome'] != "" ? '?nome='. ($_GET['nome']) : ''; ?>">Limpar Datas</a>
            </form>
            
          </div>
        </div>

      <!-- secção da tabela de historico -->
      <section class="dashboard-section" id="historico">
        <div class="card mb-3">
          <div class="card-body">

            <!-- formulario para filtrar a tabela por dispositivo -->
            <form method="GET" action="historico.php">
              <div class="mb-3">
                <label class="capture-label" for="nome">Dispositivo</label>
                <select class="form-select" id="nome" name="nome">
                  <option value="">Todos</option>
                  <?php
                  foreach ($dispositivos as $nome => $label) {
                    // se este for o dispositivo filtrado marca como selecionado
                    $selecionado = ($filtro_nome == $nome) ? 'selected' : '';
                    echo '<option value="' . ($nome) . '" ' . $selecionado . '>' . ($label) . '</option>';
                  }
                  ?>
                </select>
              </div>
              <button class="btn btn-primary" type="submit">Filtrar</button>
              <a class="btn btn-outline-secondary" href="historico.php">Limpar filtro</a>
            </form>
          </div>
        </div>
        
        <!-- tabela com os registos do historico -->
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
                <tbody id="tabela-historico">
                  <?php mostrarLinhasHistorico($historico); ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- js do bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- biblioteca para fazer o grafico de linhas -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      // busca o elemento canvas onde o grafico vai ser desenhado
      var graficoTemperatura = document.getElementById("graficoTemperatura");

      // cria o grafico com os dados que o PHP preparou lá em cima
      new Chart(graficoTemperatura, {
        type: "line", // grafico de linhas
        data: {
          // as datas e os valores foram montados em PHP e injetados aqui diretamente
          labels: [<?php echo $datas_grafico; ?>],
          datasets: [{
            label: "Temperatura (°C)",
            data: [<?php echo $valores_grafico; ?>],
            borderColor: "#0f766e",
            backgroundColor: "rgba(15, 118, 110, 0.15)",
            borderWidth: 2,
            fill: true,        // preenche a área abaixo da linha
            pointRadius: 0,    // esconde os pontos para não ficar confuso com muitos dados
            pointHitRadius: 10 // mas ainda deteta o hover numa area de 10px à volta
          }]
        },
        options: {
          maintainAspectRatio: false, // respeita a altura que definimos no css
          scales: {
            x: {
              ticks: {
                maxTicksLimit: 10, // mostra no máximo 10 datas no eixo x para não sobrepor
                maxRotation: 45,   // inclina o texto para caber melhor
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