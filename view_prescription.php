<?php
// ver receta (imprimible)
session_start();
if (!isset($_SESSION['medico_id'])) {
    header('Location: login.php'); 
    exit;
}
require 'db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { die('ID inválido'); }

// traer receta con medico y paciente usando LEFT JOIN
$stmt = $pdo->prepare("
  SELECT r.id, r.fecha_emision, r.notas,
         m.nombre AS medico_nombre, p.nombre AS paciente_nombre
  FROM recetas r
  LEFT JOIN medicos m ON r.medico_id = m.id
  LEFT JOIN pacientes p ON r.paciente_id = p.id
  WHERE r.id = ?
");
$stmt->execute([$id]);
$receta = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$receta) die('Receta no encontrada.');

// medicamentos
$stmt2 = $pdo->prepare("SELECT medicamento, posologia FROM receta_medicamentos WHERE receta_id = ?");
$stmt2->execute([$id]);
$meds = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Receta #<?= $receta['id'] ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="styles.css">
<style>
@page { size: letter; margin: 20mm; }
.print-area { max-width: 800px; margin: 0 auto; }
.header-row { display:flex; justify-content:space-between; align-items:center; }
.med-list { margin-top: 1rem; }
.med-list li { margin-bottom: 0.6rem; }
.small { font-size: 0.9rem; color: #333; }
.print-actions { margin: 1rem 0; }
</style>
</head>
<body>
<div class="container print-area" id="printable">
  <div class="header-row">
    <div>
      <h1>Receta</h1>
      <?php if (!empty($receta['fecha_emision'])): ?>
        <div class="small">Fecha: <?= date('d/m/Y H:i', strtotime($receta['fecha_emision'])) ?></div>
      <?php endif; ?>
    </div>
    <div class="small">
      <strong>Médico:</strong> <?= htmlspecialchars($receta['medico_nombre'] ?? 'N/A') ?><br>
      <strong>Paciente:</strong> <?= htmlspecialchars($receta['paciente_nombre'] ?? 'N/A') ?>
    </div>
  </div>

  <hr>

  <h3>Medicamentos</h3>
  <ul class="med-list">
    <?php foreach ($meds as $m): ?>
      <li>
        <strong><?= htmlspecialchars($m['medicamento']) ?></strong><br>
        <span><?= nl2br(htmlspecialchars($m['posologia'])) ?></span>
      </li>
    <?php endforeach; ?>
  </ul>

  <?php if (!empty($receta['notas'])): ?>
    <hr>
    <h4>Notas</h4>
    <p><?= nl2br(htmlspecialchars($receta['notas'])) ?></p>
  <?php endif; ?>

  <div style="height:40px"></div>
  <div class="small">__________________________</div>
  <div class="small"><?= htmlspecialchars($receta['medico_nombre'] ?? 'N/A') ?> — Firma</div>

  <div class="print-actions">
    <button onclick="window.print()">Imprimir (tamaño carta)</button>
    <a href="dashboard.php">Volver</a>
  </div>
</div>
</body>
</html>
