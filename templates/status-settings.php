<?php
// templates/status-settings.php
?>
<div class="wrap">
    <h1>تنظیمات وضعیت‌ها</h1>
    <form method="post">
        <label>وضعیت جدید:</label>
        <input type="text" name="status_name" required>
        <?php wp_nonce_field('siteiran_add_status_nonce'); ?>
        <input type="submit" name="add_status" class="button button-primary" value="افزودن وضعیت">
    </form>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr><th>نام وضعیت</th><th>عملیات</th></tr>
        </thead>
        <tbody>
            <?php foreach ($status_options as $status) : ?>
                <tr>
                    <td><?php echo esc_html($status->status_name); ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="status_id" value="<?php echo esc_attr($status->id); ?>">
                            <?php wp_nonce_field('delete_status_nonce'); ?>
                            <button type="submit" name="delete_status" class="button">حذف</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>