#include <SPI.h>
#include <WiFi101.h>
#include <ArduinoHttpClient.h>
#include "DHT.h"

// ==========================
// PINOS
// ==========================
#define PIN_DHT 2           // Pino do Sensor DHT11
#define PIN_SWITCH 3        // Pino do Switch (Movimento)
#define PIN_LED_CAMARA 4       // Pino do LED da Câmara
#define PIN_LED_FOGO 5       // Pino do LED de Aviso de Fogo
#define PIN_LED_TEMPERATURA 6       // Pino do LED de Aviso de Temperatura

#define TIPO_DHT DHT11
DHT sensortemperatura(PIN_DHT, TIPO_DHT);

// ==========================
// REDE & SERVIDOR
// ==========================
char SSID[] = "Servidor";                   
char PASS_WIFI[] = "12345678"; 
int status = WL_IDLE_STATUS;            

char HOST[] = "10.28.114.77"; 
int PORTO = 80;                           
String caminhoAPI = "/projeto-ti/api/api.php"; 

WiFiClient clienteWifi;
HttpClient clienteHTTP = HttpClient(clienteWifi, HOST, PORTO);

// ==========================
// VARIÁVEIS DE CONTROLO
// ==========================
int estadoAnterior = -1;
unsigned long ultimoTempoLeitura = 0;
const unsigned long intervaloLeitura = 2000; // Executa as tarefas a cada 2 segundos
float temperaturaAtual = 0.0;
const float LIMITE_TEMPERATURA = 29.0;
bool limiteTemperaturaAtingidoAnterior = false;
bool estadoFogoAnterior = false;
bool sensorChamaAnterior = false;

void setup() {
  Serial.begin(115200);         
  while (!Serial);              

  // Iniciar Pinos
  pinMode(PIN_SWITCH, INPUT);
  pinMode(PIN_LED_CAMARA, OUTPUT);
  digitalWrite(PIN_LED_CAMARA, LOW);

  pinMode(PIN_LED_FOGO, OUTPUT);
  digitalWrite(PIN_LED_FOGO, LOW);
  
  pinMode(PIN_LED_TEMPERATURA, OUTPUT);
  digitalWrite(PIN_LED_TEMPERATURA, LOW);

  // Iniciar DHT
  sensortemperatura.begin();

  Serial.println("Sistema iniciado: DHT11 + Switch + LED (Controlo Manual/Web) + API");

  // Ligar ao WiFi
  conectarWiFi();
}

void loop() {
  // ---------------------------------------------------------
  // 1. VERIFICAR SENSOR DE MOVIMENTO (Leitura instantânea)
  // ---------------------------------------------------------
  int movimento = digitalRead(PIN_SWITCH);

  if (movimento != estadoAnterior) {
    estadoAnterior = movimento;

    if (movimento == HIGH) {
      movimentoDetectado();
    } else {
      movimentoTerminado();
    }
  }

  // ---------------------------------------------------------
  // 2. TAREFAS PERIÓDICAS (A cada 2 segundos)
  // ---------------------------------------------------------
  if (millis() - ultimoTempoLeitura >= intervaloLeitura) {
    ultimoTempoLeitura = millis(); 
    
    // A. Envia a temperatura atual para o servidor
    lerEnviarTemperatura();
    
    // B. Faz GET para saber se o Dashboard mandou ligar/desligar o LED
    verificarLedCameraDashboard();

    verificarStatusFogo();

    verificarLedTemperaturaDashboard();
  }
}

// ==========================
// FUNÇÕES DE MOVIMENTO
// ==========================
void movimentoDetectado() {
  Serial.println("\nMovimento Detetado!");
  digitalWrite(PIN_LED_CAMARA, HIGH); // Liga localmente

  enviarParaAPI("sensor-movimento", "1", "Sensor", "MCU");
  enviarParaAPI("led-camera", "1", "Atuador", "MCU");

  // Envia o comando para a câmara (O script Python irá repor a 0)
  enviarParaAPI("camera", "1", "Comando", "MCU");
}

