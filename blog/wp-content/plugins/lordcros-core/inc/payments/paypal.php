<?php
/*
 * Paypal related functions
 */

/* Process paypal payment */
if ( ! function_exists( 'lordcros_core_process_payment' ) ) {
	function lordcros_core_process_payment( $payment_data, $api_info ) { 
		// $payment_data = array( 'item_name', 'item_number', 'item_desc', 'item_qty', 'item_price', 'item_total_price', 'grand_total', 'status', 'return_url', 'cancel_url', 'deposit_rate' )

		$PayPalApiUsername = $api_info['paypal_api_username'];
		$PayPalApiPassword = $api_info['paypal_api_password'];
		$PayPalApiSignature = $api_info['paypal_api_signature'];
		$PayPalMode = empty( $api_info['paypal_sandbox'] ) ? 'live' : 'sandbox';

		// SetExpressCheckOut
		if ( ! isset( $_GET["token"] ) || ! isset( $_GET["PayerID"] ) ) {
			$padata = 	'&METHOD=SetExpressCheckout' .
						'&RETURNURL=' . urlencode( $payment_data['return_url'] ) .
						'&CANCELURL=' . urlencode( $payment_data['cancel_url'] ) .
						'&PAYMENTREQUEST_0_PAYMENTACTION=' . urlencode( "SALE" ) .
						'&L_PAYMENTREQUEST_0_NAME0=' . urlencode( $payment_data['item_name'] ) .
						'&L_PAYMENTREQUEST_0_NUMBER0=' . urlencode( $payment_data['item_number'] ) .
						'&L_PAYMENTREQUEST_0_DESC0=' . urlencode( $payment_data['item_desc'] ) .
						'&L_PAYMENTREQUEST_0_AMT0=' . urlencode( $payment_data['item_price'] ) .
						'&L_PAYMENTREQUEST_0_QTY0=' . urlencode( $payment_data['item_qty'] ) .
						'&NOSHIPPING=1' .
						'&SOLUTIONTYPE=Sole' .
						'&PAYMENTREQUEST_0_ITEMAMT=' . urlencode( $payment_data['item_total_price'] ) .
						'&PAYMENTREQUEST_0_AMT=' . urlencode( $payment_data['grand_total'] ) .
						'&PAYMENTREQUEST_0_CURRENCYCODE=' . urlencode( $payment_data['currency'] ) .
						'&LOCALECODE=US' .
						'&CARTBORDERCOLOR=FFFFFF' .
						'&ALLOWNOTE=1';
			
			//We need to execute the "SetExpressCheckOut" method to obtain paypal token
			$httpParsedResponseAr = lordcros_core_paypal_http_post( 'SetExpressCheckout', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode );

			//Respond according to message we receive from Paypal
			if ( "SUCCESS" == strtoupper( $httpParsedResponseAr["ACK"] ) || "SUCCESSWITHWARNING" == strtoupper( $httpParsedResponseAr["ACK"] ) ) {
				//Redirect user to PayPal store with Token received.
				$paypalmode = ( $PayPalMode == 'sandbox' ) ? '.sandbox' : '';
				$paypalurl 	='https://www' . $paypalmode . '.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $httpParsedResponseAr["TOKEN"] . '';

				header( 'Location: ' . $paypalurl );
				exit;
			} else {
				//Show error message
				echo '<div class="alert alert-warning"><b>Error : </b>' . urldecode( $httpParsedResponseAr["L_LONGMESSAGE0"] ) . '<span class="close"></span></div>';
				echo '<pre>';
				print_r( $httpParsedResponseAr );
				echo '</pre>';

				exit;
			}
		}

		// DoExpressCheckOut
		if ( isset( $_GET["token"] ) && isset( $_GET["PayerID"] ) ) {
			$token = $_GET["token"];
			$payer_id = $_GET["PayerID"];

			$padata = 	'&TOKEN=' . urlencode( $token ) .
						'&PAYERID=' . urlencode( $payer_id ) .
						'&PAYMENTREQUEST_0_PAYMENTACTION=' . urlencode( "SALE" ) .
						'&L_PAYMENTREQUEST_0_NAME0=' . urlencode( $payment_data['item_name'] ) .
						'&L_PAYMENTREQUEST_0_NUMBER0=' . urlencode( $payment_data['item_number'] ) .
						'&L_PAYMENTREQUEST_0_DESC0=' . urlencode( $payment_data['item_desc'] ) .
						'&L_PAYMENTREQUEST_0_AMT0=' . urlencode( $payment_data['item_price'] ) .
						'&L_PAYMENTREQUEST_0_QTY0=' . urlencode( $payment_data['item_qty'] ) .
						'&PAYMENTREQUEST_0_ITEMAMT=' . urlencode( $payment_data['item_total_price'] ) .
						'&PAYMENTREQUEST_0_AMT=' . urlencode( $payment_data['grand_total'] ) .
						'&PAYMENTREQUEST_0_CURRENCYCODE=' . urlencode( $payment_data['currency'] );

			// execute the "DoExpressCheckoutPayment" at this point to Receive payment from user.
			$httpParsedResponseAr = lordcros_core_paypal_http_post( 'DoExpressCheckoutPayment', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode );

			// Check if everything went ok..
			if ( "SUCCESS" == strtoupper( $httpParsedResponseAr["ACK"] ) || "SUCCESSWITHWARNING" == strtoupper( $httpParsedResponseAr["ACK"] ) ) {
				$transation_id = urldecode( $httpParsedResponseAr["PAYMENTINFO_0_TRANSACTIONID"] );

				//echo '<div class="alert alert-success">' . __( 'Payment Received Successfully! Your Transaction ID : ', 'lordcros-core' ) . $transation_id . '<span class="close"></span></div>';

				// GetTransactionDetails requires a Transaction ID, and GetExpressCheckoutDetails requires Token returned by SetExpressCheckOut
				$padata = '&TOKEN=' . urlencode( $token );
				$httpParsedResponseAr = lordcros_core_paypal_http_post( 'GetExpressCheckoutDetails', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode );

				if ( "SUCCESS" == strtoupper( $httpParsedResponseAr["ACK"] ) || "SUCCESSWITHWARNING" == strtoupper( $httpParsedResponseAr["ACK"] ) ) {
					return array( 
						'success'		 => 1, 
						'method'		 => 'paypal', 
						'transaction_id' => $transation_id 
					);
				} else  {
					echo '<div class="alert alert-warning"><b>GetTransactionDetails failed:</b>' . urldecode( $httpParsedResponseAr["L_LONGMESSAGE0"] ) . '<span class="close"></span></div>';
					echo '<pre>';
					print_r( $httpParsedResponseAr );
					echo '</pre>';

					exit;
				}
			} else {
				echo '<div class="alert alert-warning"><b>Error : </b>' . urldecode( $httpParsedResponseAr["L_LONGMESSAGE0"] ) . '<span class="close"></span></div>';
				echo '<pre>';
				print_r( $httpParsedResponseAr );
				echo '</pre>';

				exit;
			}
		}

		return false;
	}
}

