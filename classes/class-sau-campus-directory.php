<?php
/**
 * Define the general SAU Campus Directory class
 */
class SAU_Campus_Directory {
	/**
	 * Create a container to hold generic messages that need to be output
	 */
	var $messages = array();
	var $social = array();
	var $meta = array();
	
	/**
	 * Construct the class object
	 */
	function __construct() {
		remove_action( 'admin_notices', 'sau_campus_directory_no_genesis' );
		$this->get_meta_array();
		
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		
		add_action( 'genesis_theme_settings_metaboxes', array( $this, 'add_genesis_meta_boxes' ) );
		
		/**
		 * Enable the ability to convert old format to new
		 */
		add_action( 'admin_menu', array( $this, 'add_submenu_page' ) );
		
		if ( is_admin() )
			wp_register_script( 'sau-campus-directory-admin', plugins_url( '/js/sau-campus-directory-admin.js', dirname( __FILE__ ) ), array( 'jquery' ), '0.2.10', true );
		
		/**
		 * Register all of our new RSS feeds
		 */
		add_feed( apply_filters( 'sau-contact-feed-slug', 'd/feed', 'department' ), array( $this, 'departments_feed' ) );
		add_feed( apply_filters( 'sau-contact-feed-slug', 'a/feed', 'alpha' ), array( $this, 'alpha_feed' ) );
		add_feed( apply_filters( 'sau-contact-feed-slug', 'b/feed', 'building' ), array( $this, 'building_feed' ) );
		
		/**
		 * Add various filters/actions to make our standard RSS feeds useful
		 */
		add_filter( 'the_title_rss', array( $this, 'the_title_rss_alpha' ), 1 );
		add_filter( 'the_excerpt_rss', array( $this, 'feed_item_alpha_excerpt' ), 1 );
		add_filter( 'the_content_feed', array( $this, 'feed_item_alpha' ), 1 );
		add_action( 'rss2_item', array( $this, 'feed_item_enclosure' ) );
		
		/**
		 * Make sure image/page URLs point to somewhere on this site, rather than
		 * 		pointing to somewhere on the original site
		 */
		add_filter( 'sau-contact-check-url', array( $this, 'filter_old_url' ) );
		
		/**
		 * Make all queries in this site query in alpha order, rather than date order
		 */
		add_action( 'pre_get_posts', array( $this, 'alpha_posts' ) );
		
		wp_register_style( 'sau-contact', plugins_url( '/css/sau-contact.css', dirname( __FILE__ ) ), array(), '0.1.3', 'all' );
		
		/**
		 * Unfortunately, WordPress doesn't support adding conditional comments 
		 * 		to scripts (only styles), so we have to kind of hack this together
		 * 		for now. The correct way of doing this is commented out below, so 
		 * 		it can be used in the future, hopefully, if WP implements it
		 */
		global $is_IE;
		if ( $is_IE )
			wp_register_script( 'sau-columns', plugins_url( '/js/sau-campus-directory.columns.js', dirname( __FILE__ ) ), array( 'jquery' ), '0.1.4', true );
		/*global $wp_scripts;
		$wp_scripts->add_data( 'sau-columns', 'conditional', 'lt ie10' );*/
		
		/**
		 * Filter the Twitter handle to return a URL
		 */
		add_filter( 'sau-contact-social-field-twitter', array( $this, 'twitter_url' ) );
		
		/**
		 * Prepare to register the feed widget
		 */
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
	}
	
	/**
	 * Ensures all queries pull posts in ascending alpha order.
	 * Called by using the pre_get_posts action within the __construct() method of this class
	 */
	function alpha_posts( $query ) {
		if ( is_admin() )
			return $query;
		
		$query->set( 'orderby', 'title' );
		$query->set( 'order', 'ASC' );
	}
	
	/**
	 * Build our RSS feed of all departments
	 */
	function departments_feed() {
		remove_filter( 'the_title_rss', array( $this, 'the_title_rss_alpha' ), 1 );
		remove_filter( 'the_excerpt_rss', array( $this, 'feed_item_alpha_excerpt' ), 1 );
		remove_filter( 'the_content_feed', array( $this, 'feed_item_alpha' ), 1 );
		remove_action( 'rss2_item', array( $this, 'feed_item_enclosure' ) );
		
		add_action( 'saumag-contact-taxonomy-feed-head', array( $this, 'feed_query_departments' ) );
		add_filter( 'get_wp_title_rss', array( $this, 'feed_title_departments' ) );
		
		$template_location = apply_filters( 'sau-contact-tax-feed-path', plugin_dir_path( dirname( __FILE__ ) ) . 'templates/taxonomy-feed.php', 'department' );
		/**
		 * If the filtered location is a URL, we need to translate it into a path
		 */
		if ( esc_url( $template_location ) )
			$template_location = $this->url2filepath( $template_location );
		if ( is_wp_error( $template_location ) )
			wp_die( $template_location->get_error_message() );
		/**
		 * If the filtered location does not exist, we need to reset to the default
		 */
		if ( ! file_exists( $template_location ) )
			$template_location = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/taxonomy-feed.php';
		/**
		 * If it still doesn't exist, we need to jump out before trying to use it
		 */
		if ( ! file_exists( $template_location ) )
			return;
		
		/**
		 * Output the feed by loading the chosen template
		 */
		load_template( $template_location );
	}
	
	/**
	 * Build our RSS feed of all people alphabetically
	 */
	function alpha_feed() {
		status_header(200);
		
		add_action( 'rss2_head', array( $this, 'feed_query_alpha' ) );
		add_filter( 'get_wp_title_rss', array( $this, 'feed_title_alpha' ) );
		
		do_feed_rss2( false );
	}
	
	/**
	 * Build our RSS feed of all buildings
	 */
	function building_feed() {
		remove_filter( 'the_title_rss', array( $this, 'the_title_rss_alpha' ), 1 );
		remove_filter( 'the_excerpt_rss', array( $this, 'feed_item_alpha_excerpt' ), 1 );
		remove_filter( 'the_content_feed', array( $this, 'feed_item_alpha' ), 1 );
		remove_action( 'rss2_item', array( $this, 'feed_item_enclosure' ) );
		
		add_action( 'saumag-contact-taxonomy-feed-head', array( $this, 'feed_query_buildings' ) );
		add_filter( 'get_wp_title_rss', array( $this, 'feed_title_buildings' ) );
		
		$template_location = apply_filters( 'sau-contact-tax-feed-path', plugin_dir_path( dirname( __FILE__ ) ) . 'templates/taxonomy-feed.php', 'building' );
		/**
		 * If the filtered location is a URL, we need to translate it into a path
		 */
		if ( esc_url( $template_location ) )
			$template_location = $this->url2filepath( $template_location );
		if ( is_wp_error( $template_location ) )
			wp_die( $template_location->get_error_message() );
		/**
		 * If the filtered location does not exist, we need to reset to the default
		 */
		if ( ! file_exists( $template_location ) )
			$template_location = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/taxonomy-feed.php';
		/**
		 * If it still doesn't exist, we need to jump out before trying to use it
		 */
		if ( ! file_exists( $template_location ) )
			return;
		
		/**
		 * Output the feed by loading the chosen template
		 */
		load_template( $template_location );
	}
	
	/**
	 * Run the query to pull a list of departments
	 */
	function feed_query_departments() {
		global $sau_feed_terms;
		
		$sau_feed_terms = get_terms( 'department', array( 
			'orderby'      => 'name', 
			'hide_empty'   => 0, 
			'hierarchical' => 1, 
		) );
		
		if ( empty( $sau_feed_terms ) )
			$sau_feed_terms = array();
		
		return $sau_feed_terms;
	}
	
	/**
	 * Build and retrieve the title for Departments feed
	 * @uses apply_filters() to apply the sau-contact-departments-feed-title filter to the title
	 */
	function feed_title_departments( $title ) {
		$title = apply_filters( 'sau-contact-departments-feed-title', __( 'Departments' ) );
	}
	
