<?php
/**
 * Define the general SAU Campus Directory class
 */
class SAU_Campus_Directory {
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
	}
	
	/**
	 * Build the array of meta fields
	 */
	function get_meta_array() {
		return $this->meta = apply_filters( 'sau-contacts-meta-fields', array(
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
			'menu_name'     => __( 'Department' ),
		);
		/**
		 * Define the appropriate arguments for the "department" taxonomy
		 */
		$args = array(
			'labels'       => $labels, 
			'hierarchical' => true, 
			'public'       => true, 
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
			'menu_name'     => __( 'Tag' ),
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
			'menu_name'     => __( 'Building' ),
		);
		/**
		 * Define the appropriate arguments for the "department" taxonomy
		 */
		$args = array(
			'labels'       => $labels, 
			'hierarchical' => true, 
			'public'       => true, 
		);
		register_taxonomy( 'building', array( 'contact' ), $args );
		
	}
	
	/**
	 * Register the meta box for the custom contact fields
	 */
	function add_meta_boxes() {
		add_meta_box( 'sau-contact-fields', __( 'Add New Contact' ), array( $this, 'do_meta_boxes' ), 'contact', 'normal', 'high' );
		remove_meta_box( 'buildingdiv', 'contact', 'side' );
	}
	
	/**
	 * Output the meta box for the custom contact fields
	 */
	function do_meta_boxes( $post = null ) {
		if ( is_numeric( $post ) )
			$post = get_post( $post );
		
		wp_nonce_field( 'sau-contact-fields', '_sau_contact_nonce' );
		foreach ( $this->meta as $field ) {
			$this->do_meta_field( $field, $post );
		}
		
		$buildings = get_the_terms( $post->ID, 'building' );
		$offices = maybe_unserialize( get_post_meta( $post->ID, 'office_wpcm_value' ) );
		
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
	<fieldset class="office-block" style="border-top: 1px solid #999; padding-top: 1em; margin-top: 1em;">
		<label for="building_<?php echo $i ?>"><?php _e( 'Building name:' ) ?></label> 
<?php 
			wp_dropdown_categories( array(
				'name'     => 'building[' . $i . ']', 
				'id'       => 'building_' . $i, 
				'selected' => $v->term_id, 
				'show_option_none'  => '-- None --', 
				'option_none_value' => 0, 
				'class'    => 'widefat', 
				'taxonomy' => 'building', 
			) );
?>
		<br />
		<label for="office_<?php echo $i ?>"><?php _e( 'Office/Room:' ) ?></label> 
        	<input class="widefat" type="text" name="office_wpcm_value[<?php echo $i ?>]" id="office_<?php echo $i ?>" value="<?php echo $offices[$k] ?>" />
	</fieldset>
<?php
			$i++;
		}
?>
</fieldset>
<?php
	}
	
	/**
	 * Output a form field for the meta information
	 */
	function do_meta_field( $field, $post = null ) {
		$field = array_merge( array( 
			'type'        => 'text', 
			'name'        => null, 
			'std'         => '', 
			'title'       => '', 
			'description' => '', 
		), $field );
		
		if ( empty( $field['name'] ) )
			return;
		
		$val = get_post_meta( $post->ID, $field['name'] . '_wpcm_value', true );
		switch( $field['type'] ) {
			case 'text' : 
			default : 
?>
<p>
<?php
				if ( ! empty( $field['title'] ) && 'hidden' !== $field['type'] ) {
?>
	<label for="<?php echo esc_attr( $field['name'] . '_wpcm_value' ) ?>"><?php echo $field['title'] ?></label> 
<?php
				}
?>
	<input type="<?php echo $field['type'] ?>" name="wpcm_values[<?php echo $field['name'] ?>]" id="<?php echo $field['name'] . '_wpcm_value' ?>" value="<?php echo esc_attr( $val ) ?>" class="widefat" />
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
		
		foreach( $_POST['wpcm_values'] as $key => $value ) {
			update_post_meta( $post_id, $key . '_wpcm_value', esc_attr( $value ) );
		}
		
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
			
		if ( is_post_type_archive( 'contact' ) || is_tax( 'department' ) ) {
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
	 */
	function archive_loop() {
?>
<h1>Archive of Some Sort</h1>
<?php
		$obj = get_queried_object();
		if ( is_object( $obj ) ) {
			if ( property_exists( $obj, 'name' ) )
				printf( '<h2>%s</h2>', $obj->name );
		}
		
		global $wp_query;
		$query_vars = $wp_query->query_vars;
		$query_vars = array_merge( array(
			'orderby'        => 'title', 
			'order'          => 'asc', 
			'posts_per_page' => -1, 
		), $query_vars );
		
		query_posts( $query_vars );
		
		$i = 0;
		if ( have_posts() ) : 
			while ( have_posts() ) : the_post();
				$title = apply_filters( 'title-wpcm-value', get_post_meta( get_the_ID(), 'title_wpcm_value', true ) );
				
				$names = array();
				$names[] = get_post_meta( get_the_ID(), 'first_name_wpcm_value', true );
				$names[] = get_post_meta( get_the_ID(), 'last_name_wpcm_value', true );
				$names = apply_filters( 'name-wpcm-value', implode( ' ', $names ), $names );
				
				$has_email = apply_filters( 'email-wpcm-value', get_post_meta( get_the_ID(), 'email_wpcm_value', true ) );
				
				$phone = apply_filters( 'phone-wpcm-value', get_post_meta( get_the_ID(), 'office_phone_wpcm_value', true ) );
?>
	<div class="contact<?php echo $i % 2 ? ' alt' : ''; ?>">
		<span class="m-name"><a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( $title ) ?>"><?php echo $names ?></a></span>
		<span class="m-email"><?php echo $has_email ? '<a href="mailto:' . $has_email . '">' . $has_email . '</a>' : '&nbsp;'; ?></span>
		<span class="m-mobile"><span>870-235-<?php echo $phone ?></span> (O)</span>
		<span class="title"><?php echo $title ?></span>
	</div>
<?php
				$i++;
			endwhile;
		else :
			_e('<p>Sorry, no posts matched your criteria.</p>');
		endif;
	}
	
	/**
	 * Run the loop for a single directory entry
	 */
	function single_loop() {
		add_filter( 'single_post_title', array( $this, 'contact_post_title' ), 1, 2 );
		
		if ( have_posts() ) :
			while ( have_posts() ) :
				global $post;
				
				the_post();
				$post_ID = get_the_ID();
				
				$wpcm_image_path = $this->get_contact_image_src( $post );
				$wpcm_email = is_email( get_post_meta( $post_ID, 'email_wpcm_value', true ) );
				$wpcm_website = esc_url( get_post_meta( $post_ID, 'website_wpcm_value', true ) );
				$wpcm_number_mobile = get_post_meta( $post_ID, 'mobile_wpcm_value', true );
				$wpcm_number_office = get_post_meta( $post_ID, 'office_phone_wpcm_value', true );
				$wpcm_number_fax = get_post_meta( $post_ID, 'fax_wpcm_value', true );
				$addressone = get_post_meta( $post_ID, "address1_wpcm_value", true );
				$bldgoffice = $this->get_office( $post );
?>
<div class="vitals">
	<div class="photo" style="width: 150px; float: left; margin-right: 5px;">
    	<img style="max-width: 150px;" src="<?php echo $wpcm_image_path ?>" alt="<?php echo esc_attr( apply_filters( 'the_title', $post->post_title, $post ) ) ?>" />
    </div>
    <div id="contact-info" style="width: 675px; float: left;">
    	<h1 class="name fn"><?php echo $this->get_contact_name( $post ) ?></h1>
        <span class="title"><?php echo get_post_meta( $post->ID, 'title_wpcm_value', true ); ?></span>
        <span class="organization organization-unit"><?php the_terms( $post->ID, 'department', '', ', ', '' ) ?></span>
<?php
				if ( ! empty( $wpcm_email ) ) {
?>
        <span class="email"><a href="mailto:<?php echo $wpcm_email ?>"><?php echo $wpcm_email ?></a></span>
<?php
				}
				if ( ! empty( $wpcm_website ) ) {
?>
		<span class="website"><a class="url" href="<?php echo $wpcm_website ?>"><?php echo get_post_meta($post->ID, "website_wpcm_value", true); ?></a></span>
<?php
				}
?>
		<span class="phone" style="width: 45%; float: left;">
        	<ul class="phone-numbers tel">
            	<?php echo empty( $wpcm_number_mobile ) ? '' : '<li><span class="number value">' . $wpcm_number_mobile . '</span> <span class="type">' . __( '(Mobile)' ) . '</span></li>'; ?>
                <?php echo empty( $wpcm_number_office ) ? '' : '<li><span class="number value">870-235-' . $wpcm_number_office . '</span> <span class="type">' . __( '(Office)' ) . '</span></li>'; ?>
                <?php echo empty( $wpcm_number_fax ) ? '' : '<li><span class="number value">' . $wpcm_number_fax . '</span> <span class="type">' . __( '(Fax)' ) . '</span></li>'; ?>
            </ul>
        </span>
        <span class="address" style="width: 45%; float: left;">
        	<h3 class="site-subtitle" style="display: none;"><?php _e( 'Address' ) ?></h3>
            <span class="adr" style="clear:both;">
            	<?php echo empty( $addressone ) ? '' : '<span class="post-office-box">P.O. Box ' . $addressone . '</span><br/>' ?>
                <span class="street-address"><?php _e( 'Building/Office:' ) ?> <?php echo $bldgoffice; ?></span>
            </span>
        </span>
    </div>
</div>
<div class="extra">
	<div class="notes">
    	<span class="note"><?php the_content(); ?></span>
    </div>
</div>
<div id="modified rev" style="clear: both; text-align: right;">
	<?php printf( __( 'Last updated %s at %s' ), the_modified_date( 'F j, Y', '', '', false ), the_modified_date( 'g:i a', '', '', false ) ); ?>
</div>
<?php
			endwhile;
		else :
			_e( '<p>Sorry, nothing matched your criteria.</p>' );
		endif;
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
			return '<!-- Retrieved old bldgoffice value --> ' . get_post_meta( $post->ID, 'city_wpcm_value', true );
		
		$bldg = array_values( $bldg );
		$office = array_values( $office );
		
		$bldgoffice = array();
		foreach ( $bldg as $k => $b ) {
			print( "\n<!-- Evaluating bldg term with key of $k -->\n" );
			$bldgoffice[$k] = $b->name;
			if ( array_key_exists( $k, $office ) )
				$bldgoffice[$k] .= ' ' . $office[$k]->name;
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
	 */
	function get_contact_image_src( $post = null ) {
		if ( empty( $post ) )
			global $post;
		
		$noicon_url = esc_url( genesis_get_option( 'sau_contact_noicon_url' ) );
		if ( empty( $noicon_url ) )
			$noicon_url = null;
		
		$src = '';
		if ( has_post_thumbnail( $post->ID ) )
			$src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), array( 150, 0 ), false );
		else
			$src = get_post_meta( $post->ID, 'image_path_wpcm_value', true );
		
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
<?php
	}
	
	/**
	 * Output the alphabetical list of buildings
	 */
	function building_loop() {
?>
<ul class="building-list">
<?php
		wp_list_categories( array( 
			'taxonomy'    => 'sau-building', 
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
				if ( empty( $tmp ) )
					$tmp = wp_insert_term( $cat->name, 'department', (array) $cat );
				
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
				printf( __( '<p>There was an error converting the post with an ID of %d and a title of %s</p><pre><code>%s</code></pre>' ), $post->ID, $post->post_title, $tmp->get_error_message() );
			} elseif ( empty( $tmp ) ) {
				printf( __( '<p>There was an unknown error converting the post with an ID of %d and a title of %s</p>' ), $post->ID, $post->post_title );
			} else {
				$updated[] = $tmp;
				printf( __( '<p>The post with an ID of %d and a title of %s was converted successfully.</p>' ), $post->ID, $post->post_title );
			}
		}
		
		return sprintf( __( 'Successfully converted %d posts to contact entries' ), count( $updated ) );
	}
	
}