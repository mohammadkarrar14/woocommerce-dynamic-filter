(function($) {
    'use strict';

    $(document).ready(function() {
        var mainCategoryName = $('#main-category-name').val();
        var subCategoryName = $('#sub-category-name').val();
        var mainCategoryID = $('#main-category-page-id').val();
        var subCategoryID = $('#sub-category-page-id').val();
        
        var tagPageID = $('#tag-page-id').val();

        if (mainCategoryID === subCategoryID) {
            updateProductsAjax();
        }

        // Show loader before AJAX request
        $('.filter-product-result').before(`
            <div class="product-overlay">
                <div class="ajax-loader"></div>
            </div>
        `);

        if (mainCategoryName !== subCategoryName) {
            var headingText = subCategoryName + ' / ' + mainCategoryName;
            $('#sub-category-page-heading').html('<h2 class="elementor-heading-title elementor-size-default">' + headingText + '</h2>');
        }

        $('#main-category input[type="checkbox"]').each(function() {
            var main_category_page_id = $('#main-category-page-id').val();
            if ($(this).val() === main_category_page_id && !$(this).prop('checked')) {
                $(this).prop('checked', true).trigger('change');
            }
        });

        function clearCheckboxes() {
            var shopPageUrl = $('#shop-page-id').val();
            window.location.href = shopPageUrl;
        }

        $('#clear-all').on('click', function() {
            clearCheckboxes();
            $('html, body').animate({scrollTop: $('.sorting-options').offset().top - 100}, 'fast');
        });

        $('.pagination-filter a.page-numbers').attr('href', '#');

        function updateFilters() {
            var checkedMainCategories = $('#main-category input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();

            if (checkedMainCategories.length > 0) {
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
                            subcategoryCheckboxes += '<li>';
                            subcategoryCheckboxes += '<input type="checkbox" name="sub_category[]" value="' + subcategory.id + '" id="subcategory_' + subcategory.id + '">';
                            subcategoryCheckboxes += '<label style="margin-left: 10px;" for="subcategory_' + subcategory.id + '">' + subcategory.name + ' (' + subcategory.count + ')</label>';
                            subcategoryCheckboxes += '</li>';
                        });

                        $('#sub-category').html(subcategoryCheckboxes);

                        var sub_category_page_id = $('#sub-category-page-id').val();

                        $('#sub-category input[type="checkbox"]').each(function() {
                            if ($(this).val() === sub_category_page_id) {
                                $(this).prop('checked', true).trigger('change');
                            }
                        });
                    }
                });

                updateBrands();
                updatePriceRange();
            } else {
                $('#sub-category').empty();
                $('#brands').empty();
                $("#price-range-slider").data("ionRangeSlider").update({
                    min: 0,
                    max: 100000,
                    from: 0,
                    to: 100000
                });
            }
        }

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
                        brandCheckboxes += '<li>';
                        brandCheckboxes += '<input type="checkbox" name="brands[]" value="' + brand.id + '" id="brand_' + brand.id + '">';
                        brandCheckboxes += '<label for="brand_' + brand.id + '">' + brand.name + ' (' + brand.count + ')</label>';
                        brandCheckboxes += '</li>';
                    });

                    $('#brands').html(brandCheckboxes);
                }
            });
        }

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
                    max: 100000,
                    from: 0,
                    to: 100000
                });

                $('#min-price').text('$' + 0);
                $('#max-price').text('$' + 100000);
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

        $("#price-range-slider").ionRangeSlider({
            type: "double",
            min: 0,
            max: 100000,
            from: 0,
            to: 100000,
            postfix: " $",
            onStart: function(data) {
                $('#min-price').text('$' + data.from);
                $('#max-price').text('$' + data.to);
            },
            onFinish: function(data) {
                $('#min-price').text('$' + data.from);
                $('#max-price').text('$' + data.to);
                $('#min-price-hidden').val(data.from);
                $('#max-price-hidden').val(data.to);

                updateProductsAjax();
            }
        });

        updateFilters();

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

            var selectedCategories = [];
            selectedCategories = $('#main-category-page-id').val();
            var selectedSubCategories = $('#sub-category input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();

            var selectedBrandCategories = $('#brands input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();

            var page = $('#product-page-number').val();
            var search = $('#search-filter-input').val();

            $.ajax({
                type: 'POST',
                url: WDFVars.ajaxurl,
                data: {
                    action: 'update_products',
                    categories: selectedCategories,
                    sub_category: selectedSubCategories,
                    brand_category: selectedBrandCategories,
                    tag_id: tagPageID,
                    sort_option: sortingOption,
                    min_value: min,
                    max_value: max,
                    page: page,
                    search: search,
                },
                success: function(response) {
                    var $filterProductResult = $('.filter-product-result');
                    var $customPagination = $('#custom-pagination-filter');
                    var $showingInfo = $('.showing-info');

                    $filterProductResult.fadeOut('fast', function() {
                        $(this).html(response.result).fadeIn('fast', function() {
                            var displayedProducts = response.displayed_products;
                            var totalProducts = response.total_products;

                            if (displayedProducts > 0) {
                                $showingInfo.html('Showing ' + displayedProducts + ' of ' + totalProducts);
                            }

                            $customPagination.html(response.pagination);
                        });
                    });
                },
                error: function(xhr, status, error) {},
                complete: function() {
                    $('.product-overlay').hide();
                    $('.elementor-22063 .elementor-heading-title a').each(function() {
                        var newText = $(this).text().replace(/\//g, '-');
                        $(this).text(newText);
                    });
                }
            });
        }

        function updatePageNumber(clickedPage) {
            var page = clickedPage.text();
            var pageNumber = $('#product-page-number').val();

            if ($(clickedPage).hasClass('next')) {
                page = parseInt(pageNumber) + 1;
            } else if ($(clickedPage).hasClass('prev')) {
                page = Math.max(parseInt(pageNumber) - 1, 1);
            }

            $('#product-page-number').val(page);
        }

        $(document).on('click', '.pagination-filter a.page-numbers', function(event) {
            event.preventDefault();
            var page = $(this);
            updatePageNumber(page);
            updateProductsAjax();
            $('html, body').animate({scrollTop: $('.sorting-options').offset().top - 100}, 'fast');
        });

        $(document).on('change', '#main-category input[type="checkbox"]', function() {
            // Get the URL from the corresponding hidden input
			var url = $(this).siblings('input[name="category-link[]"]').val();
			console.log('working..');
			// Navigate to the URL
			window.location.href = url;
			$('#product-page-number').val(1);
            updateFilters();
            updatePriceRange();
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
