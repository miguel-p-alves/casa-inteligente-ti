<?php
// Lista das passwords em texto limpo para cada utilizador
$passwords = [
    "Admin" => "admin",
    "Resident" => "resident",
    "Guest" => "guest"
];

echo "<h2>Hashes geradas:</h2>";
echo "<p>Copia as strings geradas abaixo (incluindo as aspas simples) para o teu array de utilizadores.</p>";
echo "<div style='background: #f4f4f4; padding: 15px; border-radius: 5px; font-family: monospace;'>";

foreach ($passwords as $user => $pass) {
    // Gera a hash com o algoritmo padrão do PHP (atualmente bcrypt)
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    
    // Mostra o resultado formatado
    echo "<strong>" . $user . "</strong> (password: " . $pass . ")<br>";
    echo "=> '" . $hash . "'<br><br>";
}

echo "</div>";
?>