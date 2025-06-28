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
        font: 'Creepster-Regular';
        font-size: 13px;
        color: #1f2937;
    }
    .wrapper {
        padding: 20px;
    }
    .logotype {
        width: 120px;
        height: auto;
        margin-bottom: 10px;
    }
    h1 {
        font-size: 22px;
        color: #111827;
        margin: 0;
        font-family: 'Montserrat', sans-serif;
    }
    .badge {
        font-size: 10px;
        font-weight: bold;
        padding: 4px 10px;
        border-radius: 6px;
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
        padding: 10px;
        text-align: left;
    }
    .products th {
        background-color: #f3f4f6;
    }
    .total {
        text-align: right;
        font-size: 16px;
        font-weight: bold;
        margin-top: 15px;
    }
    .footer-text {
        font-size: 10px;
        color: #6b7280;
        margin-top: 40px;
        text-align: center;
    }
    .subtitle {
        font-weight: bold;
        font-size: 1rem;
    }
    .text-right {
        text-align: right;
    }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <div>
                <img class="logotype" src="https://unrealsolutions.com.br/wp-content/uploads/2023/10/Unreal-Solutions-Logo-Black.png" alt="Unreal Solutions">
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
                    <td><?= esc_html($client_name) ?></td>
                    <td class="text-right">Unreal Solutions</td>
                </tr>
                <tr>
                    <td>Sin nombre</td>
                    <td class="text-right">Avenida 7mo Anillo, Calle B. Casa #11</td>
                </tr>
                <tr>
                    <td><?= esc_html($client_address) ?></td>
                    <td class="text-right">Santa Cruz de la Sierra, Bolivia</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <table class="meta-table" style="width: 100%; margin-top: 20px;">
                <tr>
                    <td class="text-left"><strong>Su orden</strong></td>
                    <td class="text-right"><strong>Método de pago:</strong> <?= $payment_method ?></td>
                </tr>
                <tr>
                    <td class="text-left">Recibo #<?= $receipt_code ?></td>
                    <td class="text-right"><strong>Moneda:</strong> <?= $currency ?></td>
                </tr>
                <tr>
                    <td class="text-left"><strong>Fecha límite:</strong> <?= $due_date ?></td>
                    <td></td>
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
