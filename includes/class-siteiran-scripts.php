<?php
class SiteIran_Scripts {
    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        // مسیر دقیق فایل اصلی پلاگین
        register_activation_hook(plugin_dir_path(__DIR__) . 'siteiran-order-plugin.php', [$this, 'register_scripts']);
    }

    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        $script_url = plugin_dir_url(dirname(__FILE__)) . 'assets/js/siteiran-custom.js';
        error_log("Enqueuing script at: " . $script_url);
        wp_enqueue_script('siteiran-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', ['jquery'], '4.0.13', true);
        wp_enqueue_style('siteiran-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');
        wp_enqueue_script('siteiran-custom-js', $script_url, ['jquery', 'siteiran-select2'], '2.0', true);
    }

    public function register_scripts() {
        $script_content = <<<JS
jQuery(document).ready(function($) {
$('.siteiran-product-select').select2();

$('#siteiran-add-product').on('click', function() {
var newRow = $('.siteiran-product-row').first().clone();
newRow.find('input').val('1');
newRow.find('select').val('').trigger('change');
$('#siteiran-products-container').append(newRow);
});

$('.siteiran-add-product').on('click', function() {
var orderId = $(this).data('order-id');
var newRow = $('#siteiran-products-container-' + orderId + ' .siteiran-product-row').first().clone();
newRow.find('input').val('1');
newRow.find('select').val('').trigger('change');
$('#siteiran-products-container-' + orderId).append(newRow);
});

$(document).on('click', '.siteiran-remove-product', function() {
if ($(this).closest('.siteiran-products-container, #siteiran-products-container').find('.siteiran-product-row').length > 1) {
$(this).closest('.siteiran-product-row').remove();
} else {
alert('حداقل یک محصول باید باقی بماند.');
}
});
});
JS;

$file_path = plugin_dir_path(DIR) . 'assets/js/siteiran-custom.js'; // مسیر از ریشه پلاگین
$dir_path = dirname($file_path);

error_log("Attempting to create file at: " . $file_path);

if (!file_exists($dir_path)) {
if (!mkdir($dir_path, 0755, true)) {
error_log("Failed to create directory: " . $dir_path);
return;
}
error_log("Created directory: " . $dir_path);
}

if (file_exists($file_path)) {
error_log("File already exists: " . $file_path);
}

if (file_put_contents($file_path, $script_content) === false) {
error_log("Failed to write file: " . $file_path);
} else {
error_log("Successfully created file: " . $file_path);
}
}
}