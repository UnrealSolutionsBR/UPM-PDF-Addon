<?php
/**
 * Plugin Name: UPM - PDF Addon
 * Description: Genera contratos y facturas PDF personalizados para Unreal Project Manager.
 * Plugin URI: https://unrealsolutions.com.br/
 * Author: Unreal Solutions
 * Version: 1.1.5
 */

defined('ABSPATH') || exit;

// 🔧 Constantes
define('UPM_PDF_PATH', plugin_dir_path(__FILE__));
define('UPM_PDF_URL', plugin_dir_url(__FILE__));

// ✅ Autoload de Composer
$autoload = UPM_PDF_PATH . 'vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
} else {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p><strong>UPM - PDF Addon:</strong> Falta <code>vendor/autoload.php</code>. Ejecuta <code>composer install</code> o sube la carpeta <code>/vendor</code>.</p></div>';
    });
    return;
}

// ✅ Helper para cargar plantillas
function upm_pdf_render_template($path, $data = []) {
    if (!file_exists($path)) return '';
    ob_start();
    extract($data);
    include $path;
    return ob_get_clean();
}

// ✅ Clase generadora de PDF
require_once UPM_PDF_PATH . 'includes/class-upm-pdf-generator.php';

// ✅ Inicializar cuando esté listo UPM
add_action('plugins_loaded', function () {
    if (class_exists('UPM_Loader')) {
        UPM_PDF_Generator::init();
    }
});
