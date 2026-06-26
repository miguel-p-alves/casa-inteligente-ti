<?php
	header('Content-Type: text/html; charset=utf-8');
	if ($_SERVER['REQUEST_METHOD'] == "POST"){
		if (isset($_FILES['imagem'])) {
			
			$tamanho = $_FILES['imagem']['size'];
			$nomeOriginal = $_FILES['imagem']['name'];

			// 1. Validar o Tamanho
			$limiteTamanho = 1024000; 
			if ($tamanho > $limiteTamanho) {
				http_response_code(400);
				echo("Upload NOT OK: Ficheiro excede o limite de 1000 KB.");
				exit();
			}
			
			// 2. Validar a Extensão
			// Definimos as extensões permitidas e lemos a do ficheiro enviado
			$extensoesPermitidas = ['jpg', 'jpeg', 'png'];
			$extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
			
			if (!in_array($extensao, $extensoesPermitidas)) {
				http_response_code(400);
				echo("Upload NOT OK: Apenas imagens JPG ou PNG são permitidas.");
				exit();
			}
			
			if (move_uploaded_file($_FILES["imagem"]["tmp_name"], 'images/webcam.jpg')){
				echo("Upload OK");
			}
			else{
				echo("Upload NOT OK");
			}
	}
		else{
			http_response_code(400);
			exit();
		}
	}
	
	else{
		http_response_code(403);
	}
	
	
?>