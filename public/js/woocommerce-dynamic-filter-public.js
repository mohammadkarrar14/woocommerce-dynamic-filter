(function($) {
    'use strict';

    $(document).ready(function() {
        

        // Show loader before AJAX request
        $('.filter-product-result').before(`
            <div class="product-overlay">
                <div class="ajax-loader"></div>
            </div>
        `);

        // Function to clear checkboxes and reset price range slider to default values
        function clearCheckboxes() {
            $('#main-category input[type="checkbox"]').prop('checked', false);
            $('#sub-category input[type="checkbox"]').prop('checked', false);
            $('#brands-filter input[type="checkbox"]').prop('checked', false);

            $("#price-range-slider").data("ionRangeSlider").update({
                min: 0,
                max: 50000,
                from: 0,
                to: 50000
            });

            $('#min-price').text('$' + 0);
            $('#max-price').text('$' + 50000);

            updateProductsAjax();
        }

        // Add event listener to Clear All button
        $('#clear-all').on('click', function() {
            clearCheckboxes();
        });

        // Set href attribute of pagination links to "#"
        $('.pagination a.page-numbers').attr('href', '#');

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
                         $('#sub-category input[type="checkbox"]').each(function() {
                            var subCategoryId = $(this).val(); // Assuming the value attribute contains the sub-category ID
                            if (subCategoryId === categoryInfo.subCategoryId) {
                                $(this).prop('checked', true).trigger('change');
                            }
                        });
                    }
                });

                updateBrands();
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
            onChange: function(data) {
                $('#min-price').text('$' + data.from);
                $('#max-price').text('$' + data.to);
                updateProductsAjax();
            }
        });
        updateFilters();

        // Function to update products via AJAX
        function updateProductsAjax() {
            var min = $('#min-price-hidden').val();
            var max = $('#max-price-hidden').val();
            var sortingOption = $('#sort-option').val();
            var selectedCategories = $('#main-category input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();
            var selectedSubCategories = $('#sub-category input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();
            var selectedBrandCategories = $('#brands input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();
            var page = $('#product-page-number').val();

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
                    page: page
                },
                success: function(response) {
                    console.log('testing', response);
                    $('.filter-product-result').html(response.result);
                    var displayedProducts = response.displayed_products;
                    var totalProducts = response.total_products;
                    if (displayedProducts > 0) {
                        $('.showing-info').html('Showing ' + displayedProducts + ' of ' + totalProducts);
                    }
                },
                error: function(xhr, status, error) {
                    console.log(error);
                },
                complete: function() {
                    $('.product-overlay').hide();
                }
            });
        }

        // Event handlers
        $(document).on('click', '.pagination-filter a.page-numbers', function(event) {
            event.preventDefault();
            updateProductsAjax();
        });

        $(document).on('change', '#main-category input[type="checkbox"]', function() {
            updateFilters();
            updatePriceRange();
            updateProductsAjax();
        });

        $(document).on('change', '#sort-option', function() {
            updateProductsAjax();
        });

        $(document).on('change', '#sub-category input[type="checkbox"]', function() {
            updateBrands();
            updatePriceRange();
            updateProductsAjax();
        });

        $(document).on('change', '#brands input[type="checkbox"]', function() {
            updatePriceRange();
            updateProductsAjax();
        });

        // Function to extract category information from the URL
        function extractCategoryInfoFromURL(url) {
            var parts = url.split('/');
            var parentCategory = parts[parts.length - 3];
            var subCategory = parts[parts.length - 2];
            return { parentCategory: parentCategory, subCategory: subCategory };
        }

        function extractBrandInfoFromURL(url) {
            var parts = url.split('/');
            var brand = '';

            // Find the index of 'brands' in the URL
            var brandIndex = parts.indexOf('brands');

            // If 'brands' found, get the next part as the brand name
            if (brandIndex !== -1 && brandIndex < parts.length - 1) {
                brand = parts[brandIndex + 1];
            }

            return brand;
        }

        // Function to select checkboxes for subcategory and its parent category
        function selectCategoryCheckboxes(parentCategory, subCategory) {
            var lowercaseParentCategory = parentCategory.toLowerCase();
            var lowercaseSubCategory = subCategory.toLowerCase();

            $('#main-category input[type="checkbox"]').each(function() {
                var label = $(this).next('label').text().trim().toLowerCase();
                if (label.includes(lowercaseParentCategory)) {
                    $(this).prop('checked', true).trigger('change');
                }
            });
        }

        // Function to uncheck checkboxes for the "brands" category
        function hideBrandsCheckbox(brandInfo) {
            $('#brands').hide();
            $('#brands input[type="checkbox"]').each(function() {
                var label = $(this).next('label').text().trim().toLowerCase();
                if (label.includes(brandInfo)) {
                    $(this).prop('checked', false).trigger('change');
                }
            });
        }

        // Select checkboxes based on extracted category information
        var currentURL = window.location.href;
        var categoryInfo = extractCategoryInfoFromURL(currentURL);
        var brandInfo = extractBrandInfoFromURL(currentURL);
        selectCategoryCheckboxes(categoryInfo.parentCategory, categoryInfo.subCategory);
        hideBrandsCheckbox(brandInfo);

    });
})(jQuery);
