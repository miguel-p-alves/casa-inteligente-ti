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

  // Se passarmos o nome da API (ex: "campainha"), ele envia para o PHP
  if (nomeApi) {
    enviarComandoAPI(nomeApi, valorEstado);
  }
}

// Nova função para comunicar com a tua API
function enviarComandoAPI(nomeDispositivo, valor) {
  const dataAtual = new Date();
  const hora = dataAtual.getHours().toString().padStart(2, '0') + ":" + dataAtual.getMinutes().toString().padStart(2, '0');

  const formData = new URLSearchParams();

  // Estes dados identificam qual dispositivo mudou e qual foi o novo estado.
  formData.append("nome", nomeDispositivo);
  formData.append("valor", valor);
  formData.append("hora", hora);

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

  if (!textoData) {
    return;
  }

  textoData.innerText = formatarDataHora(new Date());

  // Futuramente, a imagem real pode ser enviada pela Raspberry Pi para o servidor PHP.
  // Agora apenas se atualiza a data/hora de exemplo.
}

function formatarDataHora(data) {
  return data.toLocaleString("pt-PT", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit"
  });
}
