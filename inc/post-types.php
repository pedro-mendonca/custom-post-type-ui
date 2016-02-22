<?php
/**
 * Custom Post Type UI Post Type Settings.
 *
 * @package CPTUI
 * @subpackage PostTypes
 * @author WebDevStudios
 * @since 1.0.0
 */

# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add our cptui.js file, with dependencies on jQuery and jQuery UI.
 *
 * @since 1.0.0
 */
function cptui_post_type_enqueue_scripts() {

	$currentScreen = get_current_screen();

	if ( ! is_object( $currentScreen ) || $currentScreen->base == "post" ) {
		return;
	}

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	/*$post_types = get_option( 'cptui_post_types' );
	foreach( $post_types as $type ) {
		$types[] = $type['name'];
	}*/

	wp_enqueue_script( 'cptui', plugins_url( 'js/cptui.js', dirname(__FILE__) ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-accordion' ), CPTUI_VERSION, true );
	wp_localize_script(	'cptui', 'cptui_type_data',
		array(
			'confirm' => __( 'Are you sure you want to delete this?', 'custom-post-type-ui' ),
			# 'post_types' => $types,
			#'post_change_name' => '<div class="typetax-rename">' . __( 'Changing this will rename the post type.', 'custom-post-type-ui' ) . '</div>'
		)
	);
}
add_action( 'admin_enqueue_scripts', 'cptui_post_type_enqueue_scripts' );

/**
 * Create our settings page output.
 *
 * @since 1.0.0
 *
 * @return string HTML output for the page.
 */
function cptui_manage_post_types() {

	$post_type_deleted = false;

	if ( !empty( $_POST ) ) {
		if ( isset( $_POST['cpt_submit'] ) ) {
			check_admin_referer( 'cptui_addedit_post_type_nonce_action', 'cptui_addedit_post_type_nonce_field' );
			$notice = cptui_update_post_type( $_POST );
		} elseif ( isset( $_POST['cpt_delete'] ) ) {
			check_admin_referer( 'cptui_addedit_post_type_nonce_action', 'cptui_addedit_post_type_nonce_field' );
			$notice = cptui_delete_post_type( $_POST );
			$post_type_deleted = true;
		}
	}

	$tab = ( !empty( $_GET ) && !empty( $_GET['action'] ) && 'edit' == $_GET['action'] ) ? 'edit' : 'new'; ?>

	<div class="wrap">

	<?php
	if ( isset( $notice ) ) {
		echo $notice;
	}

	cptui_settings_tab_menu();

	if ( 'edit' == $tab ) {

		$post_types = get_option( 'cptui_post_types' );

		$selected_post_type = cptui_get_current_post_type( $post_type_deleted );

		if ( $selected_post_type ) {
			if ( array_key_exists( $selected_post_type, $post_types ) ) {
				$current = $post_types[ $selected_post_type ];
			}
		}
	}

	$ui = new cptui_admin_ui();

	# Will only be set if we're already on the edit screen
	if ( !empty( $post_types ) ) { ?>
		<form id="cptui_select_post_type" method="post">
			<p><?php _e( 'DO NOT EDIT the post type slug unless necessary. Changing that value registers a new post type entry for your install.', 'custom-post-type-ui' ); ?></p>
			<p><?php _e( 'Use appropriate checkbox above save/delete buttons if you wish to change slugs and update post types for existing posts.', 'custom-post-type-ui' ); ?></p>
			<label for="post_type"><?php _e( 'Select: ', 'custom-post-type-ui' ); ?></label>
			<?php
			cptui_post_types_dropdown( $post_types );

			/**
			 * Filters the text value to use on the select post type button.
			 *
			 * @since 1.0.0
			 *
			 * @param string $value Text to use for the button.
			 */
			?>
			<input type="submit" class="button-secondary" name="cptui_select_post_type_submit" value="<?php echo esc_attr( apply_filters( 'cptui_post_type_submit_select', __( 'Select', 'custom-post-type-ui' ) ) ); ?>" />
		</form>
	<?php

		/**
		 * Fires below the post type select input.
		 *
		 * @since 1.1.0
		 *
		 * @param string $value Current post type selected.
		 */
		do_action( 'cptui_below_post_type_select', $current['name'] );
	} ?>

	<form method="post">
		<table class="form-table cptui-table">
			<tr>
				<td class="outter">
					<table>
						<?php

						/*
						 * Post Slug
						 */
						echo $ui->get_text_input( array(
							'namearray'     => 'cpt_custom_post_type',
							'name'          => 'name',
							'textvalue'     => ( isset( $current['name'] ) ) ? esc_attr( $current['name'] ) : '',
							'maxlength'     => '20',
							'onblur'        => 'this.value=this.value.toLowerCase()',
							'labeltext'     => __( 'Post Type Slug', 'custom-post-type-ui' ),
							'aftertext'     => __( '(e.g. movie)', 'custom-post-type-ui' ),
							'helptext'      => esc_attr__( 'The post type name. Used to retrieve custom post type content. Should be short and unique', 'custom-post-type-ui'),
							'required'      => true
							) );

						/*
						 * Post Label
						 */
						echo $ui->get_text_input( array(
							'namearray'     => 'cpt_custom_post_type',
							'name'          => 'label',
							'textvalue'     => ( isset( $current['label'] ) ) ? esc_attr( $current['label'] ) : '',
							'labeltext'     => __( 'Plural Label', 'custom-post-type-ui' ),
							'aftertext'     => __( '(e.g. Movies)', 'custom-post-type-ui' ),
							'helptext'      => esc_attr__( 'Post type label. Used in the admin menu for displaying post types.', 'custom-post-type-ui' ),
							) );

						/*
						 * Post Singular Slug
						 */
						echo $ui->get_text_input( array(
							'namearray'     => 'cpt_custom_post_type',
							'name'          => 'singular_label',
							'textvalue'     => ( isset( $current['singular_label'] ) ) ? esc_attr( $current['singular_label'] ) : '',
							'labeltext'     => __( 'Singular Label', 'custom-post-type-ui' ),
							'aftertext'     => __( '(e.g. Movie)', 'custom-post-type-ui' ),
							'helptext'      => esc_attr__( 'Custom Post Type Singular label. Used in WordPress when a singular label is needed.', 'custom-post-type-ui' ),
							) );

						/*
						 * Post Description
						 */
						if ( isset( $current['description'] ) ) {
							$current['description'] = stripslashes_deep( $current['description'] );
						}

						echo $ui->get_textarea_input( array(
							'namearray' => 'cpt_custom_post_type',
							'name' => 'description',
							'rows' => '4',
							'cols' => '40',
							'textvalue' => ( isset( $current['description'] ) ) ?  esc_textarea( $current['description'] ) : '',
							'labeltext' => __('Description', 'custom-post-type-ui'),
							'helptext' => esc_attr__( 'Custom Post Type Description. Describe what your custom post type is used for.', 'custom-post-type-ui' )
							) );

						/*
						 * Migrate posts
						 */
						if ( 'edit' == $tab ) {
							echo $ui->get_check_input( array(
								'checkvalue' => 'update_post_types',
								'checked'    => 'false',
								'name'       => 'update_post_types',
								'namearray'  => 'update_post_types',
								'labeltext'  => __( 'Migrate posts to newly renamed post type?', 'custom-post-type-ui' ),
								'helptext'   => esc_attr__( 'Check this to migrate posts if and when renaming your post type.', 'custom-post-type-ui' ),
								'default'    => false
							) );
						}
						?>
					</table>
				<p class="submit">
					<?php wp_nonce_field( 'cptui_addedit_post_type_nonce_action', 'cptui_addedit_post_type_nonce_field' );
					if ( !empty( $_GET ) && !empty( $_GET['action'] ) && 'edit' == $_GET['action'] ) { ?>
						<?php

						/**
						 * Filters the text value to use on the button when editing.
						 *
						 * @since 1.0.0
						 *
						 * @param string $value Text to use for the button.
						 */
						?>
						<input type="submit" class="button-primary" name="cpt_submit" value="<?php echo esc_attr( apply_filters( 'cptui_post_type_submit_edit', __( 'Save Post Type', 'custom-post-type-ui' ) ) ); ?>" />
						<?php

						/**
						 * Filters the text value to use on the button when deleting.
						 *
						 * @since 1.0.0
						 *
						 * @param string $value Text to use for the button.
						 */
						?>
						<input type="submit" class="button-secondary" name="cpt_delete" id="cpt_submit_delete" value="<?php echo esc_attr( apply_filters( 'cptui_post_type_submit_delete', __( 'Delete Post Type', 'custom-post-type-ui' ) ) ); ?>" />
					<?php } else { ?>
						<?php

						/**
						 * Filters the text value to use on the button when adding.
						 *
						 * @since 1.0.0
						 *
						 * @param string $value Text to use for the button.
						 */
						?>
						<input type="submit" class="button-primary" name="cpt_submit" value="<?php echo esc_attr( apply_filters( 'cptui_post_type_submit_add', __( 'Add Post Type', 'custom-post-type-ui' ) ) ); ?>" />
					<?php } ?>

					<?php if ( !empty( $current ) ) { ?>
						<input type="hidden" name="cpt_original" id="cpt_original" value="<?php echo $current['name']; ?>" />
					<?php } ?>

					<input type="hidden" name="cpt_type_status" id="cpt_type_status" value="<?php echo $tab; ?>" />
				</p>

				<?php if ( 'new' == $tab ) { ?>
					<h2><?php _e( 'Starter Notes', 'custom-post-type-ui' ); ?></h2>
					<div><ol>
						<?php
						echo '<li>' . sprintf( __( 'Post Type names should have %smax 20 characters%s, and only contain alphanumeric, lowercase characters, underscores in place of spaces and letters that do not have accents. Reserved names: post, page, attachment, revision, nav_menu_item.', 'custom-post-type-ui' ), '<strong class="wp-ui-highlight">', '</strong>' );
						echo '<li>' . sprintf( __( 'If you are unfamiliar with the advanced post type settings, just fill in the %sPost Type Name%s and %sLabel%s fields. Remaining settings will use default values. Labels, if left blank, will be automatically created based on the post type name. Hover over the question mark for more details.', 'custom-post-type-ui' ), '<strong class="wp-ui-highlight">', '</strong>', '<strong class="wp-ui-highlight">', '</strong>' );
						echo '<li>' . sprintf( __( 'Deleting custom post types will %sNOT%s delete any content into the database or added to those post types. You can easily recreate your post types and the content will still exist.', 'custom-post-type-ui' ), '<strong class="wp-ui-highlight">', '</strong>' ); ?>
					</ol></div>
				<?php } ?>
			</td>
			<td class="outter">
				<div>
					<h2><?php _e( 'Labels', 'custom-post-type-ui' ); ?></h2>
						<div>
							<table>
							<?php
							/*
							 * Post Admin Menu Name
							 */
							echo $ui->get_text_input( array(
								'labeltext'     => __( 'Menu Name', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Custom menu name for your custom post type.', 'custom-post-type-ui' ),
								'namearray'     => 'cpt_labels',
								'name'          => 'menu_name',
								'textvalue'     => ( isset( $current['labels']['menu_name'] ) ) ? esc_attr( $current['labels']['menu_name'] ) : '',
								'aftertext'     => __( '(e.g. My Movies)', 'custom-post-type-ui' )
								) );

							/*
							 * Post All Items
							 */
							echo $ui->get_text_input( array(
								'labeltext'     => __( 'All Items', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Post type label. Used in the admin menu for displaying post types.', 'custom-post-type-ui' ),
								'namearray'     => 'cpt_labels',
								'name'          => 'all_items',
								'textvalue'     => ( isset( $current['labels']['all_items'] ) ) ? esc_attr( $current['labels']['all_items'] ) : '',
								'aftertext'     => __( '(e.g. All Movies)', 'custom-post-type-ui' )
								) );

							/*
							 * Add New Label
							 */
							echo $ui->get_text_input( array(
								'labeltext'     => __( 'Add New', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Post type label. Used in the admin menu for displaying post types.', 'custom-post-type-ui' ),
								'namearray'     => 'cpt_labels',
								'name'          => 'add_new',
								'textvalue'     => ( isset( $current['labels']['add_new'] ) ) ? esc_attr( $current['labels']['add_new'] ) : '',
								'aftertext'     => __( '(e.g. Add New)', 'custom-post-type-ui' )
								) );

							/*
							 * Add New Item Label
							 */
							echo $ui->get_text_input( array(
								'labeltext'     => __( 'Add New Item', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Post type label. Used in the admin menu for displaying post types.', 'custom-post-type-ui' ),
								'namearray'     => 'cpt_labels',
								'name'          => 'add_new_item',
								'textvalue'     => ( isset( $current['labels']['add_new_item'] ) ) ? esc_attr( $current['labels']['add_new_item'] ) : '',
								'aftertext'     => __( '(e.g. Add New Movie)', 'custom-post-type-ui' )
								) );

							/*
							 * Edit Label
							 */
							echo $ui->get_text_input( array(
								'labeltext'     => __( 'Edit', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Post type label. Used in the admin menu for displaying post types.', 'custom-post-type-ui' ),
								'namearray'     => 'cpt_labels',
								'name'          => 'edit',
								'textvalue'     => ( isset( $current['labels']['edit'] ) ) ? esc_attr( $current['labels']['edit'] ) : '',
								'aftertext'     => __( '(e.g. Edit)', 'custom-post-type-ui' )
								) );

							/*
							 * Edit Item Label
							 */
							echo $ui->get_text_input( array(
								'labeltext'     => __( 'Edit Item', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Post type label. Used in the admin menu for displaying post types.', 'custom-post-type-ui' ),
								'namearray'     => 'cpt_labels',
								'name'          => 'edit_item',
								'textvalue'     => ( isset( $current['labels']['edit_item'] ) ) ? esc_attr( $current['labels']['edit_item'] ) : '',
								'aftertext'     => __( '(e.g. Edit Movie)', 'custom-post-type-ui' )
								) );

							/*
							 * New Item Label
							 */
							echo $ui->get_text_input( array(
								'labeltext'     => __( 'New Item', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Post type label. Used in the admin menu for displaying post types.', 'custom-post-type-ui' ),
								'namearray'     => 'cpt_labels',
								'name'          => 'new_item',
								'textvalue'     => ( isset( $current['labels']['new_item'] ) ) ? esc_attr( $current['labels']['new_item'] ) : '',
								'aftertext'     => __( '(e.g. New Movie)', 'custom-post-type-ui' )
								) );

							/*
							 * View Label
							 */
							echo $ui->get_text_input( array(
								'labeltext'     => __( 'View', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Post type label. Used in the admin menu for displaying post types.', 'custom-post-type-ui' ),
								'namearray'     => 'cpt_labels',
								'name'          => 'view',
								'textvalue'     => ( isset( $current['labels']['view'] ) ) ? esc_attr( $current['labels']['view'] ) : '',
								'aftertext'     => __( '(e.g. View)', 'custom-post-type-ui' )
								) );

							/*
							 * View Item Label
							 */
							echo $ui->get_text_input( array(
								'labeltext'     => __( 'View Item', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Post type label. Used in the admin menu for displaying post types.', 'custom-post-type-ui' ),
								'namearray'     => 'cpt_labels',
								'name'          => 'view_item',
								'textvalue'     => ( isset( $current['labels']['view_item'] ) ) ? esc_attr( $current['labels']['view_item'] ) : '',
								'aftertext'     => __( '(e.g. View Movie)', 'custom-post-type-ui' )
								) );

							/*
							 * Search Item Label
							 */
							echo $ui->get_text_input( array(
								'labeltext'     => __( 'Search Item', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Post type label. Used in the admin menu for displaying post types.', 'custom-post-type-ui' ),
								'namearray'     => 'cpt_labels',
								'name'          => 'search_items',
								'textvalue'     => ( isset( $current['labels']['search_items'] ) ) ? esc_attr( $current['labels']['search_items'] ) : '',
								'aftertext'     => __( '(e.g. Search Movie)', 'custom-post-type-ui' )
								) );

							/*
							 * Not Found Label
							 */
							echo $ui->get_text_input( array(
								'labeltext'     => __( 'Not Found', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Post type label. Used in the admin menu for displaying post types.', 'custom-post-type-ui' ),
								'namearray'     => 'cpt_labels',
								'name'          => 'not_found',
								'textvalue'     => ( isset( $current['labels']['not_found'] ) ) ? esc_attr( $current['labels']['not_found'] ) : '',
								'aftertext'     => __( '(e.g. No Movies found)', 'custom-post-type-ui' )
								) );

							/*
							 * Not Found In Trash Label
							 */
							echo $ui->get_text_input( array(
								'labeltext'     => __( 'Not Found in Trash', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Post type label. Used in the admin menu for displaying post types.', 'custom-post-type-ui' ),
								'namearray'     => 'cpt_labels',
								'name'          => 'not_found_in_trash',
								'textvalue'     => ( isset( $current['labels']['not_found_in_trash'] ) ) ? esc_attr( $current['labels']['not_found_in_trash'] ) : '',
								'aftertext'     => __( '(e.g. No Movies found in Trash)', 'custom-post-type-ui' )
								) );

							/*
							 * Parent Label
							 */
							echo $ui->get_text_input( array(
								'labeltext'     => __( 'Parent', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Post type label. Used in the admin menu for displaying post types.', 'custom-post-type-ui' ),
								'namearray'     => 'cpt_labels',
								'name'          => 'parent',
								'textvalue'     => ( isset( $current['labels']['parent'] ) ) ? esc_attr( $current['labels']['parent'] ) : '',
								'aftertext'     => __( '(e.g. Parent Movie)', 'custom-post-type-ui' )
								) );

							echo $ui->get_text_input( array(
								'labeltext' => __( 'Featured Image', 'custom-post-type-ui' ),
								'helptext'  => esc_attr__( 'Post type label. Overrides the "Featured Image" phrase for this post type.', 'custom-post-type-ui' ),
								'namearray' => 'cpt_labels',
								'name'      => 'featured_image',
								'textvalue' => ( isset( $current['labels']['featured_image'] ) ) ? esc_attr( $current['labels']['featured_image'] ) : '',
								'aftertext' => __( '(e.g. Featured image for this movie)', 'custom-post-type-ui' )
								) );

							echo $ui->get_text_input( array(
								'labeltext' => __( 'Set Featured Image', 'custom-post-type-ui' ),
								'helptext'  => esc_attr__( 'Post type label. Overrides the "Set featured image" phrase for this post type.', 'custom-post-type-ui' ),
								'namearray' => 'cpt_labels',
								'name'      => 'set_featured_image',
								'textvalue' => ( isset( $current['labels']['set_featured_image'] ) ) ? esc_attr( $current['labels']['set_featured_image'] ) : '',
								'aftertext' => __( '(e.g. Set featured image for this movie)', 'custom-post-type-ui' )
								) );

							echo $ui->get_text_input( array(
								'labeltext' => __( 'Remove Featured Image', 'custom-post-type-ui' ),
								'helptext'  => esc_attr__( 'Post type label. Overrides the "Remove featured image" phrase for this post type.', 'custom-post-type-ui' ),
								'namearray' => 'cpt_labels',
								'name'      => 'remove_featured_image',
								'textvalue' => ( isset( $current['labels']['remove_featured_image'] ) ) ? esc_attr( $current['labels']['remove_featured_image'] ) : '',
								'aftertext' => __( '(e.g. Remove featured image for this movie)', 'custom-post-type-ui' )
								) );

							echo $ui->get_text_input( array(
								'labeltext' => __( 'Use Featured Image', 'custom-post-type-ui' ),
								'helptext'  => esc_attr__( 'Post type label. Overrides the "Use as featured image" phrase for this post type.', 'custom-post-type-ui' ),
								'namearray' => 'cpt_labels',
								'name'      => 'use_featured_image',
								'textvalue' => ( isset( $current['labels']['use_featured_image'] ) ) ? esc_attr( $current['labels']['use_featured_image'] ) : '',
								'aftertext' => __( '(e.g. Use as featured image for this movie)', 'custom-post-type-ui' )
							) );

							echo $ui->get_text_input( array(
								'labeltext' => __( 'Archives', 'custom-post-type-ui' ),
								'helptext'  => esc_attr__( 'Post type label. Post type archive label used in nav menus.', 'custom-post-type-ui' ),
								'namearray' => 'cpt_labels',
								'name'      => 'archives',
								'textvalue' => ( isset( $current['labels']['archives'] ) ) ? esc_attr( $current['labels']['archives'] ) : '',
								'aftertext' => __( '(e.g. Movie archives)', 'custom-post-type-ui' )
							) );

							echo $ui->get_text_input( array(
								'labeltext' => __( 'Insert into item', 'custom-post-type-ui' ),
								'helptext'  => esc_attr__( 'Post type label. Overrides the "Insert into post" or "Insert into page" phrase for this post type.', 'custom-post-type-ui' ),
								'namearray' => 'cpt_labels',
								'name'      => 'insert_into_item',
								'textvalue' => ( isset( $current['labels']['insert_into_item'] ) ) ? esc_attr( $current['labels']['insert_into_item'] ) : '',
								'aftertext' => __( '(e.g. Insert into movie)', 'custom-post-type-ui' )
							) );

							echo $ui->get_text_input( array(
								'labeltext' => __( 'Uploaded to this Item', 'custom-post-type-ui' ),
								'helptext'  => esc_attr__( 'Post type label. Overrides the "Uploaded to this post" or "Uploaded to this page" phrase for this post type.', 'custom-post-type-ui' ),
								'namearray' => 'cpt_labels',
								'name'      => 'uploaded_to_this_item',
								'textvalue' => ( isset( $current['labels']['uploaded_to_this_item'] ) ) ? esc_attr( $current['labels']['uploaded_to_this_item'] ) : '',
								'aftertext' => __( '(e.g. Uploaded to this movie)', 'custom-post-type-ui' )
							) );

							echo $ui->get_text_input( array(
								'labeltext' => __( 'Filter Items List', 'custom-post-type-ui' ),
								'helptext'  => esc_attr__( 'Post type label. Screen reader text for the filter links heading on the post type listing screen.', 'custom-post-type-ui' ),
								'namearray' => 'cpt_labels',
								'name'      => 'filter_items_list',
								'textvalue' => ( isset( $current['labels']['filter_items_list'] ) ) ? esc_attr( $current['labels']['filter_items_list'] ) : '',
								'aftertext' => __( '(e.g. Filter movies list)', 'custom-post-type-ui' )
							) );

							echo $ui->get_text_input( array(
								'labeltext' => __( 'Items List Navigation', 'custom-post-type-ui' ),
								'helptext'  => esc_attr__( 'Post type label. Screen reader text for the pagination heading on the post type listing screen.', 'custom-post-type-ui' ),
								'namearray' => 'cpt_labels',
								'name'      => 'items_list_navigation',
								'textvalue' => ( isset( $current['labels']['items_list_navigation'] ) ) ? esc_attr( $current['labels']['items_list_navigation'] ) : '',
								'aftertext' => __( '(e.g. Movies list navigation)', 'custom-post-type-ui' )
							) );

							echo $ui->get_text_input( array(
								'labeltext' => __( 'Items List', 'custom-post-type-ui' ),
								'helptext'  => esc_attr__( 'Post type label. Screen reader text for the items list heading on the post type listing screen.', 'custom-post-type-ui' ),
								'namearray' => 'cpt_labels',
								'name'      => 'items_list',
								'textvalue' => ( isset( $current['labels']['items_list'] ) ) ? esc_attr( $current['labels']['items_list'] ) : '',
								'aftertext' => __( '(e.g. Movies list)', 'custom-post-type-ui' )
							) );
							?>
							</table>
						</div>

					<h2><?php _e( 'Settings', 'custom-post-type-ui' ); ?></h2>

						<div>
							<table>
							<?php
							/*
							 * Public Boolean
							 */
							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => __( 'False', 'custom-post-type-ui' ) ),
									array( 'attr' => '1', 'text' => __( 'True', 'custom-post-type-ui' ), 'default' => 'true' )
								)
							);
							$selected = ( isset( $current ) ) ? disp_boolean( $current['public'] ) : '';
							$select['selected'] = ( !empty( $selected ) ) ? $current['public'] : '';
							echo $ui->get_select_input( array(
								'namearray'     => 'cpt_custom_post_type',
								'name'          => 'public',
								'labeltext'     => __( 'Public', 'custom-post-type-ui' ),
								'aftertext'     => __( '(CPTUI default: True.)', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'WordPress core defaults to false. CPTUI defaults to true for user sake. Whether posts of this type should be shown in the admin UI and is publicly queryable.', 'custom-post-type-ui' ),
								'selections'    => $select
							) );

							/*
							 * Show UI Boolean
							 */
							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => __( 'False', 'custom-post-type-ui' ) ),
									array( 'attr' => '1', 'text' => __( 'True', 'custom-post-type-ui' ), 'default' => 'true' )
								)
							);
							$selected = ( isset( $current ) ) ? disp_boolean( $current['show_ui'] ) : '';
							$select['selected'] = ( !empty( $selected ) ) ? $current['show_ui'] : '';
							echo $ui->get_select_input( array(
								'namearray'     => 'cpt_custom_post_type',
								'name'          => 'show_ui',
								'labeltext'     => __( 'Show UI', 'custom-post-type-ui' ),
								'aftertext'     => __( '(default: True)', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Whether to generate a default UI for managing this post type.', 'custom-post-type-ui' ),
								'selections'    => $select
							) );

							/*
							 * Show in Nav Menus
							 */
							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => __( 'False', 'custom-post-type-ui' ) ),
									array( 'attr' => '1', 'text' => __( 'True', 'custom-post-type-ui' ), 'default' => 'true' )
								)
							);
							$selected = ( isset( $current ) && ! empty( $current['show_in_nav_menus'] ) ) ? disp_boolean( $current['show_in_nav_menus'] ) : '';
							$select['selected'] = ( ! empty( $selected ) && ! empty( $current['show_in_nav_menus'] ) ) ? $current['show_in_nav_menus'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_post_type',
								'name'       => 'show_in_nav_menus',
								'labeltext'  => __( 'Show in Nav Menus', 'custom-post-type-ui' ),
								'aftertext'  => __( '(CPTUI default: true)', 'custom-post-type-ui' ),
								'helptext'   => esc_attr__( 'Whether post type is available for selection in navigation menus.', 'custom-post-type-ui' ),
								'selections' => $select
							) );

							/*
							 * show_in_rest Boolean
							 */
							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => __( 'False', 'custom-post-type-ui' ), 'default' => 'false' ),
									array( 'attr' => '1', 'text' => __( 'True', 'custom-post-type-ui' ) )
								)
							);
							$selected = ( isset( $current ) && ! empty( $current['show_in_rest'] ) ) ? disp_boolean( $current['show_in_rest'] ) : '';
							$select['selected'] = ( !empty( $selected ) && ! empty( $current['show_in_rest'] ) ) ? $current['show_in_rest'] : '';
							echo $ui->get_select_input( array(
								'namearray'     => 'cpt_custom_post_type',
								'name'          => 'show_in_rest',
								'labeltext'     => __( 'Show in REST API', 'custom-post-type-ui' ),
								'aftertext'     => __( '(default: False)', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Whether to show this post type data in the WP REST API.', 'custom-post-type-ui' ),
								'selections'    => $select
							) );

							/*
							 * rest_base slug.
							 */
							echo $ui->get_text_input( array(
								'labeltext' => __( 'REST API base slug', 'custom-post-type-ui' ),
								'helptext'  => esc_attr__( 'Slug to use in REST API URLs.', 'custom-post-type-ui' ),
								'namearray' => 'cpt_custom_post_type',
								'name'      => 'rest_base',
								'textvalue' => ( isset( $current['rest_base'] ) ) ? esc_attr( $current['rest_base'] ) : '',
							) );

							/*
							 * Has Archive Boolean
							 */
							echo $ui->get_tr_start() . $ui->get_th_start();
							echo $ui->get_label( 'has_archive', __( 'Has Archive', 'custom-post-type-ui' ) );
							echo $ui->get_help( esc_attr__( 'Whether the post type will have a post type archive URL.', 'custom-post-type-ui' ) );
							echo $ui->get_p( __( 'If left blank, the archive slug will default to the post type slug.', 'custom-post-type-ui' ) );
							echo $ui->get_th_end() . $ui->get_td_start();

							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => __( 'False', 'custom-post-type-ui' ), 'default' => 'true' ),
									array( 'attr' => '1', 'text' => __( 'True', 'custom-post-type-ui' ) )
								)
							);
							$selected = ( isset( $current ) ) ? disp_boolean( $current['has_archive'] ) : '';
							$select['selected'] = ( !empty( $selected ) ) ? $current['has_archive'] : '';
							echo $ui->get_select_input( array(
								'namearray'     => 'cpt_custom_post_type',
								'name'          => 'has_archive',
								'aftertext'     => __( '(default: False)', 'custom-post-type-ui' ),
								'selections'    => $select,
								'wrap'          => false
							) );

							/*
							 * Has Archive Input
							 */
							echo $ui->get_text_input( array(
								'namearray'     => 'cpt_custom_post_type',
								'name'          => 'has_archive_string',
								'textvalue'     => ( isset( $current['has_archive_string'] ) ) ? esc_attr( $current['has_archive_string'] ) : '',
								'helptext'      => esc_attr__( 'Slug to be used for archive URL.', 'custom-post-type-ui' ),
								'helptext_after'=> true,
								'wrap'          => false
							) );
							echo $ui->get_td_end() . $ui->get_tr_end();

							/*
							 * Exclude From Search Boolean
							 */
							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => __( 'False', 'custom-post-type-ui' ), 'default' => 'true' ),
									array( 'attr' => '1', 'text' => __( 'True', 'custom-post-type-ui' ) )
								)
							);
							$selected = ( isset( $current ) ) ? disp_boolean( $current['exclude_from_search'] ) : '';
							$select['selected'] = ( !empty( $selected ) ) ? $current['exclude_from_search'] : '';
							echo $ui->get_select_input( array(
								'namearray'     => 'cpt_custom_post_type',
								'name'          => 'exclude_from_search',
								'labeltext'     => __( 'Exclude From Search', 'custom-post-type-ui' ),
								'aftertext'     => __( '(default: False)', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Whether to exclude posts with this post type from front end search results.', 'custom-post-type-ui' ),
								'selections'    => $select
							) );

							/*
							 * Capability Type Input
							 */
							echo $ui->get_text_input( array(
								'namearray'     => 'cpt_custom_post_type',
								'name'          => 'capability_type',
								'textvalue'     => ( isset( $current['capability_type'] ) ) ? esc_attr( $current['capability_type'] ) : 'post',
								'labeltext'     => __( 'Capability Type', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'The post type to use for checking read, edit, and delete capabilities', 'custom-post-type-ui' ),
								) );

							/*
							 * Hierarchical Boolean
							 */
							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => __( 'False', 'custom-post-type-ui' ), 'default' => 'true' ),
									array( 'attr' => '1', 'text' => __( 'True', 'custom-post-type-ui' ) )
								)
							);
							$selected = ( isset( $current ) ) ? disp_boolean( $current['hierarchical'] ) : '';
							$select['selected'] = ( !empty( $selected ) ) ? $current['hierarchical'] : '';
							echo $ui->get_select_input( array(
								'namearray'     => 'cpt_custom_post_type',
								'name'          => 'hierarchical',
								'labeltext'     => __( 'Hierarchical', 'custom-post-type-ui' ),
								'aftertext'     => __( '(default: False)', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Whether the post type can have parent-child relationships', 'custom-post-type-ui' ),
								'selections'    => $select
							) );

							/*
							 * Rewrite Boolean
							 */
							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => __( 'False', 'custom-post-type-ui' ) ),
									array( 'attr' => '1', 'text' => __( 'True', 'custom-post-type-ui' ), 'default' => 'true' )
								)
							);
							$selected = ( isset( $current ) ) ? disp_boolean( $current['rewrite'] ) : '';
							$select['selected'] = ( !empty( $selected ) ) ? $current['rewrite'] : '';
							echo $ui->get_select_input( array(
								'namearray'     => 'cpt_custom_post_type',
								'name'          => 'rewrite',
								'labeltext'     => __( 'Rewrite', 'custom-post-type-ui' ),
								'aftertext'     => __( '(default: True)', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Whether or not WordPress should use rewrites for this post type', 'custom-post-type-ui' ),
								'selections'    => $select
							) );

							/*
							 * Rewrite Slug Input
							 */
							echo $ui->get_text_input( array(
								'namearray'     => 'cpt_custom_post_type',
								'name'          => 'rewrite_slug',
								'textvalue'     => ( isset( $current['rewrite_slug'] ) ) ? esc_attr( $current['rewrite_slug'] ) : '',
								'labeltext'     => __( 'Custom Rewrite Slug', 'custom-post-type-ui' ),
								'aftertext'     => __( '(default: post type slug)', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Custom post type slug to use instead of the default.', 'custom-post-type-ui' ),
							) );

							/*
							 * Rewrite With Front Boolean
							 */
							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => __( 'False', 'custom-post-type-ui' ) ),
									array( 'attr' => '1', 'text' => __( 'True', 'custom-post-type-ui' ), 'default' => 'true' )
								)
							);
							$selected = ( isset( $current ) ) ? disp_boolean( $current['rewrite_withfront'] ) : '';
							$select['selected'] = ( !empty( $selected ) ) ? $current['rewrite_withfront'] : '';
							echo $ui->get_select_input( array(
								'namearray'     => 'cpt_custom_post_type',
								'name'          => 'rewrite_withfront',
								'labeltext'     => __( 'With Front', 'custom-post-type-ui' ),
								'aftertext'     => __( '(default: True)', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Should the permastruct be prepended with the front base.', 'custom-post-type-ui' ),
								'selections'    => $select
							) );

							/*
							 * Query Var Boolean
							 */
							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => __( 'False', 'custom-post-type-ui' ) ),
									array( 'attr' => '1', 'text' => __( 'True', 'custom-post-type-ui' ), 'default' => 'true' )
								)
							);
							$selected = ( isset( $current ) ) ? disp_boolean( $current['query_var'] ) : '';
							$select['selected'] = ( !empty( $selected ) ) ? $current['query_var'] : '';
							echo $ui->get_select_input( array(
								'namearray'     => 'cpt_custom_post_type',
								'name'          => 'query_var',
								'labeltext'     => __( 'Query Var', 'custom-post-type-ui' ),
								'aftertext'     => __( '(default: True)', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Sets the query_var key for this post type.', 'custom-post-type-ui' ),
								'selections'    => $select
							) );

							/*
							 * Query Var Slug Input
							 */
							echo $ui->get_text_input( array(
								'namearray' => 'cpt_custom_post_type',
								'name'      => 'query_var_slug',
								'textvalue' => ( isset( $current['query_var_slug'] ) ) ? esc_attr( $current['query_var_slug'] ) : '',
								'labeltext' => __( 'Custom Query Var Slug', 'custom-post-type-ui' ),
								'aftertext' => __( '(default: post type slug) Query var needs to be true to use.', 'custom-post-type-ui' ),
								'helptext'  => esc_attr__( 'Custom query var slug to use instead of the default.', 'custom-post-type-ui' ),
							) );

							/*
							 * Menu Position Boolean
							 */
							echo $ui->get_tr_start() . $ui->get_th_start();
							echo $ui->get_label( 'menu_position', __( 'Menu Position', 'custom-post-type-ui' ) );
							echo $ui->get_help( esc_attr__( 'The position in the menu order the post type should appear. show_in_menu must be true.', 'custom-post-type-ui' ) );
							echo $ui->get_p( __( 'See <a href="http://codex.wordpress.org/Function_Reference/register_post_type#Parameters" target="_blank">Available options</a> in the "menu_position" section. Range of 5-100', 'custom-post-type-ui' ) );

							echo $ui->get_th_end() . $ui->get_td_start();
							echo $ui->get_text_input( array(
								'namearray'     => 'cpt_custom_post_type',
								'name'          => 'menu_position',
								'textvalue'     => ( isset( $current['menu_position'] ) ) ? esc_attr( $current['menu_position'] ) : '',
								'wrap'          => false
							) );
							echo $ui->get_td_end() . $ui->get_tr_end();

							/*
							 * Show In Menu Boolean
							 */
							echo $ui->get_tr_start() . $ui->get_th_start();
							echo $ui->get_label( 'show_in_menu', __( 'Show in Menu', 'custom-post-type-ui' ) );
							echo $ui->get_help( esc_attr__( 'Whether to show the post type in the admin menu and where to show that menu.', 'custom-post-type-ui' ) );
							echo $ui->get_p( __( '"Show UI" must be "true". If an existing top level page such as "tools.php" is indicated for second input, post type will be sub menu of that.', 'custom-post-type-ui' ) );
							echo $ui->get_th_end() . $ui->get_td_start();

							$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => __( 'False', 'custom-post-type-ui' ) ),
									array( 'attr' => '1', 'text' => __( 'True', 'custom-post-type-ui' ), 'default' => 'true' )
								)
							);
							$selected = ( isset( $current ) ) ? disp_boolean( $current['show_in_menu'] ) : '';
							$select['selected'] = ( !empty( $selected ) ) ? $current['show_in_menu'] : '';
							echo $ui->get_select_input( array(
								'namearray'     => 'cpt_custom_post_type',
								'name'          => 'show_in_menu',
								'aftertext'     => __( '(default: True)', 'custom-post-type-ui' ),
								'selections'    => $select,
								'wrap'          => false
							) );

							/*
							 * Show In Menu Input
							 */
							echo $ui->get_text_input( array(
								'namearray'     => 'cpt_custom_post_type',
								'name'          => 'show_in_menu_string',
								'textvalue'     => ( isset( $current['show_in_menu_string'] ) ) ? esc_attr( $current['show_in_menu_string'] ) : '',
								'helptext'      => esc_attr__( 'Top-level page file name to make post type a sub-menu of.', 'custom-post-type-ui' ),
								'helptext_after'=> true,
								'wrap'          => false
							) );
							echo $ui->get_td_end() . $ui->get_tr_end();
							/*
							 * Menu Icon
							 */
							echo $ui->get_text_input( array(
								'namearray'     => 'cpt_custom_post_type',
								'name'          => 'menu_icon',
								'textvalue'     => ( isset( $current['menu_icon'] ) ) ? esc_attr( $current['menu_icon'] ) : '',
								'labeltext'     => __( 'Menu Icon', 'custom-post-type-ui' ),
								'aftertext'     => __( '(Full URL for icon or Dashicon class)', 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'URL to image to be used as menu icon or Dashicon class to use instead.', 'custom-post-type-ui' ),
							) );

							echo $ui->get_tr_start() . $ui->get_th_start() . __( 'Supports', 'custom-post-type-ui' ) . $ui->get_th_end() . $ui->get_td_start() . $ui->get_fieldset_start();
							/*
							 * Supports Title Checkbox
							 */
							echo $ui->get_check_input( array(
								'checkvalue'    => 'title',
								'checked'       => ( !empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'title', $current['supports'] ) ) ? 'true' : 'false',
								'name'          => 'title',
								'namearray'     => 'cpt_supports',
								'textvalue'     => 'title',
								'labeltext'     => __( 'Title' , 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Adds the title meta box when creating content for this custom post type', 'custom-post-type-ui' ),
								'default'       => true,
								'wrap'          => false
							) );

							/*
							 * Supports Editor Checkbox
							 */
							echo $ui->get_check_input( array(
								'checkvalue'    => 'editor',
								'checked'       => ( !empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'editor', $current['supports'] ) ) ? 'true' : 'false',
								'name'          => 'editor',
								'namearray'     => 'cpt_supports',
								'textvalue'     => 'editor',
								'labeltext'     => __( 'Editor' , 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Adds the content editor meta box when creating content for this custom post type', 'custom-post-type-ui' ),
								'default'       => true,
								'wrap'          => false
							) );

							/*
							 * Supports Excerpt Checkbox
							 */
							echo $ui->get_check_input( array(
								'checkvalue'    => 'excerpt',
								'checked'       => ( !empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'excerpt', $current['supports'] ) ) ? 'true' : 'false',
								'name'          => 'excerpt',
								'namearray'     => 'cpt_supports',
								'textvalue'     => 'excerpt',
								'labeltext'     => __( 'Excerpt' , 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Adds the excerpt meta box when creating content for this custom post type', 'custom-post-type-ui' ),
								'default'       => true,
								'wrap'          => false
							) );

							/*
							 * Supports Trackbacks Checkbox
							 */
							echo $ui->get_check_input( array(
								'checkvalue'    => 'trackbacks',
								'checked'       => ( !empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'trackbacks', $current['supports'] ) ) ? 'true' : 'false',
								'name'          => 'trackbacks',
								'namearray'     => 'cpt_supports',
								'textvalue'     => 'trackbacks',
								'labeltext'     => __( 'Trackbacks' , 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Adds the trackbacks meta box when creating content for this custom post type', 'custom-post-type-ui' ),
								'default'       => true,
								'wrap'          => false
							) );

							/*
							 * Supports Custom Fields Checkbox
							 */
							echo $ui->get_check_input( array(
								'checkvalue'    => 'custom-fields',
								'checked'       => ( !empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'custom-fields', $current['supports'] ) ) ? 'true' : 'false',
								'name'          => 'custom-fields',
								'namearray'     => 'cpt_supports',
								'textvalue'     => 'custom-fields',
								'labeltext'     => __( 'Custom Fields' , 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Adds the custom fields meta box when creating content for this custom post type', 'custom-post-type-ui' ),
								'default'       => true,
								'wrap'          => false
							) );

							/*
							 * Supports Comments Checkbox
							 */
							echo $ui->get_check_input( array(
								'checkvalue'    => 'comments',
								'checked'       => ( !empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'comments', $current['supports'] ) ) ? 'true' : 'false',
								'name'          => 'comments',
								'namearray'     => 'cpt_supports',
								'textvalue'     => 'comments',
								'labeltext'     => __( 'Comments' , 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Adds the comments meta box when creating content for this custom post type', 'custom-post-type-ui' ),
								'default'       => true,
								'wrap'          => false
							) );

							/*
							 * Supports Revisions Checkbox
							 */
							echo $ui->get_check_input( array(
								'checkvalue'    => 'revisions',
								'checked'       => ( !empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'revisions', $current['supports'] ) ) ? 'true' : 'false',
								'name'          => 'revisions',
								'namearray'     => 'cpt_supports',
								'textvalue'     => 'revisions',
								'labeltext'     => __( 'Revisions' , 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Adds the revisions meta box when creating content for this custom post type', 'custom-post-type-ui' ),
								'default'       => true,
								'wrap'          => false
							) );

							/*
							 * Supports Post Thumbnail Checkbox
							 */
							echo $ui->get_check_input( array(
								'checkvalue'    => 'thumbnail',
								'checked'       => ( !empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'thumbnail', $current['supports'] ) ) ? 'true' : 'false',
								'name'          => 'thumbnail',
								'namearray'     => 'cpt_supports',
								'textvalue'     => 'thumbnail',
								'labeltext'     => __( 'Featured Image' , 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Adds the featured image meta box when creating content for this custom post type', 'custom-post-type-ui' ),
								'default'       => true,
								'wrap'          => false
							) );

							/*
							 * Supports Author Checkbox
							 */
							echo $ui->get_check_input( array(
								'checkvalue'    => 'author',
								'checked'       => ( !empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'author', $current['supports'] ) ) ? 'true' : 'false',
								'name'          => 'author',
								'namearray'     => 'cpt_supports',
								'textvalue'     => 'author',
								'labeltext'     => __( 'Author' , 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Adds the author meta box when creating content for this custom post type', 'custom-post-type-ui' ),
								'default'       => true,
								'wrap'          => false
							) );

							/*
							 * Supports Page Attributes Checkbox
							 */
							echo $ui->get_check_input( array(
								'checkvalue'    => 'page-attributes',
								'checked'       => ( !empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'page-attributes', $current['supports'] ) ) ? 'true' : 'false',
								'name'          => 'page-attributes',
								'namearray'     => 'cpt_supports',
								'textvalue'     => 'page-attributes',
								'labeltext'     => __( 'Page Attributes' , 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Adds the page attribute meta box when creating content for this custom post type', 'custom-post-type-ui' ),
								'default'       => true,
								'wrap'          => false
							) );

							/*
							 * Supports Post Formats Checkbox
							 */
							echo $ui->get_check_input( array(
								'checkvalue'    => 'post-formats',
								'checked'       => ( !empty( $current['supports'] ) && is_array( $current['supports'] ) && in_array( 'post-formats', $current['supports'] ) ) ? 'true' : 'false',
								'name'          => 'post-formats',
								'namearray'     => 'cpt_supports',
								'textvalue'     => 'post-formats',
								'labeltext'     => __( 'Post Formats' , 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Adds post format support', 'custom-post-type-ui' ),
								'default'       => true,
								'wrap'          => false
							) );

							echo $ui->get_p( __( 'Use the option below to explicitly set "supports" to false.', 'custom-post-type-ui' ) );

							echo $ui->get_check_input( array(
								'checkvalue'    => 'none',
								'checked'       => ( !empty( $current['supports'] ) && ( is_array( $current['supports'] ) && in_array( 'none', $current['supports'] ) ) ) ? 'true' : 'false',
								'name'          => 'none',
								'namearray'     => 'cpt_supports',
								'textvalue'     => 'none',
								'labeltext'     => __( 'None' , 'custom-post-type-ui' ),
								'helptext'      => esc_attr__( 'Remove all support features', 'custom-post-type-ui' ),
								'default'       => false,
								'wrap'          => false
							) );

							echo $ui->get_fieldset_end() . $ui->get_td_end() . $ui->get_tr_end();

							echo $ui->get_tr_start() . $ui->get_th_start() . __( 'Custom "Supports"', 'custom-post-type-ui' );
							echo $ui->get_p( __( 'Use this input to register custom "supports" values, separated by commas.', 'custom-post-type-ui' ) );
							echo $ui->get_th_end() . $ui->get_td_start();
							echo $ui->get_text_input( array(
								'namearray'     => 'cpt_custom_post_type',
								'name'          => 'custom_supports',
								'textvalue'     => ( isset( $current['custom_supports'] ) ) ? esc_attr( $current['custom_supports'] ) : '',
								'helptext'      => esc_attr__( 'Provide custom support slugs here.', 'custom-post-type-ui' ),
								'helptext_after'=> true,
								'wrap'          => false
							) );
							echo $ui->get_td_end() . $ui->get_tr_end();

							echo $ui->get_tr_start() . $ui->get_th_start() . __( 'Built-in Taxonomies', 'custom-post-type-ui' ) . $ui->get_th_end() . $ui->get_td_start() . $ui->get_fieldset_start();

							/**
							 * Filters the arguments for taxonomies to list for post type association.
							 *
							 * @since 1.0.0
							 *
							 * @param array $value Array of default arguments.
							 */
							$args = apply_filters( 'cptui_attach_taxonomies_to_post_type', array( 'public' => true ) );

							# If they don't return an array, fall back to the original default. Don't need to check for empty, because empty array is default for $args param in get_post_types anyway.
							if ( !is_array( $args ) ) {
								$args = array( 'public' => true );
							}

							$add_taxes = get_taxonomies( $args, 'objects' );
							unset( $add_taxes['nav_menu'] ); unset( $add_taxes['post_format'] );
							foreach ( $add_taxes as $add_tax ) {

								$core_label = ( in_array( $add_tax->name, array( 'category', 'post_tag' ) ) ) ? __( '(WP Core)', 'custom-post-type-ui' ) : '';
								/*
								 * Supports Taxonomies Checkbox
								 */
								echo $ui->get_check_input( array(
									'checkvalue'        => $add_tax->name,
									'checked'           => ( !empty( $current['taxonomies'] ) && is_array( $current['taxonomies'] ) && in_array( $add_tax->name, $current['taxonomies'] ) ) ? 'true' : 'false',
									'name'              => $add_tax->name,
									'namearray'         => 'cpt_addon_taxes',
									'textvalue'         => $add_tax->name,
									'labeltext'         => $add_tax->label . ' ' . $core_label,
									'helptext'          => sprintf( esc_attr__( 'Adds %s support', 'custom-post-type-ui' ), $add_tax->label ),
									'wrap'              => false
								) );
							}

							echo $ui->get_fieldset_end() . $ui->get_td_end() . $ui->get_tr_end(); ?>
							</table>
						</div>
				</div>
				</td>
			</tr>
		</table>
	</form>
	</div><!-- End .wrap -->
<?php
}

