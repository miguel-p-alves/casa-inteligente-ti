from gpiozero import LED
import requests
import time

print("Prima CTRL+C para terminar")

# Usamos o "LED" para controlar a campainha, pois ambos são saídas digitais.
# Substitui o 16 pelo pino GPIO onde ligaste a campainha fisicamente.
buzzer_fogo = LED(16) 

try:
    while True:
        # Faz o pedido GET à tua API. 
        # ATENÇÃO: Substitui o IP pelo endereço onde tens a pasta "api" alojada!
        request = requests.get('http://172.22.201.13/projeto-ti/api/api.php?nome=buzzer-fogo')
        
        if(request.status_code == 200):
            # Imprime a hora e o valor recebido (como no pedidohttp.py)
            print(time.strftime("%Y-%m-%d %H:%M:%S", time.gmtime()), end=" -> ")
            print("Valor da API:", request.text)
            
            # Se o valor lido for 1 (convertemos para int como fazias com o float nas aulas)
            if (int(request.text) == 1):
                # Quando a API disser que o buzzer está a 1:
                print("ALARME DE FOGO ATIVADO!")
                
                # Faz 3 beeps muito rápidos num só ciclo
                buzzer_fogo.on()
                time.sleep(0.1)
                buzzer_fogo.off()
                time.sleep(0.1)
                
                buzzer_fogo.on()
                time.sleep(0.1)
                buzzer_fogo.off()
                time.sleep(0.1)
                
                buzzer_fogo.on()
                time.sleep(0.1)
                buzzer_fogo.off()
                time.sleep(0.1)
                
                buzzer_fogo.on()
                time.sleep(0.1)
                buzzer_fogo.off()
                time.sleep(0.1)
                
                buzzer_fogo.on()
                time.sleep(0.1)
                buzzer_fogo.off()
                time.sleep(0.1)
            else:
                print("ALARME DE FOGO DESATIVADO!")
                buzzer_fogo.off()
        else:
            print("Erro no pedido HTTP")
            
        # Espera 2 segundos antes do próximo pedido
        time.sleep(2)
        
except KeyboardInterrupt:
    # captura excecao CTRL + C
    print("\n O programa foi interrompido pelo utilizador.")
    
except Exception as e:
    # captura todos os erros
    print("Erro inesperado:", e)
    print("Tenta outra vez")
    
finally:
    buzzer_fogo.close()
    print('Terminou o Programa')