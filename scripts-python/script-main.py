from gpiozero import LED, DigitalInputDevice
import requests
import time

print("--- SISTEMA DE ALARME DE INCÊNDIO INICIADO ---")
print("Prima CTRL+C para terminar\n")

# =========================================================
# 1. CONFIGURAÇÃO DOS PINOS GPIO
# =========================================================
buzzer_alarme = LED(17)                # Pino Físico 11
buzzer_fogo = LED(23)                  # Pino Físico 16
sensor_chama = DigitalInputDevice(27)  # NOVO: Pino Físico 13 

# URL base da tua API
API_URL = 'http://10.20.228.51/projeto-ti/api/api.php'

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
            resposta_post = requests.post(API_URL, data=dados_fogo)
            if resposta_post.status_code != 200:
                print(f"Erro no POST do Sensor: {resposta_post.status_code}")
        except Exception as e:
            print("Erro ao comunicar o POST do Sensor:", e)

        # ---------------------------------------------------------
        # PASSO 2: VERIFICAÇÃO DO ALARME NORMAL (GET)
        # ---------------------------------------------------------
        try:
            req_alarme = requests.get(f'{API_URL}?nome=buzzer-alarme')
            
            if req_alarme.status_code == 200:
                print(time.strftime("%H:%M:%S", time.localtime()), end=" -> ")
                print("Valor Alarme API:", req_alarme.text.strip())
                
                if int(req_alarme.text.strip()) == 1:
                    print("ALARME ATIVADO!")
                    # blink(tempo_ligado, tempo_desligado, numero_de_vezes)
                    buzzer_alarme.blink(on_time=0.2, off_time=2, n=2)
                else:
                    buzzer_alarme.off()
            else:
                print("Erro no pedido HTTP do Alarme")
        except Exception as e:
            print("Erro no GET do Alarme:", e)
            
        # ---------------------------------------------------------
        # PASSO 3: VERIFICAÇÃO DO ALARME DE FOGO (GET)
        # ---------------------------------------------------------
        try:
            req_fogo = requests.get(f'{API_URL}?nome=buzzer-fogo')
            
            if req_fogo.status_code == 200:
                print(time.strftime("%H:%M:%S", time.localtime()), end=" -> ")
                print("Valor Fogo API:", req_fogo.text.strip())
                
                if int(req_fogo.text.strip()) == 1:
                    print("ALARME DE FOGO ATIVADO!")
                    # Faz 5 beeps super rápidos em segundo plano
                    buzzer_fogo.blink(on_time=0.1, off_time=0.1, n=5)
                else:
                    buzzer_fogo.off()
            else:
                print("Erro no pedido HTTP do Fogo")
        except Exception as e:
            print("Erro no GET do Fogo:", e)
            
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