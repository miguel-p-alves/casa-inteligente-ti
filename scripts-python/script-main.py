import time

import cv2
import requests
from gpiozero import LED, DigitalInputDevice

print("--- SISTEMA DE ALARME DE INCÊNDIO E CÂMARA INICIADO ---")
print("Prima CTRL+C para terminar\n")
    
# configuração dos pinos GPIO
# LED é usado para controlar os buzzers (ligar/desligar)

buzzer_alarme = LED(17)               # Pino Físico 11 — buzzer do alarme de movimento
buzzer_fogo   = LED(23)               # Pino Físico 16 — buzzer do alarme de fogo
sensor_chama  = DigitalInputDevice(27) # Pino Físico 13 — sensor de chama


# URL base da API PHP que recebe e envia dados dos sensores
API_URL='http://10.28.114.77/projeto-ti/api/api.php'

# URL do script PHP que recebe a imagem e a guarda no servidor
UPLOAD_URL='http://10.28.114.77/projeto-ti/api/upload.php'

# URL do stream de vídeo da app DroidCam 
DROIDCAM_URL="http://10.28.114.67:4747/video"

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
        # Se retorno for False, a captura falhou
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


# estas variaveis guardam o ultimo estado conhecido de cada sensor
# precisamos delas para saber se houve uma mudança entre este ciclo e o anterior
# por exemplo se o fogo estava 0 e agora é 1, é porque acabou de ser detetado
# sem estas variaveis enviavamos dados à API a cada 2 segundos mesmo sem mudar nada

estado_fogo_anterior      = 0
estado_movimento_anterior = "0"
estado_chama_anterior     = 0

try:
    while True:    
        # passo 1: ler o sensor de chama
        # sensor_chama.value devolve 1 se estiver a detetar chama, 0 se nao detetar
        if sensor_chama.value == 1:
            estado_fogo = 1
        else:
            estado_fogo = 0
        
        # compara o estado atual com o anterior
        # so envia à API se for diferente, ou seja so quando algo mudou        if (estado_fogo != estado_chama_anterior):
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

            # atualiza o estado anterior para o proximo ciclo comparar            
            estado_chama_anterior = estado_fogo


        # passo 2: alarme de movimento
        try:

            # fazemos dois pedidos GET à API ao mesmo tempo
            # um para saber o estado do buzzer e outro para saber se ha movimento
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

                    # forcamos o estado local a 1 para o buzzer ligar ja neste ciclo
                    # sem isto tinhamos de esperar mais 2 segundos para o proximo ciclo
                    estado_alarme_api = "1"  

                elif estado_movimento_api == "0" and estado_movimento_anterior == "1":
                    # transicao de 1 para 0, o movimento parou agora
                    print("Movimento terminou! A desativar buzzer na API...")
                    requests.post(API_URL, data={
                        'nome': 'buzzer-alarme', 'valor': '0',
                        'hora': time.strftime("%d-%m-%Y %H:%M:%S"),
                        'tipo': 'Atuador', 'origem': 'Raspberry Pi'
                    })
                    estado_alarme_api = "0"

                # guarda o estado atual para comparar no proximo ciclo
                estado_movimento_anterior = estado_movimento_api


                # liga o buzzer se a API disser para ligar OU se o sensor estiver ativo
                if estado_alarme_api == "1" or estado_movimento_api == "1":
                    print("ALARME DE MOVIMENTO ATIVO!")
                    buzzer_alarme.blink(on_time=0.2, off_time=2, n=2)
                else:
                    buzzer_alarme.off()
            else:
                print("Erro: Resposta HTTP inválida ao ler alarme/movimento.")
        except Exception as e:
            print("Erro no alarme de movimento:", e)

        # passo 3: alarme de fogo
        try:
            # pergunta à API o estado atual do buzzer de fogo
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

        # Espera 2 segundos antes de repetir o ciclo
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