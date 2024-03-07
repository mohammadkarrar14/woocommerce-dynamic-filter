(function($) {
    'use strict';

    $(document).ready(function() {
        
        updateBrandProductsAjax();
        
        // Show loader before AJAX request
        $('.filter-product-result').before(`
            <div class="product-overlay">
                <div class="ajax-loader"></div>
            </div>
        `);

        // Function to clear checkboxes and reset price range slider to default values
        function clearCheckboxes() {
            $('#main-category input[type="checkbox"]').prop('checked', false);
            $('#sub-category-filter input[type="checkbox"]').prop('checked', false);
            updateBrandProductsAjax();
        }

        // Add event listener to Clear All button
        $('#clear-all').on('click', function() {
            clearCheckboxes();
        });

        // Set href attribute of pagination links to "#"
        $('.pagination-filter a.page-numbers').attr('href', '#');

        // Function to update price range based on selected categories
        function updateBrandPriceRange() {
            var brand = $('#current-brand-page').val();
            var selectedCategories = $('#main-category input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();

            var selectedSubCategories = $('#sub-category-filter input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedCategories.length === 0) {
                return; // Exit if no categories are selected
            }

            $.ajax({
                type: 'POST',
                url: WDFVars.ajaxurl,
                data: {
                    action: 'get_brands_price_range',
                    category_ids: selectedCategories,
                    sub_category_ids: selectedSubCategories,
                    brand: brand,
                    nonce: WDFVars.nonce // Add nonce to the request
                },
                success: function(response) {
                    var minPrice = parseFloat(response.min_price);
                    var maxPrice = parseFloat(response.max_price);

                    $("#price-range-slider").data("ionRangeSlider").update({
                        min: minPrice,
                        max: maxPrice,
                        from: minPrice,
                        to: maxPrice
                    });

                    $('#min-price').text('$' + minPrice);
                    $('#max-price').text('$' + maxPrice);
                    $('#min-price-hidden').val(minPrice);
                    $('#max-price-hidden').val(maxPrice);
                }
            });
        }

        // Initialize price range slider and update filters
        $("#price-range-slider").ionRangeSlider({
            type: "double",
            min: 0,
            max: $('#max-price-hidden').val(),
            from: $('#min-price-hidden').val(),
            to: $('#max-price-hidden').val(),
            postfix: " $",
            onStart: function (data) {
                $('#min-price').text('$' + data.from);
                $('#max-price').text('$' + data.to);
            },
            onChange: function (data) {
                $('#min-price').text('$' + data.from);
                $('#max-price').text('$' + data.to);
                $('#min-price-hidden').val(data.from);
                $('#max-price-hidden').val(data.to);
                updateBrandProductsAjax();
            }
        });

        // Function to update products via AJAX
        function updateBrandProductsAjax() {

            // Get the height and width of .custom-product-grid
            var productsHeight = $('.filter-product-result').height();
            var productsWidth = $('.filter-product-result').width();

            // Apply CSS to .product-overlay with dynamic height
            $('.product-overlay').css({
                'height': productsHeight + 'px',
                'width': productsWidth + 'px',
                'display': 'block',
                'z-index': '9'
            });
            
            var min = $('#min-price-hidden').val();
            var max = $('#max-price-hidden').val();
            var sortingOption = $('#sort-option').val();
            var selectedCategories = $('#main-category input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();
            var selectedSubCategories = $('#sub-category-filter input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();
            var page = $('#product-page-number').val();
            var brand = $('#current-brand-page').val();

            $.ajax({
                type: 'POST',
                url: WDFVars.ajaxurl,
                data: {
                    action: 'update_brands_products',
                    categories: selectedCategories,
                    sub_category: selectedSubCategories,
                    sort_option: sortingOption,
                    min_value: min,
                    max_value: max,
                    page: page,
                    brand: brand,
                    nonce: WDFVars.nonce // Add nonce to the request
                },
                success: function(response) {
                    $('.filter-product-result').html('');
                    $('.filter-product-result').fadeOut('slow', function() {
                        // Replace the HTML content and fade it back in
                        $(this).html(response.result + response.pagination).fadeIn('slow', function() {
                            // Update the displayed product information after the animation is complete
                            var displayedProducts = response.displayed_products;
                            var totalProducts = response.total_products;
                            if (displayedProducts > 0) {
                                $('.showing-info').html('Showing ' + displayedProducts + ' of ' + totalProducts);
                            }
                        });
                    });
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText); // Log the error response
                },
                complete: function() {
                    $('.product-overlay').hide();
                }
            });
        }

        // Pagination click event
        $(document).on('click', '.pagination-filter a.page-numbers', function(event) {
            event.preventDefault();
            $('.pagination-filter a.page-numbers').removeClass('current');
            $(this).addClass('current');
            var page = $(this).text();
            $('#product-page-number').val(page);
            updateBrandProductsAjax();
        });

        // Main category change event
        $(document).on('change', '#main-category input[type="checkbox"]', function() {
            updateBrandPriceRange();
            updateBrandProductsAjax();
        });

        // Sub-category change event
        $(document).on('change', '#sub-category-filter input[type="checkbox"]', function() {
            updateBrandPriceRange();
            updateBrandProductsAjax();
        });

        // Sorting option change event
        $(document).on('change', '#sort-option', function() {
            updateBrandProductsAjax();
        });

        // Mobile filter button click event
        $(document).on('click', '.mobile-filter-btn', function() {
            $('#woocommerce-dynamic-filters').slideToggle();
        });
    });
})(jQuery);
