<form id="woocommerce-dynamic-filters">
    <input type="hidden" name="current-brand-page" id="current-brand-page" value="<?php echo get_queried_object_id(); ?>">
    <div class="filter-class">
        <div class="container">
            <div class="title">Filters</div>
            <div class="filter-action" id="clear-all">Clear All</div>
        </div>
        <div class="showing-info"></div>
    </div>

    <div id="main-category" class="filter-box">
        <div class="filter-class">
            <div class="container-head">
                <div class="head-title">Categories</div>
                <div class="head-arrow"></div>
            </div>
        </div>
        <ul>
            <?php
                if (is_tax('brands')) {
                    $current_term_id = get_queried_object_id();
                    $current_category_id = 0;
                    // Fetch product categories efficiently
                    $product_categories_query = new WP_Term_Query(array(
                        'taxonomy'   => 'product_cat',
                        'parent'     => 0,
                        'hide_empty' => true,
                    ));
                    
                    if (!empty($product_categories_query->terms)) {
                        $product_categories = $product_categories_query->terms;
                        
                        // Function to retrieve products associated with the given brand and category
                        function get_products_for_brand_and_category($brand_id, $category_id) {
                            $args = array(
                                'post_type'      => 'product',
                                'posts_per_page' => -1,
                                'tax_query'      => array(
                                    'relation' => 'AND',
                                    array(
                                        'taxonomy' => 'brands',
                                        'field'    => 'term_id',
                                        'terms'    => $brand_id,
                                    ),
                                    array(
                                        'taxonomy' => 'product_cat',
                                        'field'    => 'term_id',
                                        'terms'    => $category_id,
                                    ),
                                ),
                            );

                            return new WP_Query($args);
                        }

                        // Loop through product categories
                        foreach ($product_categories as $category) {
                            // Fetch products associated with the current brand and category
                            $products_query = get_products_for_brand_and_category($current_term_id, $category->term_id);
                            $count = $products_query->post_count;

                            if ($count > 0) {
                                // Determine if the category should be checked
                                $checked = ($current_category_id == $category->term_id) ? 'checked' : '';
                                
                                // Output checkbox HTML
                                echo "<li>
                                        <input type='checkbox' name='main_category[]' value='{$category->term_id}' {$checked}>
                                        <label>{$category->name} ({$count})</label>
                                      </li>";
                            }
                        }
                    }
                }
                ?>
        </ul>
    </div>

    <div id="sub-category-filter" class="filter-box">
        <div class="filter-class">
            <div class="container-head">
                <div class="head-title">Sub-Categories</div>
                <div class="head-arrow"></div>
            </div>
        </div>
        <ul>
           <?php
           // Check if it's a taxonomy page for the 'brands' custom taxonomy
            if (is_tax('brands')) {
                // Get the current term ID for the 'brands' taxonomy
                $current_term_id = get_queried_object_id();

                // Get terms for the 'product_cat' taxonomy
                $product_categories = get_terms(array(
                    'taxonomy'   => 'product_cat',
                    'parent'     => 0,
                    'hide_empty' => true,
                ));

                foreach ($product_categories as $category) {
                    // Get subcategories of the current category
                    $subcategories = get_terms(array(
                        'taxonomy'   => 'product_cat',
                        'parent'     => $category->term_id,
                        'hide_empty' => true,
                    ));

                    foreach ($subcategories as $subcategory) {
                        // Retrieve products associated with both the current subcategory and the current brand
                        $args = array(
                            'post_type'      => 'product',
                            'posts_per_page' => -1,
                            'tax_query'      => array(
                                'relation' => 'AND', // Ensure both taxonomies are considered
                                array(
                                    'taxonomy' => 'product_cat',
                                    'field'    => 'term_id',
                                    'terms'    => $subcategory->term_id,
                                ),
                                array(
                                    'taxonomy' => 'brands',
                                    'field'    => 'term_id',
                                    'terms'    => $current_term_id, // Use the current brand term ID
                                ),
                            ),
                        );

                        $products_query = new WP_Query($args);
                        $count = $products_query->post_count;

                        // Check if products are found for the subcategory
                        if ($count > 0) {
                            // Output subcategory with product count
                            echo '<li>';
                            echo '<input type="checkbox" name="sub_category[]" value="' . $subcategory->term_id . '">';
                            echo '<label>' . $subcategory->name . ' (' . $count . ')</label>';
                            echo '</li>';
                        }
                    }
                }
            }
           ?> 
        </ul>
    </div>
    
    <?php
    // Initialize minimum and maximum prices
    $min_price = PHP_INT_MAX;
    $max_price = 0;

    // Get the current term ID for the 'brands' taxonomy
    $current_brand_id = get_queried_object_id();

    // Query products associated with the current brand
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'tax_query'      => array(
            array(
                'taxonomy' => 'brands',
                'field'    => 'term_id',
                'terms'    => $current_brand_id, // Use the current brand term ID
            ),
        ),
    );

    $products_query = new WP_Query($args);

    // Loop through the products to find minimum and maximum prices
    while ($products_query->have_posts()) {
        $products_query->the_post();
        global $product;

        // Get the product price
        $product_price = $product->get_regular_price();

        // Update minimum and maximum prices
        if ($product_price < $min_price) {
            $min_price = $product_price;
        }
        if ($product_price > $max_price) {
            $max_price = $product_price;
        }
    }

    // Reset post data
    wp_reset_postdata();



    // Output the minimum and maximum prices
    $min_price = 0;    
    ?>
    <div id="price-range" class="filter-box">
        <div class="filter-class">
            <div class="container-head">
                <div class="head-title">Price</div>
                <div class="head-arrow"></div>
            </div>
        </div>
        <input type="text" id="price-range-slider" name="price_range" />
        <p class="price-range">
            <span id="min-price">$<?php echo $min_price; ?></span>
            <span id="max-price">$<?php echo $max_price; ?></span>
            <input type="hidden" id="min-price-hidden" name="min-price" value="<?php echo $min_price; ?>">
            <input type="hidden" id="max-price-hidden" name="max-price" value="<?php echo $max_price; ?>">
            <input type="hidden" id="product-page-number" name="product-page-number" value="1">
        </p>
    </div>

</form>
