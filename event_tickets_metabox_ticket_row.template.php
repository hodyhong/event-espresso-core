<tr valign="top" id="edit-ticketrow-<?php echo $ticketrow; ?>" class="edit-ticket-row">
	<td>
		<input type="hidden" name="edit_tickets[<?php echo $ticketrow; ?>][TKT_ID]" class="edit-ticket-TKT_ID" value="<?echo $TKT_ID; ?>">
		<input type="hidden" name="edit_tickets[<?php echo $ticketrow; ?>][TKT_is_default]" class="edit-ticket-TKT_is_default" value="<?php echo $TKT_is_default; ?>">
		<input type="text" name="edit_tickets[<?php echo $ticketrow; ?>][TKT_name]" class="edit-ticket-TKT_name ee-large-text-inp" placeholder="Ticket Title" value="<?php echo $TKT_name; ?>">
	</td>
	<td>
		<input type="text" name="edit_tickets[<?php echo $ticketrow; ?>][TKT_start_date]" class="edit-ticket-TKT_start_date ee-text-inp ee-datepicker" value="" data-context="start-ticket" data-date-field-context="#edit-ticketrow-<?php echo $ticketrow; ?>" data-related-field=".edit-ticket-TKT_end_date" data-next-field=".edit-ticket-TKT_end_date" value="<?php echo $TKT_start_date; ?>">
	</td>
	<td>
		<input type="text" name="edit_tickets[<?php echo $ticketrow; ?>][TKT_end_date]" class="edit-ticket-TKT_end_date ee-text-inp ee-datepicker" value="" data-context="end-ticket" data-date-field-context="#edit-ticketrow-<?php echo $ticketrow; ?>" data-related-field=".edit-ticket-TKT_start_date" data-next-field=".edit-ticket-PRC_amount" value="<?php echo $TKT_end_date; ?>">
	</td>
	<td>	
		<span class="ticket-price-info-display ticket-price-dollar-sign-display"><?php echo $price_curreny_symbol; ?></span>
		<input type="text" size="1" class="edit-price-PRC_amount ee-small-text-inp" name="edit_prices[<?php echo $ticketrow; ?>][1][PRC_amount]" value="<?php echo $PRC_amount; ?>">

		<input type="hidden" name="edit_prices[<?php echo $ticketrow; ?>][1][PRT_ID]" class="edit-price-PRT_ID" value="1">
		<input type="hidden" name="edit_prices[<?php echo $ticketrow; ?>][1][PRC_ID]" class="edit-price-PRC_ID" value="<?php echo $PRC_ID; ?>">
		<input type="hidden" name="edit_prices[<?php echo $ticketrow; ?>][1][PRC_is_default]" class="edit-price-PRC_is_default" value="<?php echo $PRC_is_default; ?>">
	</td>
	<td>
		<input type="text" class="edit-ticket-TKT_qty ee-small-text-inp" name="edit_tickets[<?php echo $ticketrow; ?>][TKT_qty]" value="<?php echo $TKT_qty; ?>">
	</td>
	<td>
		<span class="trash-icon clickable" data-ticket-row="<?php echo $ticketrow; ?>" data-context="ticket">
	</td>
</tr>

<?php
/**
 * template args
 *
 * $ticketrow
 * $TKT_ID
 * $TKT_is_default
 * $TKT_name
 * $TKT_start_date
 * $TKT_end_date
 * $price_currency_symbol;
 * $PRC_amount
 * $PRT_ID
 * $PRC_ID
 * $PRC_is_default
 * $TKT_qty
 */