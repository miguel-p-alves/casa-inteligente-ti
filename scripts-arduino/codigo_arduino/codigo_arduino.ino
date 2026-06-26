#include <SPI.h>
#include <WiFi101.h>
#include <ArduinoHttpClient.h>
#include "DHT.h"

// Pinos utilizados no projeto
#define PIN_DHT 2
#define PIN_SWITCH 3
#define PIN_LED_CAMARA 4
#define PIN_LED_FOGO 5
#define PIN_LED_TEMPERATURA 6

#define TIPO_DHT DHT11
DHT sensortemperatura(PIN_DHT, TIPO_DHT);

// Configurações da rede e do servidor
char SSID[] = "Servidor";
char PASS_WIFI[] = "12345678";

char HOST[] = "10.28.114.77";
int PORTO = 80;
String caminhoAPI = "/projeto-ti/api/api.php";

WiFiClient clienteWifi;
HttpClient clienteHTTP = HttpClient(clienteWifi, HOST, PORTO);

// Variáveis para controlo de estado
int estadoAnterior = -1; // -1 para forçar o envio na primeira leitura
unsigned long ultimoTempoLeitura = 0;
const unsigned long intervaloLeitura = 2000; // leituras a cada 2 segundos

float temperaturaAtual = 0.0;
const float LIMITE_TEMPERATURA = 29.0;

// Guardamos o estado anterior para só enviar à API quando há mudança
bool limiteTemperaturaAtingidoAnterior = false;
bool estadoFogoAnterior = false;
bool sensorChamaAnterior = false;

void setup() {
  Serial.begin(115200);

  pinMode(PIN_SWITCH, INPUT);

  // LEDs começam desligados
  pinMode(PIN_LED_CAMARA, OUTPUT);
  digitalWrite(PIN_LED_CAMARA, LOW);

  pinMode(PIN_LED_FOGO, OUTPUT);
  digitalWrite(PIN_LED_FOGO, LOW);

  pinMode(PIN_LED_TEMPERATURA, OUTPUT);
  digitalWrite(PIN_LED_TEMPERATURA, LOW);

  sensortemperatura.begin();

  conectarWiFi();
}

void loop() {
  // Verifica o sensor de movimento em tempo real (sem esperar os 2 segundos)
  int movimento = digitalRead(PIN_SWITCH);

  // Só age se o estado mudou, para não fazer spam de pedidos à API
  if (movimento != estadoAnterior) {
    estadoAnterior = movimento;

    if (movimento == HIGH) {
      movimentoDetectado();
    } else {
      movimentoTerminado();
    }
  }

  // As restantes verificações correm a cada 2 segundos
  if (millis() - ultimoTempoLeitura >= intervaloLeitura) {
    ultimoTempoLeitura = millis();

    lerEnviarTemperatura();
    verificarLedCameraDashboard();
    verificarStatusFogo();
    verificarLedTemperaturaDashboard();
  }
}

// MOVIMENTO
void movimentoDetectado() {
  Serial.println("Movimento detetado!");
  digitalWrite(PIN_LED_CAMARA, HIGH);

  // Informa a API do estado do sensor, do LED e dispara a câmara
  enviarParaAPI("sensor-movimento", "1", "Sensor", "MCU");
  enviarParaAPI("led-camera", "1", "Atuador", "MCU");
  // Envio o comando para a câmara (o script Python repõe a 0)
  enviarParaAPI("camera", "1", "Comando", "MCU"); // o script Python lê este valor e tira a foto
}

void movimentoTerminado() {
  Serial.println("Movimento terminado.");
  digitalWrite(PIN_LED_CAMARA, LOW);

  enviarParaAPI("sensor-movimento", "0", "Sensor", "MCU");
  enviarParaAPI("led-camera", "0", "Atuador", "MCU");
}

// TEMPERATURA
void lerEnviarTemperatura() {
  float temperatura = sensortemperatura.readTemperature();

  // isnan verifica se a leitura falhou (o DHT11 às vezes retorna NaN)
  if (isnan(temperatura)) {
    Serial.println("Erro ao ler o DHT11.");
    return;
  }

  temperaturaAtual = temperatura; // guarda para usar nas verificações de alarme
  Serial.print("Temperatura: ");
  Serial.print(temperatura);
  Serial.println(" ºC");

  enviarParaAPI("sensor-temperatura", String(temperatura), "Sensor", "Arduino");
}

// VERIFICAÇÕES DO DASHBOARD

// Verifica se o dashboard ligou ou desligou o LED da câmara
void verificarLedCameraDashboard() {
  if (WiFi.status() != WL_CONNECTED) conectarWiFi();

  clienteHTTP.get(caminhoAPI + "?nome=led-camera");

  int codigo = clienteHTTP.responseStatusCode();
  String resposta = clienteHTTP.responseBody();
  resposta.trim(); // remove espaços e \n que possam vir da resposta

  if (codigo == 200) {
    if (resposta == "1") {
        digitalWrite(PIN_LED_CAMARA, HIGH);
    } else {
        digitalWrite(PIN_LED_CAMARA, LOW);
    }
  }
}

