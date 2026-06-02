<?php
	header('Content-Type: text/html; charset=utf-8');
	/*echo $_SERVER['REQUEST_METHOD'];*/
	if ($_SERVER['REQUEST_METHOD'] == "POST"){
		if (isset($_POST['valor'],$_POST['nome'],$_POST['hora'])) {
			// Tipo e origem completam o registro do histórico, além do nome e do valor.
			$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : "Atuador";
			$origem = isset($_POST['origem']) ? $_POST['origem'] : "Dashboard";

			// A data/hora do servidor marca quando o registro foi realmente gravado.
			$data_hora = date("d-m-Y H:i:s");

			// Estes três arquivos guardam apenas o estado atual do dispositivo.
			file_put_contents("files/".$_POST['nome']."/valor.txt",$_POST['valor']);
			file_put_contents("files/".$_POST['nome']."/nome.txt",$_POST['nome']);
			file_put_contents("files/".$_POST['nome']."/hora.txt",$_POST['hora']);

			// O log guarda vários registros, por isso cada novo comando é adicionado no final do arquivo.
			file_put_contents("files/".$_POST['nome']."/log.txt",$data_hora.";".$tipo.";".$_POST['nome'].";".$_POST['valor'].";".$origem. PHP_EOL, FILE_APPEND);
		}
		else{
			http_response_code(400);
			exit();
		}
	}
	elseif ($_SERVER['REQUEST_METHOD'] == "GET"){
		if (isset($_GET['nome'])) {
			echo file_get_contents("files/".$_GET['nome']."/valor.txt");
		}
		else{
			http_response_code(400);
		}
	}
	else{
		http_response_code(403);
	}
	
	
?>
