<?php
/**
 * Plugin Name: SiteIran Bulk Order Plugin
 * Description: پلاگین مدیریت سفارشات عمده با قابلیت ثبت، ویرایش، چاپ و تغییر وضعیت سفارشات در ووکامرس
 * Version: 2.0
 * Author: SiteIran.com
 */

if (!defined('ABSPATH')) {
    exit;
}

spl_autoload_register(function ($class_name) {
    if (strpos($class_name, 'SiteIran_') === 0) {
        $file = plugin_dir_path(__FILE__) . "includes/class-" . strtolower(str_replace('_', '-', $class_name)) . ".php";
        if (file_exists($file)) {
            require_once $file;
        } else {
            error_log("File not found for class $class_name: " . $file);
        }
    }
});

function siteiran_init() {
    if (class_exists('SiteIran_Database')) {
        $database = new SiteIran_Database();
        $admin = new SiteIran_Admin();
        $scripts = new SiteIran_Scripts();

        // ثبت هوک فعال‌سازی
        register_activation_hook(__FILE__, [$database, 'activate']);
    } else {
        error_log("Class SiteIran_Database not found!");
        wp_die("خطا: کلاس SiteIran_Database پیدا نشد. لطفاً بررسی کنید که فایل class-siteiran-database.php در پوشه includes وجود دارد.");
    }
}
add_action('plugins_loaded', 'siteiran_init');