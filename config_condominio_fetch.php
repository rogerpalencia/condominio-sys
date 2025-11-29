<?php
// config_condominio_fetch.php — Devuelve configuración actual (AJAX)
session_start();
require_once 'core/PDO.class.php';

$id_condominio = $_POST['id_condominio'] ?? 0;
if(!$id_condominio){ echo json_encode(['status'=>'error','message'=>'ID condominio requerido']); exit; }

$conn = DB::getInstance();

// Condominio
$stmt=$conn->prepare("SELECT * FROM public.condominio WHERE id_condominio=:id");
$stmt->execute([':id'=>$id_condominio]);
$condo=$stmt->fetch(PDO::FETCH_ASSOC);

// Email config
$stmt=$conn->prepare("SELECT * FROM email.config WHERE id_condominio=:id");
$stmt->execute([':id'=>$id_condominio]);
$email=$stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
  'status'=>'ok',
  'condominio'=>$condo,
  'email'=>$email
]);
