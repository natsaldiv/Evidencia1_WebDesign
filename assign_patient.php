<?php
session_start();
if (!isset($_SESSION['medico_id'])) {
    header('Location: login.php'); exit;
}
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medico_id = (int)($_POST['medico_id'] ?? 0);
    $paciente_id = (int)($_POST['paciente_id'] ?? 0);
    if ($medico_id > 0 && $paciente_id > 0) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO medico_paciente (medico_id, paciente_id) VALUES (?, ?)");
        $stmt->execute([$medico_id, $paciente_id]);
    }
}

header('Location: dashboard.php');
exit;
