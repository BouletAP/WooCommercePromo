<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * Plugin Name:       Woocommerce Promo for subs
 * Plugin URI:        https://bouletap.com
 * Description:       Extra promo types for subscriptions
 * Version:           1.0.0
 * Author:            Logiciels BouletAP
 * Author URI:        https://bouletap.com
 * License:           All Right Reserved
 * Text Domain:       woocommerce-apb-promo
 */

// error_reporting(E_ALL);
// ini_set("display_errors", true);
 

require_once(__DIR__ . '/inc/CouponModel.php');

require_once(__DIR__ . '/inc/UC01_CreateCoupon.php');
require_once(__DIR__ . '/inc/UC02_ApplyCoupon.php');
require_once(__DIR__ . '/inc/UC03_CouponOnboarding.php');


class WooCommerceAPBPromo {
    public function init() {

		$uc01 = new UC01_CreateCoupon();
		$uc01->init();
		
		$uc02 = new UC02_ApplyCoupon();
		$uc02->init();


		$uc03 = new UC03_CouponOnboarding();
		$uc03->init();
    }    
}
if( !isset($wooCommerceAPBPromo) ) {
	global $wooCommerceAPBPromo;
	$wooCommerceAPBPromo = new WooCommerceAPBPromo();
	$wooCommerceAPBPromo->init();
}