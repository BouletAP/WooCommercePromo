<?php


class UC02_ApplyCoupon {

	public $coupon = false;

    function init() {        

		//add_filter( 'woocommerce_subscriptions_product_price_string', array($this, 'format_pricing'), 80, 3 );

		// Handle discounts
		add_filter( 'woocommerce_coupon_get_discount_amount', array($this, 'get_discount_amount'), 10, 5 );

    }


	function format_pricing($subscription_string, $product, $include) {

		$free_days = 0;
		$targeted_sub = 0;

		if( !$this->coupon ) {
			return $subscription_string;
		}

		$coupon_type = wcs_get_coupon_property( $this->coupon, 'discount_type' );

		if ( in_array( $coupon_type, array( 'free_days_by_subs' ) ) ) {			

			$amt = $this->coupon->get_amount();
			if( strpos($amt, '.') !== FALSE ) {
				list($free_days, $targeted_sub) = explode('.', trim($amt));
			}

			
			// product ID == subs ID ??

			//$subscription_string = str_replace("1 year", "$free_days days", $subscription_string);
			$subscription_string = "Gratuit {$free_days} jours"; //__("");
		}




		return $subscription_string;
	}


	public function get_discount_amount( $discount, $discounting_amount, $item, $single, $coupon ) {

		$this->coupon = $coupon;

		if ( is_a( $item, 'WC_Order_Item' ) ) { // WC 3.2 support for applying coupons to line items via admin edit subscription|order screen
			$discount = self::get_discount_amount_for_line_item( $item, $discount, $discounting_amount, $single, $coupon );
		} else {
			$discount = self::get_discount_amount_for_cart_item( $item, $discount, $discounting_amount, $single, $coupon );
		}
		
		return $discount;
	}

	/**
	 * Get the discount amount which applies for a cart item for subscription coupon types
	 *
	 * @since 2.2.13
	 * @param array $cart_item
	 * @param float $discount the original discount amount
	 * @param float $discounting_amount the cart item price/total which the coupon should apply to
	 * @param boolean $single True if discounting a single qty item, false if it's the line
	 * @param WC_Coupon $coupon
	 * @return float the discount amount which applies to the cart item
	 */
	public static function get_discount_amount_for_cart_item( $cart_item, $discount, $discounting_amount, $single, $coupon ) {

		$coupon_type = wcs_get_coupon_property( $coupon, 'discount_type' );

		// Only deal with subscriptions coupon types which apply to cart items
		if ( ! in_array( $coupon_type, array( 'free_days_by_subs' ) ) ) {
			return $discount;
		}


		$free_days = 0;
		$targeted_sub = 0;

		$amt = $coupon->get_amount();
		if( strpos($amt, '.') !== FALSE ) {
			list($free_days, $targeted_sub) = explode('.', trim($amt));
		}

		// If not a subscription product return the default discount
		if ( ! wcs_cart_contains_renewal() && ! WC_Subscriptions_Product::is_subscription( $cart_item['data'] ) ) {
			return $discount;
		}
		// But if cart contains a renewal, we need to handle both subscription products and manually added non-susbscription products that could be part of a subscription
		if ( wcs_cart_contains_renewal() && ! self::is_subsbcription_renewal_line_item( $cart_item['data'], $cart_item ) ) {
			return $discount;
		}

		// nothing to apply? invalid sub?
		if ( $free_days === 0 || $targeted_sub === 0 ) {
			return $discount;
		}

		//echo '<pre>'; print_r(); echo '</pre>'; die();

		// Set our starting discount amount to 0
		if( $cart_item['product_id'] == $targeted_sub ) {
			$discount_amount = $cart_item['line_total'];
		}



		// Round - consistent with WC approach
		$discount_amount = round( $discount_amount, wcs_get_rounding_precision() );
		//echo '<pre>'; print_r($discount_amount); echo '</pre>'; die();
		return $discount_amount;
	}


	/**
	 * Get the discount amount which applies for a line item for subscription coupon types
	 *
	 * Uses methods and data structures introduced in WC 3.0.
	 *
	 * @since 2.2.13
	 * @param WC_Order_Item $line_item
	 * @param float $discount the original discount amount
	 * @param float $discounting_amount the line item price/total
	 * @param boolean $single True if discounting a single qty item, false if it's the line
	 * @param WC_Coupon $coupon
	 * @return float the discount amount which applies to the line item
	 */
	public static function get_discount_amount_for_line_item( $line_item, $discount, $discounting_amount, $single, $coupon ) {

		//echo 'get_discount_amount_for_line_item<pre>'; print_r($discount); echo '</pre>'; die();

		if ( ! is_callable( array( $line_item, 'get_order' ) ) ) {
			return $discount;
		}

		$coupon_type = wcs_get_coupon_property( $coupon, 'discount_type' );
		$order       = $line_item->get_order();
		$product     = $line_item->get_product();

		// Recurring coupons can be applied to subscriptions or any order which contains a subscription
		if ( in_array( $coupon_type, array( 'recurring_fee', 'recurring_percent' ) ) && ( wcs_is_subscription( $order ) || wcs_order_contains_subscription( $order, 'any' ) ) ) {
			if ( 'recurring_fee' === $coupon_type ) {
				$discount = min( $coupon->get_amount(), $discounting_amount );
				$discount = $single ? $discount : $discount * $line_item->get_quantity();
			} else { // recurring_percent
				$discount = (float) $coupon->get_amount() * ( $discounting_amount / 100 );
			}
		// Sign-up fee coupons apply to parent order line items which are subscription products and have a signup fee
		} elseif ( in_array( $coupon_type, array( 'sign_up_fee', 'sign_up_fee_percent' ) ) && WC_Subscriptions_Product::is_subscription( $product ) && wcs_order_contains_subscription( $order, 'parent' ) && 0 !== WC_Subscriptions_Product::get_sign_up_fee( $product ) ) {
			if ( 'sign_up_fee' === $coupon_type ) {
				$discount = min( $coupon->get_amount(), WC_Subscriptions_Product::get_sign_up_fee( $product ) );
				$discount = $single ? $discount : $discount * $line_item->get_quantity();
			} else { // sign_up_fee_percent
				$discount = (float) $coupon->get_amount() * ( WC_Subscriptions_Product::get_sign_up_fee( $product ) / 100 );
			}
		}

		return $discount;
	}
}