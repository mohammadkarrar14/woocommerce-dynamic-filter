<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://muhammadkarrar.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Dynamic_Filter
 * @subpackage Woocommerce_Dynamic_Filter/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woocommerce_Dynamic_Filter
 * @subpackage Woocommerce_Dynamic_Filter/public
 * @author     Muhammad Karrar <mohammad.karrar1995@hotmail.com>
 */
class Woocommerce_Dynamic_Filter_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
	    // Set plugin name and version
	    $this->plugin_name = $plugin_name;
	    $this->version = $version;

	    // Shortcodes for dynamic filters
	    add_shortcode('woocommerce_dynamic_filters', [ $this, 'woocommerce_dynamic_filters_shortcode' ] );
	    add_shortcode('woocommerce_sorting_filters', [ $this, 'woocommerce_dynamic_sorting_filters_shortcode' ] );
	    add_shortcode('woocommerce_brand_filters', [ $this, 'woocommerce_dynamic_brand_filters_shortcode' ] );

	    // AJAX actions for updating products and getting subcategories
	    add_action('wp_ajax_update_products', [ $this, 'update_products_callback' ] );
	    add_action('wp_ajax_nopriv_update_products', [ $this, 'update_products_callback' ] );
	    add_action('wp_ajax_get_subcategories', [ $this, 'get_subcategories_ajax' ] );
	    add_action('wp_ajax_nopriv_get_subcategories', [ $this, 'get_subcategories_ajax' ] );

	    // AJAX actions for getting price range and brands
	    add_action('wp_ajax_get_price_range', [ $this, 'get_price_range_ajax'] );
	    add_action('wp_ajax_nopriv_get_price_range', [ $this, 'get_price_range_ajax' ] );
	    add_action('wp_ajax_get_brands', [ $this, 'get_brands_ajax' ] );
	    add_action('wp_ajax_nopriv_get_brands', [ $this, 'get_brands_ajax' ] );

	    /*=========================== Brands Filters AJAX ==============================================*/
	    // AJAX actions for updating products with brand filters and getting brand subcategories
	    add_action('wp_ajax_update_brands_products', [ $this, 'update_brands_products_callback' ] );
	    add_action('wp_ajax_nopriv_update_brands_products', [ $this, 'update_brands_products_callback' ] );
	    add_action('wp_ajax_get_brands_subcategories', [ $this, 'get_brands_subcategories_ajax' ] );
	    add_action('wp_ajax_nopriv_get_brands_subcategories', [ $this, 'get_brands_subcategories_ajax' ] );
	    
	    // AJAX actions for getting price range with brand filters
	    add_action('wp_ajax_get_brands_price_range', [ $this, 'get_brands_price_range_ajax'] );
	    add_action('wp_ajax_nopriv_get_brands_price_range', [ $this, 'get_brands_price_range_ajax' ] );
	    /*=========================== Brands Filters AJAX ==============================================*/

	    // AJAX actions for getting product count message
	    add_action('wp_ajax_get_product_count_message', [ $this, 'get_product_count_message' ] );
	    add_action('wp_ajax_nopriv_get_product_count_message', [ $this, 'get_product_count_message' ] );
	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		$random_number = rand(); // Generate a random number
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce-dynamic-filter-public.css?v=' . $random_number, array(), $this->version, 'all' );

		
		wp_enqueue_style('ion-rangeslider-css', 'https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/css/ion.rangeSlider.min.css');

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
	    $random_number = rand(); // Generate a random number
	    
	    // Enqueue ion-rangeslider-js
	    wp_enqueue_script('ion-rangeslider-js', 'https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/js/ion.rangeSlider.min.js', array('jquery'), null, true);
	    
	    // Check if it's a taxonomy page for 'brands'
	    if (is_tax('brands')) {
	        // Enqueue wdf-brand-script with a random number added to the URL
	        wp_enqueue_script( 'wdf-brand-script', plugin_dir_url( __FILE__ ) . 'js/woocommerce-brand-filter-public.js?v=' . $random_number, array( 'jquery' ), $this->version, false );

	        // Get localized data to pass to scripts
	        $data = $this->get_localize_data();

	        // Localize wdf-brand-script with the localized data
	        wp_localize_script( 'wdf-brand-script', 'WDFVars', $data );
	    } else {
	        // Enqueue wdf-category-script with a random number added to the URL
	        wp_enqueue_script( 'wdf-category-script', plugin_dir_url( __FILE__ ) . 'js/woocommerce-dynamic-filter-public-v2.js?v=' . $random_number, array( 'jquery' ), $this->version, false );

	        // Get localized data to pass to scripts
	        $data = $this->get_localize_data();

	        // Localize wdf-category-script with the localized data
	        wp_localize_script( 'wdf-category-script', 'WDFVars', $data );
	    }
	}

	/**
	 * Get the localized data for JavaScript.
	 *
	 * @return array Localized data.
	 */
	public function get_localize_data() {
		$data = array(
			'ajaxurl'      	=> esc_url( admin_url( 'admin-ajax.php' ) ),
			'siteURL'      	=> site_url(),
			'nonce'  		=> wp_create_nonce( 'Woocommerce_Dynamic_Filter' ),
			'err_msg1'     	=> __( 'Something went wrong!', 'woocommerce-dynamic-filter' ),
		);

		return $data;
	}

	// Register shortcode for dynamic filters
	public function woocommerce_dynamic_filters_shortcode($atts) {
	    ob_start(); // Start output buffering

	    // Include HTML markup for the filters
	    include_once( __DIR__  . '/partials/dynamic-filters.php');


	    $output = ob_get_clean(); // Get the buffered content and clean the buffer
	    return $output; // Return the filtered output
	}

	// Register shortcode for dynamic sorting filters
	public function woocommerce_dynamic_sorting_filters_shortcode($atts) {
	    ob_start(); // Start output buffering

	    // Include HTML markup for the filters
	    include_once( __DIR__  . '/partials/sorting-filters.php');


	    $output = ob_get_clean(); // Get the buffered content and clean the buffer
	    return $output; // Return the filtered output
	}

	// Register shortcode for dynamic sorting filters
	public function woocommerce_dynamic_brand_filters_shortcode($atts) {
	    ob_start(); // Start output buffering

	    // Include HTML markup for the filters
	    include_once( __DIR__  . '/partials/brand-filters.php');


	    $output = ob_get_clean(); // Get the buffered content and clean the buffer
	    return $output; // Return the filtered output
	}

	public function get_subcategories_ajax() {
	    $parent_cat_ids = isset($_POST['parent_cat_ids']) ? $_POST['parent_cat_ids'] : array();

	    $subcategories_data = array();
	    foreach ($parent_cat_ids as $parent_cat_id) {
	        $subcategories = get_terms(array(
	            'taxonomy' => 'product_cat',
	            'parent' => $parent_cat_id,
	            'hide_empty' => true,
	        ));

	        foreach ($subcategories as $subcategory) {
	            $args = array(
	                'post_type' => 'product',
	                'post_status' => 'publish',
	                'posts_per_page' => -1,
	                'tax_query' => array(
	                    array(
	                        'taxonomy' => 'product_cat',
	                        'field' => 'term_id',
	                        'terms' => $subcategory->term_id,
	                        'operator' => 'IN',
	                    ),
	                ),
	            );

	            $products_query = new WP_Query($args);
	            $subcategory_count = $products_query->found_posts;

	            $subcategories_data[] = array(
	                'id' => $subcategory->term_id,
	                'name' => $subcategory->name,
	                'count' => $subcategory_count
	            );
	        }
	    }

	    wp_send_json($subcategories_data);
	    wp_die(); // Terminate script execution after sending the JSON response

	}


	public function get_price_range_ajax() {
	    $category_ids = isset($_POST['category_ids']) ? $_POST['category_ids'] : array();
	    $sub_category_ids = isset($_POST['sub_category_ids']) ? $_POST['sub_category_ids'] : array();
	    $brand_category_ids = isset($_POST['brand_category_ids']) ? $_POST['brand_category_ids'] : array();

	    // Tax query based on selected categories or subcategories
	    $tax_query = array(
	        'taxonomy' => 'product_cat',
	        'field' => 'term_id',
	        'terms' => !empty($sub_category_ids) ? $sub_category_ids : $category_ids,
	        'operator' => 'IN',
	    );

	    // Tax query for brands
	    $brand_tax_query = array();
	    if (!empty($brand_category_ids)) {
	        $brand_tax_query = array(
	            'taxonomy' => 'brands', // Replace with your brand taxonomy name
	            'field' => 'term_id',
	            'terms' => $brand_category_ids,
	            'operator' => 'IN',
	        );
		    // Main query arguments
		    $args = array(
		        'post_type' => 'product',
		        'post_status' => 'publish',
		        'posts_per_page' => -1,
		        'tax_query' => array(
		            'relation' => 'AND', // Ensure both category and brand filters are applied
		            $tax_query,
		            $brand_tax_query,
		        ),
		    );
	    } else {

		    // Main query arguments
		    $args = array(
		        'post_type' => 'product',
		        'post_status' => 'publish',
		        'posts_per_page' => -1,
		        'tax_query' => array(
		            'relation' => 'OR', // Ensure both category and brand filters are applied
		            $tax_query,
		            $brand_tax_query,
		        ),
		    );
	    }


	    // Query products
	    $products_query = new WP_Query($args);

	    // Initialize prices array
	    $prices = array();

	    // Loop through products and retrieve regular prices
	    if ($products_query->have_posts()) {
	        while ($products_query->have_posts()) {
	            $products_query->the_post();
	            $product = wc_get_product(get_the_ID());
	            $price = $product->get_regular_price();
	            $prices[] = $price;
	        }
	        wp_reset_postdata();
	    }

	    // Calculate min and max prices
	    $min_price = !empty($prices) ? min($prices) : 0;
	    $max_price = !empty($prices) ? max($prices) : 0;

	    // Prepare price range array
	    $price_range = array(
	        'min_price' => $min_price,
	        'max_price' => $max_price,
	    );

	    // Send JSON response
	    wp_send_json($price_range);
	    wp_die(); // Terminate script execution after sending the JSON response

	}

	public function get_brands_price_range_ajax() {

	    // Verify nonce for security
	    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
	    if (!wp_verify_nonce($nonce, 'Woocommerce_Dynamic_Filter')) {
	        $response['error'] = 'Nonce verification failed';
	        wp_send_json_error($response);
	    }

	    $category_ids = isset($_POST['category_ids']) ? $_POST['category_ids'] : array();
	    $sub_category_ids = isset($_POST['sub_category_ids']) ? $_POST['sub_category_ids'] : array();
	    $brand = isset($_POST['brand']) ? $_POST['brand'] : array();

	    // Tax query based on selected categories or subcategories
	    $tax_query = array(
	        'taxonomy' => 'product_cat',
	        'field' => 'term_id',
	        'terms' => !empty($sub_category_ids) ? $sub_category_ids : $category_ids,
	        'operator' => 'IN',
	    );

	    // Tax query for brands
	    $brand_tax_query = array(
	        'taxonomy' => 'brands', // Replace with your brand taxonomy name
	        'field' => 'term_id',
	        'terms' => array($brand),
	        'operator' => 'IN',
	    );

	    // Main query arguments
	    $args = array(
	        'post_type' => 'product',
	        'post_status' => 'publish',
	        'posts_per_page' => -1,
	        'tax_query' => array(
	            'relation' => 'AND', // Ensure both category and brand filters are applied
	            $tax_query,
	            $brand_tax_query,
	        ),
	    );

	    // Query products
	    $products_query = new WP_Query($args);

	    // Initialize prices array
	    $prices = array();

	    // Loop through products and retrieve regular prices
	    if ($products_query->have_posts()) {
	        while ($products_query->have_posts()) {
	            $products_query->the_post();
	            $product = wc_get_product(get_the_ID());
	            $price = $product->get_regular_price();
	            $prices[] = $price;
	        }
	        wp_reset_postdata();
	    }

	    // Calculate min and max prices
	    $min_price = !empty($prices) ? min($prices) : 0;
	    $max_price = !empty($prices) ? max($prices) : 0;

	    // Prepare price range array
	    $price_range = array(
	        'min_price' => $min_price,
	        'max_price' => $max_price,
	    );

	    // Send JSON response
	    wp_send_json($price_range);
	    wp_die(); // Terminate script execution after sending the JSON response
	}


	/**
	 * Retrieves brands associated with the selected categories or subcategories along with the number of products.
	 */
	public function get_brands_ajax() {
	    // Retrieve the main and subcategory IDs from the AJAX request
	    $main_category_ids = isset($_POST['main_category_ids']) ? $_POST['main_category_ids'] : array();
	    $sub_category_ids = isset($_POST['sub_category_ids']) ? $_POST['sub_category_ids'] : array();

	    // Prepare tax query based on selected categories or subcategories
	    $tax_query = array();

	    // Add subcategories to the tax query if available, otherwise use main categories
	    if (!empty($sub_category_ids)) {
	        foreach ($sub_category_ids as $sub_category_id) {
	            $tax_query[] = array(
	                'taxonomy' => 'product_cat',
	                'field' => 'term_id',
	                'terms' => $sub_category_id,
	                'operator' => 'IN',
	            );
	        }
	    } elseif (!empty($main_category_ids)) {
	        foreach ($main_category_ids as $main_category_id) {
	            $tax_query[] = array(
	                'taxonomy' => 'product_cat',
	                'field' => 'term_id',
	                'terms' => $main_category_id,
	                'operator' => 'IN',
	            );
	        }
	    }

	    // Query to get brands associated with the selected categories or subcategories
	    $brands_query = new WP_Term_Query(array(
	        'taxonomy' => 'brands', // Replace 'your_brand_taxonomy' with the actual taxonomy name for brands
	        'hide_empty' => true,
	    ));

	    // Prepare an array to store brands data
	    $brands = array();

	    // Loop through brands query results and build brands array
	    if (!empty($brands_query->terms)) {
	        foreach ($brands_query->terms as $brand) {
	            // Get the number of products associated with the brand within selected categories or subcategories
	            $product_count = 0;

	            // Loop through the selected categories or subcategories
	            foreach ($tax_query as $query) {
	                $args = array(
	                    'post_type' => 'product',
	                    'posts_per_page' => -1,
	                    'tax_query' => array(
	                        'relation' => 'AND',
	                        $query,
	                        array(
	                            'taxonomy' => 'brands',
	                            'field' => 'term_id',
	                            'terms' => $brand->term_id,
	                            'operator' => 'IN',
	                        ),
	                    ),
	                );
	                $products_query = new WP_Query($args);
	                $product_count += $products_query->found_posts;
	            }

	            // Only include brands with associated products in selected categories or subcategories
	            if ($product_count > 0) {
	                // Build brand data array with brand ID, name, and product count
	                $brands[] = array(
	                    'id' => $brand->term_id,
	                    'name' => $brand->name,
	                    'count' => $product_count,
	                );
	            }
	        }
	    }

	    // Return brands data as JSON response
	    wp_send_json($brands);
	    wp_die(); // Terminate script execution after sending the JSON response
	}

	/**
	 * Callback function to update products via AJAX.
	 */
	public function update_products_callback() {
	    // Initialize response array
	    $response = array();

	    // Get selected filter value from AJAX request
	    $main_category_ids = isset($_POST['categories']) ? $_POST['categories'] : array();
	    $sub_category_ids = isset($_POST['sub_category']) ? $_POST['sub_category'] : array();
	    $brand_ids = isset($_POST['brand_category']) ? $_POST['brand_category'] : array();
	    $min_value = isset($_POST['min_value']) ? $_POST['min_value'] : null;
	    $max_value = isset($_POST['max_value']) ? $_POST['max_value'] : null;
	    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;

	    // Get selected sorting option from AJAX request
	    $sort_option = isset($_POST['sort_option']) && $_POST['sort_option'] != 0 ? $_POST['sort_option'] : 'low-to-high';


	    // Search filter
	    $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

	    // Define default args for querying products
	    $args = array(
	        'post_type'      => 'product',
	        'post_status'    => 'publish',
	        'posts_per_page' => 12, // Adjust the number of products per page as needed
	        'paged'          => max(1, intval($_POST['page'])), // Apply pagination
	    );

	    // Add search term if provided
	    if (!empty($search_term)) {
	        $args['s'] = $search_term;
	    }

	    // If filtering by main category
	    if (!empty($main_category_ids)) {
	        $args['tax_query'][] = array(
	            'taxonomy' => 'product_cat',
	            'field'    => 'term_id',
	            'terms'    => $main_category_ids,
	            'operator' => 'IN',
	        );
	    }

	    // If filtering by sub-categories
	    if (!empty($sub_category_ids)) {
	        $args['tax_query'][] = array(
	            'taxonomy' => 'product_cat',
	            'field'    => 'term_id',
	            'terms'    => $sub_category_ids,
	            'operator' => 'IN',
	        );
	    }

	    // If filtering by brands
	    if (!empty($brand_ids)) {
	        $args['tax_query'][] = array(
	            'taxonomy' => 'brands', // Adjust taxonomy name if needed
	            'field'    => 'term_id',
	            'terms'    => $brand_ids,
	            'operator' => 'IN',
	        );
	    }

	    // Apply sorting based on selected option
	    if ($sort_option === 'high-to-low') {
	        $args['orderby']  = 'meta_value_num';
	        $args['meta_key'] = '_price';
	        $args['order']    = 'DESC';
	    } elseif ($sort_option === 'low-to-high') {
	        $args['orderby']  = 'meta_value_num';
	        $args['meta_key'] = '_price';
	        $args['order']    = 'ASC';
	    }

	    // Apply price range filtering
	    if (!empty($min_value) && !empty($max_value)) {
	        $args['meta_query'][] = array(
	            'key'     => '_price',
	            'value'   => array($min_value, $max_value),
	            'type'    => 'numeric',
	            'compare' => 'BETWEEN',
	        );
	    }

	    // Query products
	    $products_query = new WP_Query($args);

	    // Check if products were found
	    if ($products_query->have_posts()) {
	        $products_html = ''; // Initialize empty string to store HTML for products
	        while ($products_query->have_posts()) {
	            $products_query->the_post();
	            $product = wc_get_product(get_the_ID());
	            // Render Elementor template and concatenate HTML for each product
	            $products_html .= Elementor\Plugin::instance()->frontend->get_builder_content_for_display(22063);
	        }
	        // Store HTML for products in response array
	        $response['result'] = $products_html;
	        // Generate pagination HTML and store it in response array
	        $response['pagination'] = $this->generate_pagination($products_query, $page);
	    } else {
	        // If no products found, store a message in response array
	        $response['result'] = 'No products found';
	        // No pagination needed if no products found
	        $response['pagination'] = '';
	    }

	    // Total number of products
	    $response['total_products'] = $products_query->found_posts;

	    // Number of products displayed
	    $response['displayed_products'] = $products_query->post_count;

	    // Send JSON response
	    wp_send_json($response);
	    wp_die(); // Terminate script execution after sending the JSON response
	}


	public function update_brands_products_callback() {
	    // Initialize response array
	    $response = array();

	    // Verify nonce for security
	    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
	    if (!wp_verify_nonce($nonce, 'Woocommerce_Dynamic_Filter')) {
	        $response['error'] = 'Nonce verification failed';
	        wp_send_json_error($response);
	    }

	    // Get selected filter value from AJAX request
	    $main_category_ids = isset($_POST['categories']) ? $_POST['categories'] : array();
	    $sub_category_ids = isset($_POST['sub_category']) ? $_POST['sub_category'] : array();
	    $min_value = isset($_POST['min_value']) ? $_POST['min_value'] : null;
	    $max_value = isset($_POST['max_value']) ? $_POST['max_value'] : null;
	    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
	    $brand = isset($_POST['brand']) ? $_POST['brand'] : '';

	    // Get selected sorting option from AJAX request
	    $sort_option = isset($_POST['sort_option']) && $_POST['sort_option'] != 0 ? $_POST['sort_option'] : 'low-to-high';
	    
	    // Initialize args array
	    $args = array(
	        'post_type'      => 'product',
	        'post_status'    => 'publish',
	        'posts_per_page' => 12, // Adjust the number of products per page as needed
	        'paged'          => max(1, intval($_POST['page'] ?? 1)), // Apply pagination, handling if $_POST['page'] is not set
	    );

	    // Initialize tax query
	    $tax_query = array();

	    // Add brand tax query if brand is provided
	    if (!empty($brand)) {
	        $tax_query[] = array(
	            'taxonomy' => 'brands',
	            'field'    => 'term_id',
	            'terms'    => array($brand),
	            'operator' => 'IN',
	        );
	    }

	    // Add main category tax query if main category IDs are provided
	    if (!empty($main_category_ids)) {
	        $tax_query[] = array(
	            'taxonomy' => 'product_cat',
	            'field'    => 'term_id',
	            'terms'    => $main_category_ids,
	            'operator' => 'IN',
	        );
	    }

	    // Add sub-category tax query if sub-category IDs are provided
	    if (!empty($sub_category_ids)) {
	        $tax_query[] = array(
	            'taxonomy' => 'product_cat',
	            'field'    => 'term_id',
	            'terms'    => $sub_category_ids,
	            'operator' => 'IN',
	        );
	    }

	    // Set tax query
	    if (!empty($tax_query)) {
	        $args['tax_query'] = $tax_query;
	    }

	    // Set meta key for sorting
	    $args['meta_key'] = '_price';

	    // Apply sorting based on selected option
	    if ($sort_option === 'high-to-low') {
	        $args['orderby'] = 'meta_value_num';
	        $args['order'] = 'DESC';
	    } else {
	        $args['orderby'] = 'meta_value_num';
	        $args['order'] = 'ASC';
	    }

	    // Apply price range filtering
		if (!empty($main_category_ids) && !empty($sub_category_ids) && !empty($min_value) && !empty($max_value)) {
		    $args['meta_query'][] = array(
		        'key'     => '_price',
		        'value'   => array($min_value, $max_value),
		        'type'    => 'numeric',
		        'compare' => 'BETWEEN',
		    );
		}


	    // Query products
	    $products_query = new WP_Query($args);

	    // Check if products were found
	    if ($products_query->have_posts()) {
	        $products_html = ''; // Initialize empty string to store HTML for products
	        while ($products_query->have_posts()) {
	            $products_query->the_post();
	            $product = wc_get_product(get_the_ID());
	            // Render Elementor template and concatenate HTML for each product
	            // $products_html .= Elementor\Plugin::instance()->frontend->get_builder_content_for_display(22063);
	            $products_html .= $this->generate_product_card($product);
	        }
	        // Store HTML for products in response array
	        $response['result'] = $products_html;
	        // Generate pagination HTML and store it in response array
	        $response['pagination'] = $this->generate_pagination($products_query, $page);
	    } else {
	        // If no products found, store a message in response array
	        $response['result'] = 'No products found';
	        // No pagination needed if no products found
	        $response['pagination'] = '';
	    }

	    // Total number of products
	    $response['total_products'] = $products_query->found_posts;

	    // Number of products displayed
	    $response['displayed_products'] = $products_query->post_count;

	    // Send JSON response
	    wp_send_json($response);
	    wp_die(); // Terminate script execution after sending the JSON response
	}



	/**
	 * Generates HTML markup for displaying a product card.
	 *
	 * @param object $product The WooCommerce product object.
	 * @return string HTML markup for the product card.
	 */
	public function generate_product_card($product) {
	    $title = get_the_title($product->get_id());
	    $sku = $product->get_sku();
	    $amount = wc_price($product->get_regular_price());
	    $image = $product->get_image();

	    // Get brand information
	    $terms = wp_get_post_terms($product->get_id(), 'brands');
	    $brand = !empty($terms) && !is_wp_error($terms) ? strtoupper($terms[0]->name) : 'Your Brand';

	    // Escape dynamic data
	    $title = esc_html($title);
	    $sku = esc_html($sku);
	    $brand = esc_html($brand);

	    $output = '<div class="product-card">';
	    $output .= '<div class="product-image">' . $image . '</div>';
	    $output .= '<div class="product-details">';
	    $output .= '<div class="product-info">';
	    $output .= '<div class="product-brand">' . $brand . '</div>';
	    $output .= '<div class="product-title">' . $title . '</div>';
	    $output .= '<div class="product-sku">SKU: ' . $sku . '</div>';
	    $output .= '</div>'; // Close product-info
	    $output .= '<div class="add-to-cart-div">';
	    $output .= '<div class="product-price">' . $amount . '</div>';
	    $output .= '<a href="' . esc_url(get_permalink($product->get_id())) . '" class="add-to-cart-btn" data-product-id="' . $product->get_id() . '"><img class="add-cart-icon" src="'.WOOCOMMERCE_DYNAMIC_FILTER_URL . 'images/cart-add.png' .'"></a>';
	    $output .= '</div>'; // Close add-to-cart-div
	    $output .= '</div>'; // Close product-details
	    $output .= '</div>'; // Close product-card

	    return $output;
	}

	
	public function generate_pagination($products_query, $current_page) {
	    $output = '';

	    if ($products_query->max_num_pages > 1) {
		    $output .= '<div class="pagination-filter">';
		    $output .= paginate_links(array(
		        'total'      => $products_query->max_num_pages,
		        'current'    => $current_page, // Set the current page
		        'prev_text'  => '&#8592;', // Left arrow symbol (←)
		        'next_text'  => '', // Right arrow symbol (→)
		        'type'       => 'plain', // Display all page numbers
		    ));
		    $output .= '</div>';
		}

	    return $output;
	}


	public function get_product_count_message() {
	    // Get total number of products
	    $total_products = wp_count_posts('product')->publish;

	    // Get current query
	    global $wp_query;

	    // Get number of products displayed on the current page
	    $displayed_products = $wp_query->post_count;

	    // Prepare the response data
	    $response = array(
	        'displayed_products' => $displayed_products,
	        'total_products' => $total_products
	    );

	    // Return JSON response
	    wp_send_json($response);
	}

	public function get_brands_subcategories_ajax() {
	    
		// Verify nonce for security
	    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
	    if (!wp_verify_nonce($nonce, 'Woocommerce_Dynamic_Filter')) {
	        $response['error'] = 'Nonce verification failed';
	        wp_send_json_error($response);
	    }

	    $parent_cat_ids = isset($_POST['parent_cat_ids']) ? $_POST['parent_cat_ids'] : array();
	    $brand_id = isset($_POST['brand_id']) ? $_POST['brand_id'] : null;

	    $subcategories_data = array();

	    if (empty($parent_cat_ids)) {
	        // Get terms for the 'product_cat' taxonomy
	        $parent_cats = get_terms(array(
	            'taxonomy'   => 'product_cat',
	            'parent'     => 0,
	            'hide_empty' => true,
	        ));

	        // Extract parent category IDs
	        $parent_cat_ids = wp_list_pluck($parent_cats, 'term_id');
	    }

	    foreach ($parent_cat_ids as $parent_cat_id) {
	        $subcategories = get_terms(array(
	            'taxonomy'   => 'product_cat',
	            'parent'     => $parent_cat_id,
	            'hide_empty' => true,
	        ));

	        foreach ($subcategories as $subcategory) {
	            $args = array(
	                'post_type'      => 'product',
	                'post_status'    => 'publish',
	                'posts_per_page' => -1,
	                'tax_query'      => array(
	                    'relation' => 'AND',
	                    array(
	                        'taxonomy' => 'product_cat',
	                        'field'    => 'term_id',
	                        'terms'    => $subcategory->term_id,
	                    ),
	                ),
	            );

	            if ($brand_id) {
	                $args['tax_query'][] = array(
	                    'taxonomy' => 'brands',
	                    'field'    => 'term_id',
	                    'terms'    => $brand_id,
	                );
	            }

	            $products_query = new WP_Query($args);
	            $subcategory_count = $products_query->found_posts;

	            $subcategories_data[] = array(
	                'id'    => $subcategory->term_id,
	                'name'  => $subcategory->name,
	                'count' => $subcategory_count
	            );
	        }
	    }

	    wp_send_json($subcategories_data);
	    wp_die(); // Terminate script execution after sending the JSON response
	}


}
