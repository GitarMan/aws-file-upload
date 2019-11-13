<?php
/**
 * Plugin Name:       AWS File Upload
 * Description:       Upload files to AWS bucket
 * Version:           0.1.0
 * Author:            Ron Holt
 * Author URI:        http://ronholt.info/
 * Text Domain:       aws-file-upload
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include_once('settings.php');

// Register Scripts
function afu_register_scripts() {
	wp_register_script('afu_dropzone_js', 
		plugin_dir_url( __FILE__ ).'js/dropzone/min/dropzone.min.js' );

	wp_register_style( 'afu_dropzone_css', 
		plugin_dir_url( __FILE__ ).'js/dropzone/min/dropzone.min.css' );
}
add_action('wp_enqueue_scripts', 'afu_register_scripts' );



// [aws_file_upload]
function afu_shortcode( $atts ) {

	// If scripts are not already enqueued, do so now
	if ( !wp_script_is( 'afu_dropzone_js', 'enqueued' ) ) {
		wp_enqueue_script( 'afu_dropzone_js' );
	}
	if ( !wp_script_is( 'afu_dropzone_css', 'enqueued' ) ) {
		wp_enqueue_style( 'afu_dropzone_css' );
	}
?>
<style>
	.dropzone .dz-preview.dz-success:hover .dz-success-mark { opacity:1; }
	.dropzone .dz-preview .dz-image { border:2px solid rgba(0,0,0,0.3); }
</style>
<script type="text/javascript">
	document.addEventListener("DOMContentLoaded", function() {
		Dropzone.options.myDropzone = {
			maxFilesize: 20,
			maxFiles: 10,
			thumbnailMethod: 'contain',
			dictMaxFilesExceeded: 'You can only upload 10 files at once',

			init: function() {
				this.on('success', function(file, response) {
					console.log( response );	
				});
			}
		};
	});

</script>

<form action="<?php echo plugin_dir_url( __FILE__ ); ?>upload.php"
      class="dropzone"
      id="my-dropzone"></form>
<?php
}
add_shortcode( 'aws_file_upload', 'afu_shortcode' );


