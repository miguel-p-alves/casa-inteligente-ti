from gpiozero import LED, DigitalInputDevice
import requests
import time
import cv2

print("--- SISTEMA DE ALARME DE INCÊNDIO E CÂMARA INICIADO ---")
print("Prima CTRL+C para terminar\n")

# =========================================================
# 1. CONFIGURAÇÃO DOS PINOS GPIO
# =========================================================

# LED é usado aqui para controlar os buzzers (ligar/desligar)
buzzer_alarme = LED(17)               # Pino Físico 11 — buzzer do alarme de movimento
buzzer_fogo   = LED(23)               # Pino Físico 16 — buzzer do alarme de fogo
sensor_chama  = DigitalInputDevice(27) # Pino Físico 13 — sensor de chama

# =========================================================
# 2. CONFIGURAÇÕES GERAIS (API e Câmara)
# =========================================================

# URL base da API PHP que recebe e envia dados dos sensores
API_URL    = 'http://10.28.114.77/projeto-ti/api/api.php'

# URL do script PHP que recebe a imagem e a guarda no servidor
UPLOAD_URL = 'http://10.28.114.77/projeto-ti/api/upload.php'

# URL do stream de vídeo da app DroidCam (transforma o telemóvel em webcam)
DROIDCAM_URL = "http://10.28.114.67:4747/video"

# =========================================================
# 3. FUNÇÕES DA CÂMARA
# =========================================================

def capturar_e_enviar():
    """
    Liga à DroidCam, espera 3 segundos para a câmara focar,
    captura uma imagem e envia para o servidor via POST.
    Depois repõe o valor 'camera' para 0 na API.
    """
    print("A ligar à DroidCam e a aguardar 3 segundos para focar...")
    
    # Abre o stream de vídeo da DroidCam
    cap = cv2.VideoCapture(DROIDCAM_URL)
    
    # Espera 2 segundos para a câmara ter tempo de focar
    time.sleep(2)
    
    # Captura um frame (imagem) do stream
    retorno, frame = cap.read()
    
    # Liberta a câmara logo após capturar
    cap.release()

    if retorno==False:
        # Se ret for False, a captura falhou
        print("Erro: Não foi possível ler o frame da DroidCam.")
        return

    print("Imagem capturada! A enviar para o servidor...")
    
    # Converte o frame para bytes JPEG
    sucesso, img_bytes = cv2.imencode('.jpg', frame)
    
    if sucesso == False:
        print("Erro ao converter a imagem para JPEG.")
        return

    # Prepara o ficheiro para enviar via POST
    ficheiro = {
        'imagem': ('webcam.jpg', img_bytes.tobytes(), 'image/jpeg')}

    try:
        resposta = requests.post(UPLOAD_URL, files=ficheiro)
        
        if resposta.status_code == 200:
            print("[+] Imagem guardada no servidor com sucesso!")
            # Repõe o gatilho da câmara a 0 na API para não disparar de novo
            repor_camera_para_zero()
        else:
            print(f"[-] Erro no upload. Código: {resposta.status_code} | Resposta: {resposta.text}")
    except Exception as e:
        print("Erro ao comunicar com o servidor de upload:", e)


def repor_camera_para_zero():
    """
    Envia um POST à API a dizer que a câmara já não precisa de disparar (valor = 0).
    Isto evita que o gatilho fique ativo e dispare repetidamente.
    """
    print("A repor o gatilho da câmara a 0 na API...")
    
    dados = {
        'nome'   : 'camera',
        'valor'  : '0',
        'hora'   : time.strftime("%d-%m-%Y %H:%M:%S"),
        'tipo'   : 'Comando',
        'origem' : 'Raspberry Pi'
    }

    try:
        resposta = requests.post(API_URL, data=dados, timeout=5)
        if resposta.status_code == 200:
            print("Gatilho reposto a 0 com sucesso.")
        else:
            print(f"Erro ao repor a 0. Código: {resposta.status_code}")
    except Exception as e:
        print("Erro ao repor câmara a 0:", e)


# =========================================================
# 4. VARIÁVEIS DE ESTADO
# Guardam o estado anterior de cada sensor para detetar mudanças
# (transição de 0->1 ou 1->0), evitando envios repetidos para a API
# =========================================================
estado_fogo_anterior      = 0
estado_movimento_anterior = "0"
estado_chama_anterior     = 0

