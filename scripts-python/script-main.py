from gpiozero import LED, DigitalInputDevice
import requests
import time
import cv2

print("--- SISTEMA DE ALARME DE INCÊNDIO E CÂMARA INICIADO ---")
print("Prima CTRL+C para terminar\n")

# =========================================================
# 1. CONFIGURAÇÃO DOS PINOS GPIO (Sensores e Alarmes)
# =========================================================
buzzer_alarme = LED(17)                # Pino Físico 11
buzzer_fogo = LED(23)                  # Pino Físico 16
sensor_chama = DigitalInputDevice(27)  # NOVO: Pino Físico 13 

API_URL_MAIN = 'http://10.20.228.159/projeto-ti/api/api.php'

# =========================================================
# 2. CONFIGURAÇÕES DA CÂMARA
# =========================================================
NOME_SENSOR = "camera" 
BASE_URL = "http://10.20.228.159/projeto-ti/api"
API_URL_CAM = f"{BASE_URL}/api.php"
UPLOAD_URL = f"{BASE_URL}/upload.php"
DROIDCAM_URL = "http://10.20.228.158:4747/video"

# =========================================================
# 3. FUNÇÕES DA CÂMARA
# =========================================================
def capturar_e_enviar():
    print("A ligar à DroidCam e a aguardar 3 segundos para focar...")
    cap = cv2.VideoCapture(DROIDCAM_URL)
        
    # 1. Espera 3 segundos com a câmara ligada para ela ter tempo de focar
    time.sleep(3)
    
    # 2. Captura a imagem final
    ret, frame = cap.read()
    cap.release() # Liberta a câmara imediatamente

    if ret:
        print("Imagem capturada com sucesso! A preparar envio...")
        
        # 4. Codifica o frame do OpenCV (.png/.jpg) diretamente na memória para JPEG
        sucesso_conversao, img_bytes = cv2.imencode('.jpg', frame)
        
        if not sucesso_conversao:
            print("Erro ao converter o frame para JPEG.")
            return False
            
        # 5. Prepara o dicionário de ficheiros com o nome do campo esperado pelo PHP ('imagem')
        ficheiros = {
            'imagem': ('webcam.jpg', img_bytes.tobytes(), 'image/jpeg')
        }
        
        try:
            # Envia o ficheiro para o teu upload.php via POST
            resposta = requests.post(UPLOAD_URL, files=ficheiros)
            
            if resposta.status_code == 200 and "Upload OK" in resposta.text:
                print("[+] Sucesso: Imagem guardada no servidor do site!")
                return True
            else:
                print(f"[-] Erro no Servidor Web: Código {resposta.status_code}. Resposta: {resposta.text}")
                return False
                
        except Exception as e:
            print("Erro ao tentar comunicar com o servidor de upload:", e)
            return False
    else:
        print("Erro: Não foi possível ler o frame da DroidCam.")
        return False

def repor_valor_zero():
    print("A repor o valor da câmara a 0 no servidor...")
    
    dados_post = {
        'nome': NOME_SENSOR,
        'valor': '0',
        'hora': time.strftime("%H:%M:%S"),
        'tipo': 'Comando',
        'origem': 'Script Python Camera'
    }
    
    try:
        response = requests.post(API_URL_CAM, data=dados_post, timeout=5)
        if response.status_code == 200:
            print("Valor reposto a 0 com sucesso!")
        else:
            print(f"Erro ao repor a 0. Código: {response.status_code}")
    except Exception as e:
        print("Erro de comunicação ao tentar repor a 0:", e)


