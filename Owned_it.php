<?php
/*
    Plugin Name: Owned it - Conversation Rate Optimization
    Plugin URI: https://www.ownedit.com
    Description:  Owned it is an easy to use on-store conversion optimization tool that helps you to create targeted and personalized marketing campaigns for your visitors and customers.
    Version: 2.0
    Author: Owned it Ltd
    Author URI: https://www.ownedit.com
*/

// Checking version
global $wp_version;

if(!version_compare($wp_version, '3.0', '>='))
{
    die("Owned it extension requires WordPress 3.0 or above. <a href='http://codex.wordpress.org/Upgrading_WordPress'>Please update!</a>");
}
// END - Version check

//wordpress bug http://core.trac.wordpress.org/ticket/16953
$ownedit_file = __FILE__; 

if ( isset( $mu_plugin ) ) { 
    $ownedit_file = $mu_plugin; 
} 
if ( isset( $network_plugin ) ) { 
    $ownedit_file = $network_plugin; 
} 
if ( isset( $plugin ) ) { 
    $ownedit_file = $plugin; 
} 

$GLOBALS['ownedit_file'] = $ownedit_file;


if(!class_exists('Ownedit')) :

    class OwneditWidget extends WP_Widget {
        function OwneditWidget() {
            parent::WP_Widget(false, 'Owned it Widget', array('description' => 'Description'));
        }

        function widget($args, $instance) {
            echo '<div id="ownedit_widget"></div>';
        }

        function update( $new_instance, $old_instance ) {
            return parent::update($new_instance, $old_instance);
        }

        function form( $instance ) {
            return parent::form($instance);
        }
    }

    function ownedit_widget_register_widgets() {
        register_widget('OwneditWidget');
    }

    class Ownedit				
    {
        
        private $plugin_id;		
       
        private $options;		

        public function __construct($id)
        {

            $this->plugin_id = $id;           

            $this->options = array();         
           
            /*
            * Add Hooks
            */
            register_activation_hook(__FILE__, array(&$this, 'install'));  			
			
			//Run on thankyou page
			add_action('woocommerce_thankyou', array(&$this, 'ownedit_scripts'));	
			
			//Run on every page
			add_action('wp_head', array(&$this, 'ownedit_prepurchase_script'));	

            add_action('admin_init', array(&$this, 'init'));						

            add_action('admin_menu', array(&$this, 'menu'));						

            add_action('widgets_init', 'ownedit_widget_register_widgets');			
           
        }

        /** function/method
        * Usage: return plugin options
        * Arg(0): null
        * Return: array
        */
        private function get_options()
        {
            $options = get_option($this->plugin_id);        
            return $options;
        }
        /** function/method
        * Usage: update plugin options
        * Arg(0): null
        * Return: void
        */
        private function update_options($options=array())
        {
            update_option($this->plugin_id, $options);          
        }

        /** function/method
        * Usage: helper for loading ownedit.js
        * Arg(0): null
        * Return: void
        */
        public function ownedit_prepurchase_script()
        {
        	//Checking order confirmation page or not
        	if(!is_order_received_page()){ 
        	$options = $this->get_options();
                $storeid = trim($options['storeid']);
				if($storeid){
					$order_total = floatval( preg_replace( '#[^\d.]#', '', WC()->cart->get_cart_total() ) );
        		 	$owneditJS = '';
	  				$owneditJS .= "<script type = \"text/javascript\">";
	  				$owneditJS .= "var _ownedit = _ownedit || {};";
	  				$owneditJS .= "_ownedit['custom_variables'] = {
			   					total_products : '".WC()->cart->cart_contents_count."',
			   					order_total	  : '". $order_total ."'
		   						};";
					$owneditJS .= "var ss = document.createElement('script');ss.src = 'https://cdn.ownedit.com/ownedit_js/ownedit.js?store_id=".$storeid."&prepurchase=true'
						   ss.type = 'text/javascript';ss.async = 'true';var s = document.getElementsByTagName('head')[0];s.appendChild(ss);";
					$owneditJS.="</script>";
					echo $owneditJS;
				}
        	}
        	
        }
        public function ownedit_scripts($order_id)
        {
                $options = $this->get_options();
                $storeid = trim($options['storeid']);
				if($storeid){
					$owneditJS = "<script type=\"text/javascript\" src=\"https://cdn.ownedit.com/ownedit_js/ownedit.js?store_id=".$storeid."&anchor=anchor\"></script>";
			$arr = array();
			$order = new WC_Order( $order_id );
			$arr['order_id'] =$order->get_order_number();
			$arr['customer_email'] = $order->billing_email;
			$arr['order_value'] = $order->get_total();
			$arr['order_currency'] = get_option( 'woocommerce_currency' );
	      	$arr['store_name']= get_option('blogname');
			$products = array();
	      	$items = $order->get_items();
			foreach($items as $item){
				$prod = array();
				$prod['product_name'] = $item['name'];
				$prod['product_url'] = apply_filters( 'woocommerce_order_table_product_title',get_permalink( $item['product_id']));
				$post_data = get_post( $item['product_id'] );
				$prod['product_desc'] = strip_tags(html_entity_decode($post_data->post_content, ENT_QUOTES, 'UTF-8'));;
				$img = get_the_post_thumbnail( $item['product_id']);
				if($img){
					$img_src = (string) reset(simplexml_import_dom(DOMDocument::loadHTML($img))->xpath("//img/@src"));
				}
				else{
					$img_place_src = apply_filters( 'woocommerce_single_product_image_html', sprintf( '<img src="%s" alt="Placeholder" />', woocommerce_placeholder_img_src() ), $item['product_id'] );
					$img_src =(string) reset(simplexml_import_dom(DOMDocument::loadHTML($img_place_src))->xpath("//img/@src"));
				}
				$prod['product_image_url'] = $img_src;
				$prod['product_price'] = sprintf("%.2f",$item['line_total']);
				$prod['currency'] = get_option( 'woocommerce_currency' );
				$prod['product_id'] = $item['product_id'];
				$prod['product_quantity'] = $item['qty'];
				$current_category = get_the_terms( $item['product_id'], 'product_cat' ) ;
				if ( $current_category && ! is_wp_error ( $current_category ) ){
						$product_category = array_shift( $current_category );
						$prod['product_category'] = $product_category->name;
				}
				else{
						$prod['product_category'] = "";
				}
				array_push($products,$prod);
			}
			$arr['products']=$products;
			$json = json_encode($arr);
			$owneditJS.="<script type=\"text/javascript\">";
	  	    $owneditJS.="function post_to_owned_it(){";
	    	$owneditJS.="var details =$json;";
	      	$owneditJS.="post_it(details);}onLoadCallBack(post_to_owned_it);</script>";
			echo $owneditJS;
				}
        }

		

        public function install()
        {
            $this->update_options($this->options);
        }
        

        public function init()
        {
            register_setting($this->plugin_id.'_options', $this->plugin_id);
        }
                
        /** function/method
        * Usage: show options/settings form page
        * Arg(0): null
        * Return: void
        */
        public function options_page()
        {
            if (!current_user_can('manage_options'))
            {
                wp_die( __('You can manage options from the Settings->ownedit Options menu.') );
            }


            $options = $this->get_options();            
            $updated = false;

            if (!isset($options['enable_rewards'])) {
                $options['enable_rewards'] = 1;
                $updated = true;
            }

            if ($updated) {
                $this->update_options($options);
            }
            include('Owned_it_options.php');
        }
 
        public function menu()
        {
            add_options_page('Owned it Options', 'Owned it', 'manage_options', $this->plugin_id.'-plugin', array(&$this, 'options_page'));
        }
    }


    $ownedit = new ownedit('ownedit');    


endif;			
?>
