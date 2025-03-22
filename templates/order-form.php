<?php
// templates/order-form.php
?>
<div class="wrap">
    <h1>ثبت سفارش جدید</h1>
    <form method="post" id="siteiran-order-form">
        <?php wp_nonce_field('siteiran_submit_order'); ?>
        <div id="siteiran-products-container">
            <div class="siteiran-product-row">
                <label>انتخاب محصول:</label>
                <select class="siteiran-product-select" name="product_ids[]" style="width: 50%;">
                    <option value="">انتخاب کنید</option>
                    <?php
                    $products = wc_get_products(['status' => 'publish', 'limit' => -1]);
                    foreach ($products as $product) {
                        echo '<option value="' . esc_attr($product->get_id()) . '">' . esc_html($product->get_name()) . '</option>';
                    }
                    ?>
                </select>
                <label>تعداد:</label>
                <input type="number" name="quantities[]" min="1" value="1" required>
                <button type="button" class="siteiran-remove-product button">حذف</button>
            </div>
        </div>
        <button type="button" id="siteiran-add-product" class="button">افزودن محصول</button>
        <p><label>توضیحات:</label><br><textarea name="details" rows="4" style="width: 100%;"></textarea></p>
        <input type="submit" name="submit_order" class="button button-primary" value="ثبت سفارش">
    </form>
</div>