/**
 * Construct a dropdown of our post types so users can select which to edit.
 *
 * @since 1.0.0
 *
 * @param array $post_types Array of post types that are registered.
 *
 * @return string HTML select dropdown.
 */
function cptui_post_types_dropdown( $post_types = array() ) {

	$ui = new cptui_admin_ui();

	if ( !empty( $post_types ) ) {
		$select = array();
		$select['options'] = array();

		foreach( $post_types as $type ) {
			$text = ( !empty( $type['label'] ) ) ? $type['label'] : $type['name'];
			$select['options'][] = array( 'attr' => $type['name'], 'text' => $text );
		}

		$current = cptui_get_current_post_type();

		$select['selected'] = $current;
		echo $ui->get_select_input( array(
			'namearray'     => 'cptui_selected_post_type',
			'name'          => 'post_type',
			'selections'    => $select,
			'wrap'          => false
		) );
	}
}

/**
 * Get the selected post type from the $_POST global.
 *
 * @since 1.0.0
 *
 * @param bool $post_type_deleted Whether or not a post type was recently deleted.
 *
 * @return bool|string $value False on no result, sanitized post type if set.
 */
function cptui_get_current_post_type( $post_type_deleted = false ) {
	if ( ! empty( $_POST ) ) {
		if ( isset( $_POST['cptui_selected_post_type']['post_type'] ) ) {
			return sanitize_text_field( $_POST['cptui_selected_post_type']['post_type'] );
		}

		if ( $post_type_deleted ) {
			$post_types = get_option( 'cptui_post_types' );
			return key( $post_types );
		}

		if ( isset( $_POST['cpt_custom_post_type']['name'] ) ) {
			return sanitize_text_field( $_POST['cpt_custom_post_type']['name'] );
		}
	} else if ( !empty( $_GET ) && isset( $_GET['cptui_post_type'] ) ) {
		return sanitize_text_field( $_GET['cptui_post_type'] );
	} else {
		$post_types = get_option( 'cptui_post_types' );
		if ( !empty( $post_types ) ) {
			# Will return the first array key
			return key( $post_types );
		}
	}

	return false;
}

