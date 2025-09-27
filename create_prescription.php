<?php
session_start();
if (!isset($_SESSION['medico_id'])) {
    header('Location: login.php'); 
    exit;
}

require 'db.php';

$medico_id = $_SESSION['medico_id'];
$mensaje = '';

// Obtener pacientes del médico
$stmt = $pdo->prepare("
  SELECT p.id, p.nombre
  FROM pacientes p
  JOIN medico_paciente mp ON p.id = mp.paciente_id
  WHERE mp.medico_id = ?
  ORDER BY p.nombre
");
$stmt->execute([$medico_id]);
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paciente_id = (int)($_POST['paciente_id'] ?? 0);
    $notas = trim($_POST['notas'] ?? '');
    $medicamentos = $_POST['medicamento'] ?? [];
    $posologias = $_POST['posologia'] ?? [];

    if ($paciente_id <= 0 || count($medicamentos) === 0) {
        $mensaje = 'Selecciona paciente y agrega al menos un medicamento.';
    } else {
        $pdo->beginTransaction();
        try {
            // Insertar receta
            $stmt = $pdo->prepare("INSERT INTO recetas (medico_id, paciente_id, notas) VALUES (?, ?, ?)");
            $stmt->execute([$medico_id, $paciente_id, $notas]);
            $receta_id = $pdo->lastInsertId();

            // Insertar medicamentos
            $stmtMed = $pdo->prepare("INSERT INTO receta_medicamentos (receta_id, medicamento, posologia) VALUES (?, ?, ?)");
            for ($i=0; $i<count($medicamentos); $i++) {
                $m = trim($medicamentos[$i]);
                $p = trim($posologias[$i] ?? '');
                if ($m === '') continue;
                $stmtMed->execute([$receta_id, $m, $p]);
            }

            $pdo->commit();
            // Redirigir a la vista de la receta
            header('Location: view_prescription.php?id='.$receta_id);
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $mensaje = 'Error al guardar la receta: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Crear receta</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<header class="topbar">
  <a href="dashboard.php">← Volver</a>
  <h2>Crear receta</h2>
</header>

<main class="container">
  <?php if ($mensaje): ?><div class="alert"><?=htmlspecialchars($mensaje)?></div><?php endif; ?>

  <form method="post" action="create_prescription.php" id="recetaForm">
    <label>Paciente:
      <select name="paciente_id" required>
        <option value="">-- seleccionar --</option>
        <?php foreach ($pacientes as $p): ?>
          <option value="<?= $p['id'] ?>"><?=htmlspecialchars($p['nombre'])?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Notas (opcional):
      <textarea name="notas" rows="3"></textarea>
    </label>

    <h4>Medicamentos</h4>
    <div id="medicamentosBox">
      <div class="med-row">
        <input name="medicamento[]" placeholder="Nombre del medicamento" required>
        <input name="posologia[]" placeholder="Posología (ej. 1 c/8h por 5 días)" required>
        <button type="button" class="btn-remove" onclick="removeRow(this)">Eliminar</button>
      </div>
    </div>

    <button type="button" id="addMedBtn">Agregar medicamento</button>
    <br><br>
    <button type="submit">Guardar receta</button>
  </form>
</main>

<script>
function removeRow(btn){
  const row = btn.closest('.med-row');
  if (row) row.remove();
}

document.getElementById('addMedBtn').addEventListener('click', function(){
  const box = document.getElementById('medicamentosBox');
  const div = document.createElement('div');
  div.className = 'med-row';
  div.innerHTML = '<input name="medicamento[]" placeholder="Nombre del medicamento" required> '
                + '<input name="posologia[]" placeholder="Posología (ej. 1 c/8h por 5 días)" required> '
                + '<button type="button" class="btn-remove" onclick="removeRow(this)">Eliminar</button>';
  box.appendChild(div);
});
</script>
</body>
</html>