/* Send post request to paypal */
if ( ! function_exists( 'lordcros_core_paypal_http_post' ) ) {
	function lordcros_core_paypal_http_post( $methodName_, $nvpStr_, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode ) {
		// Set up your API credentials, PayPal end point, and API version.
		$API_UserName = urlencode( $PayPalApiUsername );
		$API_Password = urlencode( $PayPalApiPassword );
		$API_Signature = urlencode( $PayPalApiSignature );

		$paypalmode = ( $PayPalMode=='sandbox') ? '.sandbox' : '';

		$API_Endpoint = "https://api-3t" . $paypalmode . ".paypal.com/nvp";
		$version = urlencode( '109.0' );

		// Set the curl parameters.
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $API_Endpoint );
		curl_setopt( $ch, CURLOPT_VERBOSE, 1 );

		// Turn off the server and peer verification (TrustManager Concept).
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		// curl_setopt( $ch, CURLOPT_SSLVERSION , 1 );
		// curl_setopt( $ch, CURLOPT_SSLVERSION , 4 );

		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POST, 1 );

		// Set the API operation, version, and API signature in the request.
		$nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

		// Set the request as a POST FIELD for curl.
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $nvpreq );

		// Get response from the server.
		$httpResponse = curl_exec( $ch );

		if( ! $httpResponse ) {
			exit( "$methodName_ failed: " . curl_error( $ch ) . '(' . curl_errno( $ch ) . ')' );
		}

		// Extract the response details.
		$httpResponseAr = explode( "&", $httpResponse );

		$httpParsedResponseAr = array();
		foreach ( $httpResponseAr as $i => $value ) {
			$tmpAr = explode( "=", $value );
			if( sizeof( $tmpAr ) > 1 ) {
				$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
			}
		}

		if( ( 0 == sizeof( $httpParsedResponseAr ) ) || ! array_key_exists( 'ACK', $httpParsedResponseAr ) ) {
			exit( "Invalid HTTP Response for POST request( $nvpreq ) to $API_Endpoint." );
		}

		return $httpParsedResponseAr;
	}
}