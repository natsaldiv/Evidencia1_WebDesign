<?php
session_start();
if (!isset($_SESSION['medico_id'])) {
    header('Location: login.php');
    exit;
}
require 'db.php';

$medico_id = $_SESSION['medico_id'];
$medico_nombre = $_SESSION['medico_nombre'];

// Traer pacientes del médico (relacionados)
$stmt = $pdo->prepare("
  SELECT p.id, p.nombre
  FROM pacientes p
  JOIN medico_paciente mp ON p.id = mp.paciente_id
  WHERE mp.medico_id = ?
  ORDER BY p.nombre
");
$stmt->execute([$medico_id]);
$pacientes = $stmt->fetchAll();

// Traer lista de pacientes (todos) para añadir
$stmt2 = $pdo->query("SELECT id, nombre FROM pacientes ORDER BY nombre");
$todosPacientes = $stmt2->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Panel - <?=htmlspecialchars($medico_nombre)?></title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
  <header class="topbar">
    <h2>Bienvenido, <?=htmlspecialchars($medico_nombre)?></h2>
    <nav>
      <a href="create_patient.php">Nuevo paciente</a>
      <a href="create_prescription.php">Crear receta</a>
      <a href="logout.php">Cerrar sesión</a>
    </nav>
  </header>

  <main class="container">
    <section>
      <h3>Pacientes asignados</h3>
      <?php if (count($pacientes) === 0): ?>
        <p>No tienes pacientes asignados.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($pacientes as $p): ?>
            <li><?=htmlspecialchars($p['nombre'])?> - 
              <a href="create_prescription.php?paciente_id=<?= $p['id'] ?>">Crear receta</a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <section>
      <h3>Asignar paciente</h3>
      <form method="post" action="assign_patient.php">
        <input type="hidden" name="medico_id" value="<?= $medico_id ?>">
        <label>Paciente:
          <select name="paciente_id" required>
            <option value="">-- seleccionar --</option>
            <?php foreach ($todosPacientes as $tp): ?>
              <option value="<?= $tp['id'] ?>"><?=htmlspecialchars($tp['nombre'])?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <button type="submit">Asignar</button>
      </form>
    </section>
  </main>
</body>
</html>