	/**
	 * Run the query to pull the full alphabetical list of contacts
	 * @uses apply_filters() to apply the sau-contact-items-per-feed filter to the number of items shown in the feed
	 */
	function feed_query_alpha() {
		global $wp_query;
		$query_vars = $wp_query->query_vars;
		$query_vars = array(
			'post_type'      => 'contact', 
			'post_status'    => 'publish', 
			'orderby'        => 'title', 
			'order'          => 'ASC', 
			'posts_per_page' => apply_filters( 'sau-contact-items-per-feed', -1, 'alpha' ), 
		);
		
		query_posts( $query_vars );
	}
	
	/**
	 * Filter the main title of the alphabetical feed channel
	 * @uses apply_filters() to apply the sau-contact-alpha-feed-title filter to the title
	 */
	function feed_title_alpha( $title ) {
		$title = apply_filters( 'sau-contact-alpha-feed-title', __( 'Contact Directory' ) );
	}
	
	/**
	 * Filter the title of an article in the alphabetical feed
	 */
	function the_title_rss_alpha( $title ) {
		global $post;
		$title = $this->contact_post_title( $title, $post );
		
		/**
		 * Make sure the post content is not empty so the "the_content_feed" action is certain to fire
		 */
		$post->post_content = strlen( $post->post_content ) > 0 ? $post->post_content : ' ';
		
		return $title;
	}
	
	/**
	 * Retrieve the full content of a contact for the alpha feed
	 */
	function feed_item_alpha( $content ) {
		global $post;
		
		$base_url = parse_url( get_bloginfo( 'url' ) );
		$base_url = esc_url( trailingslashit( $base_url['scheme'] . '://' . $base_url['host'] ) );
		
		$content = str_replace( array( "'/", '"/' ), array( "'$base_url", '"' . $base_url ), str_replace(']]>', ']]&gt;', $this->get_single_entry() ) );
		
		return $content;
	}
	
	/**
	 * Retrieve the excerpt of an item for the alpha feed
	 */
	function feed_item_alpha_excerpt( $content ) {
		global $post;
		
		$base_url = parse_url( get_bloginfo( 'url' ) );
		$base_url = esc_url( trailingslashit( $base_url['scheme'] . '://' . $base_url['host'] ) );
		
		$content = str_replace( array( "'/", '"/' ), array( "'$base_url", '"' . $base_url ), $this->get_archive_entry() );
		
		return $content;
	}
	
