<form id="woocommerce-dynamic-filters">
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
                // If not a taxonomy page for 'brands', show all categories
                $product_categories = get_terms(array(
                    'taxonomy'   => 'product_cat',
                    'parent'     => 0,
                    'hide_empty' => true,
                ));

                // Get the current product category ID
                $current_category_id = is_tax('product_cat') ? get_queried_object_id() : 0;

                foreach ($product_categories as $category) {
                    $args = array(
                        'post_type'      => 'product',
                        'posts_per_page' => -1,
                        'tax_query'      => array(
                            array(
                                'taxonomy' => 'product_cat',
                                'field'    => 'term_id',
                                'terms'    => $category->term_id,
                            ),
                        ),
                    );

                    $products_query = new WP_Query($args);
                    $count = $products_query->post_count;

                    // Check if current category matches
                    $checked = ($current_category_id == $category->term_id) ? 'checked' : '';
                    
                    // Add a link to redirect to the category
                    $category_link = get_term_link($category->term_id, 'product_cat');
                    echo '<li>';
                    echo '<input type="checkbox" name="main_category[]" value="' . $category->term_id . '" ' . $checked . '>';
                    echo '<input type="hidden" name="category-link[]" value="' . esc_url($category_link) . '" >'; 
                    echo '<label>' . $category->name . ' (' . $count . ')</label>';
                    echo '</li>';

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
        <ul id="sub-category"></ul>
        <!-- Subcategory checkboxes will be dynamically populated here -->
    </div>


    <!-- Add other filters like Brands, Sort, Price Range here -->
    <div class="filter-box" id="brands-filter">
        <div class="filter-class">
            <div class="container-head">
                <div class="head-title">Brands</div>
                <div class="head-arrow"></div>
            </div>
        </div>
        <ul id="brands">
            <!-- Brands checkboxes will be dynamically populated here -->
        </ul>
    </div>    

    <div id="price-range" class="filter-box">
        <div class="filter-class">
            <div class="container-head">
                <div class="head-title">Price</div>
                <div class="head-arrow"></div>
            </div>
        </div>
        <input type="text" id="price-range-slider" name="price_range" />
        <p class="price-range">
            <span id="min-price">$0</span>
            <span id="max-price">$50000</span>
            <input type="hidden" id="min-price-hidden" name="min-price" value="0">
            <input type="hidden" id="max-price-hidden" name="max-price" value="50000">
            <input type="hidden" id="product-page-number" name="product-page-number" value="1">
        </p>
    </div>

   <?php
    // Get the current term object
    $current_term = get_queried_object();

    // Initialize variables to store main category ID and subcategory ID
    $main_category_id = $current_term->parent == 0 ? $current_term->term_id : get_term($current_term->parent, 'product_cat')->term_id;
    $sub_category_id = $current_term->term_id;
    ?>

    <input type="hidden" name="main-category-page-id" id="main-category-page-id" value="<?php echo $main_category_id; ?>">
    <input type="hidden" name="sub-category-page-id" id="sub-category-page-id" value="<?php echo $sub_category_id; ?>">
</form>
