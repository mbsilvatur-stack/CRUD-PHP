<?php
$caminho = __DIR__ . '/config.php';
if (!file_exists($caminho)){
    die("Arquivo onfig.php não encontrado. copie config.exanple.php para onfig.php e preencha suas credenciais.");
}
require $caminho;
