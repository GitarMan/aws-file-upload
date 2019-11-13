<?php
/**
 * Handle file upload
 */
require_once(rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/wp-load.php');

require 'vendor/autoload.php';
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;
use Aws\Ses\SesClient;


if ( empty( $_FILES ) ) {
	http_response_code(400);
	die("400 Error: Bad Request");
}	

$temp_file = $_FILES['file']['tmp_name'];
if ( filesize($temp_file) > 20*1024*1024 ) {
	http_response_code(413);
	die("413 Error: File size is too big");
}	

$timestamp = date( 'Y-m-d' );
$original_filename = $_FILES['file']['name'];
$target_filename = $timestamp . '_' . $original_filename;
$settings = get_option( 'afu_settings' );

try {
	$s3 = new Aws\S3\S3Client([
		'region'  => 'us-west-2',
		'version' => 'latest',
		'credentials' => [
			'key'    => $settings['afu_text_field_0_aws_key'],
			'secret' => $settings['afu_text_field_1_aws_secret']
		]
	]);

	$result = $s3->putObject([
		'Bucket' => 'awesurance-dev',
		'Key'    => $target_filename,
		'SourceFile' => $temp_file,
	]);

	$result_status = $result['@metadata']['statusCode']; 
	if( $result_status === 200 ) {
		http_response_code(200);
		echo $timestamp . " - File Uploaded: " . $original_filename;


		$presigned_url = afu_return_presigned_url( 
			$settings['afu_text_field_0_aws_key'], 
			$settings['afu_text_field_1_aws_secret'], 
			'awesurance-dev',
			$target_filename
		);

		afu_send_email_notification(
			$settings['afu_text_field_0_aws_key'], 
			$settings['afu_text_field_1_aws_secret'], 
			$settings['afu_text_field_2_email_address'],
			$presigned_url,
			$original_filename 
		);
	}
} catch (S3Exception $e) {
	http_response_code(502);
	echo "502 Error: Could not connect to storage server.";
} catch (AwsException $e) {
	http_response_code(502);
	echo "502 Error: Could not connect to storage server.";
}


// Get pre-signed URL to S3 object and return it

function afu_return_presigned_url( $aws_key, $aws_secret, $bucket, $object_key ) {

	$s3 = new Aws\S3\S3Client([
		'region'  => 'us-west-2',
		'version' => 'latest',
		'credentials' => [
		    'key'    => $aws_key,
		    'secret' => $aws_secret,
		]
	]);
	
	$cmd = $s3->getCommand('GetObject', [
		'Bucket' => $bucket,
		'Key' => $object_key
	]);
	
	try {
		$request = $s3->createPresignedRequest($cmd, '+7 days');
		$presigned_url = (string)$request->getUri();

		return $presigned_url;

	} catch (S3Exception $e) {
	    error_log( 'Presigned URL Error: ' . $e->getMessage() . PHP_EOL );
	}
	
	return;	
}	



// Send email notification using Amazon SES (Simple Email Service)

function afu_send_email_notification( $aws_key, $aws_secret, $email_address, $url, $original_filename ) {

	$SesClient = new SesClient([
		'version' => 'latest',
		'region'  => 'us-west-2',
		'credentials' => [
			'key'    => $aws_key,
			'secret' => $aws_secret,
		],

	]);

	$sender_email = 'Upload Notification <noreply@YOURSITE.com>';

	$recipient_emails = [$email_address];

	$subject = 'File Upload: ' . $original_filename;
	$plaintext_body = "Somebody just uploaded a file!\r\n";
	$plaintext_body .= "You can download the file using the following URL:\r\n";
	$plaintext_body .= $url;
	$plaintext_body .= "\r\n This link will be active for 7 days, after which the file will be deleted.";

	$html_body = '<h1>File Uploaded</h1>'.
		'<p>Somebody just uploaded a file to the site</p>'.
		'<p>You can download the file using the following URL:</p>'.
		'<a target="_blank" href="' . $url . '">' . $original_filename . '</a>'.
		'<p>This link will be active for 7 days, after which the file will be deleted.</p>';
	$char_set = 'UTF-8';

    foreach ( $recipient_emails as $to_address ) {

        try {
            $result = $SesClient->sendEmail([
                'Destination' => [
                    'ToAddresses' => [$to_address],
                ],
                'ReplyToAddresses' => [$sender_email],
                'Source' => $sender_email,
                'Message' => [
                  'Body' => [
                      'Html' => [
                          'Charset' => $char_set,
                          'Data' => $html_body,
                      ],
                      'Text' => [
                          'Charset' => $char_set,
                          'Data' => $plaintext_body,
                      ],
                  ],
                  'Subject' => [
                      'Charset' => $char_set,
                      'Data' => $subject,
                  ],
                ],
            ]);
            $messageId = $result['MessageId'];
            echo "\nEmail sent! Message ID: $messageId \n";
        } catch (AwsException $e) {
            echo "\nThe email was not sent. Error message: \n";
            echo $e->getAwsErrorMessage();
        }
    }
}
