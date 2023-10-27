<?php 

function checkAndUpdateTokens() {
    $timestamp = get_option('timestamp');
    $access_token = get_option('access_token');
    $refresh_token = get_option('refresh_token');
//	echo $refresh_token;
    // Calculate the current epoch timestamp
   $current_timestamp = time();
echo $current_timestamp - $timestamp;
    // Check if the access token needs refreshing (older than 3600 seconds)
    if ($current_timestamp - $timestamp > 3600) {
        // Use the refresh token to obtain a new access token
        $new_access_token = refreshAccessToken($refresh_token);

        // Update the options table with the new tokens and timestamp
   //     update_option('access_token', $new_access_token);
     //   update_option('timestamp', $current_timestamp);

        // Optionally, you can handle error cases when refreshing the token.
        // For example, if the refresh token is invalid or expired.
    } else {
		echo 'Access Token is still Valid';
	}
}

function refreshAccessToken($refresh_token) {
    // Make a request to the OAuth provider (Etsy) to refresh the access token
    // You'll need to implement this based on Etsy's API documentation
    // Typically, it involves sending a POST request with the refresh token.
	
	   // Define the Etsy API token endpoint URL
    $token_url = 'https://api.etsy.com/v3/public/oauth/token';
	echo $token_url;
	echo '</br></br>';
	echo $client_id =  get_option('etsy_client_id');
	echo '</br></br>';
	echo $redirect_uri = admin_url('admin.php?page=get_etsy_orders_callback');
	// Define the POST data
    $post_data = array(
        'grant_type' => 'refresh_token',
        'refresh_token' => $refresh_token,
        'client_id' => $client_id // Replace with your Etsy client ID
        
    );
	
	 // Initialize cURL session
    $ch = curl_init();

 // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CAINFO, '/wamp64/www/wordpress/cacert.pem');
	
	// Execute the cURL request
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        echo 'cURL Error: ' . curl_error($ch);
        return false;
    }

    // Close the cURL session
    curl_close($ch);

    // Parse the JSON response
    $data = json_decode($response, true);
	var_dump($data);
	 // Check if the response contains an access token
    if (isset($data['access_token'])) {
		echo $data['access_token'];
        // Update the options table with the new access token
        update_option('access_token', $data['access_token']);

        // Optionally, store the new refresh token if it's provided in the response
        if (isset($data['refresh_token'])) {
            update_option('refresh_token', $data['refresh_token']);
        }

        // Optionally, store a timestamp for token expiration tracking
        update_option('timestamp', time());

        return true;
    } else {
        // Handle the error response from Etsy (e.g., invalid refresh token)
        echo 'Error refreshing access token: ' . print_r($data, true);
        return false;
    }
}
    


?>
