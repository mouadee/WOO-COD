jQuery(document).ready(function($) {
    // Extract numeric price from #product-price text safely
    var originalPriceText = $('#product-price').text().trim(); // e.g. "399 درهم"
    var originalPrice = parseFloat(originalPriceText.replace(/[^\d.]/g, ''));
    if (isNaN(originalPrice)) originalPrice = 0;

    // Calculate discounted price (buy 2 get 1 free)
    var discountedPrice = originalPrice * 2;

    // Handle bundle offer click
    $('#offer-container').on('click', function() {
        var $this = $(this);
        $this.toggleClass('selected');

        if ($this.hasClass('selected')) {
            $('#product-price').text(discountedPrice + ' درهم');
            $('#ccc-quantity').val(2); // Update quantity to 3 for the bundle
        } else {
            $('#product-price').text(originalPrice + ' درهم');
            $('#ccc-quantity').val(1); // Reset quantity to 1
        }
    });

    // Handle form submission with AJAX
    $('#ccc-checkout-form').on('submit', function(e) {
        e.preventDefault();
        $('#ccc-message').text('');

        // Serialize form data
        var data = $(this).serialize();

        // Append final price based on bundle selection
        var finalPrice = $('#offer-container').hasClass('selected') ? discountedPrice : originalPrice;
        data += '&final_price=' + encodeURIComponent(finalPrice);
        data += '&action=ccc_handle_order';

        // Send AJAX POST to server
        $.post(ccc_ajax.ajax_url, data, function(response) {
            if (response.success) {
                $('#ccc-message').css('color', 'green').text(response.data);
                $('#ccc-checkout-form')[0].reset();
                $('#ccc-quantity').val(1);
                $('#product-price').text(originalPrice + ' درهم');
                $('#offer-container').removeClass('selected');
            } else {
                $('#ccc-message').css('color', 'red').text(response.data);
            }
        });
    });

    // Quantity buttons logic
    var minQuantity = parseInt($('#ccc-quantity').attr('min')) || 1;
    var maxQuantity = parseInt($('#ccc-quantity').attr('max')) || 99;

    function updateQuantity(value) {
        if (value >= minQuantity && value <= maxQuantity) {
            $('#ccc-quantity').val(value);
        }
    }

    $('#ccc-minus').click(function() {
        var currentVal = parseInt($('#ccc-quantity').val(), 10);
        if (currentVal > minQuantity) {
            updateQuantity(currentVal - 1);
        }
    });

    $('#ccc-plus').click(function() {
        var currentVal = parseInt($('#ccc-quantity').val(), 10);
        if (currentVal < maxQuantity) {
            updateQuantity(currentVal + 1);
        }
    });

    // Set delivery days in Arabic
    const today = new Date();
    const day = today.getDay();
    const daysArabic = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
    $('#delivery-day-1').text(daysArabic[(day + 1) % 7]);
    $('#delivery-day-2').text(daysArabic[(day + 2) % 7]);
});
