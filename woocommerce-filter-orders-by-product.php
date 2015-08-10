<?php
/**
 * Plugin Name: WooCommerce Filter Orders by Product
 * Plugin URI: http://flyoutapps.com
 * Description: This plugin lets you filter the WooCommrce Orders by any specific product
 * Version: 1.0.0
 * Author: flyoutapps
 * Author URI: http://flyoutapps.com
 * Text Domain: foa
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 */


if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && is_admin() ):
	add_action( 'restrict_manage_posts', 'foa_product_filter_in_order', 50 );
	add_filter( 'posts_where' , 'foa_product_filter_where' );
endif;

// Display dropdown
function foa_product_filter_in_order(){
	global $typenow, $wpdb;

	if ( 'shop_order' != $typenow ) {
		return;
	}

    $sql="SELECT ID,post_title FROM $wpdb->posts WHERE post_type = 'product' AND post_status = 'publish'";
	$all_posts = $wpdb->get_results($sql, ARRAY_A);

	$values = array();

	foreach ($all_posts as $all_post) {
		$values[$all_post['post_title']] = $all_post['ID'];
	}
    ?>
    <select name="foa_order_product_filter">
    <option value=""><?php _e('All products', 'foa'); ?></option>
    <?php
        $current_v = isset($_GET['foa_order_product_filter'])? $_GET['foa_order_product_filter']:'';
        foreach ($values as $label => $value) {
            printf
                (
                    '<option value="%s"%s>%s</option>',
                    $value,
                    $value == $current_v? ' selected="selected"':'',
                    $label
                );
            }
    ?>
    </select>
    <?php
}

// modify where clause in query
function foa_product_filter_where( $where ) {
	if( is_search() ) {
		global $wpdb;
		$t_posts = $wpdb->posts;
		$t_order_items = $wpdb->prefix . "woocommerce_order_items";  
		$t_order_itemmeta = $wpdb->prefix . "woocommerce_order_itemmeta";

		if ( isset( $_GET['foa_order_product_filter'] ) && !empty( $_GET['foa_order_product_filter'] ) ) {
			$product = $_GET['foa_order_product_filter'];
			$where .= " AND $product = (SELECT $t_order_itemmeta.meta_value FROM $t_order_items LEFT JOIN $t_order_itemmeta on $t_order_itemmeta.order_item_id=$t_order_items.order_item_id WHERE $t_order_items.order_item_type='line_item' AND $t_order_itemmeta.meta_key='_product_id' AND $t_posts.ID=$t_order_items.order_id)";
		}
	}
	return $where;
}

// Localization
add_action('plugins_loaded', 'foa_fobp_textdomain');
function foa_fobp_textdomain() {
	load_plugin_textdomain( 'foa', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
}