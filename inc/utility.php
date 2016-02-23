<?php
/**
 * Custom Post Type UI Utility Code.
 *
 * @package CPTUI
 * @subpackage Utility
 * @author WebDevStudios
 * @since 1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Edit links that appear on installed plugins list page, for our plugin.
 *
 * @since 1.0.0
 *
 * @param array $links Array of links to display below our plugin listing.
 * @return array Amended array of links.
 */
function cptui_edit_plugin_list_links( $links ) {
	// We shouldn't encourage editing our plugin directly.
	unset( $links['edit'] );

	// Add our custom links to the returned array value.
	return array_merge( array(
		'<a href="' . admin_url( 'admin.php?page=cptui_main_menu' ) . '">' . __( 'About', 'custom-post-type-ui' ) . '</a>',
		'<a href="' . admin_url( 'admin.php?page=cptui_support' ) . '">' . __( 'Help', 'custom-post-type-ui' ) . '</a>'
	), $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/custom-post-type-ui.php', 'cptui_edit_plugin_list_links' );

/**
 * Returns SVG icon for custom menu icon
 *
 * @since 1.2.0
 *
 * @return string
 */
function cptui_menu_icon() {
	return 'data:image/svg;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAABC9pVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDE0IDc5LjE1Njc5NywgMjAxNC8wOC8yMC0wOTo1MzowMiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wUmlnaHRzPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvcmlnaHRzLyIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIgeG1wUmlnaHRzOk1hcmtlZD0iVHJ1ZSIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo5NkE3NTc5MUJCOTIxMUU0QUVENDlFMUYwOEMyRDgwQyIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo5NkE3NTc5MEJCOTIxMUU0QUVENDlFMUYwOEMyRDgwQyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ0MgMjAxNCAoV2luZG93cykiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmRpZDo5NjMzOTU2ODgyMjhFMDExOTg5Q0MwQTFBRDAyQjVDMiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo5NjMzOTU2ODgyMjhFMDExOTg5Q0MwQTFBRDAyQjVDMiIvPiA8ZGM6cmlnaHRzPiA8cmRmOkFsdD4gPHJkZjpsaSB4bWw6bGFuZz0ieC1kZWZhdWx0Ij5DcmVhdGl2ZSBDb21tb25zIEF0dHJpYnV0aW9uIE5vbi1Db21tZXJjaWFsIE5vIERlcml2YXRpdmVzPC9yZGY6bGk+IDwvcmRmOkFsdD4gPC9kYzpyaWdodHM+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+hXhu9wAAAjdJREFUeNrcWYFtwjAQBNQB0g3oBukEDRPUnYCwQRmhE8AGhgnKBh0h2YBuQDZIbclI6GXH7/c7cfrSCxEcuHv+7x3/su/7xZxttZi5/XsChfJP5Y3ynskFKwNdAw4Xym89r9UDv0dy1wd1z2/s4F0ERGbgC+WN8iuGQJFZ2tzB303ANbCIa1O4XLZTfiLeq3H8KC8frr3BRU/g/dbzpRflZ+Wd8ta8pjAbeG2VT4VGL0JE2kArXClUeCJ/GqEvuSL/aGtKJz5nAv5oUtdKYCifuwzAa+Bf8OIS7EZdW9PnCQoWBnADox+SQhjwtQcEFby2vQ18iAr5lEOadboJlkxqczcZspWgEJBgLYYEFnwDZZObgHSsHyKBBY/6N2MISEL0sODRjZNK4IAEocGuzT1lAHiJ7dxYGV0CtZEJe0JrJBMl2xQCN+YdK0rvHSYoD/WXhNHfB4DXmfBNrQGZ4KlNBuxYaxcwThUKsYYCPpZAiCT69H5NAR9LgIuEoILnIBBL4hCQOlajyGjMrhLq/WvIGVzKs9FQ/dbrP3I73A0hoY9bfnM8ncaQOHI2Q64awNZEaN6PVgOYf4It78cacEASG668HyOFUlhUClUTg69iU6hYZGpYAtuJcb5jZ2Q5nE5DL4eGLrCIG89+Zqz5QPUQ+aGhSwsJ6JHqYUZj4j0koJlecy5a0GdeVpaLu5lEX+PsVo48380A/MWmQqkn9RzPz2LoZM7WwGrTB8oJI94a9TtB5fsTYABOp6Z0XZr87gAAAABJRU5ErkJggg==';
}

/**
 * Return boolean status depending on passed in value.
 *
 * @since 0.5.0
 *
 * @param mixed $booText text to compare to typical boolean values.
 * @return bool Which bool value the passed in value was.
 */
function get_disp_boolean( $booText ) {
	$booText = (string) $booText;
	if ( empty( $booText ) || $booText == '0' || $booText == 'false' ) {
		return false;
	}

	return true;
}

/**
 * Return string versions of boolean values.
 *
 * @since 0.1.0
 *
 * @param string $booText String boolean value.
 * @return string standardized boolean text.
 */
function disp_boolean( $booText ) {
	$booText = (string) $booText;
	if ( empty( $booText ) || $booText == '0' || $booText == 'false' ) {
		return 'false';
	}

	return 'true';
}

/**
 * Display footer links and plugin credits.
 *
 * @since 0.3.0
 *
 * @param string $original Original footer content.
 * @return string $value HTML for footer.
 */
function cptui_footer( $original = '' ) {

	$screen = get_current_screen();

	if ( ! is_object( $screen ) || 'cptui_main_menu' != $screen->parent_base ) {
		return $original;
	}

	return sprintf(
		       __( '%s version %s by %s', 'custom-post-type-ui' ),
		       sprintf(
			       '<a target="_blank" href="http://wordpress.org/support/plugin/custom-post-type-ui">%s</a>',
			       __( 'Custom Post Type UI', 'custom-post-type-ui' )
		       ),
		       CPTUI_VERSION,
		       '<a href="http://webdevstudios.com" target="_blank">WebDevStudios</a>'
	       ) .
	       ' - ' .
	       sprintf(
		       '<a href="https://github.com/WebDevStudios/custom-post-type-ui/issues" target="_blank">%s</a>',
		       __( 'Please Report Bugs', 'custom-post-type-ui' )
	       ) .
	       ' ' .
	       __( 'Follow on Twitter:', 'custom-post-type-ui' ) .
	       sprintf(
		       ' %s &middot; %s &middot; %s',
		       '<a href="http://twitter.com/tw2113" target="_blank">Michael</a>',
		       '<a href="http://twitter.com/williamsba" target="_blank">Brad</a>',
		       '<a href="http://twitter.com/webdevstudios" target="_blank">WebDevStudios</a>'
	       );
}
add_filter( 'admin_footer_text', 'cptui_footer' );

/**
 * Output starter notes for Add New Post Type and Add New Taxonomy screens.
 *
 * @since 1.3.0
 *
 * @param string $tab         Current tab being displayed.
 * @param string $object_type Whether a post type or taxonomy.
 * @return string
 */
function cptui_starter_notes( $tab = '', $object_type = '' ) {
	$output = '';
	if ( 'new' == $tab ) {

		if ( ! empty( $object_type ) ) {
			$object_type = '_' . $object_type;
		}

		/**
		 * Filters the starter notes to output to screen.
		 *
		 * This is a dynamic filter that's dependent on the object type passed in.
		 *
		 * Only add the text meant for the list item. We will handle the html. Potential
		 * filters incude `cptui_starter_notes`, `cptui_starter_notes_post_types` and
		 * `cptui_starter_notes_taxonomies`.
		 *
		 * @since 1.3.0
		 *
		 * @param array $value Array of notes to iterate over.
		 */
		$notes = apply_filters( "cptui_starter_notes{$object_type}", array() );
		if ( ! empty( $notes ) && is_array( $notes ) ) {
			$output .= '<h2>' . __( 'Starter Notes', 'custom-post-type-ui' ) . '</h2>';
			$output .= '<div><ol>';
			foreach( $notes as $note ) {
				$output .= '<li>' . $note . '</li>';
			}
			$output .= '</ol></div>';
		}
	}
	return $output;
}

/**
 * Add our starter notes for post types.
 *
 * @param $notes Array of notes to add.
 * @return array Array of notes added.
 */
function cptui_post_type_starter_notes( $notes = array() ) {
	$notes[] = sprintf( __( 'Post Type names should have %smax 20 characters%s, and only contain alphanumeric, lowercase characters, underscores in place of spaces and letters that do not have accents. Reserved names: post, page, attachment, revision, nav_menu_item.', 'custom-post-type-ui' ), '<strong class="wp-ui-highlight">', '</strong>' );

	$notes[] = sprintf( __( 'If you are unfamiliar with the advanced post type settings, just fill in the %sPost Type Name%s and %sLabel%s fields. Remaining settings will use default values. Labels, if left blank, will be automatically created based on the post type name. Hover over the question mark for more details.', 'custom-post-type-ui' ), '<strong class="wp-ui-highlight">', '</strong>', '<strong class="wp-ui-highlight">', '</strong>' );

	$notes[] = sprintf( __( 'Deleting custom post types will %sNOT%s delete any content into the database or added to those post types. You can easily recreate your post types and the content will still exist.', 'custom-post-type-ui' ), '<strong class="wp-ui-highlight">', '</strong>' );

	return $notes;
}
add_filter( 'cptui_starter_notes_post_types', 'cptui_post_type_starter_notes' );

/**
 * Add our starter notes for taxonomies.
 *
 * @param $notes Array of notes to add.
 * @return array Array of notes added.
 */
function cptui_taxonomy_starter_notes( $notes = array() ) {
	$notes[] = sprintf( __( 'Taxonomy names should have %smax 32 characters%s, and only contain alphanumeric, lowercase, characters, underscores in place of spaces, and letters that do not have accents.', 'custom-post-type-ui' ), '
<strong class="wp-ui-highlight">', '</strong>' );

	$notes[] = sprintf( __( 'If you are unfamiliar with the advanced taxonomy settings, just fill in the %sTaxonomy Name%s and choose an %sAttach to Post Type%s option. Remaining settings will use default values. Labels, if left blank, will be automatically created based on the taxonomy name. Hover over the question marks for more details.', 'custom-post-type-ui' ), '<strong class="wp-ui-highlight">', '</strong>', '<strong class="wp-ui-highlight">', '</strong>' );

	$notes[] = sprintf( __( 'Deleting custom taxonomies do %sNOT%s delete terms added to those taxonomies. You can recreate your taxonomies and the terms will return. Changing the name, after adding terms to the taxonomy, will not update the terms in the database.', 'custom-post-type-ui' ), '<strong class="wp-ui-highlight">', '</strong>' );

	return $notes;
}

add_filter( 'cptui_starter_notes_taxonomies', 'cptui_taxonomy_starter_notes' );
