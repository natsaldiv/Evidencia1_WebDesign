<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($correo === '' || $password === '') {
        $error = 'Completa correo y contraseña.';
    } else {
        $stmt = $pdo->prepare("SELECT id, nombre, correo, password_hash FROM medicos WHERE correo = ?");
        $stmt->execute([$correo]);
        $medico = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($medico) {
            // Comparación directa sin hash
            if ($password === $medico['password_hash']) {
                $_SESSION['medico_id'] = $medico['id'];
                $_SESSION['medico_nombre'] = $medico['nombre'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Contraseña incorrecta.';
            }
        } else {
            $error = 'Correo no encontrado.';
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login - Médicos</title>
<link rel="stylesheet" href="styles.css">
</head>
<body class="centered">
  <main class="card">
    <h1>Ingreso médico</h1>
    <?php if ($error): ?><div class="alert"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="post" action="login.php">
      <label>Correo
        <input type="email" name="correo" required>
      </label>
      <label>Contraseña
        <input type="password" name="password" required>
      </label>
      <button type="submit">Entrar</button>
    </form>
  </main>
</body>
</html>
