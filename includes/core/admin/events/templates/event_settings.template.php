<div class="padding">
	<table class="form-table">
		<tbody>

			<tr>
				<th>
					<label for="expire_on_registration_end">
						<?php _e('Events Expire on Reg End Date', 'event_espresso'); ?>
						<?php echo EE_Template::get_help_tab_link('events_expire_on_reg_end_date_help_tab'); ?>
					</label>
				</th>
				<td>
					<p><?php echo EE_Form_Fields::select_input( 'expire_on_registration_end', $values, $expire_on_registration_end ); ?></p>
					<p class="description">
						<?php _e('If set to "Yes", then as soon as an event\'s registration end date has passed, the event will become inactive, and will no longer appear in your event listings.', 'event_espresso'); ?>
					</p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="default_reg_status">
						<?php _e('Default Registration Status', 'event_espresso'); ?>
						<?php echo EE_Template::get_help_tab_link('default_payment_status_help_tab'); ?>					
					</label>
				</th>
				<td>
					<p><?php echo EE_Form_Fields::select_input('default_reg_status', $reg_status_array, $default_reg_status ) ?></p>
					<p class="description">
						<?php _e('This value will be automatically filled in for each person\'s registration status, until payment is made, for each event.', 'event_espresso'); ?>
					</p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="pending_counts_reg_limit">
						<?php _e('Pending Registrations Count Towards Registration Limits', 'event_espresso'); ?>
						<?php //echo EE_Template::get_help_tab_link('payment_status_info'); ?>
					</label>
				</th>
				<td>
					<p><?php echo EE_Form_Fields::select_input('pending_counts_reg_limit', $values, $pending_counts_reg_limit) ?></p>
					<p class="description">
						<?php _e('If set to "Yes", then attendee\'s whose registration status is set to "Pending" will still count towards an Event\'s registration limit, and therefore also affect the number of spaces available, tickets left, and seating options (if applicable).', 'event_espresso'); ?>
					</p>
				</td>
			</tr>

		<?php do_action('AHEE_event_settings_template_extra_content', $template_args ); ?>

		</tbody>
	</table>
</div>
