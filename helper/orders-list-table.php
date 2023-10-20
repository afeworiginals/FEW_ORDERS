<?php 

if(!class_exists('WP_List_Table')){
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

function get_unique_receipts() {
    global $wpdb;
    $table_name = 'few_etsy_orders'; // Use $wpdb->prefix to add prefix
    $query = "SELECT * FROM {$table_name} GROUP BY receipt_id ORDER BY created_timestamp ASC";
    return $wpdb->get_results($query, ARRAY_A);
}

function get_previous_receipt_id($current_receipt_id) {
    global $wpdb;
    $table_name = 'few_etsy_orders'; // Use $wpdb->prefix to add prefix
    $query = $wpdb->prepare(
        "SELECT receipt_id FROM {$table_name} WHERE receipt_id < %d ORDER BY receipt_id DESC LIMIT 1",
        $current_receipt_id
    );
    return $wpdb->get_var($query);
}

function get_next_receipt_id($current_receipt_id) {
    global $wpdb;
    $table_name = 'few_etsy_orders'; // Use $wpdb->prefix to add prefix
    $query = $wpdb->prepare(
        "SELECT receipt_id FROM {$table_name} WHERE receipt_id > %d ORDER BY receipt_id ASC LIMIT 1",
        $current_receipt_id
    );
    return $wpdb->get_var($query);
}

class Base_List_Table extends WP_List_Table {
   
}


class Orders_List_Table extends Base_List_Table { 
    
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
        $this->process_bulk_action();
        $this->_column_headers = [$this->get_columns(), [], []];
        $this->items = $this->orders;
    }

    public function column_default($item, $column_name) {
        if ($column_name == 'title' && strlen($item[$column_name]) > 30) {
            return substr($item[$column_name], 0, 30) . '...';
        } elseif ($column_name == 'decal_text') {
            return '<input type="text" name="decal_text[]" value="' . esc_attr($item[$column_name]) . '">';
        }
        return $item[$column_name];
    }
    
    public function column_receipt_id($item) {
        $receipt_link = admin_url('admin.php?page=afeworiginals-order-items&receipt_id=' . $item['receipt_id']);
        return '<a href="' . $receipt_link . '">' . $item['receipt_id'] . '</a>';
    }

    

   
    
    
   
    
    
}

class OrderItems_List_Table extends Base_List_Table {

    private $order_items;

    private function generate_select_box($name, $options, $selected_value, $row_id) {
        $select = "<select name='{$name}[]' data-row-id='{$row_id}'>";  // Added [] to the name attribute
        foreach ($options as $option) {
            $option_lower = strtolower($option);
            $selected = ($selected_value == $option_lower) ? 'selected' : '';
            $select .= "<option value=\"$option\" $selected>$option</option>";
        }
        $select .= '</select>';
        return $select;
    }
    

    protected function display_tablenav($which) {


        // Get the current receipt_id from the URL
    $current_receipt_id = isset($_GET['receipt_id']) ? intval($_GET['receipt_id']) : 0;

    // Get the previous and next receipt_ids
    $prev_receipt_id = get_previous_receipt_id($current_receipt_id);
    $next_receipt_id = get_next_receipt_id($current_receipt_id);

    // Create the URLs for the previous and next orders
    $prev_order_url = admin_url('admin.php?page=afeworiginals-order-items&receipt_id=' . $prev_receipt_id);
    $next_order_url = admin_url('admin.php?page=afeworiginals-order-items&receipt_id=' . $next_receipt_id);
    echo '<input type="hidden" id="next_receipt_id" value="' . $next_receipt_id . '">';

        ?>
        <div class="tablenav <?php echo esc_attr($which); ?>">
    
        <!-- Previous and Next Order buttons -->
        <div class="alignleft actions">
            <?php if ($prev_receipt_id): ?>
                <a href="<?php echo $prev_order_url; ?>" class="button action">Previous Order</a>
            <?php endif; ?>
            <?php if ($next_receipt_id): ?>
                <a href="<?php echo $next_order_url; ?>" class="button action">Next Order</a>
            <?php endif; ?>
        </div>
    
            <!-- Bulk actions dropdown and Apply button -->
            <div class="alignright actions bulkactions">
                <label for="bulk-action-selector-<?php echo esc_attr($which); ?>" class="screen-reader-text">Select bulk action</label>
                <select name="few_action" id="bulk-action-selector-<?php echo esc_attr($which); ?>">
                    <option value="-1">Bulk Actions</option>
                    <option value="update_db" selected>Update DB</option>
                    <option value="delete">Delete</option>
                </select>
                <input type="submit" id="doaction" class="button action" value="Apply">
            </div>
    
            <br class="clear">
        </div>
        <?php
    }
    

