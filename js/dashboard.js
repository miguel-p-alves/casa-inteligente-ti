"use strict";

function alternarAtuador(idCartao, idEstado, idBotao, estadoAtivo, estadoInativo, textoAtivo, textoInativo) {
  var cartao = document.getElementById(idCartao);
  var estado = document.getElementById(idEstado);
  var botao = document.getElementById(idBotao);

  if (!cartao || !estado || !botao) {
    return;
  }

  if (estado.innerText === estadoAtivo) {
    estado.innerText = estadoInativo;
    botao.innerText = textoInativo;

    estado.className = "state-badge state-off";
    botao.className = "btn btn-primary control-button";
    cartao.className = "card actuator-card h-100";
  } else {
    estado.innerText = estadoAtivo;
    botao.innerText = textoAtivo;

    estado.className = "state-badge state-on";
    botao.className = "btn btn-outline-secondary control-button";
    cartao.className = "card actuator-card actuator-active h-100";
  }

  // Futuramente, este ponto pode chamar uma API PHP para alterar o atuador real.
  // Agora altera apenas o aspeto do cartão no navegador.
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
