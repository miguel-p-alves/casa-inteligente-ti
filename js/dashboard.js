"use strict";

document.addEventListener("DOMContentLoaded", () => {
  const sensorGrid = document.getElementById("sensorGrid");
  const actuatorGrid = document.getElementById("actuatorGrid");
  const eventsList = document.getElementById("eventsList");
  const historyTableBody = document.getElementById("historyTableBody");
  const temperatureChart = document.getElementById("temperatureChart");
  const ultimaCaptura = document.getElementById("ultimaCaptura");
  const captureButton = document.getElementById("captureButton");
  const ultimaAtualizacaoSensores = document.getElementById("ultimaAtualizacaoSensores");

  // Integração futura: substituir estes dados por leituras reais vindas de uma API PHP.
  // Nesta fase não existe pedido HTTP, persistência, base de dados ou backend.
  const sensores = [
    {
      id: "temperatura",
      nome: "Temperatura",
      icone: "bi-thermometer-half",
      tipo: "numero",
      valor: 21.6,
      unidade: "°C",
      decimais: 1,
      origem: "MCU",
      detalhe: "Conforto térmico"
    },
    {
      id: "humidade",
      nome: "Humidade",
      icone: "bi-droplet-half",
      tipo: "numero",
      valor: 48,
      unidade: "%",
      decimais: 0,
      origem: "MCU",
      detalhe: "Ambiente interior"
    },
    {
      id: "luminosidade",
      nome: "Luminosidade",
      icone: "bi-brightness-high",
      tipo: "numero",
      valor: 64,
      unidade: "%",
      decimais: 0,
      origem: "SBC",
      detalhe: "Luz ambiente"
    },
    {
      id: "movimento",
      nome: "Movimento",
      icone: "bi-person-walking",
      tipo: "estado",
      valor: "Sem movimento",
      origem: "SBC",
      detalhe: "Sala principal"
    },
    {
      id: "gasFumo",
      nome: "Gás/Fumo",
      icone: "bi-fire",
      tipo: "estado",
      valor: "Normal",
      origem: "MCU",
      detalhe: "Cozinha"
    },
    {
      id: "estadoPorta",
      nome: "Estado da Porta",
      icone: "bi-door-closed",
      tipo: "estado",
      valor: "Fechado",
      origem: "SBC",
      detalhe: "Entrada"
    }
  ];

  const atuadores = [
    {
      id: "luzPrincipal",
      nome: "Luz principal",
      icone: "bi-lightbulb",
      ativo: true,
      etiquetas: { ativo: "Ligado", inativo: "Desligado" },
      botoes: { ativar: "Ligar", desativar: "Desligar" },
      eventos: { ativo: "Luz principal ligada", inativo: "Luz principal desligada" }
    },
    {
      id: "alarme",
      nome: "Alarme",
      icone: "bi-shield-lock",
      ativo: false,
      etiquetas: { ativo: "Ativo", inativo: "Inativo" },
      botoes: { ativar: "Ativar", desativar: "Desativar" },
      eventos: { ativo: "Alarme ativado", inativo: "Alarme desativado" }
    },
    {
      id: "ventoinha",
      nome: "Ventoinha",
      icone: "bi-fan",
      ativo: false,
      etiquetas: { ativo: "Ligado", inativo: "Desligado" },
      botoes: { ativar: "Ligar", desativar: "Desligar" },
      eventos: { ativo: "Ventoinha ligada", inativo: "Ventoinha desligada" }
    },
    {
      id: "portaGaragem",
      nome: "Porta/Garagem",
      icone: "bi-door-open",
      ativo: false,
      etiquetas: { ativo: "Aberto", inativo: "Fechado" },
      botoes: { ativar: "Abrir", desativar: "Fechar" },
      eventos: { ativo: "Porta/Garagem aberta", inativo: "Porta/Garagem fechada" }
    },
    {
      id: "estores",
      nome: "Estores",
      icone: "bi-window",
      ativo: true,
      etiquetas: { ativo: "Aberto", inativo: "Fechado" },
      botoes: { ativar: "Abrir", desativar: "Fechar" },
      eventos: { ativo: "Estores abertos", inativo: "Estores fechados" }
    },
    {
      id: "tomadaInteligente",
      nome: "Tomada inteligente",
      icone: "bi-plug",
      ativo: false,
      etiquetas: { ativo: "Ligado", inativo: "Desligado" },
      botoes: { ativar: "Ligar", desativar: "Desligar" },
      eventos: { ativo: "Tomada inteligente ligada", inativo: "Tomada inteligente desligada" }
    }
  ];

  const eventos = [
    { data: minutosAtras(2), descricao: "Movimento detetado na sala", origem: "SBC" },
    { data: minutosAtras(5), descricao: "Luz principal ligada", origem: "Dashboard" },
    { data: minutosAtras(9), descricao: "Porta aberta", origem: "SBC" },
    { data: minutosAtras(12), descricao: "Temperatura atualizada", origem: "MCU" },
    { data: minutosAtras(18), descricao: "Alarme ativado", origem: "Dashboard" }
  ];

  const historico = [
    { data: minutosAtras(3), tipo: "Sensor", nome: "Temperatura", valor: "21,6 °C", origem: "MCU" },
    { data: minutosAtras(6), tipo: "Atuador", nome: "Luz principal", valor: "Ligado", origem: "Dashboard" },
    { data: minutosAtras(8), tipo: "Sensor", nome: "Movimento", valor: "Detetado", origem: "SBC" },
    { data: minutosAtras(10), tipo: "Sensor", nome: "Gás/Fumo", valor: "Normal", origem: "MCU" },
    { data: minutosAtras(14), tipo: "Atuador", nome: "Alarme", valor: "Ativo", origem: "Dashboard" },
    { data: minutosAtras(16), tipo: "Sensor", nome: "Estado da Porta", valor: "Aberto", origem: "SBC" }
  ];

  const amostrasTemperatura = [20.9, 21.1, 21.4, 21.2, 21.7, 21.6, 21.8, 21.6];
  let dataUltimaCaptura = minutosAtras(7);

  renderizarTudo();
  captureButton.addEventListener("click", capturarImagem);
  setInterval(atualizarSensores, 4500);

  function renderizarTudo() {
    renderizarSensores();
    renderizarAtuadores();
    renderizarEventos();
    renderizarHistorico();
    renderizarGraficoTemperatura();
    renderizarCaptura();
    atualizarSeloLeituras();
  }

  function renderizarSensores() {
    sensorGrid.innerHTML = sensores.map((sensor) => {
      const valor = formatarValorSensor(sensor);
      const classe = obterClasseSensor(sensor);
      const classeEstado = obterClasseEstadoSensor(sensor);
      const estado = sensor.tipo === "estado" ? sensor.valor : "Atualizado";

      return `
        <article class="col-12 col-sm-6 col-xl-4">
          <div class="card sensor-card ${classe} h-100">
            <div class="card-body">
              <div class="d-flex align-items-start justify-content-between gap-3">
                <div>
                  <h3 class="sensor-title">${sensor.nome}</h3>
                  <p class="sensor-meta mb-0">${sensor.detalhe}</p>
                </div>
                <span class="sensor-icon"><i class="bi ${sensor.icone}"></i></span>
              </div>
              <div class="sensor-value">${valor}</div>
              <div class="d-flex align-items-center justify-content-between gap-2 mt-3">
                <span class="sensor-meta">Origem: ${sensor.origem}</span>
                <span class="state-badge ${classeEstado}">${estado}</span>
              </div>
            </div>
          </div>
        </article>
      `;
    }).join("");
  }

  function renderizarAtuadores() {
    actuatorGrid.innerHTML = atuadores.map((atuador) => {
      const estado = obterEstadoAtuador(atuador);
      const classeAtiva = atuador.ativo ? "actuator-active" : "";
      const classeAviso = atuador.id === "alarme" && atuador.ativo ? "actuator-warning" : "";
      const classeEstado = atuador.id === "alarme" && atuador.ativo ? "state-alert" : (atuador.ativo ? "state-on" : "state-off");
      const classeBotao = obterClasseBotaoAtuador(atuador);
      const textoBotao = atuador.ativo ? atuador.botoes.desativar : atuador.botoes.ativar;

      return `
        <article class="col-12 col-sm-6 col-xl-4">
          <div class="card actuator-card ${classeAtiva} ${classeAviso} h-100">
            <div class="card-body d-flex flex-column">
              <div class="d-flex align-items-start justify-content-between gap-3">
                <div>
                  <h3 class="actuator-title">${atuador.nome}</h3>
                  <p class="sensor-meta mb-0">Controlo local</p>
                </div>
                <span class="actuator-icon"><i class="bi ${atuador.icone}"></i></span>
              </div>
              <div class="d-flex align-items-center justify-content-between gap-2 mt-auto pt-4">
                <span class="state-badge ${classeEstado}">${estado}</span>
                <button class="btn ${classeBotao} control-button" type="button" data-actuator-id="${atuador.id}">
                  ${textoBotao}
                </button>
              </div>
            </div>
          </div>
        </article>
      `;
    }).join("");

    actuatorGrid.querySelectorAll("[data-actuator-id]").forEach((button) => {
      button.addEventListener("click", () => alternarAtuador(button.dataset.actuatorId));
    });
  }

  function renderizarEventos() {
    eventsList.innerHTML = eventos.map((evento) => `
      <li class="list-group-item">
        <span class="event-dot"></span>
        <span class="event-text">
          <strong>${evento.descricao}</strong>
          <small>${formatarDataHora(evento.data)} · ${evento.origem}</small>
        </span>
      </li>
    `).join("");
  }

  function renderizarHistorico() {
    historyTableBody.innerHTML = historico.map((linha) => `
      <tr>
        <td>${formatarDataHora(linha.data)}</td>
        <td>${linha.tipo}</td>
        <td>${linha.nome}</td>
        <td>${linha.valor}</td>
        <td><span class="origin-label">${linha.origem}</span></td>
      </tr>
    `).join("");
  }

  function renderizarGraficoTemperatura() {
    const valores = amostrasTemperatura.slice(-8);

    temperatureChart.innerHTML = valores.map((valor) => {
      const altura = Math.max(12, Math.min(100, Math.round(((valor - 18) / 10) * 100)));

      return `
        <div class="chart-item">
          <div class="chart-track">
            <div class="chart-bar" style="height: ${altura}%"></div>
          </div>
          <span class="chart-label">${formatarNumero(valor, 1)} °C</span>
        </div>
      `;
    }).join("");
  }

  function renderizarCaptura() {
    ultimaCaptura.textContent = formatarDataHora(dataUltimaCaptura);
  }

  function capturarImagem() {
    dataUltimaCaptura = new Date();
    renderizarCaptura();
    adicionarEvento("Imagem capturada pela webcam", "Dashboard");
    adicionarHistorico("Webcam", "Última captura", formatarDataHora(dataUltimaCaptura), "Dashboard");
  }

  function atualizarSensores() {
    const temperatura = obterSensor("temperatura");
    const humidade = obterSensor("humidade");
    const luminosidade = obterSensor("luminosidade");
    const movimento = obterSensor("movimento");
    const gasFumo = obterSensor("gasFumo");
    const estadoPorta = obterSensor("estadoPorta");

    atualizarNumero(temperatura, -0.6, 0.6, 18, 29);
    atualizarNumero(humidade, -3, 3, 34, 72);
    atualizarNumero(luminosidade, -8, 8, 18, 96);

    const metricaRegistada = escolherItem([temperatura, humidade, luminosidade]);
    adicionarHistorico("Sensor", metricaRegistada.nome, formatarValorSensor(metricaRegistada), metricaRegistada.origem);

    if (Math.random() > 0.55) {
      adicionarEvento(`${metricaRegistada.nome} atualizada`, metricaRegistada.origem);
    }

    const movimentoAnterior = movimento.valor;
    movimento.valor = Math.random() > 0.72 ? "Detetado" : "Sem movimento";
    if (movimento.valor !== movimentoAnterior) {
      const descricao = movimento.valor === "Detetado" ? "Movimento detetado na sala" : "Movimento terminou na sala";
      adicionarEvento(descricao, movimento.origem);
      adicionarHistorico("Sensor", movimento.nome, movimento.valor, movimento.origem);
    }

    const gasAnterior = gasFumo.valor;
    gasFumo.valor = Math.random() > 0.94 ? "Alerta" : "Normal";
    if (gasFumo.valor !== gasAnterior) {
      const descricao = gasFumo.valor === "Alerta" ? "Alerta de gás/fumo" : "Gás/Fumo normalizado";
      adicionarEvento(descricao, gasFumo.origem);
      adicionarHistorico("Sensor", gasFumo.nome, gasFumo.valor, gasFumo.origem);
    }

    if (Math.random() > 0.86) {
      estadoPorta.valor = estadoPorta.valor === "Fechado" ? "Aberto" : "Fechado";
      const descricao = estadoPorta.valor === "Aberto" ? "Porta aberta" : "Porta fechada";
      adicionarEvento(descricao, estadoPorta.origem);
      adicionarHistorico("Sensor", estadoPorta.nome, estadoPorta.valor, estadoPorta.origem);
    }

    amostrasTemperatura.push(Number(temperatura.valor.toFixed(1)));
    if (amostrasTemperatura.length > 12) {
      amostrasTemperatura.shift();
    }

    renderizarSensores();
    renderizarHistorico();
    renderizarGraficoTemperatura();
    atualizarSeloLeituras();
  }

  function alternarAtuador(id) {
    const atuador = atuadores.find((item) => item.id === id);

    if (!atuador) {
      return;
    }

    atuador.ativo = !atuador.ativo;
    renderizarAtuadores();

    const descricao = atuador.ativo ? atuador.eventos.ativo : atuador.eventos.inativo;
    adicionarEvento(descricao, "Dashboard");
    adicionarHistorico("Atuador", atuador.nome, obterEstadoAtuador(atuador), "Dashboard");
  }

  function adicionarEvento(descricao, origem) {
    eventos.unshift({
      data: new Date(),
      descricao,
      origem
    });

    if (eventos.length > 7) {
      eventos.pop();
    }

    renderizarEventos();
  }

  function adicionarHistorico(tipo, nome, valor, origem) {
    historico.unshift({
      data: new Date(),
      tipo,
      nome,
      valor,
      origem
    });

    if (historico.length > 10) {
      historico.pop();
    }

    renderizarHistorico();
  }

  function atualizarNumero(sensor, minimoVariacao, maximoVariacao, minimo, maximo) {
    const proximoValor = sensor.valor + numeroAleatorio(minimoVariacao, maximoVariacao);
    sensor.valor = Math.max(minimo, Math.min(maximo, proximoValor));
  }

  function obterSensor(id) {
    return sensores.find((sensor) => sensor.id === id);
  }

  function obterEstadoAtuador(atuador) {
    return atuador.ativo ? atuador.etiquetas.ativo : atuador.etiquetas.inativo;
  }

  function obterClasseBotaoAtuador(atuador) {
    if (atuador.id === "alarme") {
      return atuador.ativo ? "btn-outline-warning" : "btn-warning";
    }

    if (atuador.ativo) {
      return "btn-outline-secondary";
    }

    return "btn-primary";
  }

  function obterClasseSensor(sensor) {
    if (sensor.id === "gasFumo" && sensor.valor === "Alerta") {
      return "sensor-warning";
    }

    if (sensor.id === "movimento" && sensor.valor === "Detetado") {
      return "sensor-active";
    }

    if (sensor.id === "estadoPorta" && sensor.valor === "Fechado") {
      return "sensor-closed";
    }

    if (sensor.id === "estadoPorta" && sensor.valor === "Aberto") {
      return "sensor-warning";
    }

    return "";
  }

  function obterClasseEstadoSensor(sensor) {
    if (sensor.id === "gasFumo" && sensor.valor === "Alerta") {
      return "state-alert";
    }

    if (sensor.id === "estadoPorta" && sensor.valor === "Aberto") {
      return "state-alert";
    }

    if (sensor.tipo === "estado" && sensor.valor !== "Sem movimento") {
      return "state-on";
    }

    return "state-off";
  }

  function formatarValorSensor(sensor) {
    if (sensor.tipo === "numero") {
      return `${formatarNumero(sensor.valor, sensor.decimais)} ${sensor.unidade}`;
    }

    return sensor.valor;
  }

  function atualizarSeloLeituras() {
    ultimaAtualizacaoSensores.textContent = `Última atualização: ${formatarDataHora(new Date())}`;
  }

  function formatarNumero(valor, decimais) {
    return valor.toLocaleString("pt-PT", {
      minimumFractionDigits: decimais,
      maximumFractionDigits: decimais
    });
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

  function numeroAleatorio(minimo, maximo) {
    return Math.random() * (maximo - minimo) + minimo;
  }

  function escolherItem(lista) {
    return lista[Math.floor(Math.random() * lista.length)];
  }

  function minutosAtras(minutos) {
    return new Date(Date.now() - minutos * 60 * 1000);
  }
});
