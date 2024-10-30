<?php

namespace MaxSlider\Select2\Controls;

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('\Elementor\Base_Data_Control')) {
    return;
}
use \Elementor\Base_Data_Control;

class Select2 extends Base_Data_Control
{
    public function get_type()
    {
        return 'max-slider-select2';
    }

	public function enqueue() {
		wp_register_script( 'max-slider-select2', MAX_SLIDER_URL . 'assets/js/max-slider-select2.js',
			[ 'jquery-elementor-select2' ], VERSION, true );
		wp_localize_script(
			'max-slider-select2',
			'max_slider_select2_localize',
			[
				'ajaxurl'         => esc_url( admin_url( 'admin-ajax.php' ) ),
				'search_text'     => esc_html__( 'Search', 'max-slider' ),
				'remove'          => __( 'Remove', 'max-slider' ),
				'thumbnail'       => __( 'Image', 'max-slider' ),
				'name'            => __( 'Title', 'max-slider' ),
				'price'           => __( 'Price', 'max-slider' ),
				'quantity'        => __( 'Quantity', 'max-slider' ),
				'subtotal'        => __( 'Subtotal', 'max-slider' ),
				'cl_login_status' => __( 'User Status', 'max-slider' ),
				'cl_post_type'    => __( 'Post Type', 'max-slider' ),
				'cl_browser'      => __( 'Browser', 'max-slider' ),
				'cl_date_time'    => __( 'Date & Time', 'max-slider' ),
				'cl_recurring_day'=> __( 'Recurring Day', 'max-slider' ),
				'cl_dynamic'      => __( 'Dynamic Field', 'max-slider' ),
				'cl_query_string' => __( 'Query String', 'max-slider' ),
				'cl_visit_count'  => __( 'Visit Count', 'max-slider' ),
			]
		);
		wp_enqueue_script( 'max-slider-select2' );
	}

    protected function get_default_settings()
    {
        return [
            'multiple' => false,
            'source_name' => 'post_type',
            'source_type' => 'post',
        ];
    }

    public function content_template()
    {
        $control_uid = $this->get_control_uid();
        ?>
        <# var controlUID = '<?php echo esc_html( $control_uid ); ?>'; #>
        <# var currentID = elementor.panel.currentView.currentPageView.model.attributes.settings.attributes[data.name]; #>
        <div class="elementor-control-field">
            <# if ( data.label ) { #>
            <label for="<?php echo esc_attr( $control_uid ); ?>" class="elementor-control-title">{{{data.label }}}</label>
            <# } #>
            <div class="elementor-control-input-wrapper elementor-control-unit-5">
                <# var multiple = ( data.multiple ) ? 'multiple' : ''; #>
                <select id="<?php echo esc_attr( $control_uid ); ?>" {{ multiple }} class="max-slider-select2" data-setting="{{ data.name }}"></select>
            </div>
        </div>
        <#
        ( function( $ ) {
        $( document.body ).trigger( 'max_slider_select2_init',{currentID:data.controlValue,data:data,controlUID:controlUID,multiple:data.multiple} );
        }( jQuery ) );
        #>
        <?php
    }
}