void verificarStatusFogo() {
  if (WiFi.status() != WL_CONNECTED) conectarWiFi();

  // Lê o estado do sensor de chama guardado na API pelo Raspberry Pi
  clienteHTTP.get(caminhoAPI + "?nome=sensor-chama");
  clienteHTTP.responseStatusCode();
  String respSensor = clienteHTTP.responseBody();
  respSensor.trim();

  bool estadoFogoAtual;

 if (respSensor == "1") {
    // Chama detetada: LED liga sempre por segurança, independentemente do dashboard
    estadoFogoAtual = true;
} else if (sensorChamaAnterior == true && respSensor != "1") {
    // O sensor acabou de desligar, forçamos o LED a desligar também
    estadoFogoAtual = false;
  } else {
    // Sem chama: o dashboard é que controla o LED
    clienteHTTP.get(caminhoAPI + "?nome=led-fogo");
    clienteHTTP.responseStatusCode();
    String respLedFogo = clienteHTTP.responseBody();
    respLedFogo.trim();
    if (respLedFogo == "1") {
        estadoFogoAtual = true;
    } else {
        estadoFogoAtual = false;
    }
  }

   // Atualiza o estado anterior do sensor de chama
  if (respSensor == "1") {
        sensorChamaAnterior = true;
  } else {
        sensorChamaAnterior = false;
  }
  

  // Só envia à API quando o estado muda, para não fazer spam de POST
  if (estadoFogoAtual && !estadoFogoAnterior) {
    enviarParaAPI("led-fogo", "1", "Atuador", "Arduino");
  } else if (!estadoFogoAtual && estadoFogoAnterior) {
    enviarParaAPI("led-fogo", "0", "Atuador", "Arduino");
  }

  estadoFogoAnterior = estadoFogoAtual;
  if (estadoFogoAtual==true) {
    digitalWrite(PIN_LED_FOGO, HIGH);
  } else {
    digitalWrite(PIN_LED_FOGO, LOW);
  }
}

void verificarLedTemperaturaDashboard() {
  if (WiFi.status() != WL_CONNECTED) conectarWiFi();

  bool limiteAtingidoAtual;

  if (temperaturaAtual > LIMITE_TEMPERATURA) {
    limiteAtingidoAtual = true;
  } else {
    limiteAtingidoAtual = false;
  }

  // Só envia à API na transição de estado (ligou ou desligou)
  if (limiteAtingidoAtual == true && limiteTemperaturaAtingidoAnterior == false) {
    enviarParaAPI("led-temperatura", "1", "Atuador", "Arduino");
  } else if (limiteAtingidoAtual == false && limiteTemperaturaAtingidoAnterior == true) {
    enviarParaAPI("led-temperatura", "0", "Atuador", "Arduino");
  }

  limiteTemperaturaAtingidoAnterior = limiteAtingidoAtual;

  if (limiteAtingidoAtual == true) {
    // Temperatura acima do limite: LED fica sempre ligado, o dashboard não tem controlo
    digitalWrite(PIN_LED_TEMPERATURA, HIGH);
    return;
  }

  // Temperatura normal: segue o que o dashboard mandar
  clienteHTTP.get(caminhoAPI + "?nome=led-temperatura");
  int codigo = clienteHTTP.responseStatusCode();
  String resposta = clienteHTTP.responseBody();
  resposta.trim();

  if (codigo == 200) {
    if (resposta == "1") {
      digitalWrite(PIN_LED_TEMPERATURA, HIGH);
    } else {
      digitalWrite(PIN_LED_TEMPERATURA, LOW);
    }
  }
}

// REDE E API
void conectarWiFi() {
  Serial.print("A ligar ao Wi-Fi...");

  // Tenta ligar em loop até conseguir
  while (WiFi.status() != WL_CONNECTED) {
    WiFi.begin(SSID, PASS_WIFI);
    Serial.print(".");
    delay(3000);
  }

  Serial.println(" Ligado!");
}

void enviarParaAPI(String nome, String valor, String tipo, String origem) {
  if (WiFi.status() != WL_CONNECTED) conectarWiFi();

  // Monta os dados no formato application/x-www-form-urlencoded (equivalente a um formulário HTML)
  String dados = "nome=" + nome + "&valor=" + valor + "&hora=&tipo=" + tipo + "&origem=" + origem;

  clienteHTTP.post(caminhoAPI, "application/x-www-form-urlencoded", dados);

  int codigo = clienteHTTP.responseStatusCode();
  clienteHTTP.responseBody(); // lê e descarta o corpo da resposta para limpar o buffer

  if (codigo != 200) {
    Serial.print("Erro no POST: ");
    Serial.println(codigo);
  }
}
