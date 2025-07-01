<?php
use Dompdf\Dompdf;
use Dompdf\Options;

if (!defined('ABSPATH')) exit;

class UPM_PDF_Generator {
    public static function init() {
        require_once UPM_PDF_PATH . 'vendor/autoload.php';

        add_action('add_meta_boxes', [__CLASS__, 'add_pdf_meta_boxes']);
        add_action('save_post_upm_invoice', [__CLASS__, 'maybe_schedule_invoice_pdf']);
        add_action('save_post_upm_project', [__CLASS__, 'maybe_schedule_contract_pdf']);

        // Hooks para tareas asincrónicas
        add_action('upm_generate_invoice_pdf_event', [__CLASS__, 'handle_invoice_pdf_event']);
        add_action('upm_generate_contract_pdf_event', [__CLASS__, 'handle_contract_pdf_event']);
    }

    public static function add_pdf_meta_boxes() {
        add_meta_box(
            'upm_pdf_preview',
            'Documento PDF',
            [__CLASS__, 'render_pdf_box'],
            ['upm_invoice', 'upm_project'],
            'side'
        );
    }

    public static function render_pdf_box($post) {
        $project_id = $post->post_type === 'upm_invoice'
            ? get_post_meta($post->ID, '_upm_invoice_project_id', true)
            : $post->ID;

        $files = get_posts([
            'post_type'  => 'upm_file',
            'meta_query' => [
                ['key' => '_upm_file_project_id', 'value' => $project_id],
                ['key' => '_upm_auto_generated',  'value' => '1']
            ],
            'posts_per_page' => -1
        ]);

        if (empty($files)) {
            echo '<p>No hay PDF generado aún.</p>';
        } else {
            foreach ($files as $f) {
                $url = get_post_meta($f->ID, '_upm_file_url', true);
                echo '<p><a href="' . esc_url($url) . '" target="_blank">📄 Ver PDF</a></p>';
            }
        }
    }

    public static function maybe_schedule_invoice_pdf($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (wp_is_post_revision($post_id)) return;
        if (get_post_type($post_id) !== 'upm_invoice') return;

        // 🔁 Esperar hasta el final del ciclo para programar la tarea (evita duplicados y datos incompletos)
        add_action('shutdown', function () use ($post_id) {
            if (!wp_next_scheduled('upm_generate_invoice_pdf_event', [$post_id])) {
                wp_schedule_single_event(time() + 2, 'upm_generate_invoice_pdf_event', [$post_id]);
            }
        });
    }

    public static function maybe_schedule_contract_pdf($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (wp_is_post_revision($post_id)) return;
        if (get_post_type($post_id) !== 'upm_project') return;

        add_action('shutdown', function () use ($post_id) {
            if (!wp_next_scheduled('upm_generate_contract_pdf_event', [$post_id])) {
                wp_schedule_single_event(time() + 2, 'upm_generate_contract_pdf_event', [$post_id]);
            }
        });
    }

    public static function handle_invoice_pdf_event($post_id) {
        $project_id = get_post_meta($post_id, '_upm_invoice_project_id', true);

        // Verificar si ya existe un PDF por título
        $filename = 'Factura_' . $post_id . '.pdf';
        $existing_by_title = get_page_by_title($filename, OBJECT, 'upm_file');
        if ($existing_by_title) return;

        $html = self::get_invoice_template($post_id);
        self::generate_and_save_pdf($html, $filename, $project_id, 'Facturación');
    }

    public static function handle_contract_pdf_event($post_id) {
        $filename = 'Contrato_' . $post_id . '.pdf';
        $existing_by_title = get_page_by_title($filename, OBJECT, 'upm_file');
        if ($existing_by_title) return;

        $html = self::get_contract_template($post_id);
        self::generate_and_save_pdf($html, $filename, $post_id, 'Legal');
    }

    private static function generate_and_save_pdf($html, $filename, $project_id, $category) {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $options->set('fontCache', UPM_PDF_PATH . 'vendor/dompdf/dompdf/lib/fonts/');
        $options->set('defaultFont', 'Creepster-Regular');
        $dompdf->render();

        $upload_dir = wp_upload_dir();
        $pdf_path = $upload_dir['path'] . '/' . $filename;
        file_put_contents($pdf_path, $dompdf->output());

        $filetype = wp_check_filetype($filename, null);
        $attachment = [
            'post_mime_type' => $filetype['type'],
            'post_title'     => sanitize_file_name($filename),
            'post_status'    => 'inherit',
        ];

        $attach_id = wp_insert_attachment($attachment, $pdf_path);
        require_once ABSPATH . 'wp-admin/includes/image.php';
        wp_generate_attachment_metadata($attach_id, $pdf_path);

        $url = wp_get_attachment_url($attach_id);
        $size = size_format(filesize($pdf_path), 2);

        wp_insert_post([
            'post_type'    => 'upm_file',
            'post_title'   => $filename,
            'post_status'  => 'publish',
            'meta_input'   => [
                '_upm_file_project_id' => $project_id,
                '_upm_file_url'        => $url,
                '_upm_file_type'       => $filetype['type'],
                '_upm_file_size'       => $size,
                '_upm_file_category'   => $category,
                '_upm_auto_generated'  => '1',
            ]
        ]);
    }

    private static function get_invoice_template($post_id) {
        ob_start();
        include UPM_PDF_PATH . 'templates/invoice-template.php';
        return ob_get_clean();
    }

    private static function get_contract_template($post_id) {
        ob_start();
        include UPM_PDF_PATH . 'templates/contract-template.php';
        return ob_get_clean();
    }
}
