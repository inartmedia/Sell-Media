<?php

/*
 * simpleCart js
 */
function sell_media_cart_js(){

	$settings = sell_media_get_plugin_options(); ?>

	<script type="text/javascript">
	jQuery(document).ready(function($){

		function createPayment( data ){

			$.ajax({
				url: ajaxurl,
				data: {
					'action': 'sell_media_ajax_create_payment',
					'cart_data' : data
				},
				success:function( data ) {
					// This outputs the result of the ajax request
					console.log( data );
				},
				error: function( errorThrown ){
					console.log( errorThrown );
				}
			});
		}

		simpleCart({
			cartStyle: "table",
			checkout: {
				type: "PayPal",
				email: "<?php echo $settings->paypal_email; ?>"
			},
			cartColumns: [
				{ view: "image", attr: "image", label: false },
				{ attr: "name", label: "Name" },
				{ attr: "size", label: "Size" },
				{ view: "license", attr: "license", label: "License" },
				{ attr: "price", label: "Price", view: "currency" },
				{ view: "decrement", label: false, text: "-" },
				{ attr: "quantity", label: "Qty" },
				{ view: "increment", label: false, text: "+" },
				{ attr: "total", label: "SubTotal", view: "currency" },
				{ view: "remove", text: "Remove", label: false }
			],
			currency: "<?php echo $settings->currency; ?>",
			success: "<?php echo get_permalink( $settings->thanks_page ); ?>",
			cancel: "<?php echo get_permalink( $settings->checkout_page ); ?>",
			notify_url: "<?php echo site_url( '?sell_media-listener=IPN' ); ?>",
			shipping: 0 // 0 prompt & optional, 1 no prompt, 2 prompt & required
			
		});

		// callback beforeCheckout
		simpleCart.bind( 'beforeCheckout' , function( data ){
			// validate items and price sent to cart
			// optionally create new draft post (getting rid of this)
			// createPayment(data);
			console.log(data);
		});
	});
	</script>

<?php }
add_action( 'wp_head', 'sell_media_cart_js' );


/*
 * Set Ajax URL
 */
function sell_media_ajaxurl() {
?>
	<script type="text/javascript">
		var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
	</script>
<?php
}
add_action( 'wp_head', 'sell_media_ajaxurl' );


/*
 * Callback to create a new pending payment
 */
function sell_media_ajax_create_payment(){

	if ( isset($_REQUEST) ) {
		$cart_data = $_REQUEST['cart_data'];

		$data = array(
			'post_title' => $cart_data[0],
			'post_status' => 'pending',
			'post_type' => 'sell_media_payment',
			'post_date' => date('Y-m-d H:i:s')
		);

		$payment_id = wp_insert_post( $data );

		die();
	}
}
add_action( 'wp_ajax_sell_media_ajax_create_payment', 'sell_media_ajax_create_payment' );

/*
 * Checkout Shortcode
 */
function sell_media_checkout_shortcode( $atts ){

	do_action( 'sell_media_checkout_before_cart' );
	$html = '<div class="simpleCart_items"></div>';
	$html .= '<div class="sell-media-totals">';
	$html .= '<div class="subtotal"><span class="sell-media-itemize">' . __( 'Subtotal', 'sell_media' ) . ':</span> <span class="simpleCart_total sell-media-bold"></span></div>';
	$html .= '<div class="tax"><span class="sell-media-itemize">' . __( 'Tax', 'sell_media' ) . ':</span> <span class="simpleCart_tax sell-media-bold"></span></div>';
	$html .= '<div class="shipping"><span class="sell-media-itemize">' . __( 'Shipping', 'sell_media' ) . ':</span> <span class="simpleCart_shipping sell-media-bold"></span></div>';
	$html .= '<div class="total"><span class="sell-media-itemize">'  . __( 'Total', 'sell_media' ) . ':</span> <span class="simpleCart_grandTotal sell-media-bold sell-media-green"></span></div>';
	$html .= '</div>';
	do_action( 'sell_media_checkout_registration_fields' );
	do_action( 'sell_media_checkout_after_registration_fields' );
	$html .= '<a href="javascript:;" class="simpleCart_checkout sell-media-buy-button">'. __( 'Checkout', 'sell_media' ) . '</a>';
	do_action( 'sell_media_checkout_after_checkout_button' );

	return $html;

}
add_shortcode( 'sell_media_checkout', 'sell_media_checkout_shortcode' );