/**
 * Delete our custom post type from the array of post types.
 *
 * @since 1.0.0
 *
 * @param $data array $_POST values.
 *
 * @return bool|string False on failure, string on success.
 */
function cptui_delete_post_type( $data = array() ) {

	if ( is_string( $data ) && post_type_exists( $data ) ) {
		$data = array(
			'cpt_custom_post_type' => array(
				'name' => $data
			)
		);
	}

	if ( empty( $data['cpt_custom_post_type']['name'] ) || ! post_type_exists( $data['cpt_custom_post_type']['name'] ) ) {
		return cptui_admin_notices( 'error', '', false, __( 'Please provide a post type to delete', 'custom-post-type-ui' ) );
	}

	/**
	 * Fires before a post type is deleted from our saved options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Array of post type data we are deleting.
	 */
	do_action( 'cptui_before_delete_post_type', $data );

	$post_types = get_option( 'cptui_post_types' );

	if ( array_key_exists( strtolower( $data['cpt_custom_post_type']['name'] ), $post_types ) ) {

		unset( $post_types[ $data['cpt_custom_post_type']['name'] ] );

		$success = update_option( 'cptui_post_types', $post_types );
	}

	/**
	 * Fires after a post type is deleted from our saved options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Array of post type data that was deleted.
	 */
	do_action( 'cptui_after_delete_post_type', $data );

	flush_rewrite_rules();

	if ( isset( $success ) ) {
		return cptui_admin_notices( 'delete', $data['cpt_custom_post_type']['name'], $success );
	}
	return false;
}

