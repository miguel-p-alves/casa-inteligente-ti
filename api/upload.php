<?php
	header('Content-Type: text/html; charset=utf-8');
	/*echo $_SERVER['REQUEST_METHOD'];*/
	if ($_SERVER['REQUEST_METHOD'] == "POST"){
		if (isset($_FILES['imagem'])) {
			print_r($_FILES['imagem']);
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