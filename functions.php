<?php
/*******************************************************************
 * Change variation product button
 ******************************************************************/
function variation_radio_buttons($html, $args) {
	$args = wp_parse_args(apply_filters('woocommerce_dropdown_variation_attribute_options_args', $args), array(
		'options'          => false,
		'attribute'        => false,
		'product'          => false,
		'selected'         => false,
		'name'             => '',
		'id'               => '',
		'class'            => '',
		'show_option_none' => __('Choose an option', 'woocommerce'),
	));
  
	if(false === $args['selected'] && $args['attribute'] && $args['product'] instanceof WC_Product) {
	  $selected_key     = 'attribute_'.sanitize_title($args['attribute']);
	  $args['selected'] = isset($_REQUEST[$selected_key]) ? wc_clean(wp_unslash($_REQUEST[$selected_key])) : $args['product']->get_variation_default_attribute($args['attribute']);
	}
  
	$options               = $args['options'];
	$product               = $args['product'];
	$attribute             = $args['attribute'];
	$name                  = $args['name'] ? $args['name'] : 'attribute_'.sanitize_title($attribute);
	$id                    = $args['id'] ? $args['id'] : sanitize_title($attribute);
	$class                 = $args['class'];
	$show_option_none      = (bool)$args['show_option_none'];
	$show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __('Choose an option', 'woocommerce');
  
	if(empty($options) && !empty($product) && !empty($attribute)) {
	  $attributes = $product->get_variation_attributes();
	  $options    = $attributes[$attribute];
	}
  
	$radios = '<div class="variation-radios">';
  
	if(!empty($options)) {
	  	if($product && taxonomy_exists($attribute)) {
			$terms = wc_get_product_terms($product->get_id(), $attribute, array(
				'fields' => 'all',
			));
	
			foreach($terms as $term) {
				if(in_array($term->slug, $options, true)) {
					$radios .= '<label for="'.esc_attr($term->slug).'">'.esc_html(apply_filters('woocommerce_variation_option_name', $term->name)).'<input type="radio" name="'.esc_attr($name).'" value="'.esc_attr($term->slug).'" id="'.esc_attr($term->slug).'"'.checked(sanitize_title($args['selected']), $term->slug, false).'><span class="checkmark"></span></label>';
				}
			}
	  	} else {
			foreach($options as $option) {
				$checked    = sanitize_title($args['selected']) === $args['selected'] ? checked($args['selected'], sanitize_title($option), false) : checked($args['selected'], $option, false);
				$radios    .= '<input type="radio" name="'.esc_attr($name).'" value="'.esc_attr($option).'" id="'.sanitize_title($option).'" '.$checked.'><label for="'.sanitize_title($option).'">'.esc_html(apply_filters('woocommerce_variation_option_name', $option)).'</label>';
			}
	  	}
	}
  
	$radios .= '</div>';
  
	return $html.$radios;
}
add_filter('woocommerce_dropdown_variation_attribute_options_html', 'variation_radio_buttons', 20, 2);

// It adds a JS script only on the WooCommerce product page.
function add_script_to_product_page() {

    // Only on the product page.
    if ( ! is_product() ) {
        return;
    }
    ?>
    <script type="text/javascript">
		jQuery(document).on('change', '.variation-radios input', function() {
			jQuery('select[name="'+jQuery(this).attr('name')+'"]').val(jQuery(this).val()).trigger('change');
		});
    </script>
    <?php
}
add_action( 'wp_footer', 'add_script_to_product_page' );


// It adds a Style only on the WooCommerce product page.
function add_style_to_product_page() {
    if ( ! is_product() ) {
        return;
    }
    ?>
    <style>
		table.variations select {
			display: none;
		}
    </style>
    <?php
}
add_action( 'wp_head', 'add_style_to_product_page' );