/**
 * Add to or update our CPTUI option with new data.
 *
 * @since 1.0.0
 *
 * @param array $data Array of post type data to update.
 *
 * @return bool|string False on failure, string on success.
 */
function cptui_update_post_type( $data = array() ) {

	/**
	 * Fires before a post_type is updated to our saved options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Array of post_type data we are updating.
	 */
	do_action( 'cptui_before_update_post_type', $data );

	# They need to provide a name
	if ( empty( $data['cpt_custom_post_type']['name'] ) ) {
		return cptui_admin_notices( 'error', '', false, __( 'Please provide a post type name', 'custom-post-type-ui' ) );
	}

	if ( !empty( $data['cpt_original'] ) && $data['cpt_original'] != $data['cpt_custom_post_type']['name'] ) {
		if ( !empty( $data['update_post_types'] ) ) {
			cptui_convert_post_type_posts( $data['cpt_original'], $data['cpt_custom_post_type']['name'] );
		}
	}

	# clean up $_POST data
	foreach( $data as $key => $value ) {
		if ( is_string( $value ) ) {
			$data[ $key ] = sanitize_text_field( $value );
		} else {
			array_map( 'sanitize_text_field', $data[ $key ] );
		}
	}

	# Check if they didn't put quotes in the name or rewrite slug.
	if ( false !== strpos( $data['cpt_custom_post_type']['name'], '\'' ) ||
		 false !== strpos( $data['cpt_custom_post_type']['name'], '\"' ) ||
		 false !== strpos( $data['cpt_custom_post_type']['rewrite_slug'], '\'' ) ||
		 false !== strpos( $data['cpt_custom_post_type']['rewrite_slug'], '\"' ) ) {

		return cptui_admin_notices( 'error', '', false, __( 'Please do not use quotes in post type names or rewrite slugs', 'custom-post-type-ui' ) );
	}

	$post_types = get_option( 'cptui_post_types', array() );

	# Check if we already have a post type of that name.
	if ( 'new' == $data['cpt_type_status'] && ( array_key_exists( strtolower( $data['cpt_custom_post_type']['name'] ), $post_types ) || in_array( $data['cpt_custom_post_type']['name'], cptui_reserved_post_types() ) ) ) {
		return cptui_admin_notices( 'error', '', false, sprintf( __( 'Please choose a different post type name. %s is already registered.', 'custom-post-type-ui' ), $data['cpt_custom_post_type']['name'] ) );
	}

	if ( empty( $data['cpt_addon_taxes'] ) || !is_array( $data['cpt_addon_taxes'] ) ) {
		$data['cpt_addon_taxes'] = array();
	}

	if ( empty( $data['cpt_supports'] ) || !is_array( $data['cpt_supports'] ) ) {
		$data['cpt_supports'] = array();
	}

	foreach( $data['cpt_labels'] as $key => $label ) {
		if ( empty( $label ) ) {
			unset( $data['cpt_labels'][ $key ] );
		}

		$label = str_replace( '"', '', htmlspecialchars_decode( $label ) );
		$label = htmlspecialchars( $label, ENT_QUOTES );
		$label = trim( $label );
		$data['cpt_labels'][ $key ] = stripslashes_deep( $label );
	}

	if ( empty( $data['cpt_custom_post_type']['menu_icon'] ) ) {
		$data['cpt_custom_post_type']['menu_icon'] = null;
	}

	$label = str_replace( '"', '', htmlspecialchars_decode( $data['cpt_custom_post_type']['label'] ) );
	$label = htmlspecialchars( stripslashes( $label ), ENT_QUOTES );

	$singular_label = str_replace( '"', '', htmlspecialchars_decode( $data['cpt_custom_post_type']['singular_label'] ) );
	$singular_label = htmlspecialchars( stripslashes( $singular_label ), ENT_QUOTES );

	$name = trim( $data['cpt_custom_post_type']['name'] );
	$description = stripslashes_deep( $data['cpt_custom_post_type']['description'] );
	$rest_base = trim( $data['cpt_custom_post_type']['rest_base'] );
	$has_archive_string = trim( $data['cpt_custom_post_type']['has_archive_string'] );
	$capability_type = trim( $data['cpt_custom_post_type']['capability_type'] );
	$rewrite_slug = trim( $data['cpt_custom_post_type']['rewrite_slug'] );
	$query_var_slug = trim( $data['cpt_custom_post_type']['query_var_slug'] );
	$menu_position = trim( $data['cpt_custom_post_type']['menu_position'] );
	$show_in_menu_string = trim( $data['cpt_custom_post_type']['show_in_menu_string'] );
	$menu_icon = trim( $data['cpt_custom_post_type']['menu_icon'] );
	$custom_supports = trim( $data['cpt_custom_post_type']['custom_supports'] );

	$post_types[ $data['cpt_custom_post_type']['name'] ] = array(
		'name'                  => $name,
		'label'                 => $label,
		'singular_label'        => $singular_label,
		'description'           => $description,
		'public'                => disp_boolean( $data['cpt_custom_post_type']['public'] ),
		'show_ui'               => disp_boolean( $data['cpt_custom_post_type']['show_ui'] ),
		'show_in_nav_menus'     => disp_boolean( $data['cpt_custom_post_type']['show_in_nav_menus'] ),
		'show_in_rest'          => disp_boolean( $data['cpt_custom_post_type']['show_in_rest'] ),
		'rest_base'             => $rest_base,
		'has_archive'           => disp_boolean( $data['cpt_custom_post_type']['has_archive'] ),
		'has_archive_string'    => $has_archive_string,
		'exclude_from_search'   => disp_boolean( $data['cpt_custom_post_type']['exclude_from_search'] ),
		'capability_type'       => $capability_type,
		'hierarchical'          => disp_boolean( $data['cpt_custom_post_type']['hierarchical'] ),
		'rewrite'               => disp_boolean( $data['cpt_custom_post_type']['rewrite'] ),
		'rewrite_slug'          => $rewrite_slug,
		'rewrite_withfront'     => disp_boolean( $data['cpt_custom_post_type']['rewrite_withfront'] ),
		'query_var'             => disp_boolean( $data['cpt_custom_post_type']['query_var'] ),
		'query_var_slug'        => $query_var_slug,
		'menu_position'         => $menu_position,
		'show_in_menu'          => disp_boolean( $data['cpt_custom_post_type']['show_in_menu'] ),
		'show_in_menu_string'   => $show_in_menu_string,
		'menu_icon'             => $menu_icon,
		'supports'              => $data['cpt_supports'],
		'taxonomies'            => $data['cpt_addon_taxes'],
		'labels'                => $data['cpt_labels'],
		'custom_supports'       => $custom_supports
	);

	$success = update_option( 'cptui_post_types', $post_types );

	/**
	 * Fires after a post type is updated to our saved options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Array of post type data that was updated.
	 */
	do_action( 'cptui_after_update_post_type', $data );

	flush_rewrite_rules();

	if ( isset( $success ) ) {
		if ( 'new' == $data['cpt_type_status'] ) {
			return cptui_admin_notices( 'add', $data['cpt_custom_post_type']['name'], $success );
		}
	}
	return cptui_admin_notices( 'update', $data['cpt_custom_post_type']['name'], true );
}

