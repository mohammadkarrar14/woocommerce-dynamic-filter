(function($) {
    'use strict';


    $(document).ready(function() {
        
        updateProductsAjax();

        // Show loader before AJAX request
        $('.filter-product-result').before(`
            <div class="product-overlay">
                <div class="ajax-loader"></div>
            </div>
        `);
                
        $('#main-category input[type="checkbox"]').each(function() {
            var main_category_page_id = $('#main-category-page-id').val();
            if ($(this).val() === main_category_page_id && !$(this).prop('checked')) {
                $(this).prop('checked', true).trigger('change');
            }
        });

        $('input[name="main_category[]"]').change(function() {
            // Uncheck previously checked checkbox
            $('input[name="main_category[]"]').not(this).prop('checked', false);
            
            // Check if checkbox is checked
            if ($(this).is(':checked')) {
                // Get category link from corresponding hidden input field
                var categoryLink = $(this).siblings('input[name="category-link[]"]').val();
                // Redirect to category page
                window.location.href = categoryLink;
            }
        });



        // Function to clear checkboxes and reset price range slider
        function clearCheckboxes() {
            // $('#main-category input[type="checkbox"]').prop('checked', false);
            $('#sub-category input[type="checkbox"]').prop('checked', false);
            $('#brands-filter input[type="checkbox"]').prop('checked', false);
            $('#product-page-number').val(1);
            $("#price-range-slider").data("ionRangeSlider").update({
                min: 0,
                max: 50000,
                from: 0,
                to: 50000
            });
            $('#min-price').text('$0');
            $('#max-price').text('$50000');
            updateProductsAjax();
        }

        // Add event listener to Clear All button
        $('#clear-all').on('click', function() {
            clearCheckboxes();
            $('html, body').animate({scrollTop: $('.sorting-options').offset().top - 100}, 'fast');

        });

        // Set href attribute of pagination links to "#"
        $('.pagination-filter a.page-numbers').attr('href', '#');

        // Function to update subcategories and brands based on selected main category
        function updateFilters() {
            var checkedMainCategories = $('#main-category input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();

            if (checkedMainCategories.length > 0) {
                // Update subcategories for the selected main category
                $.ajax({
                    type: 'POST',
                    url: WDFVars.ajaxurl,
                    data: {
                        action: 'get_subcategories',
                        parent_cat_ids: checkedMainCategories
                    },
                    success: function(response) {
                        var subcategories = response;
                        var subcategoryCheckboxes = '';

                        subcategories.forEach(function(subcategory) {
                            // Construct the checkbox and its label
                            subcategoryCheckboxes += '<li>';
                            subcategoryCheckboxes += '<input type="checkbox" name="sub_category[]" value="' + subcategory.id + '" id="subcategory_' + subcategory.id + '">';
                            subcategoryCheckboxes += '<label style="margin-left: 10px;" for="subcategory_' + subcategory.id + '">' + subcategory.name + ' (' + subcategory.count + ')</label>';
                            subcategoryCheckboxes += '</li>';
                        });

                        $('#sub-category').html(subcategoryCheckboxes);


                        // Select subcategory checkbox if exists in URL
                        var sub_category_page_id = $('#sub-category-page-id').val();

                        $('#sub-category input[type="checkbox"]').each(function() {
                            if( $(this).val() === sub_category_page_id ){
                                $(this).prop('checked', true).trigger('change');
                            }
                        });
                    }
                });

                updateBrands();
                updatePriceRange();
                // $('html, body').animate({scrollTop: $('.sorting-options').offset().top - 100}, 'fast');

            } else {
                // Clear subcategories, brands, and price range if no main categories selected
                $('#sub-category').empty();
                $('#brands').empty();
                $("#price-range-slider").data("ionRangeSlider").update({
                    min: 0,
                    max: 50000,
                    from: 0,
                    to: 50000
                });
            }
        }

        // Function to update brands based on selected main category and subcategories
        function updateBrands() {
            var checkedSubCategories = $('#sub-category input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();

            var checkedMainCategories = $('#main-category input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();

            $.ajax({
                type: 'POST',
                url: WDFVars.ajaxurl,
                data: {
                    action: 'get_brands',
                    main_category_ids: checkedMainCategories,
                    sub_category_ids: checkedSubCategories,
                },
                success: function(response) {
                    var brands = response;
                    var brandCheckboxes = '';

                    brands.forEach(function(brand) {
                        // Construct the checkbox and its label
                        brandCheckboxes += '<li>';
                        brandCheckboxes += '<input type="checkbox" name="brands[]" value="' + brand.id + '" id="brand_' + brand.id + '">';
                        brandCheckboxes += '<label for="brand_' + brand.id + '">' + brand.name + ' (' + brand.count + ')</label>';
                        brandCheckboxes += '</li>';
                    });

                    $('#brands').html(brandCheckboxes);
                }
            });
        }

        // Function to update price range based on selected categories
        function updatePriceRange() {
            var selectedCategories = $('#main-category input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();

            var selectedSubCategories = $('#sub-category input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();

            var selectedBrandCategories = $('#brands input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedCategories.length === 0) {
                $("#price-range-slider").data("ionRangeSlider").update({
                    min: 0,
                    max: 50000,
                    from: 0,
                    to: 50000
                });

                $('#min-price').text('$' + 0);
                $('#max-price').text('$' + 50000);
            } else {
                $.ajax({
                    type: 'POST',
                    url: WDFVars.ajaxurl,
                    data: {
                        action: 'get_price_range',
                        category_ids: selectedCategories,
                        sub_category_ids: selectedSubCategories,
                        brand_category_ids: selectedBrandCategories,
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
                    }
                });
            }
        }

        // Initialize price range slider and update filters
        $("#price-range-slider").ionRangeSlider({
            type: "double",
            min: 0,
            max: 50000,
            from: 0,
            to: 50000,
            postfix: " $",
            onStart: function(data) {
                $('#min-price').text('$' + data.from);
                $('#max-price').text('$' + data.to);
            },
            onFinish: function(data) {
                $('#min-price').text('$' + data.from);
                $('#max-price').text('$' + data.to);
                $('#min-price-hidden').val( data.from);
                $('#max-price-hidden').val( data.to);

                updateProductsAjax();
            }
        });
        updateFilters();

        // Function to update products via AJAX
        function updateProductsAjax() {


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

            /*var selectedCategories = $('#main-category input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();
            */

            var selectedCategories = [];
            selectedCategories =  $('#main-category-page-id').val();
            var selectedSubCategories = $('#sub-category input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();

            var selectedBrandCategories = $('#brands input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();

            var page = $('#product-page-number').val();

            var search = $('#search-input-0e28eb2').val();

            $.ajax({
                type: 'POST',
                url: WDFVars.ajaxurl,
                data: {
                    action: 'update_products',
                    categories: selectedCategories,
                    sub_category: selectedSubCategories,
                    brand_category: selectedBrandCategories,
                    sort_option: sortingOption,
                    min_value: min,
                    max_value: max,
                    page: page,
                    search: search,
                },
                success: function(response) {
                    // Cache frequently accessed elements
                    var $filterProductResult = $('.filter-product-result');
                    var $customPagination = $('#custom-pagination-filter');
                    var $showingInfo = $('.showing-info');

                    // Fade out .filter-product-result quickly
                    $filterProductResult.fadeOut('fast', function() {
                        // Replace the HTML content and fade it back in quickly
                        $(this).html(response.result).fadeIn('fast', function() {
                            // Update the displayed product information after the animation is complete
                            var displayedProducts = response.displayed_products;
                            var totalProducts = response.total_products;
                            
                            // Update the showing info
                            if (displayedProducts > 0) {
                                $showingInfo.html('Showing ' + displayedProducts + ' of ' + totalProducts);
                            }

                            // Update the pagination content
                            $customPagination.html(response.pagination);
                        });
                    });


                },
                error: function(xhr, status, error) {
                },
                complete: function() {
                    $('.product-overlay').hide();
                }
            });
        }

        function updatePageNumber(clickedPage) {
            var page = clickedPage.text(); // Retrieve the current page number
            var pageNumber = $('#product-page-number').val();

            // Check if the clicked page number has the class 'next' or 'prev'
            if ($(clickedPage).hasClass('next')) {
                page = parseInt(pageNumber) + 1; // Increment the current page number by 1
            } else if ($(clickedPage).hasClass('prev')) {
                page = Math.max( parseInt( pageNumber ) - 1, 1); // Ensure the page number is not less than 1
            }

            $('#product-page-number').val(page); // Set the value of the '#product-page-number' input field to the updated page number
        }

        $(document).on('click', '.pagination-filter a.page-numbers', function(event) {
            event.preventDefault();
            var page = $(this);
            updatePageNumber( page );
            updateProductsAjax();
            $('html, body').animate({scrollTop: $('.sorting-options').offset().top - 100}, 'fast');

        });


        $(document).on('change', '#main-category input[type="checkbox"]', function() {
            $('#product-page-number').val(1);
            updateFilters();
            updatePriceRange();
            // updateProductsAjax();
        });

        $(document).on('change', '#sort-option', function() {
            updateProductsAjax();
        });

        $(document).on('change', '#sub-category input[type="checkbox"]', function() {
            $('#product-page-number').val(1);
            updateBrands();
            updatePriceRange();
            updateProductsAjax();

        });

        $(document).on('change', '#brands input[type="checkbox"]', function() {
            $('#product-page-number').val(1);
            updatePriceRange();
            updateProductsAjax();
            
        });

        $(document).on('click', '.mobile-filter-btn', function() {
            $('#woocommerce-dynamic-filters').slideToggle();
        });
        
    });
})(jQuery);