void movimentoTerminado() {
  Serial.println("\nMovimento Terminado!");
  digitalWrite(PIN_LED_CAMARA, LOW); // Desliga localmente

  enviarParaAPI("sensor-movimento", "0", "Sensor", "MCU");
  enviarParaAPI("led-camera", "0", "Atuador", "MCU");
}

// ==========================
// FUNÇÕES DE TEMPERATURA
// ==========================
void lerEnviarTemperatura() {
  float temperatura = sensortemperatura.readTemperature();

  if (isnan(temperatura)) {
    Serial.println("Erro ao ler do sensor DHT!");
  } else {
    temperaturaAtual = temperatura; // <--- Guarda o valor aqui
    Serial.print("\n[DHT11] Temperatura: ");
    Serial.print(temperatura);
    Serial.println(" °C");

    enviarParaAPI("sensor-temperatura", String(temperatura), "Sensor", "Arduino");
  }
}

// ==========================
// FUNÇÃO GET - CONSULTAR O DASHBOARD
// ==========================
void verificarLedCameraDashboard() {
  if (WiFi.status() != WL_CONNECTED) {
    conectarWiFi();
  }

  // Monta o URL de consulta baseado na lógica GET da tua api.php (?nome=led-camera)
  String urlGETLedCamera = caminhoAPI + "?nome=led-camera";
  clienteHTTP.get(urlGETLedCamera);

  int codigoestado = clienteHTTP.responseStatusCode();
  String resposta = clienteHTTP.responseBody();
  resposta.trim(); // Limpa espaços em branco ou quebras de linha ocultas

  if (codigoestado == 200) {
    Serial.print("Valor do Led da Câmara: ");
    Serial.println(resposta);

    // Atua no LED físico dependendo do que está escrito no valor.txt do servidor
    if (resposta == "1") {
      digitalWrite(PIN_LED_CAMARA, HIGH);
    } 
    else if (resposta == "0") {
      digitalWrite(PIN_LED_CAMARA, LOW);
    }
  } else {
    Serial.print("Erro no GET. Código: ");
    Serial.println(codigoestado);
  }
}

// ==========================
// FUNÇÃO COMBINADA: LED DE FOGO
// ==========================
void verificarStatusFogo() {
  if (WiFi.status() != WL_CONNECTED) {
    conectarWiFi();
  }

  // 1. Ler o sensor de chama (fonte automática, independente do led-fogo)
  clienteHTTP.get(caminhoAPI + "?nome=sensor-chama");
  int codigoSensor = clienteHTTP.responseStatusCode();
  String respSensorChama = clienteHTTP.responseBody();
  respSensorChama.trim();
  bool sensorAtivo = (respSensorChama == "1");
  

  bool estadoFogoAtual;

  if (sensorAtivo) {
    // Sensor detetou chama -> LED liga sempre (prioridade de segurança)
    estadoFogoAtual = true;
  }
  else if (sensorChamaAnterior && !sensorAtivo) {
    // O sensor ACABOU de desligar agora mesmo
    // -> Forçar OFF, ignorando o GET de led-fogo (que ainda teria o "1" que nós próprios escrevemos)
    estadoFogoAtual = false;
  }
  else {
    // Sensor inativo (e já estava inativo antes) -> segue o comando do dashboard
    clienteHTTP.get(caminhoAPI + "?nome=led-fogo");
    int codigoDash = clienteHTTP.responseStatusCode();
    String respLedFogo = clienteHTTP.responseBody();
    respLedFogo.trim();
    estadoFogoAtual = (respLedFogo == "1");
  }

  sensorChamaAnterior = sensorAtivo; // guarda para o próximo ciclo

  // 2. Envia para a API SÓ na transição (evita spam de POST)
  if (estadoFogoAtual && !estadoFogoAnterior) {
    Serial.println("LED Fogo: ligar -> a enviar para a API...");
    enviarParaAPI("led-fogo", "1", "Atuador", "Arduino");
  }
  else if (!estadoFogoAtual && estadoFogoAnterior) {
    Serial.println("LED Fogo: desligar -> a enviar para a API...");
    enviarParaAPI("led-fogo", "0", "Atuador", "Arduino");
  }
  estadoFogoAnterior = estadoFogoAtual;

  // 3. Atua no LED físico
  digitalWrite(PIN_LED_FOGO, estadoFogoAtual ? HIGH : LOW);
}

