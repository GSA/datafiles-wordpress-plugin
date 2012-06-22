<?php 
/**
 * Template for exclusive taxonomies metabox
 */ 
?><?php foreach ($terms as $term) { ?>
	<input type="radio" name="datafile_extension" value="<?php echo $term->term_id; ?>" id="<?php echo $term->slug; ?>"<?php	if ( isset( $current[0]->term_id ) )
			checked( $term->term_id, $current[0]->term_id );
?>>
	<label for="<?php echo $term->slug; ?>"><?php echo $term->name; ?></label><br />
<?php } ?>
<a href="#" id="add_datafile_extension_toggle">+ <?php _e( $taxonomy->labels->add_new_item, 'datafiles' ); ?></a>
<div id="add_datafile_extension_div" style="display:none">
	<label for="new_datafile_extension"><?php _e( $taxonomy->labels->singular_name, 'datafiles' ); ?>:</label>
	<input type="text" name="new_datafile_extension" id="new_datafile_extension" /><br />
	<input type="button" value="Add New" id="add_datafile_extension_button" />
	<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" id="datafile_extension-ajax-loading" style="display:none;" alt="" />
</div>
<?php wp_nonce_field( 'add_datafile_extension', '_datafile_extension_nonce' ); ?>
