<?php

function register_plugin_activation( $main_plugin_file ) {
    register_activation_hook($main_plugin_file, 'get_etsy_orders_activation');
}


function get_etsy_orders_activation() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'etsy_orders';

    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE `few_etsy_orders` (
				  `id` int(11) NOT NULL,
				  `transaction_id` bigint(11) NOT NULL,
				  `title` varchar(135) DEFAULT NULL,
				  `buyer_name` text NOT NULL,
				  `buyer_email` varchar(255) NOT NULL,
				  `seller_user_id` int(11) DEFAULT NULL,
				  `buyer_user_id` int(11) DEFAULT NULL,
				  `create_timestamp` int(11) DEFAULT NULL,
				  `created_timestamp` int(11) DEFAULT NULL,
				  `paid_timestamp` int(11) DEFAULT NULL,
				  `shipped_timestamp` int(11) DEFAULT NULL,
				  `quantity` int(11) DEFAULT NULL,
				  `receipt_quantity` int(11) NOT NULL,
				  `listing_image_id` bigint(11) DEFAULT NULL,
				  `receipt_id` bigint(11) DEFAULT NULL,
				  `is_digital` tinyint(1) DEFAULT NULL,
				  `file_data` text DEFAULT NULL,
				  `listing_id` bigint(11) DEFAULT NULL,
				  `sku` varchar(255) DEFAULT NULL,
				  `product_id` int(11) DEFAULT NULL,
				  `transaction_type` varchar(255) DEFAULT NULL,
				  `price_amount` int(11) DEFAULT NULL,
				  `price_divisor` int(11) DEFAULT NULL,
				  `price_currency_code` char(3) DEFAULT NULL,
				  `shipping_cost_amount` int(11) DEFAULT NULL,
				  `shipping_cost_divisor` int(11) DEFAULT NULL,
				  `shipping_cost_currency_code` char(3) DEFAULT NULL,
				  `vinyl_color` text NOT NULL,
				  `decal_text` text NOT NULL,
				  `variations_name_1` text DEFAULT NULL,
				  `variations_value_1` text NOT NULL,
				  `variations_name_2` text NOT NULL,
				  `variations_value_2` text NOT NULL,
				  `variations_name_3` text NOT NULL,
				  `variations_value_3` text NOT NULL,
				  `product_data` text DEFAULT NULL,
				  `shipping_profile_id` int(11) DEFAULT NULL,
				  `min_processing_days` int(11) DEFAULT NULL,
				  `max_processing_days` int(11) DEFAULT NULL,
				  `shipping_method` varchar(255) DEFAULT NULL,
				  `shipping_upgrade` varchar(255) DEFAULT NULL,
				  `expected_ship_date` int(11) DEFAULT NULL,
				  `buyer_coupon` int(11) DEFAULT NULL,
				  `shop_coupon` int(11) DEFAULT NULL,
				  `buyer_message` text NOT NULL
				) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
	
	
	$text_decal_listings = array(
	514887146,
	1123947063,
			
	);
	
update_option('text_decal_listings', $text_decal_listings);
	
$font_names = array(
    'alexis',
    'barbie',
			    'bonnie',
			    'carol',
			    'cowboy',
			    'darcy',
			    'disney',
			    'erica',
			    'flynn',
			    'georgia',
			    'isabella',
			    'jackson',
			    'kayla',
			    'laura',
			    'maggie',
			    'natalie',
			    'peter',
			    'spaceman',
			    'superhero',
			    'tristan',
			    'ulysses',
			    'vintage',
			    'william',
			    'adam',
			    'becky',
			    'chris',
			    'denise',
			    'edward',
			    'fiona',
			    'greg',
			'hannah',
			'isaac',
			'jenna',
			'kyle',
			'lisa',
			'mark',
			'nancy',
			'olivia',
			'paige',
			'reagan',
			'scott',
			'tracy',
			'ursala',
			'vanessa',
			'wade',
			'zaine',
			'aaron',
			'bailey',
			'cathy',
			'dennis',
			'elviria',
			'dennis',
			'frank',
			'gabriela',
			'hamlet',
			'ingrid',
			'james',
			'kacey',
			'lance',
			'michael',
			'nadine',
			'owen',
			'patrick',
			'quimby',
			'rachel',
			'ryan',
			'tyler',
			'umberto',
			'victor',
			'zena'
   
);

update_option('font_names', $font_names);
	
	$glitter_colors = array(
    'black glitter',
    'white glitter',
    'dark grey glitter',
    'gold glitter',
    'silver glitter',
    'champagne glitter',
    'rose gold glitter',
    'dark red glitter',
    'flo pink glitter',
    'coral glitter',
    'orange glitter',
    'flo orange glitter',
    'd. amethyst glitter',
    'purple glitter',
    'blue glitter',
    'light blue glitter',
    'green glitter',
    'emerald glitter',
    'teal glitter',
    'tiff blue glitter',
    'sea foam glitter',
    'flo green glitter',
    'lime-tree glitter',
    'yellow glitter',
    'melon glitter',
    'flo blue glitter'
);

update_option('glitter_colors', $glitter_colors);
	
	$standard_colors = array(
    'black',
    'white',
    'matte black',
    'matte white',
    'gold metallic',
    'silver metallic',
    'copper metallic',
    'imitation gold',
    'golden yellow',
    'signal yellow',
    'yellow',
    'light yellow',
    'brimstone yellow',
    'purple red',
    'burgundy',
    'dark red',
    'red',
    'light red',
    'orange red',
    'orange',
    'light orange',
    'pastel orange',
    'coral',
    'purple',
    'violet',
    'lavender',
    'lilac',
    'pink',
    'soft pink',
    'deep sea blue',
    'steel blue',
    'dark blue',
    'cobalt blue',
    'king blue',
    'brilliant blue',
    'blue',
    'traffic blue',
    'gentian blue',
    'azure blue',
    'sky blue',
    'light blue',
    'ice blue',
    'turquoise blue',
    'turquoise',
    'mint',
    'dark green',
    'forest green',
    'green',
    'grass green',
    'light green',
    'yellow green',
    'lime-tree green',
    'brown',
    'nut brown',
    'light brown',
    'beige',
    'cream',
    'dark grey',
    'grey',
    'telegrey',
    'middle grey',
    'light grey'
);

update_option('standard_colors', $standard_colors);
	
	$fluorescent_colors = array(
		'orange flo',
		'red orange flo',
		'red flo',
		'pink flo',
		'yellow flo',
		'green flo'
	
	);
	
	
	update_option('fluorescent_colors', $fluorescent_colors);
	
	
		$camouflage_colors = array(
		'brown camo',
		'black camo',
		'red camo',
		'pink camo',
		'blue camo',
		'green camo'
	
	);
	
	
	update_option('camouflage_colors', $camouflage_colors);
	
	
			$holographic_colors = array(
		'amber holo',
		'bright green holo',
		'green holo',
		'white holo',
		'yellow holo',
		'blue holo',
		'orange holo',
		'pink holo',
		'gold holo',
	
	);
	
	
	update_option('holographic_colors', $holographic_colors);
	
	
			$chrome_colors = array(
		'silver chrome',
		'gold chrome',
	
	);
	
	
	update_option('chrome_colors', $chrome_colors);
	

	$vinyl_type = array(
		'Standard',
		'Premium',
		'Holographic',
		'Camouflage',
		'Fluorescent',
		'Chrome'
	
	);
	
	
	update_option('vinyl_types', $vinyl_type);
	
	
}
?>