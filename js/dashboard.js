"use strict";

// esta função é chamada quando o utilizador carrega num botão de ligar/desligar
// recebe os ids dos elementos HTML e os textos que devem aparecer em cada estado
function alternarAtuador(idCartao, idEstado, idBotao, estadoAtivo, estadoInativo, textoAtivo, textoInativo, nomeApi) {
  // vai buscar os elementos HTML pelos seus ids
  var cartao = document.getElementById(idCartao);
  var estado = document.getElementById(idEstado);
  var botao = document.getElementById(idBotao);

  // se algum elemento nao existir na pagina, para aqui para nao dar erro
  if (!cartao || !estado || !botao) {
    return;
  }

  let valorEstado = "0";

  // verifica o texto atual do badge para saber em que estado esta
  // se ja estiver ativo, desliga. se estiver inativo, liga.
  if (estado.innerText === estadoAtivo) {
    // estava ligado, entao desligamos
    estado.innerText = estadoInativo;
    botao.innerText = textoInativo;

    // muda as classes CSS para o visual de desligado
    estado.className = "state-badge state-off";
    botao.className = "btn btn-primary control-button";
    cartao.className = "card actuator-card h-100";
    valorEstado = "0";
  } else {
    // estava desligado, entao ligamos
    estado.innerText = estadoAtivo;
    botao.innerText = textoAtivo;

    // muda as classes CSS para o visual de ligado
    estado.className = "state-badge state-on";
    botao.className = "btn btn-outline-secondary control-button";
    cartao.className = "card actuator-card actuator-active h-100";
    valorEstado = "1";
  }

  // se tiver um nome de API, envia o novo estado para o servidor
  // assim o Raspberry Pi tambem fica a saber que o estado mudou
  if (nomeApi) {
    enviarComandoAPI(nomeApi, valorEstado);
  }
}

// esta função envia um comando para a API PHP via fetch (pedido HTTP em background)
// o fetch funciona como um requests.post do python mas no browser, sem recarregar a pagina
function enviarComandoAPI(nomeDispositivo, valor) {
  const dataFormatada = get_date();

  // URLSearchParams é a forma do javascript montar os dados para enviar via POST
  // funciona como um dicionario, adicionamos os campos um a um com .append
  const formData = new URLSearchParams();

  formData.append("nome", nomeDispositivo);
  formData.append("valor", valor);
  formData.append("hora", dataFormatada);

  // estes campos extras permitem preencher as colunas Tipo e Origem no historico
  formData.append("tipo", "Atuador");
  formData.append("origem", "Dashboard");

  // o fetch envia o pedido POST para o PHP e espera pela resposta
  // o .then encadeia o que fazer depois de receber a resposta
  fetch("api/api.php", {
    method: "POST",
    body: formData,
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    }
  })
  .then(response => {
    if(response.ok) {
      console.log("Comando enviado com sucesso para: " + nomeDispositivo);
    } else {
      console.error("Erro ao comunicar com a API.");
    }
  })
  .catch(error => console.error("Erro no Fetch:", error));
}

// esta função é chamada quando o utilizador carrega no botao de tirar foto
function capturarImagem() {
  var textoData = document.getElementById("ultimaCaptura");
  var imagemWebcam = document.getElementById("imagemWebcam");

  if (!textoData || !imagemWebcam) {
    return;
  }

  // atualiza o texto da ultima captura com a hora atual
  textoData.innerText = get_date();

  // envia o valor "1" para a API no campo "camera"
  // o script python esta sempre a verificar este valor e quando ve "1" dispara a camara
  enviarComandoAPI("camera", "1");

  // espera 6 segundos antes de atualizar a imagem no HTML
  setTimeout(() => {
    // o "?t=" com o tempo atual serve para enganar o cache do browser
    // sem isto o browser mostrava a imagem antiga que ja tinha guardado
    imagemWebcam.src = "api/images/webcam.jpg?t=" + new Date().getTime();
  }, 6000); 
}

// esta função monta a data e hora atual no formato "dd-mm-aaaa hh:mm:ss"
// o padStart(2, "0") garante que numeros de 1 digito ficam com zero à frente (ex: 9 -> "09")
function get_date() {
  const agora = new Date();
  
  const ano = agora.getFullYear();
  const mes = String(agora.getMonth() + 1).padStart(2, "0"); // +1 porque os meses comecam no 0
  const dia = String(agora.getDate()).padStart(2, "0");
  
  const hora = String(agora.getHours()).padStart(2, "0");
  const minuto = String(agora.getMinutes()).padStart(2, "0");
  const segundo = String(agora.getSeconds()).padStart(2, "0");

  const datahora = `${dia}-${mes}-${ano} ${hora}:${minuto}:${segundo}`;

  return datahora; 
}