# =========================================================
# 5. CICLO PRINCIPAL
# =========================================================
try:
    while True:

        # -----------------------------------------------
        # PASSO 1: LER SENSOR DE CHAMA E ENVIAR À API
        # -----------------------------------------------
        
        # sensor_chama.value é 1 se detetar chama, 0 caso contrário
        if sensor_chama.value == 1:
            estado_fogo = 1
        else:
            estado_fogo = 0
        
        # Só envia à API se o estado mudou
        if (estado_fogo != estado_chama_anterior):
            if estado_fogo == 1:
                print("ALERTA: Chama detetada!")
            else:
                print("Chama: Não detetada.")
            try:
                requests.post(API_URL, data={
                    'nome'   : 'sensor-chama',
                    'valor'  : estado_fogo,
                    'hora'   : time.strftime("%d-%m-%Y %H:%M:%S"),
                    'tipo'   : 'Sensor',
                    'origem' : 'Raspberry Pi'
                })
            except Exception as e:
                print("Erro ao enviar estado do sensor de chama:", e)
            
            estado_chama_anterior = estado_fogo


        # -----------------------------------------------
        # PASSO 2: ALARME DE MOVIMENTO (GET + POST + Buzzer)
        # -----------------------------------------------
        try:
            # Pede à API o estado atual do buzzer de alarme e do sensor de movimento
            req_alarme   = requests.get(f'{API_URL}?nome=buzzer-alarme')
            req_movimento = requests.get(f'{API_URL}?nome=sensor-movimento')

            if req_alarme.status_code == 200 and req_movimento.status_code == 200:
                estado_alarme_api   = req_alarme.text # "0" ou "1"
                estado_movimento_api = req_movimento.text # "0" ou "1"

                print(time.strftime("%H:%M:%S"), end=" -> ")
                print(f"Buzzer Alarme: {estado_alarme_api} | Movimento: {estado_movimento_api}")

                # Só atualiza a API quando há uma MUDANÇA de estado (evita registos repetidos)
                if estado_movimento_api == "1" and estado_movimento_anterior == "0":
                    # Movimento começou agora, ativa o buzzer na API
                    print("Movimento detetado! A ativar buzzer na API...")
                    requests.post(API_URL, data={
                        'nome': 'buzzer-alarme', 'valor': '1',
                        'hora': time.strftime("%d-%m-%Y %H:%M:%S"),
                        'tipo': 'Atuador', 'origem': 'Raspberry Pi'
                    })
                    estado_alarme_api = "1"  # Força o estado local para reagir já neste ciclo

                elif estado_movimento_api == "0" and estado_movimento_anterior == "1":
                    # Movimento parou → desativa o buzzer na API
                    print("Movimento terminou! A desativar buzzer na API...")
                    requests.post(API_URL, data={
                        'nome': 'buzzer-alarme', 'valor': '0',
                        'hora': time.strftime("%d-%m-%Y %H:%M:%S"),
                        'tipo': 'Atuador', 'origem': 'Raspberry Pi'
                    })
                    estado_alarme_api = "0"

                # Guarda o estado atual do movimento para a próxima iteração
                estado_movimento_anterior = estado_movimento_api

                # Liga o buzzer se a API mandar OU se o sensor estiver ativo
                if estado_alarme_api == "1" or estado_movimento_api == "1":
                    print("ALARME DE MOVIMENTO ATIVO!")
                    buzzer_alarme.blink(on_time=0.2, off_time=2, n=2)
                else:
                    buzzer_alarme.off()
            else:
                print("Erro: Resposta HTTP inválida ao ler alarme/movimento.")
        except Exception as e:
            print("Erro no alarme de movimento:", e)


        # -----------------------------------------------
        # PASSO 3: ALARME DE FOGO (GET + POST + Buzzer)
        # -----------------------------------------------
        try:
            # Pede à API o estado atual do buzzer de fogo
            req_fogo = requests.get(f'{API_URL}?nome=buzzer-fogo')

            if req_fogo.status_code == 200:
                estado_api_fogo = req_fogo.text  # "0" ou "1"

                # Só atualiza a API quando há uma MUDANÇA de estado do sensor físico
                if estado_fogo == 1 and estado_fogo_anterior == 0:
                    # Sensor detetou fogo agora, ativa o buzzer de fogo na API
                    print("Fogo detetado pelo sensor! A ativar na API...")
                    requests.post(API_URL, data={
                        'nome': 'buzzer-fogo', 'valor': '1',
                        'hora': time.strftime("%d-%m-%Y %H:%M:%S"),
                        'tipo': 'Atuador', 'origem': 'Raspberry Pi'
                    })
                    estado_api_fogo = "1"

                elif estado_fogo == 0 and estado_fogo_anterior == 1:
                    # Fogo cessou → desativa o buzzer de fogo na API
                    print("Fogo cessou! A desativar na API...")
                    requests.post(API_URL, data={
                        'nome': 'buzzer-fogo', 'valor': '0',
                        'hora': time.strftime("%d-%m-%Y %H:%M:%S"),
                        'tipo': 'Atuador', 'origem': 'Raspberry Pi'
                    })
                    estado_api_fogo = "0"

                # Guarda o estado atual do fogo para a próxima iteração
                estado_fogo_anterior = estado_fogo

                # Liga o buzzer se a API mandar OU se o sensor estiver a detetar
                if estado_api_fogo == "1" or estado_fogo == 1:
                    print(f"{time.strftime('%H:%M:%S')} -> ALARME DE FOGO ATIVO!")
                    buzzer_fogo.blink(on_time=0.1, off_time=0.1, n=5)
                else:
                    buzzer_fogo.off()
            else:
                print("Erro: Resposta HTTP inválida ao ler estado de fogo.")
        except Exception as e:
            print("Erro no alarme de fogo:", e)


        # -----------------------------------------------
        # PASSO 4: VERIFICAÇÃO DO GATILHO DA CÂMARA (GET)
        # -----------------------------------------------
        try:
            # Pergunta à API se o gatilho da câmara está ativo (valor = "1")
            req_cam = requests.get(f'{API_URL}?nome=camera')

            if req_cam.status_code == 200:
                if req_cam.text.strip() == "1":
                    print("\n[!] Gatilho da câmara ativo! A capturar imagem...")
                    # A função já trata de capturar, enviar e repor a 0
                    capturar_e_enviar()
            else:
                print(f"Erro ao verificar gatilho da câmara. Código: {req_cam.status_code}")
        except Exception as e:
            print("Erro ao verificar câmara:", e)


        # -----------------------------------------------
        # Espera 2 segundos antes de repetir o ciclo
        # -----------------------------------------------
        print("--------------------------------------------------")
        time.sleep(2)


except KeyboardInterrupt:
    # O utilizador premiu CTRL+C — termina o programa de forma controlada
    print("\nPrograma interrompido pelo utilizador.")

except Exception as e:
    # Qualquer outro erro inesperado
    print("\nErro inesperado:", e)

finally:
    # Garante que os GPIOs são sempre libertados ao sair, mesmo com erro
    buzzer_alarme.close()
    buzzer_fogo.close()
    sensor_chama.close()
    print("Programa terminado com segurança.")