/**
 * Return an array of names that users should not or can not use for post type names.
 *
 * @since 1.0.0
 *
 * @return array $value Array of names that are recommended against.
 */
function cptui_reserved_post_types() {

	$reserved = array(
		'post',
		'page',
		'attachment',
		'revision',
		'nav_menu_item',
		'action',
		'order',
		'theme'
	);

	/**
	 * Filters the list of reserved post types to check against.
	 *
	 * 3rd party plugin authors could use this to prevent duplicate post types.
	 *
	 * @since 1.0.0
	 *
	 * @param array $value Array of post type slugs to forbid.
	 */
	$custom_reserved = apply_filters( 'cptui_reserved_post_types', array() );

	if ( is_string( $custom_reserved ) && ! empty( $custom_reserved ) ) {
		$reserved[] = $custom_reserved;
	} else if ( is_array( $custom_reserved ) && ! empty( $custom_reserved ) ) {
		foreach ( $custom_reserved as $slug ) {
			$reserved[] = $slug;
		}
	}

	return $reserved;
}

/**
 * Converts post type between original and newly renamed.
 *
 * @since 1.1.0
 *
 * @param string $original_slug Original post type slug.
 * @param string $new_slug      New post type slug.
 */
function cptui_convert_post_type_posts( $original_slug = '', $new_slug = '' ) {
	$args = array(
		'posts_per_page' => -1,
		'post_type'      => $original_slug
	);
	$convert = new WP_Query( $args );

	if ( $convert->have_posts() ) : while ( $convert->have_posts() ) : $convert->the_post();
		set_post_type( get_the_ID(), $new_slug );
	endwhile; endif;

	cptui_delete_post_type( $original_slug );
}
