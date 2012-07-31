<?php
class Contact_Feed_Widget extends WP_Widget {
	function __construct() {
		$widget_options = array(
			'classname'   => 'sau_feed_widget', 
			'description' => __( 'Displays a dynamic RSS widget showing news items related to the staff member currently being viewed. Only appears on single Contact entries, and will only appear if the feed exists and has items to display.' ), 
		);
		$control_options = array(
			'width'   => 250, /* optional */
			'height'  => 350, /* optional */
		);
		
		parent::__construct( 
			'sau_contact_feed_widget', /* Base ID */
			'SAU Contact Feed Widget', /* Widget Name */
			$widget_options, 
			$control_options
		);
	}
	
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, apply_filters( 'sau-contact-feed-widget-defaults', array( 
			'title'        => null, 
			'url'          => null, 
			'items'        => 5, 
			'show_summary' => false, 
			'show_author'  => false, 
			'show_date'    => false, 
		) ) );
?>
<p><label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Title:' ) ?></label> 
	<input type="text" name="<?php echo $this->get_field_name( 'title' ) ?>" id="<?php echo $this->get_field_id( 'title' ) ?>" value="<?php echo esc_attr( $instance['title'] ) ?>" /></p>
<p><label for="<?php echo $this->get_field_id( 'items' ) ?>"><?php _e( 'Number of items to display:' ) ?></label> 
	<input type="text" name="<?php echo $this->get_field_name( 'items' ) ?>" id="<?php echo $this->get_field_id( 'items' ) ?>" value="<?php echo intval( $instance['items'] ) ?>" /></p>
<p><input type="checkbox" name="<?php echo $this->get_field_name( 'show_summary' ) ?>" id="<?php echo $this->get_field_id( 'show_summary' ) ?>" value="1"<?php checked( $instance['show_summary'] ) ?> /> 
	<label for="<?php echo $this->get_field_id( 'show_summary' ) ?>"><?php _e( 'Show summary with each post?' ) ?></label></p>
<p><input type="checkbox" name="<?php echo $this->get_field_name( 'show_author' ) ?>" id="<?php echo $this->get_field_id( 'show_author' ) ?>" value="1"<?php checked( $instance['show_author'] ) ?> /> 
	<label for="<?php echo $this->get_field_id( 'show_author' ) ?>"><?php _e( 'Show author of each post?' ) ?></label></p>
<p><input type="checkbox" name="<?php echo $this->get_field_name( 'show_date' ) ?>" id="<?php echo $this->get_field_id( 'show_date' ) ?>" value="1"<?php checked( $instance['show_date'] ) ?> /> 
	<label for="<?php echo $this->get_field_id( 'show_date' ) ?>"><?php _e( 'Show publish date of each post?' ) ?></label></p>
<?php
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = empty( $new_instance['title'] ) ? null : esc_attr( $new_instance['title'] );
		$instance['items'] = empty( $new_instance['items'] ) || ! is_numeric( $new_instance['items'] ) ? 5 : intval( $new_instance['items'] );
		$instance['show_summary'] = isset( $new_instance['show_summary'] ) && '1' == $new_instance['show_summary'] ? true : false;
		$instance['show_author'] = isset( $new_instance['show_author'] ) && '1' == $new_instance['show_author'] ? true : false;
		$instance['show_date'] = isset( $new_instance['show_date'] ) && '1' == $new_instance['show_date'] ? true : false;
		
		return $instance;
	}
	
	function widget( $args, $instance ) {
		global $post;
		if ( ! is_singular( 'contact' ) )
			return;
		
		/**
		 * Check to make sure the feed exists before 
		 * 		trying to output anything
		 */
		$url = $this->test_feed( $post->ID );
		if ( false === $url )
			return;
		
		$firstname = get_post_meta( $post->ID, 'first_name_wpcm_value', true );
		$lastname = get_post_meta( $post->ID, 'last_name_wpcm_value', true );
		
		$instance['url'] = $url;
		$instance['title'] = sprintf( str_replace( array( '%firstname%', '%lastname%' ), array( '%1$s', '%2$s' ), $instance['title'] ), $firstname, $lastname );
		
		print( "\n<!-- Feed URL: {$instance['url']} -->\n" );
		
		/**
		 * Run the standard RSS feed widget, now that we've 
		 * 		built the URL for the feed
		 */
		the_widget( 'WP_Widget_RSS', $instance, $args );
	}
	
	/**
	 * Check a feed to make sure it exists before trying to output a feed widget with it
	 */
	function test_feed( $post_id ) {
		if ( isset( $_REQUEST['delete_sau_transient'] ) )
			delete_transient( 'contact-feed-widget-' . $post_id );
		
		if ( false !== ( $tmp = get_transient( 'contact-feed-widget-' . $post_id ) ) )
			return intval( $tmp ) === 0 ? $tmp : false;
		
		$firstname = get_post_meta( $post_id, 'first_name_wpcm_value', true );
		$lastname = get_post_meta( $post_id, 'last_name_wpcm_value', true );
		if ( empty( $firstname ) || empty( $lastname ) )
			return false;
		
		if ( ! class_exists( 'WP_Http' ) )
			include_once( ABSPATH . WPINC . '/class-http.php' );
		
		$url = apply_filters( 'sau-contact-feed-address', 'http://web.saumag.edu/news/?feed=rss2&s=%s' );
		$url = sprintf( $url, '"' . urlencode( $firstname . ' ' . $lastname ) . '"' );
		$url = $url;
		if ( empty( $url ) )
			return false;
		
		$request = new WP_Http();
		$response = $request->request( $url );
		if ( is_wp_error( $response ) || ! is_array( $response ) )
			return false;
		
		set_transient( 'contact-feed-widget-' . $post_id, ( 200 === intval( $response['response']['code'] ) ? $url : 0 ), apply_filters( 'sau-contact-short-timeout', 60 * 60 ) );
		return 200 === intval( $response['response']['code'] ) ? $url : false;
	}
}
