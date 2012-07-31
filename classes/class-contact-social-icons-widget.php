<?php
class Contact_Social_Icons_Widget extends WP_Widget {
	function __construct() {
		$widget_options = array(
			'classname'   => 'sau_social_icons_widget', 
			'description' => __( 'Displays a dynamic list of social media links based on the contact profile that is currently being viewed. This widget only displays on indivudal contact profiles.' ), 
		);
		$control_options = array(
			'width'   => 250, /* optional */
			'height'  => 350, /* optional */
		);
		
		parent::__construct( 
			'sau_social_icons_widget', /* Base ID */
			'SAU Contact Social Icons Widget', /* Widget Name */
			$widget_options, 
			$control_options
		);
	}
	
	function form( $instance ) {
		$instance = wp_parse_args( $instance, array( 'title' => null ) );
?>
<p><label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Title:' ) ?></label> 
	<input type="text" name="<?php echo $this->get_field_name( 'title' ) ?>" id="<?php echo $this->get_field_id( 'title' ) ?>" value="<?php echo esc_attr( $instance['title'] ) ?>" /></p>
<?php
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = empty( $new_instance['title'] ) ? null : esc_attr( $new_instance['title'] );
		
		return $instance;
	}
	
	function widget( $args, $instance ) {
		if ( ! is_singular( 'contact' ) )
			return;
		
		global $sau_campus_directory_obj, $post;
		
		$sau_campus_directory_obj->get_social_array();
		$wpcm_social_fields = array();
		foreach ( $sau_campus_directory_obj->social as $field ) {
			$tmp = get_post_meta( $post->ID, $field['name'] . '_wpcm_value', true );
			if ( ! empty( $tmp ) )
				$wpcm_social_fields[$field['name']] = array( 'field' => $field, 'value' => apply_filters( 'sau-contact-social-field-' . $field['name'], $tmp ) );
		}
		
		if ( empty( $wpcm_social_fields ) )
			return;
		
		extract( $args );
		
		$firstname = get_post_meta( $post->ID, 'first_name_wpcm_value', true );
		$lastname = get_post_meta( $post->ID, 'last_name_wpcm_value', true );
		
		$instance['title'] = sprintf( str_replace( array( '%firstname%', '%lastname%' ), array( '%1$s', '%2$s' ), $instance['title'] ), $firstname, $lastname );
		
		$title = empty( $instance['title'] ) ? '' : $before_title . $instance['title'] . $after_title;
		
		echo $before_widget;
		echo $title;
		
		echo '<ul class="social-icons">';
		foreach ( $wpcm_social_fields as $k => $v ) {
			if ( empty( $v['value'] ) )
				continue;
			
			echo '<li class="' . esc_attr( strtolower( $k ) ) . '"><a href="' . $v['value'] . '">' . $v['field']['title'] . '</a></li>';
		}
		echo '</ul>';
		
		echo $after_widget;
	}
}