    public function __construct($order_items) {
        parent::__construct();
        $this->order_items = $order_items;
    }

    public function get_columns() {
        return [
            'cb' => '<input type="checkbox" class="check-all" />', 
            'db_id' => 'ID',  // Add this line
            'listing_image_id' => '',
            'transaction_id' => 'Trans ID',
            'title' => 'Title',
            'variations_value_1' => '<input type="checkbox" id="check-all-font" /> Font',
            'variations_value_2' => 'Size',
            'vinyl_type' => '<input type="checkbox" id="check-all-vinyl-type" /> Type',
            'vinyl_color' => '<input type="checkbox" id="check-all-vinyl-color" /> Color',
            'decal_text' => '<input type="checkbox" id="check-all-decal-text" /> Text',
            'buyer_message' => 'Message',
        ];
    }

    public function prepare_items() {
        $this->_column_headers = [$this->get_columns(), [], []];
        $this->items = $this->order_items;
    }

    public function column_default($item, $column_name) {
        $select = '';  // Initialize $select
        error_log(print_r($item, true));
        if ($column_name == 'cb') {
            return '<input type="checkbox" name="item_ids[]" value="' . $item['id'] . '">';
        } elseif ($column_name == 'db_id') {
            return '<input type="hidden" name="db_ids[]" value="' . $item['id'] . '">' . $item['id'];        
        } elseif ($column_name == 'title' && strlen($item[$column_name]) > 30) {
            return substr($item[$column_name], 0, 30) . '...';
        } elseif ($column_name == 'decal_text') {
            return '<input type="text" name="decal_text[]" value="' . esc_attr($item[$column_name]) . '">';
        } elseif ($column_name == 'variations_value_1') {
            // Get the font list from the WordPress options table 
            $font_list = get_option('font_names', []);
            // Get the font name from the $item array and force lowercase
            $font_name = strtolower($item[$column_name]);
            // Create and return select box with the font_list as options and the font_name as selected
        
            return $this->generate_select_box('variations_value_1', $font_list, strtolower($item[$column_name]), $item['id']);

        } elseif ($column_name == 'vinyl_type') {
            // Get the id from the $item array
           $id = $item['id'];
            
            // Get the vinyl type list from the WordPress options table
            $vinyl_type_list = get_option('vinyl_types', []);
            // Get the vinyl type from the $item array and force lowercase
            $vinyl_type = strtolower($item['vinyl_type']);
         
            // Create and return select box with the vinyl_type_list as options and the vinyl_type as selected
          return $this->generate_select_box('vinyl_type', $vinyl_type_list, strtolower($item['vinyl_type']), $item['id']);

        } elseif ($column_name == 'vinyl_color') {
            // Get the vinyl type from the $item array and force lowercase
            $vinyl_type = strtolower($item['vinyl_type']);
            // Get the dropdown data based on the vinyl type
            $vinyl_color_data = get_option($vinyl_type, []);
            // Get the id from the $item array
          $id = $item['id'];
       
            // Create and return select box with the vinyl_color_data as options
            return $this->generate_select_box('vinyl_color', $vinyl_color_data, $item['vinyl_color'], $item['id']);
            return $select;

        } elseif ($column_name == 'buyer_message') {
            $buyer_personalization = esc_html($item['variations_value_3']);
            return  $buyer_personalization;
        }
        return esc_html($item[$column_name]);
    }

    public function display() {
        echo '<form id="order-items-form" method="post">';
        // Removed the display_tablenav('top') call here
        parent::display();
        // Removed the display_tablenav('bottom') call here
        echo '</form>';
    }

    

    
}



?>