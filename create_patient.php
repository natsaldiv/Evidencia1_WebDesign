<?php
session_start();
if (!isset($_SESSION['medico_id'])) {
    header('Location: login.php'); exit;
}
require 'db.php';

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $fecha_nac = $_POST['fecha_nacimiento'] ?? null;
    if ($nombre === '') {
        $mensaje = 'Nombre requerido.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO pacientes (nombre, fecha_nacimiento) VALUES (?, ?)");
        $stmt->execute([$nombre, $fecha_nac ?: null]);
        $mensaje = 'Paciente creado.';
    }
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Nuevo paciente</title>
<link rel="stylesheet" href="styles.css"></head>
<body>
  <header class="topbar">
    <a href="dashboard.php">← Volver</a>
    <h2>Nuevo paciente</h2>
  </header>
  <main class="container">
    <?php if ($mensaje): ?><div class="alert"><?=htmlspecialchars($mensaje)?></div><?php endif; ?>
    <form method="post" action="create_patient.php">
      <label>Nombre:
        <input type="text" name="nombre" required>
      </label>
      <label>Fecha de nacimiento:
        <input type="date" name="fecha_nacimiento">
      </label>
      <button type="submit">Crear paciente</button>
    </form>
  </main>
</body>
</html>
