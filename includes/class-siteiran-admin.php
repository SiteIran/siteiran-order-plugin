<?php
class SiteIran_Admin {
    private $database;

    public function __construct() {
        $this->database = new SiteIran_Database();
        add_action('admin_menu', [$this, 'create_admin_menu']);
    }

    // ایجاد منوهای ادمین
    public function create_admin_menu() {
        add_menu_page('ثبت سفارشات', 'ثبت سفارشات', 'manage_options', 'bulk-order', [$this, 'order_page'], 'dashicons-cart', 6);
        add_submenu_page('bulk-order', 'لیست سفارشات', 'لیست سفارشات', 'manage_options', 'order-list', [$this, 'order_list_page']);
        add_submenu_page('bulk-order', 'تنظیمات وضعیت‌ها', 'تنظیمات وضعیت‌ها', 'manage_options', 'order-status-settings', [$this, 'settings_page']);
    }

    // صفحه ثبت سفارش
    public function order_page() {
        $wpdb = $this->database->get_wpdb();
        if (isset($_POST['submit_order']) && check_admin_referer('siteiran_submit_order')) {
            $products = $this->process_products($_POST['product_ids'], $_POST['quantities']);
            $details = sanitize_textarea_field($_POST['details'] ?? '');

            if (!empty($products)) {
                $wpdb->insert($this->database->get_bulk_orders_table(), [
                    'user_id' => get_current_user_id(),
                    'order_data' => serialize($products),
                    'details' => $details,
                    'status' => 'pending'
                ]);
                echo "<div class='notice notice-success'><p>سفارش با موفقیت ثبت شد.</p></div>";
            } else {
                echo "<div class='notice notice-error'><p>لطفاً حداقل یک محصول انتخاب کنید.</p></div>";
            }
        }

        $template_path = plugin_dir_path(dirname(__FILE__)) . 'templates/order-form.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            error_log("Template file not found: " . $template_path);
            echo "<div class='notice notice-error'><p>خطا: فایل قالب order-form.php پیدا نشد.</p></div>";
        }
    }

    // صفحه لیست سفارشات
    public function order_list_page() {
        $wpdb = $this->database->get_wpdb();
        
        if (isset($_POST['edit_order']) && wp_verify_nonce($_POST['_wpnonce'], 'edit_order_nonce')) {
            $this->handle_edit_order();
        }

        if (isset($_GET['action']) && $_GET['action'] === 'print' && isset($_GET['order_id'])) {
            $this->print_order(intval($_GET['order_id']));
            return;
        }

        // دریافت سفارشات از دیتابیس
        $orders = $wpdb->get_results("SELECT * FROM {$this->database->get_bulk_orders_table()} ORDER BY created_at DESC");
        $status_options = $wpdb->get_results("SELECT status_name FROM {$this->database->get_status_table()}");
        
        $template_path = plugin_dir_path(dirname(__FILE__)) . 'templates/order-list.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            error_log("Template file not found: " . $template_path);
            echo "<div class='notice notice-error'><p>خطا: فایل قالب order-list.php پیدا نشد.</p></div>";
        }
    }

    // صفحه تنظیمات وضعیت‌ها
    public function settings_page() {
        $wpdb = $this->database->get_wpdb();
        
        if (isset($_POST['add_status']) && check_admin_referer('siteiran_add_status_nonce')) {
            $this->add_status();
        }

        if (isset($_POST['delete_status']) && wp_verify_nonce($_POST['_wpnonce'], 'delete_status_nonce')) {
            $this->delete_status();
        }

        $status_options = $wpdb->get_results("SELECT * FROM {$this->database->get_status_table()}");
        
        $template_path = plugin_dir_path(dirname(__FILE__)) . 'templates/status-settings.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            error_log("Template file not found: " . $template_path);
            echo "<div class='notice notice-error'><p>خطا: فایل قالب status-settings.php پیدا نشد.</p></div>";
        }
    }

    // پردازش محصولات فرم
    private function process_products($product_ids, $quantities) {
        $products = [];
        if (!is_array($product_ids) || !is_array($quantities)) {
            return $products; // اگر ورودی نامعتبر باشد، آرایه خالی برگردان
        }
    
        foreach ($product_ids as $index => $product_id) {
            $product_id = intval($product_id);
            $quantity = isset($quantities[$index]) ? intval($quantities[$index]) : 0;
            if ($product_id > 0 && $quantity > 0) {
                $products[] = ['product_id' => $product_id, 'quantity' => $quantity];
            }
        }
        return $products;
    }

    // ویرایش سفارش
    private function handle_edit_order() {
        $wpdb = $this->database->get_wpdb();
        $order_id = intval($_POST['order_id']);
        $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->database->get_bulk_orders_table()} WHERE id = %d", $order_id));
        
        if ($order && $order->status === 'pending') {
            $products = $this->process_products($_POST['product_ids'], $_POST['quantities']);
            $details = sanitize_textarea_field($_POST['details'] ?? '');
            $status = sanitize_text_field($_POST['status']);

            // به‌روزرسانی سفارش با تمام داده‌های جدید
            $wpdb->update(
                $this->database->get_bulk_orders_table(),
                [
                    'order_data' => serialize($products), // محصولات جدید
                    'details' => $details,
                    'status' => $status
                ],
                ['id' => $order_id],
                ['%s', '%s', '%s'],
                ['%d']
            );

            if ($wpdb->last_error) {
                error_log("Failed to update order $order_id: " . $wpdb->last_error);
                echo "<div class='notice notice-error'><p>خطا در ویرایش سفارش: " . esc_html($wpdb->last_error) . "</p></div>";
            } else {
                echo "<div class='notice notice-success'><p>سفارش با موفقیت ویرایش شد.</p></div>";
            }
        } else {
            echo "<div class='notice notice-error'><p>فقط سفارشات در وضعیت 'در انتظار' قابل ویرایش هستند.</p></div>";
        }
    }
    // چاپ سفارش
    private function print_order($order_id) {
        $wpdb = $this->database->get_wpdb();
        $order = $wpdb->get_row("SELECT * FROM {$this->database->get_bulk_orders_table()} WHERE id = $order_id");
        if (!$order) {
            wp_die('سفارش یافت نشد.');
        }
        $order_data = unserialize($order->order_data);
        $total = 0;
        ?>
        <div id="siteiran-print-content">
            <h1>فاکتور سفارش شماره <?php echo esc_html($order->id); ?></h1>
            <p><strong>تاریخ ثبت:</strong> <?php echo esc_html($order->created_at); ?></p>
            <p><strong>وضعیت:</strong> <?php echo esc_html($order->status); ?></p>
            <p><strong>توضیحات:</strong> <?php echo esc_html($order->details); ?></p>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>محصول</th>
                        <th>تعداد</th>
                        <th>قیمت واحد</th>
                        <th>جمع</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_data as $item) : 
                        $product = wc_get_product($item['product_id']);
                        if ($product) :
                            $price = $product->get_price();
                            $subtotal = $price * $item['quantity'];
                            $total += $subtotal;
                            ?>
                            <tr>
                                <td><?php echo esc_html($product->get_name()); ?></td>
                                <td><?php echo esc_html($item['quantity']); ?></td>
                                <td><?php echo wc_price($price); ?></td>
                                <td><?php echo wc_price($subtotal); ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3"><strong>جمع کل</strong></td>
                        <td><?php echo wc_price($total); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <script>
            window.onload = function() {
                window.print();
            }
        </script>
        <?php
        exit;
    }

    // افزودن وضعیت جدید
    private function add_status() {
        $wpdb = $this->database->get_wpdb();
        $status_name = sanitize_text_field($_POST['status_name']);
        if ($status_name && !$wpdb->get_var("SELECT COUNT(*) FROM {$this->database->get_status_table()} WHERE status_name = '$status_name'")) {
            $wpdb->insert($this->database->get_status_table(), ['status_name' => $status_name]);
            echo "<div class='notice notice-success'><p>وضعیت با موفقیت اضافه شد.</p></div>";
        } else {
            echo "<div class='notice notice-error'><p>وضعیت تکراری یا نامعتبر است.</p></div>";
        }
    }

    // حذف وضعیت
    private function delete_status() {
        $wpdb = $this->database->get_wpdb();
        $status_id = intval($_POST['status_id']);
        $status = $wpdb->get_var("SELECT status_name FROM {$this->database->get_status_table()} WHERE id = $status_id");
        if ($wpdb->get_var("SELECT COUNT(*) FROM {$this->database->get_bulk_orders_table()} WHERE status = '$status'") == 0) {
            $wpdb->delete($this->database->get_status_table(), ['id' => $status_id]);
            echo "<div class='notice notice-success'><p>وضعیت با موفقیت حذف شد.</p></div>";
        } else {
            echo "<div class='notice notice-error'><p>نمی‌توان وضعیتی که در سفارشات استفاده شده را حذف کرد.</p></div>";
        }
    }
}