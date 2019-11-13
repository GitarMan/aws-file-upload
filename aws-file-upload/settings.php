<?php
add_action( 'admin_menu', 'afu_add_admin_menu' );
add_action( 'admin_init', 'afu_settings_init' );


function afu_add_admin_menu(  ) { 

	add_submenu_page( 'options-general.php', 
						'AWS File Upload', 
						'AWS File Upload', 
						'manage_options', 
						'aws_file_upload', 
						'afu_options_page' );
}


function afu_settings_init(  ) { 

	register_setting( 'pluginPage', 'afu_settings' );

	add_settings_section(
		'afu_pluginPage_section_0', 
		__( 'Amazon Web Services Credentials', 'aws-file-upload' ), 
		'afu_settings_section_callback_0', 
		'pluginPage'
	);

	add_settings_field( 
		'afu_text_field_0_aws_key', 
		__( 'AWS Public Key', 'aws-file-upload' ), 
		'afu_text_field_0_render', 
		'pluginPage', 
		'afu_pluginPage_section_0' 
	);

	add_settings_field( 
		'afu_text_field_1_aws_secret', 
		__( 'AWS Secret', 'aws-file-upload' ), 
		'afu_text_field_1_render', 
		'pluginPage', 
		'afu_pluginPage_section_0' 
	);


	add_settings_section(
		'afu_pluginPage_section_1', 
		__( 'Notifications', 'aws-file-upload' ), 
		'afu_settings_section_callback_1', 
		'pluginPage'
	);

	add_settings_field( 
		'afu_text_field_2_email_address', 
		__( 'Email Address', 'aws-file-upload' ), 
		'afu_text_field_2_render', 
		'pluginPage', 
		'afu_pluginPage_section_1' 
	);
}


function afu_text_field_0_render(  ) { 

	$options = get_option( 'afu_settings' );
	?>
	<input type='text' name='afu_settings[afu_text_field_0_aws_key]' 
		value='<?php echo $options['afu_text_field_0_aws_key']; ?>'>
	<?php

}


function afu_text_field_1_render(  ) { 

	$options = get_option( 'afu_settings' );
	?>
	<input type='password' name='afu_settings[afu_text_field_1_aws_secret]' 
		value='<?php echo $options['afu_text_field_1_aws_secret']; ?>'>
	<?php

}

function afu_text_field_2_render(  ) { 

	$options = get_option( 'afu_settings' );
	?>
	<input type='text' name='afu_settings[afu_text_field_2_email_address]' 
		value='<?php echo $options['afu_text_field_2_email_address']; ?>'>
	<?php

}

function afu_settings_section_callback_0(  ) { 

	echo __( 'Please include your credentials for the AWS S3 storage bucket where you would like to upload files to.', 'aws-file-upload' );

}

function afu_settings_section_callback_1(  ) { 

	echo __( 'Enter the email address you would like to be notified at when a file is uploaded. You will receieve a temporary link that will allow you to download the file. The link will expire in 7 days, after which the file will be deleted.', 'aws-file-upload' );

}

function afu_options_page(  ) { 

		?>
		<form action='options.php' method='post'>

			<h2>AWS File Upload</h2>

			<?php
			settings_fields( 'pluginPage' );
			do_settings_sections( 'pluginPage' );
			submit_button();
			?>

		</form>
		<?php
}

