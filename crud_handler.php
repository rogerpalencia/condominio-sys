<?php
require_once("core/funciones.php");
require_once("config_crud.php");
$func = new Funciones();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$tabla = $_POST['tabla'] ?? $_GET['tabla'] ?? '';
$config = $crud_config[$tabla] ?? null;

if (!$config) die(json_encode(['estatus' => 0, 'respuesta' => 'Tabla no válida']));

switch ($action) {
    case 'read':
        $columns = array_filter(array_keys($config['fields']), function($field) use ($config) {
            return $config['fields'][$field]['datatable'] ?? false;
        });
        $sql = " anumite id_moneda, codigo, nombre, simbolo, fecha_creacion FROM $tabla";
        $totalData = $func->countRows($sql);
        $totalFiltered = $totalData;

        if (!empty($_POST['search']['value'])) {
            $where = [];
            foreach ($columns as $col) {
                $where[] = "$col LIKE '%" . $_POST['search']['value'] . "%'";
            }
            $sql .= " WHERE (" . implode(' OR ', $where) . ")";
            $totalFiltered = $func->countRows($sql);
        }

        $sql .= " ORDER BY " . $columns[$_POST['order'][0]['column']] . " " . $_POST['order'][0]['dir'];
        $sql .= " LIMIT " . $_POST['start'] . "," . $_POST['length'];

        $data = $func->fetchAll($sql);
        echo json_encode([
            "draw" => intval($_POST['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data
        ]);
        break;

    case 'get_form':
        $id = $_POST['id'] ?? null;
        $record = $id ? $func->fetchOne("SELECT * FROM $tabla WHERE {$config['primary_key']} = $id") : [];
        $html = '';
        foreach ($config['fields'] as $field => $props) {
            if ($props['visible'] === false) continue;
            $value = $record[$field] ?? $props['default'] ?? '';
            $html .= "<div class='mb-3'>";
            $html .= "<label>{$props['label']}</label>";
            switch ($props['type']) {
                case 'text':
                case 'datetime':
                    $html .= "<input type='{$props['type']}' name='$field' class='form-control' value='$value'" . ($props['required'] ? ' required' : '') . ($props['readonly'] ? ' readonly' : '') . ">";
                    break;
                case 'textarea':
                    $html .= "<textarea name='$field' class='form-control'" . ($props['required'] ? ' required' : '') . ">$value</textarea>";
                    break;
                case 'select':
                    if (isset($props['foreign_table'])) {
                        $options = $func->fetchAll("SELECT {$props['foreign_key']}, {$props['foreign_label']} FROM {$props['foreign_table']}");
                        $html .= "<select name='$field' class='form-control'" . ($props['required'] ? ' required' : '') . ">";
                        foreach ($options as $opt) {
                            $selected = $opt[$props['foreign_key']] == $value ? 'selected' : '';
                            $html .= "<option value='{$opt[$props['foreign_key']]}' $selected>{$opt[$props['foreign_label']]}</option>";
                        }
                        $html .= "</select>";
                    } else {
                        $html .= "<select name='$field' class='form-control'" . ($props['required'] ? ' required' : '') . ">";
                        foreach ($props['options'] as $opt) {
                            $selected = $opt == $value ? 'selected' : '';
                            $html .= "<option value='$opt' $selected>$opt</option>";
                        }
                        $html .= "</select>";
                    }
                    break;
                case 'checkbox':
                    $checked = $value ? 'checked' : '';
                    $html .= "<input type='checkbox' name='$field' value='1' $checked>";
                    break;
            }
            $html .= "</div>";
        }
        echo $html;
        break;

    case 'create':
    case 'edit':
        $id = $_POST['id'] ?? null;
        $fields = [];
        foreach ($config['fields'] as $field => $props) {
            if ($props['visible'] === false || $props['readonly']) continue;
            $value = $_POST[$field] ?? $props['default'] ?? '';
            $fields[] = "$field = '$value'";
        }
        if ($action == 'edit') {
            $sql = "UPDATE $tabla SET " . implode(', ', $fields) . ", fecha_actualizacion=NOW() WHERE {$config['primary_key']} = $id";
        } else {
            $sql = "INSERT INTO $tabla SET " . implode(', ', $fields) . ", fecha_creacion=NOW()";
        }
        $result = $func->execute($sql);
        echo json_encode(['estatus' => $result ? 1 : 0, 'respuesta' => $result ? 'Guardado exitosamente' : 'Error al guardar']);
        break;

    case 'delete':
        $id = $_POST['id'];
        $sql = "DELETE FROM $tabla WHERE {$config['primary_key']} = $id";
        $result = $func->execute($sql);
        echo json_encode(['estatus' => $result ? 1 : 0, 'respuesta' => $result ? 'Eliminado exitosamente' : 'Error al eliminar']);
        break;

    default:
        echo json_encode(['estatus' => 0, 'respuesta' => 'Acción no válida']);
}
?>