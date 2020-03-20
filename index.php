<?php
/*
Plugin Name: Paysolutions Payment Gateway for WooCommerce
Plugin URI:
Description: WooCommerce with Paysolutions payment gateway.
Version: 1.0
Author: Panya  Saraphi
Author URI: http://www.opencart2u.com

Copyright: © 2020 
*/
		if ( ! defined( 'ABSPATH' ) )
				exit;
		add_action('plugins_loaded', 'woocommerce_amdev_paysolutions_init', 0);

		function woocommerce_amdev_paysolutions_init()
		{

				if ( !class_exists( 'WC_Payment_Gateway' ) ) return;

			 /**
			 * Gateway class
			 */
			 class WC_amdev_Paysolutions extends WC_Payment_Gateway
			 {

					public function __construct()
					{

								$this -> id           = 'paysolutions';
								$this -> method_title = __('Paysolutions', 'amdev');
								$this -> icon         =  plugins_url( 'images/logo.png' , __FILE__ );
								$this -> has_fields   = true;

								$this -> init_form_fields();
								$this -> init_settings();

								$this -> title            = $this -> settings['title'];
								$this -> description      = $this -> settings['description'];
								$this -> merchantId      = $this -> settings['merchantId'];
								$this -> merchantkey      = $this -> settings['merchantkey'];
								$this -> merchantApiName      = $this -> settings['merchantApiName'];
								$this -> sandbox      	  = $this -> settings['sandbox'];
								$this -> sandBoxURL      = $this -> settings['sandboxURL'];
								$this -> liveURL      = $this -> settings['liveURL'];

								if($this -> sandbox=='yes')
								{
									 $this -> liveurlonly = $this -> sandBoxURL;
								}
								else
								{
									$this -> liveurlonly = $this -> liveURL;
								}
								
								$this -> redirecturl  = $this -> liveurlonly;

								$this->notify_url = add_query_arg( 'wc-api', 'WC_amdev_Paysolutions', home_url( '/' ) );

								$this -> msg['message'] = "";
								$this -> msg['class']   = "";

								$this->currency_code = $this->get_paysolutions_currency_code();

								add_action( 'woocommerce_api_wc_amdev_paysolutions', array( $this, 'check_paysolutions_response' ) );

								if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) )
								{
										add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
								}
								else
								{
										add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
								}
								add_action('woocommerce_receipt_paysolutions', array($this, 'receipt_page'));
								add_action('woocommerce_thankyou_paysolutions',array($this, 'thankyou_page'));
								
								//Order Email Hook
								add_action( 'woocommerce_email_after_order_table', array($this, 'amdev_add_paysolutions_payment_id_to_order_email'), 10, 2 );
								
								//Order Thank you page Hook
								add_action( 'woocommerce_order_details_after_order_table', array($this, 'amdev_add_paysolutions_payment_id_to_order_detail_table'), 10, 1 );
								
					}

					/**
					 * is_valid_for_use()
					 *
					 * Check if this gateway is enabled and available in the base currency being traded with.
					 *
					 * @since 1.0.0
					 * @return bool
					 */
					public function is_valid_for_use() {
						$is_available          = false;
						$is_available_currency = in_array( get_woocommerce_currency(), $this->available_currencies );

						if ( $is_available_currency && $this->merchantId && $this->merchantKey ) {
							$is_available = true;
						}

						return $is_available;
					}

					public function get_paysolutions_currency_code() {

						$currencies = array (
								'AUD',
								'CAD',
								'EUR',
								'GBP',
								'JPY',
								'USD',
								'NZD',
								'CHF',
								'HKD',
								'SGD',
								'SEK',
								'DKK',
								'PLN',
								'NOK',
								'HUF',
								'CZK',
								'ILS',
								'MXN',
								'MYR',
								'BRL',
								'PHP',
								'TWD',
								'THB',
								'TRY',
								'THB' 
						);
						
						$paysolution_currencies = array (
								'THB' => '00',
								'USD' => '01',
								'JPY' => '02',
								'SGD' => '03',
								'HKD' => '04',
								'EUR' => '05',
								'GBP' => '06',
								'AUD' => '07',
								'CHF' => '08'
						);
						
						if (in_array ( get_woocommerce_currency(), $currencies )) {
							if (array_key_exists ( get_woocommerce_currency(), $paysolution_currencies )) {
								$currency = $paysolution_currencies [get_woocommerce_currency()];
								// $currency = $order_info['currency_code'];
							} else {
								$currency = '00'; // Default THB
							}
						} else {
							$currency = '00'; // Default THB
						}	
						
						return $currency;
		
					}


					function init_form_fields()
					{
							$this -> form_fields = array(
									'enabled' => array(
											'title' => __('Enable/Disable : ', 'amdev'),
											'type' => 'checkbox',
											'label' => __('Enable Paysolutions Payment Option.', 'amdev'),
											'default' => 'no'),
					 				'sandbox' => array(
											'title' => __('Enable Sandbox? : ', 'amdev'),
											'type' => 'checkbox',
											'label' => __('Enable Sandbox Paysolutions Payment.', 'amdev'),
											'default' => 'no'),
									'title' => array(
											'title' => __('Title : ', 'amdev'),
											'type'=> 'text',
											'description' => __('This controls the title which the user sees during checkout.', 'amdev'),
											'default' => __('Credit/Debit Card', 'amdev')),
									'description' => array(
											'title' => __('Description : ', 'amdev'),
											'type' => 'textarea',
											'description' => __('This controls the description which the user sees during checkout.', 'amdev'),
											'default' => __('Pay securely by Credit or Debit card through Paysolutions Secure Servers.', 'amdev')),
									'merchantId' => array(
											'title' => __('Merchant Id : ', 'amdev'),
											'type' => 'text',
											'description' => __('This is merchant Id - provided by Paysolutions team."')),
								    'merchantApiName' => array(
											'title' => __('Merchant API Name : ', 'amdev'),
											'type' => 'text',
											'description' => __('This is merchant API Name - provided by Paysolutions team."')),
									'merchantkey' => array(
											'title' => __('Key : ', 'amdev'),
											'type' => 'text',
											'description' =>  __('Secret Key - provided by Paysolutions', 'amdev')),
									'sandboxURL' => array(
											'title' => __('Sandbox URL : ', 'amdev'),
											'type' => 'text',
											'description' => __('This is the sandbox Payment Gateway URL provided by Paysolutions.', 'amdev'),
											'default' => __('https://sandbox.thaiepay.com/epaylink/payment.aspx', 'amdev')),
									'liveURL' => array(
											'title' => __('Live URL : ', 'amdev'),
											'type' => 'text',
											'description' => __('This is the live Payment Gateway URL provided by Paysolutions.', 'amdev'),
											'default' => __('https://www.thaiepay.com/epaylink/payment.aspx', 'amdev')
									)
							);

					}

					/**
					 * Admin Panel Options
					 * - Options for bits like 'title' and availability on a country-by-country basis
					 **/

					public function admin_options()
					{
							echo '<h3>'.__('Paysolutions Payment Gateway', 'amdev').'</h3>';
							echo '<p>'.__('Paysolutions is most popular payment gateway for online shopping in Thailand.').'</p>';
							echo '<table class="form-table">';
							$this -> generate_settings_html();
							echo '</table>';

					}

					/**
					 *  There are no payment fields for Paysolutions, but we want to show the description if set.
					 **/
					function payment_fields()
					{
							if($this -> description) echo wpautop(wptexturize($this -> description));
					}

					/**
					 * Receipt Page
					 **/
					function receipt_page($order)
					{
							//if($this -> iframemode=='no')
							echo '<p>'.__('Thank you for your order, please click the button below to pay with Paysolutions.', 'amdev').'</p>';
							echo $this -> generate_paysolutions_form($order);
					}

					/*** Thankyou Page**/
					function thankyou_page($order)
					{
							if (!empty($this->instructions))
							echo wpautop( wptexturize( $this->instructions ) );
					}
					
					/*** Show paysolutions transaction ID in order email ***/
					function amdev_add_paysolutions_payment_id_to_order_email( $order, $is_admin_email ) {
						$paysolutions_tid = get_post_meta( $order->get_id(), "paysolutions_tid", true);
						if(!empty($paysolutions_tid))
							echo '<table width="100%" style="border: 1px solid #e5e5e5;color: #636363;font-family: "Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;width: 100%;" border="1" cellspacing="0" cellpadding="6"><tbody><tr style="width:100%;"><th style="border: 1px solid #ccc;color: #636363;padding: 12px;text-align: left;">'.__('Paysolutions Transaction ID').': </th><td style="color: #636363;padding: 12px;text-align: right;width:25%"><span>'. $paysolutions_tid . '</span></td></tr></tbody></table>';
					}
					
					/*** Show paysolutions transaction ID in order detail page ***/
					function amdev_add_paysolutions_payment_id_to_order_detail_table($order){
						$paysolutions_tid = get_post_meta( $order->get_id(), "paysolutions_tid", true);
						if(!empty($paysolutions_tid))
							echo '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details"><tbody><tr><th scope="row">'.__('Paysolutions Transaction ID').':</th><td>' . $paysolutions_tid . '</td></tr></tbody></table>';
					}

					/**
					 * Process the payment and return the result
					 **/
					function process_payment($order_id)
					{
							$order = wc_get_order( $order_id );
							update_post_meta($order_id,'_post_data',$_POST);

							return array('result' => 'success', 'redirect' => $order->get_checkout_payment_url( true ));
					}

					/**
					 * Check for valid Paysolutions server callback
					 **/
					function check_paysolutions_response()
					{

						http_response_code(200);

						if(isset($_REQUEST['refno'])) {
							$req_order_id = intval($_REQUEST['refno']);
							$order = wc_get_order($req_order_id);

							if($order && $order -> get_status() !=='completed' && $order -> get_status() !=='processing') {
								$order_id = $order->get_id();
								update_post_meta( $order_id, 'paysolutions_tid', $req_order_id );

								$order -> payment_complete();
								$order -> add_order_note('Paysolutions payment successful<br/>Paysolutions Ref Number: '.$_REQUEST['refno']);
								WC()->cart->empty_cart();
								die(print_r($_REQUEST));
	

							} else {
								die(print_r($_REQUEST));
							}

						} else {
							die(print_r($_REQUEST));
						}

					}
					/**
					 * Generate Paysolutions button link
					 **/
					public function generate_paysolutions_form($order_id)
					{
							$order = wc_get_order($order_id);

							$post_data = get_post_meta($order_id,'_post_data',true);
							update_post_meta($order_id,'_post_data',array());

							$the_currency = get_woocommerce_currency();
							$the_order_total = $order->get_total();
							//$hash	= getHash($this->merchantId, $this->merchantkey, $order_id, $the_order_total);
							$description = get_bloginfo( 'name' ) . ' - ' . $order->get_id();

							$customer_id = get_current_user_id();
							$firstName = $order->get_billing_first_name();
							$lastName = $order->get_billing_last_name();
							$phone = $order->get_billing_phone();
							$email = $order->get_billing_email();
							

							$form = '
									<form action="' . $this->redirecturl.'?lang=t" method="post" id="payment_method_paysolutions">
									<!-- Button Fallback -->

									<input type="hidden" name="merchantid" value="'.$this->merchantId.'">
									<input type="hidden" name="total" value="'.$the_order_total.'">
									<input type="hidden" name="productdetail" value="'.$description.'">
									<input type="hidden" name="cc" value="'.$this->get_paysolutions_currency_code().'">
									<input type="hidden" name="returnurl" value="'.$this->get_return_url( $order ).'">
									<input type="hidden" name="customeremail" value="'.$email.'">
									<input type="hidden" name="refno" value="'.sprintf("%010d", $order_id).'">

									<input type="submit" class="button alt" id="checkout_button_nopopup" value="' . __( 'Pay Now', 'woocommerce' ) . '" /> 
									<a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'woocommerce' ) . '</a>


									</form>


									<h5>with Credit/Debit Card</h5>
									<img src="'.plugins_url( 'images/logo.png' , __FILE__ ).'" alt="Paysolutions" />
									';

							
							return $form;
					}
			}
			
			/**
			 * Add the Gateway to WooCommerce
			 **/
			function woocommerce_add_amdev_paysolutions_gateway($methods)
			{
					$methods[] = 'WC_amdev_Paysolutions';

					return $methods;
			}

			add_filter('woocommerce_payment_gateways', 'woocommerce_add_amdev_paysolutions_gateway' );
	}
	
	function getHash($paysolutions_merchant_id, $paysolutions_api_key, $transactionId, $amount = '') {
		$hash = base64_encode(hash_hmac('sha512', $paysolutions_merchant_id . $transactionId . $amount, $paysolutions_api_key, true));
		return $hash;
	}