	/**
	 * Check for a featured image & include it as an enclosure in a feed
	 */
	function feed_item_enclosure() {
		error_reporting( E_ALL );
		$url = null;
		
		global $post;
		if ( empty( $post ) || ! is_object( $post ) ) {
			/*echo '<error>No post object</error>';*/
			return;
		}
		
		if ( ! has_post_thumbnail( $post->ID ) ) {
			$wpcm_image_value = get_post_meta( $post->ID, 'image_path_wpcm_value', true );
			
			/**
			 * If we still didn't find an image to use, we'll kick out of the function
			 */
			if ( empty( $wpcm_image_value ) )
				return;
			
			$url = esc_url( $wpcm_image_value );
		}
		
		/**
		 * If we didn't look for and/or find a URL in the old meta style, grab the URL of the featured image
		 */
		if ( empty( $url ) )
			$url = esc_url( wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), apply_filters( 'sau-contact-enclosure-size', 'full' ) ) );
		
		if ( empty( $url ) ) {
			/*echo '<error>There was an issue retrieving the URL</error>';*/
			return;
		}
		
		$url = apply_filters( 'sau-contact-check-url', $url );
		
		$pInfo = pathinfo( $url );
		if ( ! array_key_exists( 'extension', $pInfo ) )
			return;
		
		switch( strtolower( $pInfo['extension'] ) ) {
			case 'png' :
				$mime = 'image/png';
				break;
			case 'jpg' :
			case 'jpeg' : 
				$mime = 'image/jpeg';
				break;
			case 'gif' : 
				$mime = 'image/gif';
				break;
			default : 
				$mime = 'application/octet-stream';
		}
		
		$path = $this->url2filepath( $url );
		
		if ( is_wp_error( $path ) || ! file_exists( $path ) )
			return;
		
		$size = filesize( $path );
		
		echo '<enclosure url="' . $url . '" length="' . $size . '" type="' . $mime . '" />' . "\n";
	}
	
	/**
	 * Retrieve an array of items to include in the feed of the buildings taxonomy
	 */
	function feed_query_buildings() {
		global $sau_feed_terms;
		
		$sau_feed_terms = get_terms( 'building', array( 
			'orderby'      => 'name', 
			'hide_empty'   => 0, 
			'hierarchical' => 1, 
		) );
		
		if ( empty( $sau_feed_terms ) )
			$sau_feed_terms = array();
		
		return $sau_feed_terms;
	}
	
	/**
	 * Build and retrieve the title for Buildings feed
	 * @uses apply_filters() to apply the sau-contact-buildings-feed-title filter to the title
	 */
	function feed_title_buildings( $title ) {
		$title = apply_filters( 'sau-contact-buildings-feed-title', __( 'Contact Buildings' ) );
	}
	
	/**
	 * Build the array of meta fields
	 * @uses apply_filters() to apply the sau-contact-meta-fields filter to the array
	 */
	function get_meta_array() {
		return $this->meta = apply_filters( 'sau-contact-meta-fields', array(
			array(
				'name'        => 'first_name',
				'std'         => '',
				'title'       => __( 'First Name' ),
				'description' => '', 
			),
			array(
				'name'        => 'last_name',
				'std'         => '',
				'title'       => __( 'Last Name' ),
				'description' => '', 
			),
			array(
				'name'        => 'organization',
				'std'         => '',
				'title'       => __( 'Organization' ),
				'description' => '', 
			),
			array(
				'name'        => 'title',
				'std'         => '',
				'title'       => __( 'Title' ),
				'description' => '', 
			),
			array(
				'name'        => 'email',
				'std'         => '',
				'title'       => __( 'Email' ),
				'description' => '', 
			),
			array(
				'name'        => 'mobile',
				'std'         => '',
				'title'       => __( 'Mobile' ),
				'description' => '', 
			),
			array(
				'name'        => 'office_phone',
				'std'         => '',
				'title'       => __( 'Office Phone' ),
				'description' => '', 
			),
			array(
				'name'        => 'home_phone',
				'std'         => '',
				'title'       => __( 'Home Phone' ),
				'description' => '', 
			),
			array(
				'name'        => 'fax',
				'std'         => '',
				'title'       => __( 'Fax' ),
				'description' => '', 
			),
			array(
				'name'        => 'website',
				'std'         => '',
				'title'       => __( 'Website' ),
				'description' => '', 
			),
			array(
				'name'        => 'address1',
				'std'         => '',
				'title'       => __( 'P.O. Box' ),
				'description' => '', 
			),
			array(
				'name'        => 'address_2',
				'std'         => '',
				'title'       => __( 'Slot Number' ),
				'description' => '', 
			),
			array(
				'name'        => 'city',
				'std'         => '',
				'title'       => __( 'Building/Room' ),
				'description' => '', 
				'type'        => 'hidden', 
			),
			array(
				'name'        => 'state',
				'std'         => '',
				'title'       => __( 'State' ),
				'description' => '', 
			),
			array(
				'name'        => 'zip',
				'std'         => '',
				'title'       => __( 'Zip' ),
				'description' => '', 
			),
			array(
				'name'        => 'country',
				'std'         => '',
				'title'       => __( 'Country' ),
				'description' => '', 
			),
			array(
				'name'        => 'image_path',
				'std'         => '',
				'title'       => __( 'Image Path' ),
				'description' => '', 
				'type'        => 'hidden', 
			),
		) );
	}
	
	/**
	 * Build the array of social media fields
	 */
	function get_social_array() {
		return $this->social = apply_filters( 'sau-contact-social-fields', array(
			array(
				'name'        => 'facebook', 
				'title'       => __( 'Facebook' ), 
				'description' => __( 'Enter the URL (address) to your Facebook profile or page' ), 
				'type'        => 'url', 
			), 
			array(
				'name'        => 'twitter', 
				'title'       => __( 'Twitter' ), 
				'description' => __( 'Enter your Twitter handle (username)' ), 
			), 
			array(
				'name'        => 'linkedin', 
				'title'       => __( 'LinkedIn' ), 
				'description' => __( 'Enter the URL (address) to your LinkedIn public profile or page' ), 
				'type'        => 'url', 
			), 
			array(
				'name'        => 'youtube', 
				'title'       => __( 'YouTube' ), 
				'description' => __( 'Enter the URL (address) to your YouTube profile or channel' ), 
				'type'        => 'url', 
			), 
			array(
				'name'        => 'vimeo', 
				'title'       => __( 'Vimeo' ), 
				'description' => __( 'Enter the URL (address) to your Vimeo profile' ), 
				'type'        => 'url', 
			), 
			array(
				'name'        => 'blog', 
				'title'       => __( 'Blog Feed' ), 
				'description' => __( 'Enter the URL (address) to your blog or website RSS feed' ), 
				'type'        => 'url', 
			), 
		) );
	}
	
	/**
	 * Register the appropriate post type and taxonomies
	 * @uses register_post_type()
	 * @uses register_taxonomy()
	 */
	function register_post_type() {
		/**
		 * Set the various labels for the "contact" post type
		 */
		$labels = array(
			'name'          => _x( 'Contacts', 'post type general name' ),
			'singular_name' => _x( 'Contract', 'post type singular name' ),
			'add_new'       => _x( 'Add New', 'contact' ),
			'add_new_item'  => __( 'Add New Contact' ),
			'edit_item'     => __( 'Edit Contact' ),
			'new_item'      => __( 'New Contact' ),
			'all_items'     => __( 'All Contacts' ),
			'view_item'     => __( 'View Contacts' ),
			'search_items'  => __( 'Search Contacts' ),
			'not_found'     =>  __( 'No contacts found' ),
			'not_found_in_trash' => __( 'No contacts found in Trash' ), 
			'parent_item_colon' => '',
			'menu_name'     => 'Contacts'
		);
		/**
		 * Set the appropriate arguments for the "contact" post type
		 */
		$args = array(
			'labels'        => $labels,
			'public'        => true,
			'publicly_queryable' => true,
			'show_ui'       => true, 
			'show_in_menu'  => true, 
			'query_var'     => true,
			'rewrite'       => true,
			'capability_type' => 'post',
			'has_archive'   => true, 
			'hierarchical'  => false,
			'menu_position' => null,
			'supports'      => array( 'title', 'editor', 'author', 'thumbnail' ), 
		);
		register_post_type( 'contact', $args );
		
		/**
		 * Define the various labels for the "department" taxonomy
		 */
		$labels = array(
			'name'          => _x( 'Departments', 'taxonomy general name' ),
			'singular_name' => _x( 'Department', 'taxonomy singular name' ),
			'search_items'  =>  __( 'Search Departments' ),
			'all_items'     => __( 'All Departments' ),
			'parent_item'   => __( 'Parent Department' ),
			'parent_item_colon' => __( 'Parent Department:' ),
			'edit_item'     => __( 'Edit Department' ), 
			'update_item'   => __( 'Update Department' ),
			'add_new_item'  => __( 'Add New Department' ),
			'new_item_name' => __( 'New Department Name' ),
			'menu_name'     => __( 'Departments' ),
		);
		/**
		 * Define the appropriate arguments for the "department" taxonomy
		 */
		$args = array(
			'labels'       => $labels, 
			'hierarchical' => true, 
			'public'       => true, 
			'rewrite'      => array( 
				'slug'         => 'departments', 
				'with_front'   => true, 
				'hierarchical' => true, 
			), 
		);
		register_taxonomy( 'department', array( 'contact' ), $args );
		
		/**
		 * Define the various labels for the "department" taxonomy
		 */
		$labels = array(
			'name'          => _x( 'Tags', 'taxonomy general name' ),
			'singular_name' => _x( 'Tag', 'taxonomy singular name' ),
			'search_items'  =>  __( 'Search Tags' ),
			'all_items'     => __( 'All Tags' ),
			'parent_item'   => __( 'Parent Tag' ),
			'parent_item_colon' => __( 'Parent Tag:' ),
			'edit_item'     => __( 'Edit Tag' ), 
			'update_item'   => __( 'Update Tag' ),
			'add_new_item'  => __( 'Add New Tag' ),
			'new_item_name' => __( 'New Tag Name' ),
			'menu_name'     => __( 'Tags' ),
		);
		/**
		 * Define the appropriate arguments for the "department" taxonomy
		 */
		$args = array(
			'labels'       => $labels, 
			'hierarchical' => false, 
			'public'       => true, 
		);
		register_taxonomy( 'contact-tag', array( 'contact' ), $args );
		
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
		
		/**
		 * Define the various labels for the "department" taxonomy
		 */
		$labels = array(
			'name'          => _x( 'Buildings', 'taxonomy general name' ),
			'singular_name' => _x( 'Building', 'taxonomy singular name' ),
			'search_items'  =>  __( 'Search Buildings' ),
			'all_items'     => __( 'All Buildings' ),
			'parent_item'   => __( 'Parent Building' ),
			'parent_item_colon' => __( 'Parent Building:' ),
			'edit_item'     => __( 'Edit Building' ), 
			'update_item'   => __( 'Update Building' ),
			'add_new_item'  => __( 'Add New Building' ),
			'new_item_name' => __( 'New Building Name' ),
			'menu_name'     => __( 'Buildings' ),
		);
		/**
		 * Define the appropriate arguments for the "department" taxonomy
		 */
		$args = array(
			'labels'       => $labels, 
			'hierarchical' => true, 
			'public'       => true, 
			'rewrite'      => array( 
				'slug'         => 'buildings', 
				'with_front'   => true, 
				'hierarchical' => true, 
			), 
		);
		register_taxonomy( 'building', array( 'contact' ), $args );
		
	}
	
	/**
	 * Register the meta box for the custom contact fields
	 */
	function add_meta_boxes() {
		add_meta_box( 'sau-contact-fields', __( 'Add New Contact' ), array( $this, 'do_meta_boxes' ), 'contact', 'normal', 'high' );
		remove_meta_box( 'buildingdiv', 'contact', 'side' );
		
		add_meta_box( 'sau-social-fields', __( 'Social Media' ), array( $this, 'do_social_meta_box' ), 'contact', 'normal', 'default' );
	}
	
	/**
	 * Output the meta box for the custom contact fields
	 */
	function do_meta_boxes( $post = null ) {
		wp_enqueue_script( 'sau-campus-directory-admin' );
		
		if ( is_numeric( $post ) )
			$post = get_post( $post );
		
		wp_nonce_field( 'sau-contact-fields', '_sau_contact_nonce' );
		foreach ( $this->meta as $field ) {
			$this->do_meta_field( $field, $post );
		}
		
		$this->build_office_fieldset( $post );
	}
	
	/**
	 * Output the meta box for social media fields
	 */
	function do_social_meta_box( $post = null ) {
		if ( is_numeric( $post ) )
			$post = get_post( $post );
		
		if ( empty( $post ) )
			global $post;
		
		/**
		 * Not necessary, since we're noncing the other meta box
		 */
		/*wp_nonce_field( 'sau-social-fields', '_sau_social_contact_nonce' );*/
		
		do_action( 'sau-before-social-fields', $post );
		
		$this->social = $this->get_social_array();
		foreach ( $this->social as $field ) {
			$this->do_meta_field( $field, $post );
		}
		
		do_action( 'sau-after-social-fields', $post );
	}
	
	/**
	 * Output a form field for the meta information
	 */
	function do_meta_field( $field, $post = null, $suffix = '_wpcm_value' ) {
		$field = array_merge( array( 
			'type'        => 'text', 
			'name'        => null, 
			'std'         => '', 
			'title'       => '', 
			'description' => '', 
		), $field );
		
		if ( empty( $field['name'] ) )
			return;
		
		$val = get_post_meta( $post->ID, $field['name'] . $suffix, true );
		switch( $field['type'] ) {
			case 'text' : 
			default : 
?>
<p>
<?php
				if ( ! empty( $field['title'] ) && 'hidden' !== $field['type'] ) {
?>
	<label for="<?php echo esc_attr( $field['name'] . $suffix ) ?>"><?php echo $field['title'] ?></label> 
<?php
				}
?>
	<input type="<?php echo $field['type'] ?>" name="wpcm_values[<?php echo $field['name'] ?>]" id="<?php echo $field['name'] . $suffix ?>" value="<?php echo esc_attr( $val ) ?>" class="widefat" />
<?php
				if ( ! empty( $field['description'] ) ) {
?>
	<br /><span class="note"><?php echo $field['description'] ?></span>
<?php
				}
?>
</p>
<?php
		}
	}
	
	/**
	 * Output the fieldset for building/office meta data
	 */
	function build_office_fieldset( $post = null ) {
		wp_enqueue_style( 'sau-contact' );
		
		$buildings = get_the_terms( $post->ID, 'building' );
		$offices = get_post_meta( $post->ID, 'office_wpcm_value', true );
		
		/*print( "\n<!-- Building list:\n" );
		var_dump( $buildings );
		print( "\nOffice list:\n" );
		var_dump( $offices );
		print( "\n-->\n" );*/
		
		if ( empty( $buildings ) || is_wp_error( $buildings ) )
			$buildings = array();
		if ( empty( $offices ) || is_wp_error( $offices ) )
			$offices = array();
		
		$buildings[] = new stdClass( array( 'term_id' => 0 ) );
		$offices[] = '';
?>
<fieldset class="offices">
	<legend><?php _e( 'Office Location(s)' ) ?></legend>
<?php
		$i = 0;
		foreach( $buildings as $k => $v ) {
?>
	<fieldset class="office-block">
    	<legend><?php printf( __( 'Office location %d' ), ( $i + 1 ) ) ?></legend>
		<label for="building_<?php echo $i ?>"><?php _e( 'Building name:' ) ?></label> 
<?php 
			wp_dropdown_categories( array(
				'name'       => 'building[' . $i . ']', 
				'id'         => 'building_' . $i, 
				'selected'   => $v->term_id, 
				'show_option_none'  => '-- None --', 
				'option_none_value' => 0, 
				'class'      => 'widefat office-building-field', 
				'taxonomy'   => 'building', 
				'orderby'    => 'title', 
				'hide_empty' => 0, 
			) );
?>
		<br />
		<label for="office_<?php echo $i ?>"><?php _e( 'Office/Room:' ) ?></label> 
        	<input class="widefat office-room-field" type="text" name="office_wpcm_value[<?php echo $i ?>]" id="office_<?php echo $i ?>" value="<?php echo $offices[$v->term_id] ?>" />
	</fieldset>
<?php
			$i++;
		}
?>
</fieldset>
<?php
	}
	
	/**
	 * Save the custom post data
	 */
	function save_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return;
		
		if ( ! wp_verify_nonce( $_POST['_sau_contact_nonce'], 'sau-contact-fields' ) )
			return;
		
		if ( 'contact' !== $_POST['post_type'] )
			return;
		
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;
		
		$this->get_meta_array();
		foreach ( $this->meta as $field ) {
			if ( ! array_key_exists( $field['name'], $_POST['wpcm_values'] ) ) {
				delete_post_meta( $post_id, $field['name'] . '_wpcm_value' );
				continue;
			}
			
			switch ( $field['type'] ) {
				case 'url' : 
					$_POST['wpcm_values'][$field['name']] = esc_url( $_POST['wpcm_values'][$field['name']] );
					break;
				default :
					$_POST['wpcm_values'][$field['name']] = esc_attr( $_POST['wpcm_values'][$field['name']] );
			}
			
			update_post_meta( $post_id, $field['name'] . '_wpcm_value', $_POST['wpcm_values'][$field['name']] );
		}
		
		$this->get_social_array();
		foreach ( $this->social as $field ) {
			if ( ! array_key_exists( $field['name'], $_POST['wpcm_values'] ) ) {
				delete_post_meta( $post_id, $field['name'] . '_wpcm_value' );
				continue;
			}
			
			switch ( $field['type'] ) {
				case 'url' : 
					$_POST['wpcm_values'][$field['name']] = esc_url( $_POST['wpcm_values'][$field['name']] );
					break;
				default :
					$_POST['wpcm_values'][$field['name']] = esc_attr( $_POST['wpcm_values'][$field['name']] );
			}
			
			update_post_meta( $post_id, $field['name'] . '_wpcm_value', $_POST['wpcm_values'][$field['name']] );
		}
		
		wp_set_object_terms( $post_id, NULL, 'building' );
		
		$offices = array();
		foreach ( $_POST['building'] as $key => $value ) {
			if ( empty( $value ) || ! is_numeric( $value ) || $value <= 0 )
				continue;
			
			wp_set_object_terms( $post_id, intval( $value ), 'building', true );
			$offices[intval($value)] = $_POST['office_wpcm_value'][$key];
		}
		
		$tmp = update_post_meta( $post_id, 'office_wpcm_value', $offices );
		
		return $post_id;
	}
	
	function add_genesis_meta_boxes() {
		global $_genesis_theme_settings_pagehook;
		add_meta_box( 'sau-genesis-contact-settings', __( 'SAU Contact Settings' ), array( $this, 'genesis_meta_boxes' ), $_genesis_theme_settings_pagehook, 'main', 'high');
	}
	
	/**
	 * Generate our Genesis options
	 */
	function genesis_meta_boxes() {
		$azpage = genesis_get_option( 'sau_contact_az_page' );
		if ( empty( $azpage ) )
			$azpage = 0;
		$deptpage = genesis_get_option( 'sau_contact_dept_page' );
		if ( empty( $deptpage ) )
			$deptpage = 0;
		$bldgpage = genesis_get_option( 'sau_contact_bldg_page' );
		if ( empty( $bldgpage ) )
			$bldgpage = 0;
		$noicon_url = genesis_get_option( 'sau_contact_noicon_url' );
		if ( empty( $noicon_url ) )
			$noicon_url = null;
?>
<p><label for="azpage"><?php _e( 'Which page should display the full alphabetical list of contacts?' ) ?></label> 
<?php 
		wp_dropdown_pages( array( 
			'name' => GENESIS_SETTINGS_FIELD . '[sau_contact_az_page]', 
			'id' => 'azpage', 
			'selected' => $azpage, 
			'show_option_none'  => '-- None --', 
			'option_none_value' => 0, 
			'class' => 'widefat', 
		) ) 
?></p>
<p><label for="deptpage"><?php _e( 'Which page should display the full alphabetical list of departments?' ) ?></label> 
<?php 
		wp_dropdown_pages( array( 
			'name' => GENESIS_SETTINGS_FIELD . '[sau_contact_dept_page]', 
			'id' => 'deptpage', 
			'selected' => $deptpage, 
			'show_option_none'  => '-- None --', 
			'option_none_value' => 0, 
			'class' => 'widefat', 
		) ) 
?></p>
<p><label for="bldgpage"><?php _e( 'Which page should display the full alphabetical list of buildings?' ) ?></label> 
<?php 
		wp_dropdown_pages( array( 
			'name' => GENESIS_SETTINGS_FIELD . '[sau_contact_bldg_page]', 
			'id' => 'bldgpage', 
			'selected' => $bldgpage, 
			'show_option_none'  => '-- None --', 
			'option_none_value' => 0, 
			'class' => 'widefat', 
		) ) 
?></p>
<p><label for="noicon-url"><?php _e( 'Enter the URL (Web address) of the image that should be used if a contact does not have a featured image:' ) ?></label> 
	<input class="widefat" type="url" name="<?php echo GENESIS_SETTINGS_FIELD ?>[sau_contact_noicon_url]" id="noicon_url" value="<?php echo esc_attr( $noicon_url ) ?>" />
    <br /><span class="note"><?php _e( 'If left blank, the default icon included with the plugin will be used.' ) ?></span></p>
<?php
	}
	
	/**
	 * Figure out which template to display
	 */
	function template_redirect() {
		$azpage = genesis_get_option( 'sau_contact_az_page' );
		if ( empty( $azpage ) )
			$azpage = 0;
		$deptpage = genesis_get_option( 'sau_contact_dept_page' );
		if ( empty( $deptpage ) )
			$deptpage = 0;
		$bldgpage = genesis_get_option( 'sau_contact_bldg_page' );
		if ( empty( $bldgpage ) )
			$bldgpage = 0;
			
		if ( is_post_type_archive( 'contact' ) || is_tax( 'department' ) || is_tax( 'building' ) ) {
			remove_all_actions( 'genesis_loop' );
			add_action( 'genesis_loop', array( $this, 'archive_loop' ) );
		} elseif ( is_singular( 'contact' ) ) {
			remove_all_actions( 'genesis_loop' );
			add_action( 'genesis_loop', array( $this, 'single_loop' ) );
		} elseif ( ! empty( $deptpage ) && is_page( $deptpage ) ) {
			remove_all_actions( 'genesis_loop' );
			add_action( 'genesis_loop', array( $this, 'department_loop' ) );
		} elseif ( ! empty( $azpage ) && is_page( $azpage ) ) {
			remove_all_actions( 'genesis_loop' );
			add_action( 'genesis_loop', array( $this, 'alpha_list_loop' ) );
		} elseif ( ! empty( $bldgpage ) && is_page( $bldgpage ) ) {
			remove_all_actions( 'genesis_loop' );
			add_action( 'genesis_loop', array( $this, 'building_loop' ) );
		} else {
			return;
		}
	}
	
	/**
	 * Run the custom loop for archive pages
	 * @uses do_action() to run the sau-contact-start-archive-loop and 
	 * 		sau-contact-done-archive-loop actions to allow output before/after 
	 * 		the loop
	 */
	function archive_loop() {
		wp_enqueue_style( 'sau-contact' );
		
		$obj = get_queried_object();
		if ( is_object( $obj ) ) {
			/*if ( property_exists( $obj, 'taxonomy' ) ) {
				$tmp = get_taxonomy( $obj->taxonomy );
				printf( '<h1>%s</h1>', $tmp->labels->name );
			}*/
			if ( property_exists( $obj, 'name' ) )
				printf( '<h1 class="%2$s">%1$s</h1>', $obj->name, apply_filters( 'sau-contact-archive-title-class', 'archive-title' ) );
			if ( is_tax() ) {
				$this->tax_query( $obj );
			}
		}
		
		$i = 0;
		global $post;
		do_action( 'sau-contact-start-archive-loop' );
		if ( have_posts() ) : 
			while ( have_posts() ) : the_post();
				/*error_log( '[SAU Debug]: ' . $post->post_name );*/
				$this->do_archive_entry( $post, $i );
				$i++;
			endwhile;
		else :
			_e( apply_filters( 'sau-contact-no-posts', '<p>Sorry, no posts matched your criteria.</p>' ) );
		endif;
		do_action( 'sau-contact-done-archive-loop' );
	}
	
	/**
	 * Run multiple queries to retrieve all posts within child terms
	 */
	function tax_query( $obj ) {
		if ( ! is_object( $obj ) ) {
			print( "\n<!-- For some reason, the obj var is not an object -->\n" );
			return false;
		}
		
		$tax = $obj->taxonomy;
		$term = intval( $obj->term_id );
		
		$children = get_terms( $tax, array( 'child_of' => $term ) );
		
		/*print( "\n<pre><code>\n" );
		var_dump( $children );
		print( "\n</code></pre>\n" );*/
		
		if ( empty( $children ) || is_wp_error( $children ) || ! is_array( $children ) ) {
			return false;
		}
		
		$posts = array();
		foreach ( $children as $child ) {
			print( "\n<!-- Adding {$child->name} to the query -->\n" );
			$tmp = get_posts( array( 'post_type' => 'contact', 'tax_query' => array( 'taxonomy' => $tax, 'field' => 'slug', 'terms' => $child->slug ) ) );
			/*print( "\n<pre><code>\n" );
			print( "\n{$child->name}\n" );
			var_dump( $tmp );
			print( "\n</code></pre>\n" );*/
			
			if ( ! empty( $tmp ) )
				$posts = array_merge( $posts, $tmp );
		}
		$tmp = $posts;
		$posts = array();
		foreach ( $tmp as $p ) {
			$posts[$p->ID] = $p->ID;
		}
		
		print( "\n<pre><code>\n" );
		var_dump( $term );
		var_dump( $tax );
		var_dump( $posts );
		/*var_dump( $tmp );*/
		print( "\n</code></pre>\n" );
		wp_die( 'Done' );
		
		query_posts( array( 'post__in' => $posts ) );
		return;
	}
	
	/**
	 * Output the actual content for an entry on an archive page
	 * @param stdClass $post the WordPress post object
	 * @param int $i a counter for determining which class to assign
	 */
	function do_archive_entry( $post = null, $i = 0 ) {
		echo $this->get_archive_entry( $post, $i );
	}
	
	function get_archive_entry( $post = null, $i = 0 ) {
		if ( empty( $post ) )
			global $post;
		
		setup_postdata( $post );
		
		$title = apply_filters( 'title-wpcm-value', get_post_meta( $post->ID, 'title_wpcm_value', true ) );
		
		$names = array();
		$names[] = get_post_meta( get_the_ID(), 'first_name_wpcm_value', true );
		$names[] = get_post_meta( get_the_ID(), 'last_name_wpcm_value', true );
		$names = apply_filters( 'name-wpcm-value', implode( ' ', $names ), $names );
		
		$has_email = is_email( apply_filters( 'email-wpcm-value', get_post_meta( get_the_ID(), 'email_wpcm_value', true ) ) );
		
		$phone = apply_filters( 'phone-wpcm-value', get_post_meta( get_the_ID(), 'office_phone_wpcm_value', true ) );
		
		$d = get_the_terms( get_the_ID(), 'department' );
		$depts = array();
		foreach ( $d as $t ) {
			$depts[] = $t->name;
		}
		$depts = '<span class="departments">' . implode( ', ', $depts ) . '</span>';
		
		return apply_filters( 'sau-contact-archive-entry', '
	<div class="contact' . ( $i % 2 ? ' alt' : '' ) . '">
		<span class="m-name"><a href="' . get_permalink() . '" title="' . esc_attr( $title ) . '">' . $names . '</a></span>
		' . ( empty( $phone ) ? '<span class="m-email m-phone">' . ( $has_email ? '<a href="mailto:' . $has_email . '">' . $has_email . '</a>' : '&nbsp;' ) . '</span>' : '<span class="m-mobile"><span>' . $this->format_phone( $phone ) . '</span> (O)</span>' ) . '
		<span class="title">' . $title . '</span>
		' . $depts . '
	</div>' );
	}
	
	/**
	 * Run the loop for a single directory entry
	 * @uses do_action() to run the sau-contact-start-single-loop and 
	 * 		sau-contact-done-single-loop actions to allow output before/after 
	 * 		the loop
	 */
	function single_loop() {
		wp_enqueue_style( 'sau-contact' );
		
		add_filter( 'single_post_title', array( $this, 'contact_post_title' ), 1, 2 );
		
		do_action( 'sau-contact-start-single-loop' );
		if ( have_posts() ) :
			while ( have_posts() ) : the_post();
				$this->do_single_entry();
			endwhile;
		else :
			_e( '<p>Sorry, nothing matched your criteria.</p>' );
		endif;
		do_action( 'sau-contact-done-single-loop' );
	}
	
	/**
	 * Actually output the content of a single entry
	 */
	function do_single_entry() {
		echo $this->get_single_entry();
	}
	
	function get_single_entry() {
		global $post;
		
		$post_ID = $post->ID;
		
		$wpcm_image_path = $this->get_contact_image_src( $post );
		$wpcm_email = is_email( get_post_meta( $post_ID, 'email_wpcm_value', true ) );
		$wpcm_website = esc_url( get_post_meta( $post_ID, 'website_wpcm_value', true ) );
		$wpcm_number_mobile = get_post_meta( $post_ID, 'mobile_wpcm_value', true );
		$wpcm_number_office = get_post_meta( $post_ID, 'office_phone_wpcm_value', true );
		$wpcm_number_fax = get_post_meta( $post_ID, 'fax_wpcm_value', true );
		$addressone = get_post_meta( $post_ID, 'address1_wpcm_value', true );
		$bldgoffice = $this->get_office( $post );
		
		$rt = '
<div class="vitals">
	<div class="photo">
    	<img src="' . $wpcm_image_path . '" alt="' . esc_attr( apply_filters( 'the_title', $post->post_title, $post ) ) . '" />
    </div>
    <div id="contact-info">
    	<h1 class="name fn">' . $this->get_contact_name( $post ) . '</h1>
        <span class="title">' . get_post_meta( $post->ID, 'title_wpcm_value', true ) . '</span>
        <span class="organization organization-unit">' . get_the_term_list( $post->ID, 'department', '', ', ', '' ) . '</span>';
		
		if ( ! empty( $wpcm_email ) ) {
			$rt .= '
        <span class="email"><a href="mailto:' . $wpcm_email . '">' . $wpcm_email . '</a></span>';
		}
		if ( ! empty( $wpcm_website ) ) {
			$rt .= '
		<span class="website"><a class="url" href="' . $wpcm_website . '">' . get_post_meta($post->ID, "website_wpcm_value", true) . '</a></span>';
		}
		
		$rt .= '
		<span class="phone">
        	<ul class="phone-numbers tel">';
			
		$rt .= empty( $wpcm_number_mobile ) ? '' : '
				<li><span class="number value">' . $this->format_phone( $wpcm_number_mobile ) . '</span> <span class="type">' . __( '(Mobile)' ) . '</span></li>';
		$rt .= empty( $wpcm_number_office ) ? '' : '
				<li><span class="number value">' . $this->format_phone( $wpcm_number_office ) . '</span> <span class="type">' . __( '(Office)' ) . '</span></li>';
		$rt .= empty( $wpcm_number_fax ) ? '' : '
				<li><span class="number value">' . $this->format_phone( $wpcm_number_fax ) . '</span> <span class="type">' . __( '(Fax)' ) . '</span></li>';
				
		$rt .= '
            </ul>
        </span>
        <span class="address">
        	<h3 class="site-subtitle">' . __( 'Address' ) . '</h3>
            <span class="adr">';
		
		$rt .= empty( $addressone ) ? '' : '
			<span class="post-office-box">P.O. Box ' . $addressone . '</span><br/>';
		$rt .= '
                <span class="street-address">' . __( 'Building/Office:' ) . ' ' . $bldgoffice . '</span>
            </span>
        </span>
    </div>
</div>
<div class="extra">
	<div class="notes">
    	<span class="note">' . apply_filters( 'the_content', get_the_content() ) . '</span>
    </div>
</div>
<div class="modified rev">
	' . sprintf( __( 'Last updated %s at %s' ), the_modified_date( 'F j, Y', '', '', false ), the_modified_date( 'g:i a', '', '', false ) ) . '
</div>';
		
		return apply_filters( 'sau-contact-single-entry', $rt );
	}
	
	/**
	 * Format a phone number appropriately
	 */
	function format_phone( $number ) {
		if ( empty( $number ) )
			return false;
		
		$num = preg_replace( '/[^0-9]/', '', $number );
		switch ( strlen( $num ) ) {
			case 4 :
				$num = '235' . $num;
			case 7 :
				$num = '870' . $num;
			case 10 : 
				break;
				
			default : 
				/* Return the original input if it doesn't look the way we'd expect */
				return $number;
		}
		
		$num = $num[0] . $num[1] . $num[2] . '-' . $num[3] . $num[4] . $num[5] . '-' . $num[6] . $num[7] . $num[8] . $num[9];
		
		return apply_filters( 'sau-contact-phone-format', $num );
	}
	
	/**
	 * Format a Twitter handle as a URL to Twitter
	 */
	function twitter_url( $handle ) {
		if ( 'http' == substr( $handle, 0, strlen( 'http' ) ) )
			return $handle;
		
		return esc_url( 'https://twitter.com/' . $handle );
	}
	
	/**
	 * Retrieve the building name and office number for a contact
	 */
	function get_office( $post = null ) {
		if ( empty( $post ) )
			global $post;
		
		$bldg = get_the_terms( $post->ID, 'building' );
		$office = maybe_unserialize( get_post_meta( $post->ID, 'office_wpcm_value', true ) );
		
		if ( empty( $bldg ) || empty( $office ) || is_wp_error( $bldg ) || is_wp_error( $office ) || ! is_array( $bldg ) || ! is_array( $office ) )
			return get_post_meta( $post->ID, 'city_wpcm_value', true );
		
		/*$bldg = array_values( $bldg );
		$office = array_values( $office );*/
		
		$bldgoffice = array();
		foreach ( $bldg as $k => $b ) {
			$bldgoffice[$k] = $b->name;
			if ( array_key_exists( $k, $office ) )
				$bldgoffice[$k] .= ' ' . $office[$k];
		}
		
		return implode( ', ', $bldgoffice );
	}
	
	/**
	 * Filter the title so that it uses the person's first and last name
	 */
	function contact_post_title( $title, $post = null ) {
		if ( empty( $post ) )
			global $post;
		
		if ( 'contact' !== $post->post_type )
			return $title;
		
		return $this->get_contact_name( $post );
	}
	
	/**
	 * Retrieve a contact's first and last name
	 */
	function get_contact_name( $post = null ) {
		if ( empty( $post ) )
			global $post;
		
		$names = array();
		$names[] = get_post_meta( $post->ID, 'first_name_wpcm_value', true );
		$names[] = get_post_meta( $post->ID, 'last_name_wpcm_value', true );
		
		return implode( ' ', $names );
	}
	
	/**
	 * Retrieve the URL for the contact image
	 * @param stdClass $post the WordPress Post object to which the image is related
	 * @param string $no_icon what to use if no related image is found (default|null)
	 */
	function get_contact_image_src( $post = null, $no_icon = 'default' ) {
		if ( empty( $post ) )
			global $post;
		
		$noicon_url = esc_url( genesis_get_option( 'sau_contact_noicon_url' ) );
		if ( empty( $noicon_url ) )
			$noicon_url = null;
		
		$src = '';
		if ( has_post_thumbnail( $post->ID ) ) {
			$src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), array( 150, 0 ), false );
			$src = $src[0];
		} else {
			$src = get_post_meta( $post->ID, 'image_path_wpcm_value', true );
		}
		
		if ( empty( $src ) && is_null( $no_icon ) )
			return false;
		
		if ( empty( $src ) )
			$src = $noicon_url;
		
		if ( empty( $src ) )
			$src = apply_filters( 'sau-default-contact-image', plugins_url( '/images/contact-default.png', dirname( __FILE__ ) ) );
		
		return $src;
	}
	
	/**
	 * Output the alphabetical list of contacts
	 */
	function alpha_list_loop() {
		wp_enqueue_script( 'sau-columns' );
		wp_enqueue_style( 'sau-contact' );
?>
<h1><?php _e( 'Employees A to Z' ) ?></h1>
<?php
		
		global $wpdb;
		$posts = array();
		
		foreach( range( 'a', 'z' ) as $l ) {
			$post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type=%s AND post_status=%s AND post_title LIKE %s", 'contact', 'publish', $l . '%' ) );
			if ( empty( $post_ids ) || is_wp_error( $post_ids ) )
				continue;
			
			$tmp = get_posts( array( 'post_type' => 'contact', 'post__in' => $post_ids, 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'asc' ) );
			if ( ! empty( $tmp ) )
				$posts[$l] = $tmp;
		}
		
		if ( empty( $posts ) ) {
			_e( '<p>Unfortunately, no posts were found that matched the criteria specified.</p>' );
			return;
		}
?>
<ul class="alpha-links" id="alpha-links">
<?php
		foreach( array_keys( $posts ) as $l ) {
?>
	<li class="letter"><a href="#letter-<?php echo strtolower( $l ) ?>"><?php echo strtoupper( $l ) ?></a></li>
<?php
		}
?>
</ul>
<br class="clear" />
<ul class="alpha-list">
<?php
		foreach( $posts as $l => $post_list ) {
?>
	<li class="alpha-letter" id="letter-<?php echo strtolower( $l ) ?>">
    	<h2><?php echo strtoupper( $l ) ?></h2>
        <ul class="letter-list">
<?php
			foreach( $post_list as $post ) {
?>
			<li>
            	<a href="<?php echo get_permalink( $post->ID ) ?>" title="<?php echo apply_filters( 'the_title_attribute', $post->post_title ) ?>"><?php echo apply_filters( 'the_title', $post->post_title ) ?></a>
            </li>
<?php
			}
?>
        </ul>
    </li>
<?php
		}
?>
</ul>
<br class="clear" />
<?php
	}
	
	/**
	 * Output the alphabetical list of buildings
	 */
	function building_loop() {
?>
<h1 class="page-title"><?php the_title() ?></h1>
<ul class="building-list">
<?php
		wp_list_categories( array( 
			'taxonomy'    => 'building', 
			'orderby'     => 'name', 
			'hierachical' => 1, 
			'title_li'    => ''
		) );
?>
</ul>
<?php
	}
	
	/**
	 * Output the alphabetical, hierarchical list of departments
	 */
	function department_loop() {
?>
<h1 class="page-title"><?php the_title() ?></h1>
<ul class="department-list">
<?php
		wp_list_categories( array( 
			'taxonomy'    => 'department', 
			'orderby'     => 'name', 
			'hierachical' => 1, 
			'title_li'    => ''
		) );
?>
</ul>
<?php
	}

	/**
	 * Register the admin page to convert old posts to contact entries
	 */
	function add_submenu_page() {
		add_management_page( __( 'Convert Old Directory Posts to Contact Entries' ), __( 'Directory Conversion' ), 'delete_users', 'convert-sau-directory', array( $this, 'conversion_page' ) );
	}
	
	/**
	 * Output the conversion page
	 */
	function conversion_page() {
		if ( isset( $_POST['sau-convert-submit'] ) )
			$msg = $this->convert_posts();
		
?>
<div class="wrap">
<?php
		if ( ! empty( $msg ) ) {
?>
	<div class="updated fade">
<?php
			if ( is_wp_error( $msg ) ) {
				echo '<p>' . $msg->get_error_message() . '</p>';
			} else {
				echo '<p>' . $msg . '</p>';
			}
?>
    </div>
<?php
		}
		if ( count( $this->messages ) ) {
?>
	<div class="updated fade">
<?php
			foreach ( $this->messages as $m ) {
?>
		<p><?php echo $m ?></p>
<?php
			}
?>
    </div>
<?php
		}
?>
	<h2><?php _e( 'Convert Old Directory Posts to Contact Entries' ) ?></h2>
    <p><?php _e( 'Clicking the "Convert" button below will process all existing posts on this site and convert them to "Contact" posts; reassigning the custom meta and taxonomy information appropriately.' ) ?></p>
    <form action="" method="post">
    	<?php wp_nonce_field( 'sau-directory-convert', '_sau_convert_nonce' ) ?>
        <input type="hidden" name="step" value="<?php echo isset( $_POST['step'] ) && is_numeric( $_POST['step'] ) ? ( $_POST['step'] + 1 ) : 1 ?>" />
        <input type="submit" class="button-primary" name="sau-convert-submit" value="<?php isset( $_POST['step'] ) && is_numeric( $_POST['step'] ) && $_POST['step'] >= 1 ? _e( 'Continue' ) : _e( 'Convert' ) ?>" />
    </form>
</div>
<?php
	}
	
	/**
	 * Perform the conversion of old posts to contact posts
	 */
	function convert_posts() {
		if ( ! wp_verify_nonce( $_POST['_sau_convert_nonce'], 'sau-directory-convert' ) )
			return new WP_Error( 'sau-no-nonce', __( 'The information was not saved because the nonce could not be verified.' ) );
		
		$step = isset( $_POST['step'] ) && is_numeric( $_POST['step'] ) ? $_POST['step'] : 1;
		$args = array(
			'post_type'   => 'post', 
			'post_status' => 'publish', 
			'numberposts' => 50, 
		);
		/**
		 * No need to offset, since the old posts no longer exist
		 */
		/*if ( $step > 1 )
			$args['offset'] = ( $step * 50 );*/
			
		$posts = get_posts( $args );
		
		if ( empty( $posts ) )
			return new WP_Error( 'sau-no-posts', __( 'The information was not converted, because no posts could be retrieved' ) );
		if ( is_wp_error( $posts ) )
			return $posts;
		
		$updated = array();
		$this->messages = array();
		foreach ( $posts as $post ) {
			$cats = get_the_terms( $post->ID, 'category' );
			
			/*print( "\n<pre><code>\n" );
			var_dump( $cats );
			print( "\n</code></pre>\n" );
			wp_die( 'dumped' );*/
			
			if ( is_wp_error( $cats ) ) {
				error_log( '[SAU Debug]: There was an error collecting categories for post with ID of ' . $post->ID . "\n" . $cats->get_error_message() );
				continue;
			} elseif ( empty( $cats ) ) {
				$cats = array();
			}
			
			$depts = array();
			$deleted = array();
			foreach( $cats as $cat ) {
				if ( 'uncategorized' == $cat->slug ) {
					error_log( '[SAU Debug]: The post with an ID of ' . $post->ID . ' had uncategorized as one of its taxonomies' );
					continue;
				}
				
				$cat->taxonomy = 'department';
				/*$deleted[] = wp_delete_term( $cat->term_id, 'category' );*/
				$tmp = get_term_by( 'slug', $cat->slug, 'department' );
				if ( empty( $tmp ) ) {
					unset( $cat->term_id );
					$tmp = wp_insert_term( $cat->name, 'department', (array) $cat );
				}
				
				if ( is_wp_error( $tmp ) )
					/*wp_die( $tmp->get_error_message() );*/
					continue;
				
				if ( is_array( $tmp ) )
					$depts[] = get_term( $tmp['term_id'], 'department' );
				elseif ( is_object( $tmp ) )
					$depts[] = $tmp;
				elseif ( is_numeric( $tmp ) )
					$depts[] = get_term( $tmp, 'department' );
			}
			
			$cats = get_the_terms( $post->ID, 'department' );
			if ( ! empty( $cats ) && is_array( $cats ) )
				$depts = array_merge( $depts, $cats );
			
			foreach( $depts as $k => $cat ) {
				if ( is_numeric( $cat ) )
					$depts[$k] = intval( $cat );
				elseif ( is_object( $cat ) )
					$depts[$k] = intval( $cat->term_id );
				elseif ( is_array( $cat ) )
					$depts[$k] = intval( $cat['term_id'] );
			}
			
			/*print( "\n<pre><code>Modified categories to change them into departments:\n" );
			var_dump( $depts );
			print( "\nDeleted Terms:\n" );
			var_dump( $deleted );
			print( "\n</code></pre>\n" );
			wp_die( 'dumped' );*/
			
			$post->post_type = 'contact';
			
			$tmp = wp_update_post( array( 
				'ID'        => $post->ID, 
				'post_type' => 'contact', 
			) );
			wp_set_object_terms( $post->ID, $depts, 'department', false );
			
			if ( is_wp_error( $tmp ) ) {
				$this->messages[] = sprintf( __( '<p>There was an error converting the post with an ID of %d and a title of %s</p><pre><code>%s</code></pre>' ), $post->ID, $post->post_title, $tmp->get_error_message() );
			} elseif ( empty( $tmp ) ) {
				$this->messages[] = sprintf( __( '<p>There was an unknown error converting the post with an ID of %d and a title of %s</p>' ), $post->ID, $post->post_title );
			} else {
				$photo_id = $this->_import_photo( $tmp, $post->post_name );
				if ( ! empty( $photo_id ) && ! is_wp_error( $photo_id ) ) {
					$inserted_photo = update_post_meta( $tmp, '_thumbnail_id', $photo_id );
					$this->messages[] = sprintf( __( '<p>The image for %s was supposedly attached as the featured image properly.<br/>%s</p>' ), $post->post_name, $inserted_photo );
				} elseif ( is_wp_error( $photo_id ) ) {
					$this->messages[] = sprintf( __( '<p>There was an error importing the featured image for %s. Error message: %s</p>' ), $post->post_name, $photo_id->get_error_message() );
				} else {
					$this->messages[] = sprintf( __( '<p>The thumbnail for %s was not attached successfully for some reason.</p>' ), $post->post_name );
				}
				
				$updated[] = $tmp;
				$this->messages[] = sprintf( __( '<p>The post with an ID of %d and a title of %s was converted successfully.</p>' ), $post->ID, $post->post_title );
			}
		}
		
		return sprintf( __( 'Successfully converted %d posts to contact entries' ), count( $updated ) );
	}
	
	/**
	 * Attempt to convert a URL to an absolute path
	 */
	function url2filepath( $url ) {
		$uploads = wp_upload_dir();
		
		if ( stristr( $url, $uploads['baseurl'] ) )
			return str_ireplace( $uploads['baseurl'], $uploads['basedir'], $url );
		
		$original_url = $url;
		
		$cd = trailingslashit( plugin_dir_path( __FILE__ ) );
		$cu = trailingslashit( plugins_url( '', __FILE__ ) );
		
		$cd = explode( DIRECTORY_SEPARATOR, $cd );
		$cu = explode( '/', $cu );
		
		$dp = $du = null;
		
		while( $dp == $du ) {
			$dp = array_pop( $cd );
			$du = array_pop( $cu );
		}
		
		$cd[] = $dp;
		$cu[] = $du;
		
		$cd = implode( DIRECTORY_SEPARATOR, $cd );
		$cu = implode( '/', $cu );
		
		if ( substr( $url, 0, 1 ) === '/' )
			$url = untrailingslashit( $cu ) . $url;
		
		if ( ! empty( $cd ) && stristr( $url, $cd ) )
			return $original_url;
		
		if ( ! empty( $cu ) && ! empty( $cd ) && stristr( $url, $cu ) )
			return str_ireplace( $cu, $cd, $url );
		
		return new WP_Error( 'bad-url', sprintf( __( 'The URL %s sent to the function was either not a usable URL or was not in the current file system.' ), $url ) );
	}
	
	/**
	 * Modify an absolute URL that points to the original site location
	 * 		so that it points to somewhere relative to this specific site
	 */
	function filter_old_url( $url ) {
		$siteurl = untrailingslashit( get_bloginfo( 'url' ) );
		$oldurl = 'http://web.saumag.edu/directory';
		
		if ( stristr( $url, $oldurl ) && $siteurl !== $oldurl )
			$url = str_ireplace( $oldurl, $siteurl, $url );
		
		return $url;
	}
	
	/**
	 * Retrieve a photo from original location and insert
	 * 		as an attachment/featured image
	 * @param int $postid the ID of the post to which this should be attached
	 * @param string $post_slug the slug for the post to which this should be attached
	 */
	function _import_photo( $postid, $post_slug ) {
		$post = get_post( $postid );
		if( empty( $post ) )
			return new WP_Error( 'empty-post', __( 'The post ID does not exist' ) );
		
		/**
		 * If the post already has a featured image, we skip over importing it
		 */
		$imgid = get_post_thumbnail_id( $postid );
		if ( ! empty( $imgid ) )
			return new WP_Error( 'exists', sprintf( __( 'The post already appears to have a featured image with an ID of %d' ), $imgid ) );
		
		/**
		 * Check to make sure this post has an image specified.
		 * If it doesn't, we jump out of this function.
		 */
		$wpcm_image_path = $this->get_contact_image_src( $post, null );
		if ( empty( $wpcm_image_path ) )
			return new WP_Error( 'no-icon', __( 'The post does not appear to have an image associated' ) );
		elseif ( is_wp_error( $wpcm_image_path ) )
			return $wpcm_image_path;
		
		$wpcm_image_path = esc_url( $wpcm_image_path );
		if ( empty( $wpcm_image_path ) )
			return new WP_Error( 'invalid-url', __( 'The URL provided for the image was not valid.' ) );
		
		/**
		 * Fix any relative links so they point to the right place
		 */
		if ( '/directory/' == strtolower( substr( $wpcm_image_path, 0, strlen( '/directory/' ) ) ) )
			$wpcm_image_path = str_ireplace( '/directory/', trailingslashit( get_bloginfo( 'url' ) ), $wpcm_image_path );
			
		$attach_id = url_to_postid( $wpcm_image_path );
		if ( ! empty( $attach_id ) ) {
			return $attach_id;
		}
		
		if( !class_exists( 'WP_Http' ) )
		  include_once( ABSPATH . WPINC. '/class-http.php' );
		
		$photo = new WP_Http();
		$photo = $photo->request( $wpcm_image_path );
		if ( is_wp_error( $photo ) ) {
			printf( __( 'There was an error retrieving the following URL: %s' ), $wpcm_image_path );
			return $photo;
		}
		
		if( $photo['response']['code'] != 200 ) {
			/*print( 'Import failed for ' . $post_slug . '. Response code: ' . $photo['response']['code'] . "\n\n" );
			var_dump( $photo );*/
			return new WP_Error( 'not-retrieved', sprintf( __( 'The original image returned a status header of %s' ), $photo['response']['code'] ) );
		}
		
		$filetype = wp_check_filetype( basename( $wpcm_image_path ), null );
		
		/**
		 * If, for some reason, we didn't get the file extension/mime type, we should jump
		 */
		if ( empty( $filetype ) )
			return new WP_Error( 'no-filetype', sprintf( __( 'The file type for the image %s could not be determined' ), $wpcm_image_path ) );
		
		$attachment = wp_upload_bits( $post_slug . '.' . $filetype['ext'], null, $photo['body'], date("Y-m", strtotime( $photo['headers']['last-modified'] ) ) );
		if( ! empty( $attachment['error'] ) ) {
			print( 'Import failed for ' . $post_slug . '. Error: ' . $attachment['error'] . "\n\n" );
			return new WP_Error( 'import-failed', sprintf( __( 'There was an error uploading the image: %s' ), $attachment['error'] ) );
		}
		
		$filename = $attachment['file'];
		$filetype = wp_check_filetype( basename( $filename ), null );
		$wp_upload_dir = wp_upload_dir();
		$postinfo = array(
			'guid'              => $wp_upload_dir['baseurl'] . _wp_relative_upload_path( $filename ),
			'post_mime_type'	=> $filetype['type'],
			'post_title'		=> $post->post_title . ' faculty photograph',
			'post_content'		=> '',
			'post_status'		=> 'inherit',
		);
		$attach_id = wp_insert_attachment( $postinfo, $filename, (int) $postid );
	
		if( !function_exists( 'wp_generate_attachment_data' ) )
			require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
		wp_update_attachment_metadata( $attach_id,  $attach_data );
		/*print( 'Import successful for ' . $post_slug . "\n\n" );*/
		return $attach_id;
	}
	
	/**
	 * Register any widgets that we use with this plugin
	 */
	function register_widgets() {
		do_action( 'sau-contact-before-widget-registration' );
		if ( ! class_exists( 'Contact_Feed_Widget' ) )
			require_once( plugin_dir_path( __FILE__ ) . 'class-contact-feed-widget.php' );
			
		register_widget( 'Contact_Feed_Widget' );
		
		if ( ! class_exists( 'Contact_Social_Icons_Widget' ) )
			require_once( plugin_dir_path( __FILE__ ) . 'class-contact-social-icons-widget.php' );
		
		register_widget( 'Contact_Social_Icons_Widget' );
		
		do_action( 'sau-contact-after-widget-registration' );
	}
	
	/**
	 * Sort an array of post objects by post title
	 */
	function sort_by_title( $a, $b ) {
		return strcmp( $a->post_title, $b->post_title );
	}
}