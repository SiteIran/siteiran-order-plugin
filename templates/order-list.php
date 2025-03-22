<?php
// templates/order-list.php
?>
<div class="wrap">
    <h1>لیست سفارشات</h1>
    <?php if (empty($orders)) : ?>
        <p>هنوز سفارشی ثبت نشده است.</p>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>شماره سفارش</th>
                    <th>محصولات</th>
                    <th>جمع تعداد</th>
                    <th>وضعیت</th>
                    <th>توضیحات</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order) : 
                    $order_data = isset($order->order_data) ? unserialize($order->order_data) : [];
                    $total_quantity = is_array($order_data) ? array_sum(array_column($order_data, 'quantity')) : 0;
                    ?>
                    <tr>
                        <td><?php echo esc_html($order->id); ?></td>
                        <td>
                            <form method="post" id="edit-form-<?php echo esc_attr($order->id); ?>">
                                <div id="siteiran-products-container-<?php echo esc_attr($order->id); ?>" class="siteiran-products-container">
                                    <?php if (is_array($order_data) && !empty($order_data)) : ?>
                                        <?php foreach ($order_data as $index => $item) : ?>
                                            <div class="siteiran-product-row">
                                                <select class="siteiran-product-select" name="product_ids[<?php echo $index; ?>]" style="width: 50%;">
                                                    <option value="">انتخاب کنید</option>
                                                    <?php
                                                    $products = wc_get_products(['status' => 'publish', 'limit' => -1]);
                                                    foreach ($products as $product) {
                                                        $selected = (isset($item['product_id']) && $item['product_id'] == $product->get_id()) ? 'selected' : '';
                                                        echo '<option value="' . esc_attr($product->get_id()) . '" ' . $selected . '>' . esc_html($product->get_name()) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                                <input type="number" name="quantities[<?php echo $index; ?>]" min="1" value="<?php echo isset($item['quantity']) ? esc_attr($item['quantity']) : 1; ?>" required>
                                                <button type="button" class="siteiran-remove-product button">حذف</button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <p>داده‌ای برای نمایش وجود ندارد.</p>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="siteiran-add-product button" data-order-id="<?php echo esc_attr($order->id); ?>">افزودن محصول</button>
                        </td>
                        <td><?php echo esc_html($total_quantity); ?></td>
                        <td>
                            <select name="status">
                                <?php foreach ($status_options as $status) : ?>
                                    <option value="<?php echo esc_attr($status->status_name); ?>" <?php selected($order->status ?? '', $status->status_name); ?>><?php echo esc_html($status->status_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <textarea name="details"><?php echo esc_textarea($order->details ?? ''); ?></textarea>
                        </td>
                        <td>
                                <?php wp_nonce_field('edit_order_nonce', '_wpnonce'); ?>
                                <input type="hidden" name="order_id" value="<?php echo esc_attr($order->id); ?>">
                                <button type="submit" name="edit_order" class="button">ویرایش</button>
                            </form>
                            <a href="?page=order-list&action=print&order_id=<?php echo esc_attr($order->id); ?>" class="button">چاپ</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>