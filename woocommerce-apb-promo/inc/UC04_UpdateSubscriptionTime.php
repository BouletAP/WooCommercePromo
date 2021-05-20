<?php


class UC04_UpdateSubscriptionTime {


    function init() {        

		add_action( 'woocommerce_thankyou', array($this, 'update_subscription'), 90 );
    }


	function update_subscription( $order_id ) {

		echo '<pre>'; print_r($order_id); echo '</pre>';die();
		$order = wc_get_order( $order_id );

		

		// Loop through WC_Order_Item_Coupon Objects
		foreach ( $order->get_items( 'coupon' ) as $item ) {
			// Get the WC_Coupon Object
			$coupon = new WC_Coupon($item->get_code());
			
			$sub_id = CouponModel::getSubForCoupon($coupon_code);

			if( $sub_id ) {
				echo '<pre>'; print_r($sub_id); echo '</pre>';die();
			}
		}
	}

}