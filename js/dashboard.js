"use strict";

function alternarAtuador(idCartao, idEstado, idBotao, estadoAtivo, estadoInativo, textoAtivo, textoInativo, nomeApi) {
  var cartao = document.getElementById(idCartao);
  var estado = document.getElementById(idEstado);
  var botao = document.getElementById(idBotao);

  if (!cartao || !estado || !botao) {
    return;
  }

  let valorEstado = "0";

  if (estado.innerText === estadoAtivo) {
    estado.innerText = estadoInativo;
    botao.innerText = textoInativo;

    estado.className = "state-badge state-off";
    botao.className = "btn btn-primary control-button";
    cartao.className = "card actuator-card h-100";
    valorEstado = "0"; // 0 para desligado
  } else {
    estado.innerText = estadoAtivo;
    botao.innerText = textoAtivo;

    estado.className = "state-badge state-on";
    botao.className = "btn btn-outline-secondary control-button";
    cartao.className = "card actuator-card actuator-active h-100";
    valorEstado = "1"; // 1 para ligado
  }

  // Se passarmos o nome da API (ex: "luz-camera"), ele envia para o PHP.
  if (nomeApi) {
    enviarComandoAPI(nomeApi, valorEstado);
  }
}

// Função para comunicar com a API
function enviarComandoAPI(nomeDispositivo, valor) {
  const dataFormatada = get_date();

  const formData = new URLSearchParams();

  // Estes dados identificam qual dispositivo mudou e qual foi o novo estado.
  formData.append("nome", nomeDispositivo);
  formData.append("valor", valor);
  formData.append("hora", dataFormatada);

  // Estes campos extras permitem preencher as colunas Tipo e Origem no histórico.
  formData.append("tipo", "Atuador");
  formData.append("origem", "Dashboard");

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

function capturarImagem() {
  var textoData = document.getElementById("ultimaCaptura");
  var imagemWebcam = document.getElementById("imagemWebcam");

  if (!textoData || !imagemWebcam) {
    return;
  }

  // 1. Atualiza a data no ecrã
  textoData.innerText = get_date();

  // 2. Envia o comando "1" para a API, o que vai acordar o teu script Python!
  enviarComandoAPI("camera", "1");

  // 3. Espera 6 segundos (tempo para o Python focar a câmara, tirar a foto e fazer upload) e atualiza a imagem no HTML
  setTimeout(() => {
    // O "?t=" adiciona um valor único ao link para forçar o navegador a esquecer a imagem antiga e carregar a nova
    imagemWebcam.src = "api/images/webcam.jpg?t=" + new Date().getTime();
  }, 6000); 
}

function get_date() {
  const agora = new Date();
  
  const ano = agora.getFullYear();
  const mes = String(agora.getMonth() + 1).padStart(2, "0");
  const dia = String(agora.getDate()).padStart(2, "0");
  
  const hora = String(agora.getHours()).padStart(2, "0");
  const minuto = String(agora.getMinutes()).padStart(2, "0");
  const segundo = String(agora.getSeconds()).padStart(2, "0");

  const datahora = `${dia}-${mes}-${ano} ${hora}:${minuto}:${segundo}`;

  // Se quiseres atualizar um texto no HTML (como na foto), podes manter a linha abaixo.
  // Caso contrário, podes apagá-la ou deixá-la comentada.
  // document.getElementById("time").innerHTML = datahora;

  // Isto é o que permite que a enviarComandoAPI use esta data!
  return datahora; 
}

function atualizarDispositivo(nomeApi, idCartao, idBadge, idElementoExtra, isAtuador) {
  fetch("api/api.php?nome=" + nomeApi)
    .then(response => {
      if (!response.ok) throw new Error("HTTP " + response.status);
      return response.text();
    })
    .then(data => {
      // Vai buscar os elementos pelo ID que lhe passamos
      const cartao = document.getElementById(idCartao);
      const badge = document.getElementById(idBadge);
      const elementoExtra = document.getElementById(idElementoExtra); // Pode ser o Sensor ou o Atuador

      if (!cartao || !badge || !elementoExtra){
        return;
      }

      const estado = data.trim();

      // --- TRATAMENTO ESPECIAL PARA A TEMPERATURA ---
      if (nomeApi === "sensor-temperatura") {
        // Atualiza o valor grande no centro do cartão com o número do ficheiro + " °C"
        elementoExtra.innerText = estado + " °C"; 
        
        // Atualiza a badge pequena em baixo
        badge.innerText = "Monitorizando";
        badge.className = "state-badge state-on";
        
        // Mantém o cartão com um visual neutro/ativo
        cartao.className = "card sensor-card sensor-active h-100";
        return; // Sai da função para não executar o código binário abaixo
      }

      // Se o dispositivo estiver LIGADO / ATIVO ("1")
      if (estado === "1") {
        badge.innerText = "Ativo";
        badge.className = "state-badge state-on";
        
        if (isAtuador) {
          elementoExtra.innerText = "Desligar";
          elementoExtra.className = "btn btn-outline-secondary control-button";
          cartao.className = "card actuator-card actuator-active h-100";
        } else {
          elementoExtra.innerText = "Ativo";
          cartao.className = "card sensor-card sensor-active h-100";
        }
      } 
      // Se o dispositivo estiver DESLIGADO / INATIVO ("0")
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

function atualizarHoraWebcam() {
  const elementoHora = document.getElementById("horaWebcam");
  
  if (elementoHora) {
    // Carimbo de tempo para evitar que o browser guarde o texto em cache
    const tempoAtual = new Date().getTime();
    
    // O caminho do ficheiro tem de ser igual ao que usaste no PHP
    fetch("api/files/camera/hora.txt?t=" + tempoAtual)
      .then(resposta => {
        if (resposta.ok) {
          return resposta.text();
        }
        throw new Error("Ficheiro não encontrado");
      })
      .then(textoDaHora => {
        // Atualiza o texto que está no HTML
        elementoHora.innerText = textoDaHora.trim();
      })
      .catch(erro => console.error("Erro a atualizar a hora da câmara:", erro));
  }
}

function atualizarHistorico() {
  const tabela = document.getElementById("tabela-historico");

  if (!tabela) {
    return;
  }

  const parametros = new URLSearchParams(window.location.search);
  const filtroNome = parametros.get("nome");
  let url = "historico.php?tabela=sim";

  if (filtroNome) {
    url += "&nome=" + encodeURIComponent(filtroNome);
  }

  // Pede ao PHP apenas as linhas da tabela.
  fetch(url)
    .then(resposta => resposta.text()) 
    .then(linhas => tabela.innerHTML = linhas);
}

function atualizarImagemWebcam() {
  const imagem = document.getElementById("imagemWebcam");
  
  if (imagem) {
    // Pega no tempo atual em milissegundos
    const tempoAtual = new Date().getTime();
    
    // Atualiza a imagem adicionando o timestamp para enganar o cache do browser.
    // Substitui o "images/webcam.jpg" pelo caminho correto se o teu for diferente!
    imagem.src = "api/images/webcam.jpg?t=" + tempoAtual;
  }
}

function atualizarTudo() {
  if (document.getElementById("cartaoSensorMovimento")) {
  // Sensores Arduino e Raspberry Pi.
  atualizarDispositivo("sensor-movimento", "cartaoSensorMovimento", "badgeSensorMovimento", "valorSensorMovimento", false);
  atualizarDispositivo("sensor-temperatura", "cartaoSensorTemperatura", "badgeSensorTemperatura", "valorSensorTemperatura", false);
  atualizarDispositivo("sensor-chama", "cartaoSensorChama", "badgeSensorChama", "valorSensorChama", false);

  // Atuadores finais do protótipo.
  atualizarDispositivo("led-camera", "cartaoLedCamera", "estadoLedCamera", "botaoLedCamera", true);
  atualizarDispositivo("led-fogo", "cartaoLedFogo", "estadoLedFogo", "botaoLedFogo", true);
  atualizarDispositivo("led-temperatura", "cartaoLedTemperatura", "estadoLedTemperatura", "botaoLedTemperatura", true);
  atualizarDispositivo("buzzer-fogo", "cartaoBuzzerFogo", "estadoBuzzerFogo", "botaoBuzzerFogo", true);
  atualizarDispositivo("buzzer-alarme", "cartaoBuzzerAlarme", "estadoBuzzerAlarme", "botaoBuzzerAlarme", true);
  }

  //Histórico:
  atualizarHistorico();

  //Imagem da Webcam:
  atualizarImagemWebcam();
  atualizarHoraWebcam();
}

// 3. O Relógio (setInterval) que corre a cada 2 segundos
setInterval(atualizarTudo, 2000);
