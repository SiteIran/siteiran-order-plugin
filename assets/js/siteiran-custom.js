jQuery(document).ready(function($) {
    // تابع برای مقداردهی اولیه یا مجدد Select2
    function initializeSelect2($elements) {
        $elements.select2({
            width: '50%' // حفظ استایل اصلی
        });
    }

    // مقداردهی اولیه به تمام منوهای کشویی موجود
    initializeSelect2($('.siteiran-product-select'));

    // افزودن محصول در فرم ثبت سفارش
    $('#siteiran-add-product').on('click', function() {
        var $newRow = $('.siteiran-product-row').first().clone();
        $newRow.find('input').val('1');
        $newRow.find('select').val('').removeClass('select2-hidden-accessible').next('.select2-container').remove(); // حذف Select2 قبلی
        $('#siteiran-products-container').append($newRow);
        initializeSelect2($newRow.find('.siteiran-product-select')); // مقداردهی مجدد Select2
    });

    // افزودن محصول در لیست سفارشات
    $('.siteiran-add-product').on('click', function() {
        var orderId = $(this).data('order-id');
        var $newRow = $('#siteiran-products-container-' + orderId + ' .siteiran-product-row').first().clone();
        $newRow.find('input').val('1');
        $newRow.find('select').val('').removeClass('select2-hidden-accessible').next('.select2-container').remove(); // حذف Select2 قبلی
        $('#siteiran-products-container-' + orderId).append($newRow);
        initializeSelect2($newRow.find('.siteiran-product-select')); // مقداردهی مجدد Select2
    });

    // حذف ردیف محصول
    $(document).on('click', '.siteiran-remove-product', function() {
        var $container = $(this).closest('.siteiran-products-container, #siteiran-products-container');
        if ($container.find('.siteiran-product-row').length > 1) {
            $(this).closest('.siteiran-product-row').remove();
        } else {
            alert('حداقل یک محصول باید باقی بماند.');
        }
    });
});