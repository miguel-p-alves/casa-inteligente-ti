#from gpiozero import LED
import requests
import time

print("Prima CTRL+C para terminar")

# Usamos o "LED" para controlar a campainha, pois ambos são saídas digitais.
# Substitui o 15 pelo pino GPIO onde ligaste a campainha fisicamente.
#led_fogo = LED(15) 

try:
    while True:
        # Faz o pedido GET à tua API. 
        # ATENÇÃO: Substitui o IP pelo endereço onde tens a pasta "api" alojada!
        request = requests.get('http://172.22.201.13/projeto-ti/api/api.php?nome=led-fogo')
        
        if(request.status_code == 200):
            # Imprime a hora e o valor recebido (como no pedidohttp.py)
            print(time.strftime("%Y-%m-%d %H:%M:%S", time.gmtime()), end=" -> ")
            print("Valor da API:", request.text)
            
            # Se o valor lido for 1 (convertemos para int como fazias com o float nas aulas)
            if (int(request.text) == 1):
                # Quando a API disser que o LED está a 1:
                print("LED DE AVISO DE FOGO ATIVADO!")
                # Liga o LED
                led_fogo.on()
            else:
                print("LED DE AVISO DE FOGO DESATIVADO!")
                led_fogo.off()
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
    #led_fogo.close()
    print('Terminou o Programa')