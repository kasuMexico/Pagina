<?php
declare(strict_types=1);

if (!function_exists('mesa_status_chip')) {
    function mesa_status_chip(string $status): string
    {
        $map = [
            'ACTIVO'          => ['label' => 'Activo',           'icon' => 'check_circle',    'class' => 'status-ok'],
            'COBRANZA'        => ['label' => 'Cobranza',         'icon' => 'request_quote',   'class' => 'status-warn'],
            'COBRO'           => ['label' => 'Cobro',            'icon' => 'request_quote',   'class' => 'status-warn'],
            'PAGO'            => ['label' => 'Pago',             'icon' => 'paid',            'class' => 'status-ok'],
            'MORA'            => ['label' => 'En mora',          'icon' => 'schedule',        'class' => 'status-warn'],
            'CANCELADO'       => ['label' => 'Cancelado',        'icon' => 'cancel',          'class' => 'status-danger'],
            'CANCELO'         => ['label' => 'Canceló',          'icon' => 'cancel',          'class' => 'status-danger'],
            'ACTIVACION'      => ['label' => 'Activación',       'icon' => 'bolt',            'class' => 'status-info'],
            'PREVENTA'        => ['label' => 'Preventa',         'icon' => 'factory',         'class' => 'status-info'],
            'FUNERARIO'       => ['label' => 'Funerario',        'icon' => 'local_florist',   'class' => 'status-info'],
            'SEGURIDAD'       => ['label' => 'Seguridad',        'icon' => 'verified_user',   'class' => 'status-info'],
            'TRANSPORTE'      => ['label' => 'Transporte',       'icon' => 'local_shipping',  'class' => 'status-info'],
            'DISTRIBUIDOR'    => ['label' => 'Distribuidor',     'icon' => 'store',           'class' => 'status-info'],
            'RETIRO'          => ['label' => 'Retiro',           'icon' => 'savings',         'class' => 'status-info'],
            'VALIDO'          => ['label' => 'Válido',           'icon' => 'task_alt',        'class' => 'status-ok'],
            'VALIDO VENTA'    => ['label' => 'Válido Venta',     'icon' => 'shopping_cart',   'class' => 'status-ok'],
            'VENTA'           => ['label' => 'Venta',            'icon' => 'shopping_bag',    'class' => 'status-ok'],
            'OK'              => ['label' => 'OK',               'icon' => 'task_alt',        'class' => 'status-ok'],
        ];

        $key   = strtoupper(trim($status));
        $entry = $map[$key] ?? ['label' => ($status !== '' ? $status : 'Sin estado'), 'icon' => 'info', 'class' => 'status-default'];

        $label = htmlspecialchars($entry['label'], ENT_QUOTES, 'UTF-8');
        $icon  = htmlspecialchars($entry['icon'], ENT_QUOTES, 'UTF-8');
        $class = htmlspecialchars($entry['class'], ENT_QUOTES, 'UTF-8');

        return '<span class="status-chip ' . $class . '"><i class="material-icons" aria-hidden="true">'
            . $icon . '</i><span>' . $label . '</span></span>';
    }
}
