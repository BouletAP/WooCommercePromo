<?php

namespace BouletAP;

class CouponModel {

    //static $instance = false;

    static function getSubForCoupon($slug) {
        $targeted_sub = false;
        $amount = self::getCouponAmount($slug);
        if( $amount ) {
            list($free_days, $targeted_sub) = explode('.', trim($amount));
        }
        return $targeted_sub;
    }
    static function getDaysForCoupon($slug) {
        $free_days = false;
        $amount = self::getCouponAmount($slug);
        if( $amount ) {
            list($free_days, $targeted_sub) = explode('.', trim($amount));
        }
        return $free_days;
    }



    static function getCouponAmount($slug) {

        $coupon = new \WC_Coupon($slug);
        $free_days = 0;
		$targeted_sub = 0;

		if( !$coupon ) {
			return false;
		}

		$coupon_type = wcs_get_coupon_property( $coupon, 'discount_type' );

		if ( in_array( $coupon_type, array( 'free_days_by_subs' ) ) ) {			

			$amt = $coupon->get_amount();
			if( strpos($amt, '.') !== FALSE ) {
                return $amt;
			}
		}
        return false;
    }

}