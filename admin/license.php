<?php

$license_key = $prefix . '_license_key';
$license     = get_option( $license_key );
$status      = get_option( $prefix . '_license_status' );

$license_is_valid = $status && $status == 'valid' ? true : false;

?>

<h1>License Key</h1>
<p>Follow the actions indicated by the solid blue buttons, if any.</p>

<?php

	/**
	 * Status notices
	 */
	if ( ! $license ) :
		echo '<p class="license-status"><strong>Status:</strong> You must save your license key before you can activate it.</p>';

	elseif ( $license && ! $status ) :
		echo '<p class="license-status"><strong>Status:</strong> Your license key has been saved. Now, activate it by clicking "Activate License."</p>';

	elseif ( $license && $status && $status !== 'valid' ) :
		echo '<p class="license-status"><strong>Status:</strong> Error: Your license key is either incorrect or not valid.</p>';

	elseif ( $license && $license_is_valid ) :
		echo '<p class="license-status active-license"><strong>Status:</strong> Active. Your license key has been successfully activated!</p>';

	endif;

?>
<br>
<form method="post" action="options.php">
	<?php settings_fields( $license_key ); ?>

	<?php if ( ! $license || ! $license_is_valid ) : ?>
	<input
		type="password"
		name="<?php echo $license_key; ?>"
		id="<?php echo $license_key; ?>"
		class="regular-text"
		value="<?php esc_attr_e( $license ); ?>"
		placeholder="Enter your license key" />
	<?php endif; ?>

	<?php

	// License key option exists in database, is not null or false
	if ( $license ) :

		wp_nonce_field( $prefix . '_nonce', $prefix . '_nonce' );

		// Valid status in database; show Deactivate
		if ( $license_is_valid ) :
			?>

			<input 
				type="submit" 
				name="<?php echo $prefix; ?>_license_deactivate" 
				class="button-secondary" 
				value="<?php _e('Deactivate License'); ?>" />

			<?php
		// Not valid status in database; show Activate
		else :
			?>

			<input 
				type="submit" 
				name="<?php echo $prefix; ?>_license_activate" 
				class="button-primary" 
				value="<?php _e('Activate License'); ?>" />

			<?php
		endif;
	endif;

	?>
	<div class="save-and-clear-license-row">
		<?php

		$submit_button_type = $license ? 'secondary' : 'primary';

		if ( ! $license || ! $license_is_valid ) :
			submit_button( 'Save License', $submit_button_type );
		endif;

		if ( $license ) :

		?>
		<input 
			type="submit" 
			name="<?php echo $prefix; ?>_license_clear" 
			class="button-secondary" 
			value="<?php _e('Clear License'); ?>" />
		<?php

		endif;

		?>
	</div>
</form>
