<?php 

if(!class_exists('WP_List_Table')){
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

function get_unique_receipts() {
    global $wpdb;
    $table_name = 'few_etsy_orders';  // Note: Prefix is added
    $query = "
        SELECT * 
        FROM {$table_name}
        GROUP BY receipt_id
        ORDER BY created_timestamp ASC
    ";
    return $wpdb->get_results($query, ARRAY_A);
}




class Orders_List_Table extends WP_List_Table {
    
    private $orders;

    public function __construct($orders) {
        parent::__construct();
        $this->orders = $orders;
    }

    public function get_columns() {
	
    return [
		'listing_image_id' => '',
        'receipt_id' => 'Receipt ID',
		'buyer_name' => 'Buyer Name',
		'title' => 'Title',
        'transaction_id' => 'Price',
        'buyer_email' => 'Contact',
         ];
}

    public function prepare_items() {
        $this->_column_headers = [$this->get_columns(), [], []];
        $this->items = $this->orders;
    }

  public function column_default($item, $column_name) {
    if ($column_name == 'title' && strlen($item[$column_name]) > 20) {
        return substr($item[$column_name], 0, 20) . '...';
    } elseif ($column_name == 'decal_text') {
        return '<input type="text" name="decal_text" value="' . esc_attr($item[$column_name]) . '">';
    }
    return $item[$column_name];
}
	
	
	public function column_receipt_id($item) {
    $receipt_link = admin_url('admin.php?page=afeworiginals-order-items&receipt_id=' . $item['receipt_id']);
    return '<a href="' . $receipt_link . '">' . $item['receipt_id'] . '</a>';
}


	
}

/// Function to get font names from the WordPress options table
function get_font_names() {
    // Retrieve the 'font_names' option from the WordPress options table
    $fonts = get_option('font_names', []);
    return is_array($fonts) ? $fonts : [];
}

/// Function to get vinyl types from the WordPress options table
function get_vinyl_types() {
    // Retrieve the 'vinyl_types' option from the WordPress options table
    $vinyl_types = get_option('vinyl_types', []);
    return is_array($vinyl_types) ? $vinyl_types : [];
}







// Existing class definition
class OrderItems_List_Table extends WP_List_Table {

    private $order_items;
  	private $current_vinyl_type;  // <-- New variable
    public function __construct($order_items) {
        parent::__construct();
        $this->order_items = $order_items;
    }

    public function get_columns() {
        // Updated columns definition to include 'Vinyl Type' and 'Buyer Message'
        return [
			'listing_image_id' => '',
            'transaction_id' => 'Transaction ID',
            'title' => 'Title',
			'variations_value_1' => 'Font',
			'variations_value_2' => 'Size',
            'vinyl_type' => 'Vinyl Type', // <-- Added this new column
			'vinyl_color' => 'Color',
			'decal_text' => 'Decal Text',
            'buyer_message' => 'Buyer Message' // <-- Added this new column
        ];
    }

    public function prepare_items() {
        $this->_column_headers = [$this->get_columns(), [], []];
        $this->items = $this->order_items;
    }
	
	
	
	
	
	
	
	
	public function column_default($item, $column_name) {
		
			
		//var_dump($item);
    if ($column_name == 'variations_value_1') { // Handle 'Font' column
        $fonts = get_font_names(); // Retrieve the list of font names
        $current_font = $item['variations_value_1']; // Get the current font from variations_value_1
		// Initialize dropdown
        $dropdown = '<select name="font">';

        // Loop through fonts to create options
        foreach ($fonts as $font) {
            $selected = strtolower($font) === strtolower($current_font) ? ' selected="selected"' : '';
            $dropdown .= "<option value=\"{$font}\"{$selected}>{$font}</option>";
        }
        $dropdown .= '</select>';
        return $dropdown;
		
    } elseif ($column_name == 'vinyl_type') { // <-- Handle 'Vinyl Type' column
        $vinyl_types = get_vinyl_types(); // Retrieve the list of vinyl types
		//echo $item['variations_value_2'];
		 // Check if the 'Size' contains the word 'Premium'
       $is_premium = stripos($item['variations_value_2'], 'Premium Vinyl') !== false;

		
		
		// If it's Premium, default to 'glitter_colors'; otherwise, 'standard_colors'
        $default_vinyl_type = $is_premium ? 'glitter_colors' : 'standard_colors';
		
		echo $default_vinyl_type;
		
        echo $current_vinyl_type = $item['vinyl_type']; // Get the current vinyl type from the item
		
        // Initialize dropdown
        $dropdown = '<select name="vinyl_type">';
      // Loop through vinyl types to create options
    foreach ($vinyl_types as $vinyl_type) {
        $selected = '';
        if (strtolower($vinyl_type) === strtolower($current_vinyl_type)) {
            $selected = ' selected="selected"';
        } elseif (empty($current_vinyl_type) && strtolower($vinyl_type) === strtolower($default_vinyl_type)) {
            $selected = ' selected="selected"';
        }
        $dropdown .= "<option value=\"{$vinyl_type}\"{$selected}>{$vinyl_type}</option>";
    }
    $dropdown .= '</select>';
    
    $this->current_vinyl_type = $current_vinyl_type;  // <-- Store it at the class level

    return $dropdown;
    } elseif ($column_name == 'title' && strlen($item[$column_name]) > 30) {
        return substr($item[$column_name], 0, 30) . '...';
    } elseif ($column_name == 'decal_text') {
        return '<input type="text" name="' . esc_attr($column_name) . '" value="' . esc_attr($item[$column_name]) . '">';
    }   // Handle 'Color' column
    elseif ($column_name == 'vinyl_color') { 
        // Read current vinyl_type to decide which colors to display
        $vinyl_type = $this->current_vinyl_type;
		echo $default_vinyl_type;
        // Fetch colors from the corresponding WordPress options table
        $colors = get_option($vinyl_type, []);  // Fetch from WordPress options table

        // Initialize dropdown
        $dropdown = '<select name="vinyl_color">';
        
        // Loop through colors to create options
        foreach ($colors as $color) {
            $selected = $color === $item['vinyl_color'] ? ' selected="selected"' : '';
            $dropdown .= "<option value=\"{$color}\"{$selected}>{$color}</option>";
        }
        
        $dropdown .= '</select>';
        return $dropdown;
    }
    return $item[$column_name];
}
	
	
	
	
	
	

}

?>