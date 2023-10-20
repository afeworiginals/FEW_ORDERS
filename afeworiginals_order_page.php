<?php
/*
Plugin Name: A Few Originals Order Page
Description: Etsy Order Managment Plugin
Version: 2.0
Author: Wade Keller
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo "Hi there! I'm just a plugin, not much I can do when called directly.";
    exit;
}


include_once plugin_dir_path( __FILE__ ) . 'helper/check-tokens.php';
include_once plugin_dir_path( __FILE__ ) . 'helper/api-helpers.php';
include_once plugin_dir_path( __FILE__ ) . 'helper/activation.php';
register_plugin_activation( __FILE__ );

include_once plugin_dir_path( __FILE__ ) . 'helper/orders-list-table.php';







// Function to enqueue scripts and styles
function afop_enqueue_scripts() {
    $version = time(); // current timestamp
    wp_enqueue_script('jquery'); // Enqueue jQuery which comes with WordPress
    wp_enqueue_script('afop-ajax-script', plugin_dir_url(__FILE__) . 'js/afop_ajax.js?ver=' . $version, ['jquery'], null, true);
    //wp_enqueue_script('afop-ajax-script', plugin_dir_url( __FILE__ ) . 'js/afop_ajax.js', array('jquery'), '1.0', true);
    wp_localize_script('afop-ajax-script', 'afop_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ));
 


}
add_action('admin_enqueue_scripts', 'afop_enqueue_scripts');

function get_etsy_config_data() {
    return [
        'client_id' => get_option('etsy_client_id'),
        'secret_key' => get_option('etsy_secret_key'),
        'shop_id' => get_option('etsy_shop_id'),
        'access_token' => get_option('access_token'),
        'refresh_token' => get_option('refresh_token'),
        'timestamp' => get_option('timestamp'),
        'state' => get_option('state'),
        'code_verifier' => get_option('code_verifier'),
        'code_challenge' => get_option('code_challenge')
    ];
}


// Function to handle the AJAX request
function afop_etsy_api_callback() {
    // Ensure the user is logged in
    if (!is_user_logged_in()) {
        echo "Please log in to access this feature.";
        wp_die();
    }
	
	// Check and possibly refresh the tokens
    checkAndUpdateTokens();
	
	
  // 1. Fetch Data from Etsy
    $config_data = get_etsy_config_data();
	echo $config_data['shop_id'].'<br>';
    $endpoint = "https://api.etsy.com/v3/application/shops/{$config_data['shop_id']}/receipts?was_shipped=false&limit=5";
	echo $endpoint;
    $apiResponse = fetchEtsyData($endpoint, $config_data['access_token'], $config_data['client_id']);
	
	
    if (isset($apiResponse['error'])) {
    echo $apiResponse['error'];
    wp_die();
	}
	$etsyData = $apiResponse['data'];
	//echo json_encode($etsyData); 
    // 2. Process the Fetched Data
    extractReceiptData($etsyData);
	
	
	

    // Response to front-end (Optional)
    echo "Etsy data processed and saved.";
    wp_die();
}

add_action('wp_ajax_afop_hello_world', 'afop_etsy_api_callback'); // If logged in





// Create the main page
function afop_main_page() {
    add_menu_page('A Few Originals Order Page', 'A Few Originals Order Page', 'manage_options', 'afeworiginals-order-page', 'afop_display_page');
}
add_action('admin_menu', 'afop_main_page');

// Register the submenu page
function afop_submenu_page() {
    add_submenu_page(
        'afeworiginals-order-page',
        'Order Display',
        'Order Display',
        'manage_options',
        'afeworiginals-order-display',
        'afop_display_orders'
    );
	
	add_submenu_page(
        'afeworiginals-order-page',
        'Order Items',
        'Order Items',
        'manage_options',
        'afeworiginals-order-items',
        'afop_display_order_items'
    );
	
	 add_submenu_page(
        'afeworiginals-order-page', // Parent slug
        'Order Options',     // Page title
        'Order Options',            // Menu title
        'manage_options',          // Capability
        'few-orderr-options',     // Menu slug
        'afop_display_order_options' // Callback function
    );
}
add_action('admin_menu', 'afop_submenu_page');

// Function to display the main page content
function afop_display_page() {
    // Get the URL for the Order Display page
    $order_display_url = admin_url('admin.php?page=afeworiginals-order-display');
    ?>
    <div class="wrap">
        <h1>A Few Originals Order Page</h1>
        <button id="hello-world-btn" class="button-primary">Update Database</button>
        <div id="result"></div>
        
        <!-- Add a button linking to the Order Display page -->
        <a href="<?php echo $order_display_url; ?>" class="button button-primary">View Orders</a>
    </div>
    <?php
}



// Function to display the orders
function afop_display_orders() {
        $unique_receipts = get_unique_receipts();
	
    $orders_list = new Orders_List_Table($unique_receipts);
	 echo '<div class="wrap"><h2>Order Displays</h2>'; 
    $orders_list->prepare_items();
    $orders_list->display();
   


    echo '</div>';
}


function afop_display_order_items() {
    $receipt_id = $_GET['receipt_id'];

    // Fetch all items for the receipt_id from the database
    global $wpdb;
    $table_name = 'few_etsy_orders';
    $query = $wpdb->prepare("SELECT * FROM {$table_name} WHERE receipt_id = %d", $receipt_id);
    $order_items = $wpdb->get_results($query, ARRAY_A);

    $items_list = new OrderItems_List_Table($order_items);

    echo '<div class="wrap"><h2>Items for Receipt ID: ' . esc_html($receipt_id) . '</h2>'; 
    $items_list->prepare_items();
    $items_list->display();
    echo '</div>';
}


function afop_display_order_options() {
 // Available options tables
    $option_tables = [
        'font_names',
        'glitter_colors',
        'standard_colors',
        'fluorescent_colors',
        'camouflage_colors',
        'holographic_colors',
        'chrome_colors'
    ];
	
	
	
     echo '<div class="wrap">';
    echo '<h1>Vinyl Color Options</h1>';
    
    // Dropdown menu
    echo '<select id="option_table_selector">';
    foreach ($option_tables as $table) {
        echo "<option value='$table'>" . ucwords(str_replace('_', ' ', $table)) . "</option>";
    }
    echo '</select>';

    // Placeholder for the data
    echo '<div id="options_data"></div>';

    echo '</div>';

}

add_action('wp_ajax_afop_fetch_options', 'afop_fetch_options_callback');

function afop_fetch_options_callback() {
	error_log("Inside afop_fetch_options_callback");  // Debug line
    $table_name = sanitize_text_field($_POST['table_name']);
    $options = get_option($table_name, []);
	error_log("table_name: " . print_r($table_name, true));  // Debug line
error_log("options: " . print_r($options, true));  // Debug line


    if (empty($options)) {
        echo '<p>No options defined.</p>';
    } else {
        echo '<ul>';
        foreach ($options as $option) {
            echo '<li>' . esc_html($option) . '</li>';
        }
        echo '</ul>';
    }
    wp_die();
}


add_action('wp_ajax_process_bulk_action', 'few_process_bulk_action');

function few_process_bulk_action() {
    global $wpdb;
    $table_name = 'few_etsy_orders';

    // Log the incoming POST data for debugging
    error_log('few_process_bulk_action function is being executed DUDE.');
    error_log(print_r($_POST, true));

    // Parse the form data
    parse_str($_POST['formData'], $formDataArray);

    // Check if action is set
    if (!isset($_POST['action'])) {
        error_log('selectedAction is not set.');
        die();
    }

    $selectedAction = $formDataArray['few_action'];
    error_log('SELECTED ACTION: ' . $selectedAction);

    // Log the parsed form data
    error_log(print_r($formDataArray, true));

    // Handle 'update_db' action
    if ($selectedAction === 'update_db') {
        if (!isset($formDataArray['db_ids'])) {
            error_log('db_ids are not set.');
            die();
        }

        $db_ids = $formDataArray['db_ids'];
        foreach ($db_ids as $index => $db_id) {
            error_log("Processing db_id: " . $db_id);

            // Extract other form data
            $font = $formDataArray['variations_value_1'][$index] ?? null;
            $vinyl_type = $formDataArray['vinyl_type'][$index] ?? null;
            $color = $formDataArray['vinyl_color'][$index] ?? null;
            $decal_text = $formDataArray['decal_text'][$index] ?? null;

            // Update the database
            $wpdb->update(
                $table_name,
                [
                    'variations_value_1' => $font,
                    'vinyl_type' => $vinyl_type,
                    'vinyl_color' => $color,
                    'decal_text' => $decal_text
                ],
                ['id' => $db_id]
            );
        }
    }
    // Handle 'delete' action
    elseif ($selectedAction === 'delete') {
        if (!isset($formDataArray['db_ids'])) {
            error_log('db_ids are not set.');
            die();
        }

        $db_ids = $formDataArray['db_ids'];
        foreach ($db_ids as $db_id) {
            error_log("Deleting db_id: " . $db_id);

            // Delete the database row
            $wpdb->delete(
                $table_name,
                ['id' => $db_id]
            );
        }
    }

    die();

}





function get_vinyl_colors() {
    $vinyl_type = strtolower($_POST['vinyl_type']);
    $vinyl_color_data = get_option($vinyl_type, []);
    $options = '';

    if (!empty($vinyl_color_data)) {
        foreach ($vinyl_color_data as $color) {
            $options .= "<option value=\"$color\">$color</option>";
        }
    } else {
        $options = '<option value="">No colors available</option>';
    }

    echo $options;
    wp_die();
}

add_action('wp_ajax_get_vinyl_colors', 'get_vinyl_colors');



