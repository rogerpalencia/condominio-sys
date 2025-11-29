<?php
require_once("core/PDO.class.php");
$conn = DB::getInstance();
$id_condominio = (int)($_GET['id_condominio'] ?? 0);

$stmt = $conn->prepare("SELECT id_plan, codigo, nombre FROM plan_cuenta WHERE id_condominio = :id AND tipo = 'ingreso' ORDER BY codigo");
$stmt->execute([':id' => $id_condominio]);

$options = '<option value="">Seleccione cuenta</option>';
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $label = htmlspecialchars("{$row['codigo']} - {$row['nombre']}");
  $options .= "<option value=\"{$row['id_plan']}\">{$label}</option>";
}
echo $options;
