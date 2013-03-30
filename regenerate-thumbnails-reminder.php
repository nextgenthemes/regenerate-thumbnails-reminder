<?php /*

*******************************************************************************

Plugin Name:  Regenerate Thumbnails reminder
Plugin URI:   http://nextgenthemes.com/plugins/regenerate-thumbnails-reminder/
Description:  Checks if your image sizes has changed or there was a new one added, if so it reminds you to go regenerate them. Redirects you to the "Regenerate Thumbnails" plugin's tool page, but you can use whatever plugin you prefer to regenerate thumbnails (images).
Version:      1.1
Author:       Nicolas Jonas
Author URI:   http://nextgenthemes.com
Licence:      GPLv3 or later

*******************************************************************************

Copyright (c) 2013 Nicolas Jonas

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

Dieses Programm ist Freie Software: Sie können es unter den Bedingungen
der GNU General Public License, wie von der Free Software Foundation,
Version 3 der Lizenz oder (nach Ihrer Option) jeder späteren
veröffentlichten Version, weiterverbreiten und/oder modifizieren.

Dieses Programm wird in der Hoffnung, dass es nützlich sein wird, aber
OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
Siehe die GNU General Public License für weitere Details.

Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.

_  _ ____ _  _ ___ ____ ____ _  _ ___ _  _ ____ _  _ ____ ____  ____ ____ _  _ 
|\ | |___  \/   |  | __ |___ |\ |  |  |__| |___ |\/| |___ [__   |    |  | |\/| 
| \| |___ _/\_  |  |__] |___ | \|  |  |  | |___ |  | |___ ___] .|___ |__| |  | 

*******************************************************************************

*/

if ( ! defined( 'ABSPATH' ) )
	die( "Can't load this file directly" );

class RegenerateThumbnailsReminder {

	function __construct() {
		add_action( 'admin_init', array($this, 'action_admin_init') );
		add_action( 'admin_post_reg_thumb_reminder_apa', array($this, 'admin_post_callback') );
	}

	function action_admin_init() {
		// only if we're in the admin panel, and the current user has permission
		// to edit options
		if ( ! current_user_can( 'manage_options' ) )
			return;
		
		$all_image_sizes = $this->get_all_image_sizes();

		$options = get_option( 'regenerate_thumbs_reminder_options', array() );

		if( ! isset ( $options['all_image_sizes'] ) ) {
			$options['all_image_sizes'] = $all_image_sizes;
			update_option( 'regenerate_thumbs_reminder_options', $options, '', 'yes' );
			return;
		}

		if( $all_image_sizes == $options['all_image_sizes'] ) {
			return;
		} else {
			add_action( 'admin_notices', array( $this, 'action_admin_notice') );
		}
	}

	function action_admin_notice() {

		$regen_url = admin_url( 'admin-post.php?action=reg_thumb_reminder_apa' );
		$dismiss_url = admin_url( 'admin-post.php?action=reg_thumb_reminder_apa&dismiss=true' );

		?>
		<div class="updated">
			<p>
				<?php _e('Your image sizes have been changed, you might want to '); ?>
				<?php echo "<a href='$regen_url'>" . __('regenerate them now.') . '</a> | '; ?>
				<?php echo "<a href='$dismiss_url'>" . __('Dismiss.') . '</a> '; ?>
				<span class="description"><?php _e('(in case a image size was removed ignore this and click Dismiss) '); ?></span>
			</p>
		</div>
		<?php
	}

	function get_all_image_sizes() {
		$all_image_sizes = array();

		foreach (get_intermediate_image_sizes() as $s) {

			global $_wp_additional_image_sizes;

			if ( isset($_wp_additional_image_sizes[$s]) ) {
				$all_image_sizes[$s]['width'] = (int) $_wp_additional_image_sizes[$s]['width'];
				$all_image_sizes[$s]['height'] = (int) $_wp_additional_image_sizes[$s]['height'];
			} else {
				$all_image_sizes[$s]['width'] = (int) get_option($s.'_size_w');
				$all_image_sizes[$s]['height'] = (int) get_option($s.'_size_h');
			}
		}

		return $all_image_sizes;
	}

	function admin_post_callback() {
		if ( ! current_user_can( 'manage_options' ) )
			return;

		$options['all_image_sizes'] = $this->get_all_image_sizes();

		update_option( 'regenerate_thumbs_reminder_options', $options );

		// if just dismiss was clicked
		if ( isset( $_GET["dismiss"] ) ) {

			if ( wp_get_referer() )
				wp_redirect( wp_get_referer() );
			else
				wp_redirect( admin_url() );
			exit;
		}

		wp_redirect( admin_url( 'tools.php?page=regenerate-thumbnails' ) );
		exit;
	}

}

$rtr = new RegenerateThumbnailsReminder();