// esta função vai buscar o estado de um dispositivo à API e atualiza o HTML
// isAtuador serve para saber se deve mostrar um botao (atuador) ou so texto (sensor)
function atualizarDispositivo(nomeApi, idCartao, idBadge, idElementoExtra, isAtuador) {
  // fetch com GET, so precisamos de passar o nome do dispositivo no URL
  fetch("api/api.php?nome=" + nomeApi)
    .then(response => {
      if (!response.ok) throw new Error("HTTP " + response.status);
      return response.text(); // a API devolve "0" ou "1" em texto simples
    })
    .then(data => {
      const cartao = document.getElementById(idCartao);
      const badge = document.getElementById(idBadge);
      const elementoExtra = document.getElementById(idElementoExtra);

      if (!cartao || !badge || !elementoExtra){
        return;
      }

      // o trim() remove espacos e quebras de linha que possam vir da API
      const estado = data.trim();

      // a temperatura é um caso especial porque nao é 0 ou 1, é um numero como "23.5"
      // por isso tratamos ela separadamente antes de chegar ao codigo de 0/1
      if (nomeApi === "sensor-temperatura") {
        elementoExtra.innerText = estado + " °C"; 
        badge.innerText = "Monitorizando";
        badge.className = "state-badge state-on";
        cartao.className = "card sensor-card sensor-active h-100";
        return; // sai da funcao aqui para nao executar o codigo de baixo
      }

      // para todos os outros dispositivos, o estado é "1" (ativo) ou "0" (inativo)
      if (estado === "1") {
        badge.innerText = "Ativo";
        badge.className = "state-badge state-on";
        
        // atuadores tem botao, sensores so tem texto
        if (isAtuador) {
          elementoExtra.innerText = "Desligar";
          elementoExtra.className = "btn btn-outline-secondary control-button";
          cartao.className = "card actuator-card actuator-active h-100";
        } else {
          elementoExtra.innerText = "Ativo";
          cartao.className = "card sensor-card sensor-active h-100";
        }
      } 
      else {
        badge.innerText = "Inativo";
        badge.className = "state-badge state-off";
        
        if (isAtuador) {
          elementoExtra.innerText = "Ligar";
          elementoExtra.className = "btn btn-primary control-button";
          cartao.className = "card actuator-card h-100";
        } else {
          elementoExtra.innerText = "Inativo";
          cartao.className = "card sensor-card sensor-closed h-100";
        }
      }
    })
    .catch(error => console.error("Erro a ler " + nomeApi + ":", error));
}

// vai buscar a hora da ultima foto ao ficheiro de texto e atualiza o HTML
function atualizarHoraWebcam() {
  const elementoHora = document.getElementById("horaWebcam");
  
  if (elementoHora) {
    // o ?t= com o timestamp evita que o browser use uma versao antiga do ficheiro em cache
    const tempoAtual = new Date().getTime();
    
    fetch("api/files/camera/hora.txt?t=" + tempoAtual)
      .then(resposta => {
        if (resposta.ok) {
          return resposta.text();
        }
        throw new Error("Ficheiro não encontrado");
      })
      .then(textoDaHora => {
        elementoHora.innerText = textoDaHora.trim();
      })
      .catch(erro => console.error("Erro a atualizar a hora da câmara:", erro));
  }
}

// vai buscar as linhas atualizadas da tabela de historico ao PHP e substitui o conteudo
function atualizarHistorico() {
  const tabela = document.getElementById("tabela-historico");

  if (!tabela) {
    return;
  }

  // le os parametros do URL atual para manter os filtros ativos
  // por exemplo se o url for "historico.php?nome=sensor-chama" mantemos esse filtro
  const parametros = new URLSearchParams(window.location.search);
  const filtroNome = parametros.get("nome");
  let url = "historico.php?tabela=sim";

  if (filtroNome) {
    url += "&nome=" + encodeURIComponent(filtroNome);
  }

  // o PHP ao receber ?tabela=sim devolve so as linhas da tabela, sem o HTML todo
  // assim conseguimos atualizar apenas o interior da tabela sem recarregar a pagina
  fetch(url)
    .then(resposta => resposta.text()) 
    .then(linhas => tabela.innerHTML = linhas);
}

// atualiza a imagem da webcam com um timestamp para forcar o browser a buscar a versao mais recente
function atualizarImagemWebcam() {
  const imagem = document.getElementById("imagemWebcam");
  
  if (imagem) {
    const tempoAtual = new Date().getTime();
    imagem.src = "api/images/webcam.jpg?t=" + tempoAtual;
  }
}

// esta função chama todas as outras de atualizacao de uma vez
// é ela que o setInterval chama a cada 2 segundos
function atualizarTudo() {
  // so atualiza os sensores e atuadores se estivermos na pagina do dashboard
  // verificamos isso checando se um dos cartoes existe no HTML
  if (document.getElementById("cartaoSensorMovimento")) {
    atualizarDispositivo("sensor-movimento", "cartaoSensorMovimento", "badgeSensorMovimento", "valorSensorMovimento", false);
    atualizarDispositivo("sensor-temperatura", "cartaoSensorTemperatura", "badgeSensorTemperatura", "valorSensorTemperatura", false);
    atualizarDispositivo("sensor-chama", "cartaoSensorChama", "badgeSensorChama", "valorSensorChama", false);

    atualizarDispositivo("led-camera", "cartaoLedCamera", "estadoLedCamera", "botaoLedCamera", true);
    atualizarDispositivo("led-fogo", "cartaoLedFogo", "estadoLedFogo", "botaoLedFogo", true);
    atualizarDispositivo("led-temperatura", "cartaoLedTemperatura", "estadoLedTemperatura", "botaoLedTemperatura", true);
    atualizarDispositivo("buzzer-fogo", "cartaoBuzzerFogo", "estadoBuzzerFogo", "botaoBuzzerFogo", true);
    atualizarDispositivo("buzzer-alarme", "cartaoBuzzerAlarme", "estadoBuzzerAlarme", "botaoBuzzerAlarme", true);
  }

  atualizarHistorico();
  atualizarImagemWebcam();
  atualizarHoraWebcam();
}

// o setInterval repete a funcao atualizarTudo a cada 2000 milissegundos (2 segundos)
// é assim que a pagina se atualiza automaticamente sem o utilizador ter de carregar F5
setInterval(atualizarTudo, 2000);