void verificarLedTemperaturaDashboard() {
  if (WiFi.status() != WL_CONNECTED) {
    conectarWiFi();
  }

  // 1. Verifica se a temperatura atual está em condição de alarme
  bool limiteAtingidoAtual = (temperaturaAtual > LIMITE_TEMPERATURA);

  // 2. Envia para a API SÓ na transição (evita spam de POST)
  if (limiteAtingidoAtual && !limiteTemperaturaAtingidoAnterior) {
    Serial.println("Temperatura acima do limite! A enviar LED=1 para a API...");
    enviarParaAPI("led-temperatura", "1", "Atuador", "Arduino");
  }
  else if (!limiteAtingidoAtual && limiteTemperaturaAtingidoAnterior) {
    Serial.println("Temperatura normalizou. A enviar LED=0 para a API...");
    enviarParaAPI("led-temperatura", "0", "Atuador", "Arduino");
  }
  limiteTemperaturaAtingidoAnterior = limiteAtingidoAtual;

  // 3. Se estiver em alarme, o LED fica sempre ligado (override de segurança)
  if (limiteAtingidoAtual) {
    digitalWrite(PIN_LED_TEMPERATURA, HIGH);
    return; // não precisa de consultar o dashboard neste ciclo
  }

  // 4. Se NÃO estiver em alarme, o controlo é feito pelo dashboard (GET)
  String urlGETLedTemperatura = caminhoAPI + "?nome=led-temperatura";
  clienteHTTP.get(urlGETLedTemperatura);

  int codigoestado = clienteHTTP.responseStatusCode();
  String resposta = clienteHTTP.responseBody();
  resposta.trim();

  if (codigoestado == 200) {
    Serial.print("Valor do Led de Temperatura (dashboard): ");
    Serial.println(resposta);

    if (resposta == "1") {
      digitalWrite(PIN_LED_TEMPERATURA, HIGH);
    } else if (resposta == "0") {
      digitalWrite(PIN_LED_TEMPERATURA, LOW);
    }
  } else {
    Serial.print("Erro no GET Temperatura. Código: ");
    Serial.println(codigoestado);
  }
}

// ==========================
// FUNÇÕES DE REDE E API (POST)
// ==========================
void conectarWiFi() {
  Serial.print("A ligar ao Wi-Fi: ");
  Serial.println(SSID);

  while (WiFi.status() != WL_CONNECTED) {
    WiFi.begin(SSID, PASS_WIFI);
    Serial.print(".");
    delay(3000);
  }

  Serial.println("\nWi-Fi ligado.");
}

void enviarParaAPI(String nome, String valor, String tipo, String origem) {
  if (WiFi.status() != WL_CONNECTED) {
    conectarWiFi();
  }
  
  String dadosPOST = "nome=" + nome + "&valor=" + valor + "&hora=" + "" + "&tipo=" + tipo + "&origem=" + origem;

  Serial.print("[HTTP POST] A enviar: ");
  Serial.println(dadosPOST);

  clienteHTTP.post(caminhoAPI, "application/x-www-form-urlencoded", dadosPOST);
  
  int codigoestado = clienteHTTP.responseStatusCode(); 
  String respostaAPI = clienteHTTP.responseBody(); // Limpa a memória do buffer
  
  if(codigoestado != 200){                             
    Serial.print("-> ERRO HTTP POST: ");                             
    Serial.println(codigoestado);                        
  }
}