<?php
// config_crud.php
$crud_config = [
    'moneda' => [
        'title' => 'Monedas',
        'fields' => [
            'id_moneda' => ['label' => 'ID', 'type' => 'hidden', 'visible' => false],
            'codigo' => ['label' => 'Código', 'type' => 'text', 'required' => true, 'datatable' => true],
            'nombre' => ['label' => 'Nombre', 'type' => 'text', 'required' => true, 'datatable' => true],
            'simbolo' => ['label' => 'Símbolo', 'type' => 'text', 'required' => false, 'datatable' => true],
            'fecha_creacion' => ['label' => 'Fecha Creación', 'type' => 'datetime', 'readonly' => true, 'datatable' => true],
        ],
        'actions' => ['create', 'edit', 'delete'],
        'primary_key' => 'id_moneda'
    ],
    'condominio' => [
        'title' => 'Condominios',
        'fields' => [
            'id_condominio' => ['label' => 'ID', 'type' => 'hidden', 'visible' => false],
            'nombre' => ['label' => 'Nombre', 'type' => 'text', 'required' => true, 'datatable' => true],
            'direccion' => ['label' => 'Dirección', 'type' => 'textarea', 'required' => true, 'datatable' => true],
            'id_moneda_base' => ['label' => 'Moneda Base', 'type' => 'select', 'foreign_table' => 'moneda', 'foreign_key' => 'id_moneda', 'foreign_label' => 'nombre', 'required' => true, 'datatable' => true],
            'esquema_cuota' => ['label' => 'Esquema Cuota', 'type' => 'select', 'options' => ['fija', 'fija_alicuota', 'equitativa', 'alicuota'], 'required' => true, 'datatable' => true],
            'estado' => ['label' => 'Estado', 'type' => 'checkbox', 'default' => true, 'datatable' => true],
        ],
        'actions' => ['create', 'edit', 'delete'],
        'primary_key' => 'id_condominio'
    ],
    // Agrega las demás tablas aquí...
];
?>