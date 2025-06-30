<?php
if (!defined('ABSPATH')) exit;

$invoice = get_post($post_id);
$project_id = get_post_meta($post_id, '_upm_invoice_project_id', true);
$client_id = get_post_meta($post_id, '_upm_invoice_client_id', true);
$amount = (float) get_post_meta($post_id, '_upm_invoice_amount', true);
$status = get_post_meta($post_id, '_upm_invoice_status', true);
$due_date = get_the_date('Y-m-d', $invoice);

// Dummy por ahora
$receipt_code = 'FNM-35276';
$payment_method = 'Transferencia bancaria';
$currency = 'USD';

$project_title = $project_id ? get_the_title($project_id) : 'Sin proyecto asignado';
$client = get_user_by('ID', $client_id);
$client_name = $client ? $client->display_name : 'Cliente sin nombre';
$client_address = 'Santa Cruz de la Sierra, Bolivia';

$badge_class = 'badge-pending';
if (strtolower($status) === 'pagada' || strtolower($status) === 'pagado') {
    $badge_class = 'badge-paid';
}
?>
<!DOCTYPE html>
<html lang="es">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
<head>
    <meta charset="UTF-8">
    <title>Recibo <?= $receipt_code ?></title>
    <style>
    * {
        box-sizing: border-box;
    }
    @page {
        margin: 0;
    }
    html, body {
        margin: 0;
        padding: 0;
        color: #1f2937;
    }
    .wrapper {
        padding: 20px;
    }
    .logotype {
        width: 145px;
        height: auto;
        margin-bottom: 10px;
    }
    .header-title-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    h1 {
        font-size: 22px;
        color: #111827;
        margin: 0;
        font-family: 'Montserrat', sans-serif;
    }
    .badge {
        font-family: "Inter", sans-serif;
        font-size: 10px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 5;
        margin-left: 10px;
        vertical-align: middle;
        color: white;
    }
    .badge-pending {
        background-color: #FF686B;
    }
    .badge-paid {
        background-color: #52B958;
    }
    .section {
        margin-top: 20px;
    }
    .meta-table td {
        padding: 2px 0;
    }
    .products {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
    }
    .products th, .products td {
        border: 1px solid #e5e7eb;
        font-family: "Inter", sans-serif;
        padding: 10px;
        text-align: left;
    }
    .products th {
        background-color: #f3f4f6;
        font-family: 'Montserrat', sans-serif;
        text-align: center;
        font-weight: 600;
        font-size: 12px;
    }
    .total {
        font-family: 'Montserrat', sans-serif;
        text-align: right;
        font-size: 16px;
        font-weight: 700;
        margin-top: 15px;
    }
    .footer-text {
        font-size: 10px;
        color: #6b7280;
        margin-top: 40px;
        text-align: center;
        font-family: "Inter", sans-serif;
    }
    .subtitle {
        font-family: "Inter", sans-serif;
        font-weight: 700;
        font-size: 14px;
    }
    .meta-text {
        font-family: "Inter", sans-serif;
        font-weight: 400;
        font-size: 12px;
    }
    .meta-text-sb {
        font-family: "Inter", sans-serif;
        font-weight: 600;
        font-size: 12px;
    }
    .text-right {
        text-align: right;
    }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <img class="logotype" src="https://unrealsolutions.com.br/wp-content/uploads/2023/10/Unreal-Solutions-Logo-Black.png" alt="Unreal Solutions">
            <div class="header-title-row">                
                <h1>Recibo</h1>
                <span class="badge <?= esc_attr($badge_class) ?>"><?= strtoupper($status) ?></span> 
            </div>
        </div>

        <div class="section">
            <table class="meta-table" style="width: 100%; margin-top: 20px;">
                <tr>
                    <td class="subtitle">Recibo para</td>
                    <td class="subtitle text-right">Recibo de</td>
                </tr>
                <tr>
                    <td class="meta-text-sb"><?= esc_html($client_name) ?></td>
                    <td class="meta-text-sb text-right">Unreal Solutions</td>
                </tr>
                <tr>
                    <td class="meta-text">Sin nombre</td>
                    <td class="meta-text text-right">Avenida 7mo Anillo, Calle B. Casa #11</td>
                </tr>
                <tr>
                    <td class="meta-text"><?= esc_html($client_address) ?></td>
                    <td class="meta-text text-right">Santa Cruz de la Sierra, Bolivia</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <table class="meta-table" style="width: 100%; margin-top: 20px;">
                <tr>
                    <td class="subtitle"><strong>Su orden</strong></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="meta-text">Recibo #<?= $receipt_code ?></td>
                    <td class="meta-text text-right"><strong>Método de pago:</strong> <?= $payment_method ?></td>
                </tr>
                <tr>
                    <td class="meta-text"><strong>Fecha límite:</strong> <?= $due_date ?></td>
                    <td class="meta-text text-right"><strong>Moneda:</strong> <?= $currency ?></td>
                </tr>
            </table>
        </div>

        <table class="products">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= esc_html($project_title) ?></td>
                    <td>1</td>
                    <td><?= $currency ?> <?= number_format($amount, 2, '.', ',') ?></td>
                </tr>
            </tbody>
        </table>

        <p class="total"><?= $currency ?> <?= number_format($amount, 2, '.', ',') ?></p>

        <p class="footer-text">
            Si tiene algún problema con su orden (ejemplo: no reconoce el cobro o sospecha de fraude) por favor entre en contacto con: hola@unrealsolutions.com.br
        </p>
    </div>
</body>
</html>
