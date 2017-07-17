<?php

if ( !defined('ABSPATH') ) die('This file should not be accessed directly.');

function shortcode_rs_dealer_form( $atts, $content = '' ) {
	
	$dealer_added = isset($_REQUEST['rs_dealer_added']);
	
	ob_start();
	?>
	<div class="rs-dealer-shortcode <?php echo $dealer_added ? 'rs-expanded' : 'rs-collapsed'; ?>">
		<div class="rs-inner">
			
			<?php
			if ( !$dealer_added ) {
				$nonce = wp_create_nonce('rs_dealer_add');
				
				$name = isset($_REQUEST['rst']['name']) ? stripslashes($_REQUEST['rst']['name']) : false;
				$email = isset($_REQUEST['rst']['email']) ? stripslashes($_REQUEST['rst']['email']) : false;
				$location = isset($_REQUEST['rst']['location']) ? stripslashes($_REQUEST['rst']['location']) : false;
				$content = isset($_REQUEST['rst']['content']) ? stripslashes($_REQUEST['rst']['content']) : false;
				?>
				<div class="rs-form-closed">
					<div class="rs-dealer-button">
						<a href="#" class="rs-add-dealer">Submit Your Dealer</a>
					</div>
				</div>
				
				<div class="rs-form-opened" style="display: none;">
					<div class="rs-dealer-button">
						<a href="#" class="rs-close-dealer-form">Close</a>
					</div>
					
					<form action="" method="POST" enctype="multipart/form-data">
						<div class="rs-field rs-field-name">
							<label for="rs-dealer-name" class="screen-reader-text">Name:</label>
							<input type="text" name="rst[name]" id="rs-dealer-name" placeholder="Name" value="<?php echo esc_attr($name); ?>" required>
						</div>
						<div class="rs-field rs-field-email">
							<label for="rs-dealer-email" class="screen-reader-text">Email:</label>
							<input type="email" name="rst[email]" id="rs-dealer-email" placeholder="Email" value="<?php echo esc_attr($email); ?>" required>
						</div>
						<div class="rs-field rs-field-location">
							<label for="rs-dealer-location" class="screen-reader-text">Organization / Location:</label>
							<input type="text" name="rst[location]" id="rs-dealer-location" placeholder="Organization / Location" value="<?php echo esc_attr($location); ?>" required>
						</div>
						<div class="rs-field rs-field-content">
							<label for="rs-dealer-content" class="screen-reader-text">Your Dealer:</label>
							<textarea name="rst[content]" id="rs-dealer-content" placeholder="Your Dealer" required><?php echo esc_attr($content); ?></textarea>
						</div>
						<div class="rs-image">
							<label for="rs-dealer-image" class="rs-label">Upload Photo:</label>
							<input type="file" name="rst-image" id="rs-dealer-image" required>
						</div>
						<div class="rs-submit">
							<input type="hidden" name="rst[nonce]" value="<?php echo esc_attr($nonce); ?>">
							<input type="submit" value="Submit">
						</div>
					</form>
				</div>
				<?php
				
			}else{
				
				?>
				<div class="rs-form-opened">
					<div class="rs-dealer-button">
						<a href="#" class="rs-close-dealer-all">Close</a>
					</div>
					
					<div class="rs-dealer-added-text">
						<p><strong>Dealer Added</strong></p>
						<p>Thank you for submitting a dealer. Please note that we will review your dealer before publishing it to our website.</p>
					</div>
				</div>
				<?php
			}
			?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'rs_dealer_form', 'shortcode_rs_dealer_form' );


function rst_add_dealer_from_shortcode() {
	if ( !isset($_POST['rst']) ) return;
	
	$post = stripslashes_deep($_POST['rst']);
	if ( empty($post['nonce']) || !wp_verify_nonce($post['nonce'], 'rs_dealer_add') ) return;
	
	$errors = array();
	
	if ( empty($post['name']) ) $errors[] = 'Name is required';
	
	if ( empty($post['email']) ) $errors[] = 'Email is required';
	else if ( !is_email($post['email']) ) $errors[] = 'Email appears to be invalid';
	
	if ( empty($post['location']) ) $errors[] = 'Organization / Location is required';
	if ( empty($post['content']) ) $errors[] = 'Dealer message is required';
	
	if ( empty($_FILES['rst-image']) ) $errors[] = 'Image is required';
	
	// Die for errors
	if ( !empty($errors) ) {
		wp_die('<p><strong>Failed to add dealer:</strong></p><ul><li>'. implode('</li><li>', $errors) . '</li></ul>');
		exit;
	}
	
	// Create the dealer
	$args = array(
		'post_type' => 'rs_dealer',
	    'post_status' => 'pending',
	    'post_title' => esc_html($post['name']),
	    'post_content' => esc_html($post['content']),
	);
	
	$post_id = wp_insert_post( $args );
	
	if ( !$post_id || is_wp_error($post_id) ) {
		if ( !is_wp_error($post_id) ) $post_id = new WP_Error( 'insert_post_failed_generic', 'Failed to insert dealer into database.' );
		wp_die( $post_id );
		exit;
	}
	
	update_post_meta( $post_id, 'email', esc_html($post['email']) );
	update_post_meta( $post_id, 'organization-location', esc_html($post['location']) );
	
	// Upload the photo
	// These files need to be included as dependencies when on the front end.
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );
	
	$attachment_id = media_handle_upload( 'rst-image', $post_id );
	
	if ( !$attachment_id || is_wp_error($attachment_id) ) {
		if ( !is_wp_error($attachment_id) ) $attachment_id = new WP_Error( 'upload_attachment_failed_generic', 'Failed to upload image to the website.' );
		wp_die( $attachment_id );
		exit;
	}
	
	// Make it the featured image for the post
	set_post_thumbnail( $post_id, $attachment_id );
	
	// Make the shortcode display a message telling the user that the dealer was added.
	wp_redirect( add_query_arg( array('rs_dealer_added' => 1) ) );
	exit;
}
add_action( 'init', 'rst_add_dealer_from_shortcode' );