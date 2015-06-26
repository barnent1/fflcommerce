<?php

function fflcommerce_email_data_box($post)
{
	wp_nonce_field('fflcommerce_save_data', 'fflcommerce_meta_nonce');
	?>

	<div id="coupon_options" class="panel fflcommerce_options_panel">
		<style>
			.fflcommerce_options_panel .mid, .fflcommerce_options_panel .form-field input.mid{
				width:100%;
				margin-right:10px;
			}
		</style>
		<script>
			jQuery(document).ready(function($) {
				$('#fflcommerce_email_actions').change(function() {
					$.ajax({
						type: "POST",
						url: fflcommerce_params.ajax_url,
						data: {
							'action':'update_variable_list',
							'fflcommerce_email_actions' : $('#fflcommerce_email_actions').val()
						},
						success:function(data) {
							$('#avalible_arguments').replaceWith(data);
						},
						error: function(errorThrown){
							console.log(errorThrown);
						}
					});

				});
			});
		</script>
		<div class="options_group">
			<?php
			$args = array(
				'id' => 'fflcommerce_email_subject',
				'label' => __('Subject', 'fflcommerce'),
				'class' => 'mid',
				'multiple' => true,
			);
			echo FFLCommerce_Forms::input($args);

			$registered_mails = fflcommerce_emails::get_mail_list();
			$mails = array();
			if (!empty($registered_mails)) {
				foreach ($registered_mails as $hook => $detalis) {
					$mails[$hook] = $detalis['description'];
				}
			}

			$args = array(
				'id' => 'fflcommerce_email_actions',
				'label' => __('Actions', 'fflcommerce'),
				'multiple' => true,
				'class' => 'select mid',
				'placeholder' => __('No email action', 'fflcommerce'),
				'options' => $mails,
				'selected' => ''
			);
			echo FFLCommerce_Forms::select($args);
			?>
		</div>
	</div>
<?php
}

function fflcommerce_email_variable_box($post)
{
	?>
	<div id="coupon_options" class="panel fflcommerce_options_panel">
		<div class="options_group">
			<?php update_variable_list($post); ?>
		</div>
	</div>
	<?php
}

add_action('fflcommerce_process_shop_email_meta', 'fflcommerce_process_shop_email_meta', 1, 2);

function fflcommerce_process_shop_email_meta($post_id, $post)
{
	update_post_meta($post_id, 'fflcommerce_email_subject', isset($_POST['fflcommerce_email_subject']) ? $_POST['fflcommerce_email_subject'] : '');
	update_post_meta($post_id, 'fflcommerce_email_actions', isset($_POST['fflcommerce_email_actions']) ? $_POST['fflcommerce_email_actions'] : '');
	fflcommerce_emails::set_actions($post_id, isset($_POST['fflcommerce_email_actions']) ? $_POST['fflcommerce_email_actions'] : '');
}

add_action('wp_ajax_update_variable_list', function() { update_variable_list(); exit;});
add_action('wp_ajax_nopriv_update_variable_list', function() { update_variable_list(); exit;});

function update_variable_list($post = ''){
	?><div id="avalible_arguments">
		<table width="100%">
			<?php
			$i = 0;
			$selected = isset($_POST['fflcommerce_email_actions']) ? $_POST['fflcommerce_email_actions'] : (array)get_post_meta($post->ID, 'fflcommerce_email_actions', true);
			$registered_mails = fflcommerce_emails::get_mail_list();
			if (!empty($selected[0]) && !empty($registered_mails[$selected[0]])) {
				$keys = array_keys($registered_mails[$selected[0]]['accepted_args']);
				if (count($selected) > 1) {
					foreach ($selected as $hook) {
						$keys = array_intersect(array_keys($registered_mails[$hook]['accepted_args']), $keys);
					}
				}
				asort($keys);
				if(empty($keys)){
					_e('Selected actions have not common variables', 'fflcommerce');
				}
				foreach ($keys as $key) : ?>
					<?php if($i % 3 == 0): ?> <tr> <?php endif; ?>
						<td width="33.33%"><strong>[<?php echo $key ?>] </strong> - <?php echo $registered_mails[$selected[0]]['accepted_args'][$key] ?> <br/>
					<?php if($i % 3 == 2): ?> </tr> <?php endif; ?>
					<?php $i++; ?>
				<?php endforeach;
			} else {
				_e('Select email action to see avalible variables', 'fflcommerce');
			}?>
		</table>
	</div><?php
}


