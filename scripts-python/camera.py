import cv2
import requests
import time

# --- CONFIGURAÇÕES ---
# O nome tem de ser igual ao que o Arduino envia ("camera")
NOME_SENSOR = "camera" 

# Atualizado com o caminho correto da pasta no teu servidor Apache/XAMPP
BASE_URL = "http://10.28.114.77/projeto-ti/api"
API_URL = f"{BASE_URL}/api.php"
UPLOAD_URL = f"{BASE_URL}/upload.php"

# O IP da DroidCam (Não te esqueças de substituir pelo IP real que aparece na App)
DROIDCAM_URL = "http://100.98.50.223:4747/video" 
# ---------------------

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
    
    # O api.php exige 'nome', 'valor' e 'hora' num POST. Adicionamos 'tipo' e 'origem' para o log ficar bonito.
    dados_post = {
        'nome': NOME_SENSOR,
        'valor': '0',
        'hora': time.strftime("%H:%M:%S"),
        'tipo': 'Comando',
        'origem': 'Script Python Camera'
    }
    
    try:
        response = requests.post(API_URL, data=dados_post, timeout=5)
        if response.status_code == 200:
            print("Valor reposto a 0 com sucesso!")
        else:
            print(f"Erro ao repor a 0. Código: {response.status_code}")
    except Exception as e:
        print("Erro de comunicação ao tentar repor a 0:", e)


print("--- À escuta da API (CTRL+C para terminar) ---")

try:
    while True:
        # 1. Faz o pedido GET (passando o nome por parâmetro na URL)
        pedido = requests.get(f"{API_URL}?nome={NOME_SENSOR}")
        
        if pedido.status_code == 200:
            valor_atual = pedido.text.strip()
            
            # 2. Verifica se o Arduino mandou disparar (valor == 1)
            if valor_atual == "1":
                print("\n[!] Gatilho ativado pelo Arduino! A capturar imagem...")
                
                # Se a captura e o upload correrem bem, repõe a 0
                sucesso = capturar_e_enviar()
                if sucesso:
                    repor_valor_zero()
                
                # Espera uns segundos para não fazer chamadas seguidas instantaneamente
                time.sleep(2) 
            else:
                # Se não for 1, espera 2 segundos antes de verificar novamente
                time.sleep(2)
        else:
            print(f"Erro a comunicar com a API. Código HTTP: {pedido.status_code}")
            time.sleep(5)
            
except KeyboardInterrupt:
    print("\nPrograma terminado pelo utilizador.")
except Exception as e:
    print("Erro inesperado:", e)