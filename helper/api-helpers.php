<?php 




include_once plugin_dir_path( __FILE__ ) . '../afeworiginals_order_page.php';

/**
 * Fetch data from a given URL with specified headers.
 * 
 * @param string $url - The endpoint URL.
 * @param string $token - The access token for authorization.
 * @param string $clientId - The client ID for the API request.
 * @param array $headers - Additional headers for the API request.
 * 
 * @return array - An array containing 'data' or an 'error' message.
 */
function fetchEtsyData($url, $token, $clientId, $headers = []) {
    $ch = curl_init();

    $default_headers = [
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Bearer ' . $token,
        'x-api-key: ' . $clientId
    ];

    // Merge default headers with the provided headers
    $headers = array_merge($default_headers, $headers);

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CAINFO => '/wamp64/www/wordpress/cacert.pem'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        return [
            'error' => 'cURL Error: ' . curl_error($ch)
        ];
    }
    
    if ($httpCode >= 400) {
        $parsedResponse = json_decode($response, true);
        $error_message = $parsedResponse['error'] ?? 'Unknown error from Etsy API.';
        return [
            'error' => "API Error: {$error_message}"
        ];
    }

    curl_close($ch);

    return [
        'data' => json_decode($response, true)
    ];
}


/*This function pulls the information we want from the api call*/
function extractReceiptData($parsedData) {
    global $wpdb;
	include_once plugin_dir_path( __FILE__ ) . '../afeworiginals_order_page.php';
    $table_name = 'few_etsy_orders';
    $db_receipt_ids = $wpdb->get_col("SELECT receipt_id FROM {$table_name}");
    $text_decal_listings_options = get_etsy_config_data();

    foreach ($parsedData['results'] as $receipt) {
        if (in_array($receipt['receipt_id'], $db_receipt_ids)) continue;

        foreach ($receipt['transactions'] as $transaction) {
            if (in_array($transaction['listing_id'], $text_decal_listings_options)) continue;

            $data = compileReceiptData($receipt, $transaction);
            for ($i = 0; $i < $transaction['quantity']; $i++) {
                $wpdb->insert($table_name, $data);
				if($wpdb->last_error !== '') {
					echo "Database error: " . $wpdb->last_error;
					wp_die();
				}

            }
        }
    }
}

/*This function compiles all the varations and iterates through each variation to retreive and compile the varation variables*/
function compileReceiptData($receipt, $transaction) {
    $baseData = [
        'receipt_id' => $receipt['receipt_id'],
        'seller_user_id' => $receipt['seller_user_id'],
        'buyer_user_id' => $receipt['buyer_user_id'],
        'buyer_email' => $receipt['buyer_email'],
        'buyer_name' => $receipt['name'],
        'buyer_message' => $receipt['message_from_buyer'] ?? "No Buyer Message", // Default to "No Buyer Message" if null
        'created_timestamp' => $receipt['created_timestamp']
    ];

    $transactionData = [
        'transaction_id' => $transaction['transaction_id'],
        'title' => $transaction['title'],
        'quantity' => $transaction['quantity'],
        'listing_image_id' => $transaction['listing_image_id'],
        'listing_id' => $transaction['listing_id'],
        'shipping_method' => $transaction['shipping_method']
    ];

    $nameFontMapping = array(
        'alexis' => 'arial',
        'barbie' => 'dollie',
        'bonnie' => 'bebas',
        'carol' => 'ambassador',
        'cowboy' => 'west',
        'darcy' => 'lightning',
        'disney' => 'new walt',
        'erica' => 'code',
        'flynn' => 'cavorting',
        'georgia' => 'cool',
        'isabella' => 'ludicrous',
        'jackson' => 'freshman',
        'kayla' => 'minya nouvelle',
        'laura' => 'montage',
        'maggie' => 'luna',
        'natalie' => 'master of break',
        'peter' => 'pacifico',
        'spaceman' => 'space ranger',
        'superhero' => 'avengeance',
        'tristan' => 'trashhand',
        'ulysses' => 'rosaline',
        'vintage' => 'vintage',
        'william' => 'sprightly',
        'adam' => 'worchestershire',
        'becky' => 'bujole',
        'chris' => 'black oval',
        'denise' => 'syukur',
        'edward' => 'bring',
        'fiona' => 'comely',
        'greg' => 'burtons',
        'hannah' => 'young coconut display',
        'isaac' => 'cartwheel',
        'jenna' => 'fashionista',
        'kyle' => 'young coconut script',
        'lisa' => 'fish',
        'mark' => 'charlotte',
        'nancy' => 'all spice',
        'olivia' => 'valentino',
        'paige' => 'parkranger',
        'reagan' => 'holiday brush',
        'scott' => 'sandy beaches',
        'tracy' => 'kubika',
        'ursala' => 'hot deals',
        'vanessa' => 'intruding cat',
        'wade' => 'lonssa',
        'zaine' => 'sling normal',
        'aaron' => 'tragic marker',
        'bailey' => 'lemon drizzle',
        'cathy' => 'adl mountain',
        'dennis' => 'pathfinder',
        'elviria' => 'blackchancery',
        'frank' => 'the trickster',
        'gabriela' => 'archemy',
        'hamlet' => 'klsunnybeach',
        'ingrid' => 'winery',
        'james' => 'cornelia',
        'kacey' => 'bangers',
        'lance' => 'horizon',
        'michael' => 'ardilla',
        'nadine' => 'lovestone',
        'owen' => 'jonathan',
        'patrick' => 'hello dina',
        'quimby' => 'paramount',
        'rachel' => 'stencil',
        'ryan' => 'valentine',
        'tyler' => 'stacked',
        'umberto' => 'old press',
        'victor' => 'some time later',
        'zena' => 'sofia'

);

 

    $variations_data = [];
    $vinyl_type = 'standard_colors'; // Default value
 echo '<br>';
    if (isset($transaction['variations'])) {
        foreach ($transaction['variations'] as $index => $variation) {
            $variations_data["variations_name_" . ($index + 1)] = $variation['formatted_name'];
            $variations_data["variations_value_" . ($index + 1)] = $variation['formatted_value'];

              if ($index + 1 === 1 && strpos(strtolower($variation['formatted_name']), 'font') !== false) {
                $base_font = strtolower($variation['formatted_value']);
                // Lookup the true font using the base font from the nameFontMapping array
                $true_font = $nameFontMapping[$base_font] ?? 'default_font'; // You can replace 'default_font' with a suitable default if the font name isn't found in the mapping.

                echo 'base font - ' .$base_font;
                echo '<br>';
                 echo 'true font - ' .$true_font;
                echo '<br>';

            }

         // Check if the current variation is the second one
            if ($index + 1 === 2) {
                
                // Always strip down the formatted_value
                $stripped_value_base = substr($variation['formatted_value'], 0, 4);
                $stripped_value = preg_replace('/[a-zA-Z]/', '', $stripped_value_base);
                 // Convert the stripped value to a float and add 0.1
                $adjusted_value = floatval($stripped_value) + 0.1;
                echo 'stripped value - ' . $stripped_value;
                echo '<br>';
                echo 'adjusted_value - ' . $adjusted_value;
                echo '<br>';

                // Then check if the formatted_value contains the word "Premium"
                if (strpos(strtolower($variation['formatted_value']), 'premium') !== false) {
                    $vinyl_type = 'glitter_colors';
                }
            }

        

           
        }
    }


    // If you need to use the $true_font in your return array, you can merge it like so:
return array_merge($baseData, $transactionData, $variations_data, ['vinyl_type' => $vinyl_type, 'true_font' => $true_font, 'true_size' => $adjusted_value]);
}









?>