# =========================================================
# 4. CICLO PRINCIPAL (Loop Único)
# =========================================================
estado_fogo_anterior = 0
try:
    while True:
        # ---------------------------------------------------------
        # PASSO 1: LER SENSOR DE CHAMA E ENVIAR PARA A API (POST)
        # ---------------------------------------------------------
        if sensor_chama.value == 1:
            print("ALERTA: Chama detetada pelo Sensor!")
            estado_fogo = 1
        else:
            print("Chama: Não detetada.")
            estado_fogo = 0

        agora = time.strftime("%d-%m-%Y %H:%M:%S", time.localtime())

        dados_fogo = {
            'nome': 'sensor-chama',
            'valor': estado_fogo,
            'hora': agora,
            'tipo': 'Sensor',
            'origem': 'Raspberry Pi'
        }

        try:
            resposta_post = requests.post(API_URL_MAIN, data=dados_fogo)
            print(estado_fogo)
            if resposta_post.status_code != 200:
                print(f"Erro no POST do Sensor: {resposta_post.status_code}")
        except Exception as e:
            print("Erro ao comunicar o POST do Sensor:", e)

        # ---------------------------------------------------------
        # PASSO 2: VERIFICAÇÃO DO ALARME NORMAL (GET Buzzer + GET Movimento)
        # ---------------------------------------------------------
        try:
            # 1. Adicionas o GET do sensor de movimento além do buzzer
            req_alarme = requests.get(f'{API_URL_MAIN}?nome=buzzer-alarme')
            req_movimento = requests.get(f'{API_URL_MAIN}?nome=sensor-movimento')
            
            if req_alarme.status_code == 200 and req_movimento.status_code == 200:
                estado_alarme_api = req_alarme.text.strip()
                estado_movimento_api = req_movimento.text.strip()
                
                print(time.strftime("%H:%M:%S", time.localtime()), end=" -> ")
                print(f"Buzzer Alarme API: {estado_alarme_api} | Movimento API: {estado_movimento_api}")
                
                # 2. Alteras o IF para ligar se o site mandar (estado_alarme_api) OU o sensor detetar (estado_movimento_api)
                if estado_alarme_api == "1" or estado_movimento_api == "1":
                    print("ALARME NORMAL ATIVADO!")
                    buzzer_alarme.blink(on_time=0.2, off_time=2, n=2)
                else:
                    buzzer_alarme.off()
            else:
                print("Erro nos pedidos HTTP do Alarme ou Movimento")
        except Exception as e:
            print("Erro no GET do Alarme/Movimento:", e)
            
        # ---------------------------------------------------------
        # PASSO 3: VERIFICAÇÃO DO ALARME DE FOGO (GET + POST)
        # ---------------------------------------------------------
        try:
            req_fogo = requests.get(f'{API_URL_MAIN}?nome=buzzer-fogo')
            
            if req_fogo.status_code == 200:
                estado_api = req_fogo.text.strip()
                
                # 1. ATUALIZAR A API APENAS QUANDO O SENSOR MUDA (Transição)
                if estado_fogo == 1 and estado_fogo_anterior == 0:
                    print("Sensor detetou fogo! A atualizar API para 1...")
                    requests.post(API_URL_MAIN, data={'nome': 'buzzer-fogo', 'valor': '1', 'hora': time.strftime("%H:%M:%S"), 'tipo': 'Atuador', 'origem': 'Raspberry Pi'})
                    estado_api = "1" # Força localmente para atuar já
                    
                elif estado_fogo == 0 and estado_fogo_anterior == 1:
                    print("Fogo cessou! A repor API para 0...")
                    requests.post(API_URL_MAIN, data={'nome': 'buzzer-fogo', 'valor': '0', 'hora': time.strftime("%H:%M:%S"), 'tipo': 'Atuador', 'origem': 'Raspberry Pi'})
                    estado_api = "0"
                
                # Guarda o estado atual para comparar na próxima volta do ciclo
                estado_fogo_anterior = estado_fogo

                # 2. CONTROLAR O BUZZER
                # Liga se o site mandar (estado_api) OU se o sensor estiver a detetar (estado_fogo)
                if estado_api == "1" or estado_fogo == 1:
                    print(f"{time.strftime('%H:%M:%S')} -> ALARME DE FOGO ATIVADO!")
                    buzzer_fogo.blink(on_time=0.1, off_time=0.1, n=5)
                else:
                    buzzer_fogo.off()
                    
            else:
                print("Erro no pedido HTTP do Fogo")
        except Exception as e:
            print("Erro no GET/POST do Fogo:", e)
            
        # ---------------------------------------------------------
        # PASSO 4: VERIFICAÇÃO DA CÂMARA (GET)
        # ---------------------------------------------------------
        try:
            pedido_cam = requests.get(f"{API_URL_CAM}?nome={NOME_SENSOR}")
            
            if pedido_cam.status_code == 200:
                valor_atual_cam = pedido_cam.text.strip()
                
                if valor_atual_cam == "1":
                    print("\n[!] Gatilho ativado pelo Arduino! A capturar imagem...")
                    sucesso = capturar_e_enviar()
                    if sucesso:
                        repor_valor_zero()
            else:
                print(f"Erro a comunicar com a API da Câmara. Código HTTP: {pedido_cam.status_code}")
        except Exception as e:
            print("Erro no GET da Câmara:", e)

        # ---------------------------------------------------------
        # Espera 2 segundos antes do próximo ciclo
        print("-" * 50)
        time.sleep(2)
        
except KeyboardInterrupt:
    print("\nO programa foi interrompido pelo utilizador.")
    
except Exception as e:
    print("\nErro inesperado:", e)
    
finally:
    buzzer_alarme.close()
    buzzer_fogo.close()
    sensor_chama.close()
    print('Terminou o Programa com segurança.')