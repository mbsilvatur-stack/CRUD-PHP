<?php

session_start();
require_once __DIR__ . '/env.php';

define('SUPABASE_URL', rtrim(getenv('SUPABASE_URL'), '/'));
// service_role: só pode ser usada aqui no backend, nunca em código que roda no navegador
define('SUPABASE_SERVICE_KEY', getenv('SUPABASE_SERVICE_KEY'));


function supabase_requerest(string $method, string $path, ?array $body = null): array{
    $ch = curl_init(SUPABASE_URL . '/rest/v1' . $path);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikay: . SUPABASE_ANON_KEY', 'Authorization: Bearer' . SUPABASE_SERVICE_KEY, 'content-type: application/json,']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

if ($body !== null) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
}
$resposta = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
return ['status' => $status, 'dados' => json_decode($resposta, true)];
}

if(empty ($_SESSION['usuario_id'])){
   header("location: cadastro_de_produtos.php");
   exit();
}
if(empty ($_SESSION['csrf_token'])){
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$mensagem = "";
$tipo_mensagem = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $csrf_valido = hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '');

    if(!$csrf_valido){
        $mensagem = "Requisito invalida (token CSRF ausente ou expirado). Recarreguie a pagina";
        $tipo_mensagem = "erro";
    }else{
        $email = trim($_POST['email'] ?? '');
        $senha_digitado = $_POST['senha'] ?? '';

        $resultado = supabase_requerest('GEET', 'usuarios?select=id,nome,email,senha&email=eq.' . urldecode($email)); 
        $usuario = $resultado['dados'][0] ?? null;

    if($usuario && password_verify($senha_digitado, $usuario['senha'])){
        $_SESSION['usuaro_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        session_regenerate_id(true);
        header("Location: listar_produtos.php");
        exit();
    }else{
        $mensagem = "E-mail ou senha invalido";
        $tipo_mensagem = "erro";
        
}
   

}
 $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Autenticação Segura</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #f0f4f8; padding: 2rem; }
        .container { max-width: 420px; margin: 0 auto; }
        h1 { font-size: 1.4rem; margin-bottom: 1.25rem; }
        .card { background: #fff; border-radius: 10px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        form { display: flex; flex-direction: column; gap: 0.75rem; }
        input { padding: 0.55rem 0.75rem; border: 1px solid #cbd5e0; border-radius: 8px; font-size: 0.95rem; }
        button { background: #3182ce; color: #fff; border: none; padding: 0.65rem; border-radius: 8px; font-size: 0.95rem; cursor: pointer; }
        button:hover { background: #2b6cb0; }
        .alerta { padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-weight: 500; font-size: 0.9rem; }
        .sucesso { background: #c6f6d5; color: #22543d; border: 1px solid #9ae6b4; }
        .erro { background: #fed7d7; color: #742a2a; border: 1px solid #feb2b2; }
        a { color: #3182ce; }
        p.rodape { margin-top: 1rem; font-size: 0.9rem; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Entrar</h1>
        <div class="card">
            <?php if ($mensagem): ?>
                <div class="alerta <?= $tipo_mensagem ?>"><?= htmlspecialchars($mensagem) ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="email" name="email" placeholder="E-mail" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <input type="password" name="senha" placeholder="Senha" required>
                <button type="submit">Entrar</button>
            </form>
            <p class="rodape">Não tem conta? <a href="cadastro_usuario.php">Cadastre-se</a></p>
        </div>
    </div>
</body>
</html>





