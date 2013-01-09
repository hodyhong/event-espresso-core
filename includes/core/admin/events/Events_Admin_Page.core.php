<?php
if (!defined('EVENT_ESPRESSO_VERSION') )
	exit('NO direct script access allowed');

/**
 * Event Espresso
 *
 * Event Registration and Management Plugin for Wordpress
 *
 * @package		Event Espresso
 * @author		Seth Shoultes
 * @copyright	(c)2009-2012 Event Espresso All Rights Reserved.
 * @license		http://eventespresso.com/support/terms-conditions/  ** see Plugin Licensing **
 * @link		http://www.eventespresso.com
 * @version		3.2.P
 *
 * ------------------------------------------------------------------------
 *
 * Events_Admin_Page
 *
 * This contains the logic for setting up the Events related pages.  Any methods without phpdoc comments have inline docs with parent class. 
 *
 * NOTE:  TODO: This is a straight conversion from the legacy 3.1 events related pages.  It is NOT optimized and will need modification to fully use the new system (and also will need adjusted when Events model is setup)
 *
 * @package		Events_Admin_Page
 * @subpackage	includes/core/admin/Events_Admin_Page.core.php
 * @author		Darren Ethier
 *
 * ------------------------------------------------------------------------
 */
class Events_Admin_Page extends EE_Admin_Page {


	/**
	 * _event
	 * This will hold the event object for event_details screen.
	 *
	 * @access private
	 * @var object
	 */
	private $_event;


	public function __construct($wp_page_slug) {
		parent::__construct($wp_page_slug);
	}




	protected function _init_page_props() {
		$this->page_slug = 'events';
	}




	protected function _ajax_hooks() {
		//todo: all hooks for events ajax goes in here.
	}





	protected function _define_page_props() {
		$this->_admin_base_url = EVENTS_ADMIN_URL;
		$this->_admin_page_title = __('Events', 'event_espresso');
		$this->_labels = array(
			'buttons' => array(
				'add' => __('Add New Event', 'event_espresso'),
				'edit' => __('Edit Event', 'event_espresso'),
				'delete' => __('Delete Event', 'event_espresso')
			)
		);
	}




	protected function _set_page_routes() {
		$this->_page_routes = array(
			'default' => '_events_overview_list_table',
			'edit_event' => array(
				'func' => '_event_details',
				'args' => array('edit')
				),
			'add_event' => array(
				'func' => '_event_details',
				'args' => array('add')
				),
			'delete_events' => '_delete_events',
			'insert_event' => array(
				'func' => '_insert_or_update_event',
				'args' => array('new_event' => TRUE)
				 ),
			'update_event' => array(
				'func' => '_insert_or_update_event',
				'args' => array('new_event' => FALSE )
				),
			'trash_events' => array(
				'func' => '_trash_or_restore_events',
				'args' => array('trash' => TRUE )
				),
			'restore_events' => array(
				'func' => '_trash_or_restore_events',
				'args' => array('trash' => FALSE )
				),
			'view_report' => '_view_report',
			'export_events' => '_events_export',
			'export_payments' => '_payment_export',
			'import_events' => '_import_events',
			'import' => '_import_events'
			);
	}




	protected function _set_page_config() {
		$this->_page_config = array(
			'default' => array(
				'nav' => array(
					'label' => __('Overview', 'event_espresso'),
					'order' => 10
					),
				'list_table' => 'Events_Admin_List_Table'
				),
			'view_report' => array(
				'nav' => array(
					'label' => __('Report', 'event_espresso'),
					'order' => 20
					)
				),
			'import_events' => array(
				'nav' => array(
					'label' => __('Import', 'event_esprsso'),
					'order' => 30
					),
				'metaboxes' => array('_espresso_news_post_box', '_espresso_links_post_box')
				),
			'add_event' => array(
				'nav' => array(
					'label' => __('Add Event', 'event_espresso'),
					'order' => 5,
					'persistent' => false
					),
				'metaboxes' => array('_register_event_editor_meta_boxes', '_premium_event_editor_meta_boxes')
				),
			'edit_event' => array(
				'nav' => array(
					'label' => __('Edit Event', 'event_espresso'),
					'order' => 5,
					'persistent' => false
					),
				'metaboxes' => array('_register_event_editor_meta_boxes', '_premium_event_editor_meta_boxes')
				)
			);
	}



	protected function _add_screen_options() {
		//todo
	}


	protected function _add_screen_options_default() {
		$this->_add_screen_options_overview();
	}


	protected function _add_screen_options_overview() {
		$this->_per_page_screen_option();
	}



	protected function _add_help_tabs() {
		//todo
	}





	protected function _add_feature_pointers() {
		//todo
	}





	public function load_scripts_styles() {
		//todo note: we also need to load_scripts_styles per view (i.e. default/view_report/event_details)
	}



	//nothing needed for events with these methods.
	public function admin_init() {}
	public function admin_notices() {}
	public function admin_footer_scripts() {}




	protected function _set_list_table_views() {
		$this->_views = array(
			'all' => array(
				'slug' => 'all',
				'label' => __('View All Events', 'event_espresso'),
				'count' => 0,
				'bulk_action' => array(
					'delete_events' => __('Delete Permanently', 'event_espresso'),
					'export_events' => __('Export Events', 'event_espresso'),
					'export_payments' => __('Export Payments', 'event_espresso')
					)
				),
			'today' => array(
				'slug' => 'today',
				'label' => __('Today', 'event_espresso'),
				'count' => 0,
				'bulk_action' => array(
					'delete_events' => __('Delete Permanently', 'event_espresso'),
					'export_events' => __('Export Events', 'event_espresso'),
					'export_payments' => __('Export Payments', 'event_espresso')
					)
				),
			'month' => array(
				'slug' => 'month',
				'label' => __('This Month', 'event_espresso'),
				'count' => 0,
				'bulk_action' => array(
					'delete_events' => __('Delete Permanently', 'event_espresso'),
					'export_events' => __('Export Events', 'event_espresso'),
					'export_payments' => __('Export Payments', 'event_espresso')
					)
				)
			);
	}




	/**
	 * _events_overview_list_table
	 * This contains the logic for showing the events_overview list
	 *
	 * @access protected
	 * @return string html for generated table
	 */
	protected function _events_overview_list_table() {
		do_action( 'action_hook_espresso_log', __FILE__, __FUNCTION__, '' );

		$this->_admin_page_title .= $this->_get_action_link_or_button('add_event', 'add', array(), 'button add-new-h2');
		$this->display_admin_list_table_page_with_no_sidebar();
	}






	/**
	 * _event_details
	 * Depending on the given argument, this will display the event_details page (add or edit)	
	 * @access protected
	 * @param  string $view add or edit
	 * @return string     html for event_details page.
	 */
	protected function _event_details($view) {

		//load formatter helper
		require_once EVENT_ESPRESSO_PLUGINFULLPATH . '/helpers/EE_Formatter.helper.php';

		//load field generator helper
		require_once EVENT_ESPRESSO_PLUGINFULLPATH . '/helpers/EE_Form_Fields.helper.php';

		//set _event property
		$this->_set_event_object($view);

		//any specific javascript here.
		//todo: this needs to be done properly via an enqueue and wp_localize_scripts() for vars
		add_action( 'action_hook_espresso_event_editor_footer', array($this, 'event_editor_footer_js') );

		//take care of form tag and initial hidden fields setup
		$hidden_action_field_args['action'] = array(
			'type' => 'hidden',
			'value' => $view == 'edit' ? 'update_event' : 'insert_event'
			);

		$hidden_action_field = $this->_generate_admin_form_fields($hidden_action_field_args, 'array');
		$nonce = $view == 'edit' ? wp_nonce_field('update_event_nonce', '_wpnonce', false, false ) : wp_nonce_field('add_event_nonce', '_wpnonce', false, false ) ;
		$this->_template_args['before_admin_page_content'] = '<form name="form" method="post" action="' . $this->_admin_base_url. '" id="' . $view . '_event_form" >';
		$this->_template_args['before_admin_page_content'] .= "\n\t" . $nonce;
		$this->_template_args['before_admin_page_content'] .= "\n\t" . $hidden_action_field['action']['field'];
		$this->_template_args['after_admin_page_content'] = '</form>';


		//take care of contents
		$this->_template_args['admin_page_content'] = $this->_event_details_display();
		$this->display_admin_page_with_sidebar();
	}




	/**
	 * [event_editor_footer_js description]
	 * todo: temporary.  Replace with proper enqueue and wp_localize_script
	 * @return string
	 */
	public function event_editor_footer_js($content) {
		ob_start();
		include_once( EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/admin_screens/events/help.php');
		$n_content = ob_get_contents();
		ob_end_clean();
		$content .= $n_content;
		return $content;
	}




	/**
	 * _event_details_display
	 * takes care of setting up the html for the main event details (add/edit) display content.
	 *
	 * @access private
	 * @return string html
	 */
	private function _event_details_display() {
		$content = $this->_editor_title_div();
		$content .= $this->_editor_description_div();
		$content = apply_filters('action_hook_espresso_event_editor_footer', $content);
		return $content;
	}




	

	/**
	 * _editor_title_div
	 * returns the html for the title area on the event editor.
	 *
	 * @access private
	 * @return string html
	 */
	private function _editor_title_div() {
		ob_start();
		?>
		<div id="titlediv">
			<div id="titlewrap">
				<h5 style="margin: 1em .5em .1em;"><?php _e('Event Title', 'event_espresso'); ?></h5>
				<input id="title" type="text" autocomplete="off" value="<?php echo $this->_event->event_name; ?>" tabindex="1" size="30" name="event">
			</div>
			<!-- /titlewrap -->

			<div class="inside">
				<div id="edit-slug-box" style="height:auto;">

					<strong><?php _e('Permalink:', 'event_espresso'); ?></strong>

					<span id="sample-permalink">
						<?php echo $this->_event->page_url; ?><input size="50" type="text" tabindex="2" name="slug" id="slug" value ="<?php echo $this->_event->slug; ?>" />
					</span>

					<?php if ( ! $this->_event->is_new ) : ?>
						<a class="button" onclick="prompt('Shortcode:', jQuery('#shortcode').val()); return false;" href="#"><?php _e('Shortcode'); ?></a>
						<a class="button" onclick="prompt('Short URL:', jQuery('#shortlink').val()); return false;" href="#"><?php _e('Short URL'); ?></a>
						<a class="button" onclick="prompt('Full URL:', jQuery('#fulllink').val()); return false;" href="#"><?php _e('Full URL'); ?></a>
						<a class="button" onclick="prompt('Unique Event Identifier:', jQuery('#identifier').val()); return false;" href="#"><?php _e('Identifier'); ?></a>
						<a class="button" target="_blank" href="<?php echo $this->_event->page_url . $this->_event->slug; ?>/"><?php _e('View Post'); ?></a>
					<?php endif; ?>

					<input id="shortcode" type="hidden" value='[SINGLEEVENT single_event_id="<?php echo $this->_event->event_identifier; ?>"]'>
					<input id="shortlink" type="hidden" value="<?php echo add_query_arg(array('ee' => $this->_event->id), $this->_event->page_url); ?>">
					<input id="fulllink" type="hidden" value="<?php echo $this->_event->page_url . $this->_event->slug; ?>">
					<input id="identifier" type="hidden" value="<?php echo $this->_event->event_identifier; ?>">
					
				</div>
				<!-- /edit-slug-box -->
			</div>
		</div>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}




	/**
	 * _editor_description_div
	 * returns the html for the editor description field
	 * 
	 * @return string html
	 */
	private function _editor_description_div() {
		$editor_args['event_desc'] = array(
				'type' => 'wp_editor',
				'value' => EE_Formatter::admin_format_content($this->_event->event_desc),
				'class' => 'my_editor_custom',
			);

		$_wp_editor = $this->_generate_admin_form_fields($editor_args, 'array');
		$content = $_wp_editor['event_desc']['field'];

		ob_start();
		?>
		<div id="postdivrich" class="postarea">
			<?php echo $content; ?>
			<table id="post-status-info" cellspacing="0">
				<tbody>
					<tr>
						<td id="wp-word-count"><?php echo __('Word count:', 'event_espresso') ?> <span class="word-count"></span></td>
						<td class="autosave-info"><span class="autosave-message"></span><span id="last-edit"></span></td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
		$n_content = ob_get_contents();
		ob_end_clean();
		return $n_content;
	}






	/**
	 * _set_event_object
	 * this sets the _event property for the event details screen.
	 *
	 * @access private
	 * @return void
	 */
	private function _set_event_object($type = 'add') {
		if ( is_object($this->_event) )
			return; //get out we've already set the object
		
		if ( $type == 'add' ) {
			$this->_set_add_event_object();
		} else {
			$this->_set_edit_event_object();
		}
	}




	/**
	 * _set_add_event_object
	 * this sets the _event property for the event details screen when adding.
	 *
	 * @access private
	 * @return void
	 */
	private function _set_add_event_object() {
		global $wpdb, $org_options, $espresso_premium, $current_user;
		get_currentuserinfo();
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		$this->_event = new stdClass();
		$this->_event->is_new = TRUE;
		$this->_event->id = 0;
		$this->_event->event_name = '';
		$this->_event->start_date = date( 'Y-m-d', time() + (60 * 60 * 24 * 30));
		$this->_event->event_desc = '';
		$this->_event->phone = '';
		$this->_event->externalURL = '';
		$this->_event->early_disc = '';
		$this->_event->early_disc_date = '';
		$this->_event->early_disc_percentage = '';
		$this->_event->event_identifier = '';

		$this->_event->status = array('display' => 'OPEN');
		$this->_event->address = '';
		$this->_event->address2 = '';
		$this->_event->city = '';
		$this->_event->state = '';
		$this->_event->zip = '';
		$this->_event->country = '';
		$this->_event->virtual_url = '';
		$this->_event->virtual_phone = '';
		$this->_event->payment_email_id = 0;
		$this->_event->confirmation_email_id = 1;
		$this->_event->submitted = '';
		$this->_event->google_map_link = espresso_google_map_link(array(
				'address' => $this->_event->address,
				'city' => $this->_event->city,
				'state' => $this->_event->state,
				'zip' => $this->_event->zip,
				'country' => $this->_event->country));
		$this->_event->question_groups = array();
		$this->_event->event_meta = array(
				'additional_attendee_reg_info' => 1,
				'default_payment_status' => '',
				'add_attendee_question_groups' => array('1'),
				'originally_submitted_by' => $current_user->ID);
		$this->_event->wp_user = $current_user->ID;
		$sql = "SELECT qg.* FROM " . EVENTS_QST_GROUP_TABLE . " qg JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr ON qg.id = qgr.group_id ";
		$sql2 = apply_filters('filter_hook_espresso_event_editor_question_groups_sql', " WHERE wp_user = '0' OR wp_user = '1' ", $this->_event->id);
		$sql .= $sql2 . " GROUP BY qg.id ORDER BY qg.group_order";
		$sql = apply_filters('filter_hook_espresso_question_group_sql', $sql);
		//Debug:
		//echo $sql;
		$this->_event->q_groups = $wpdb->get_results($sql);
		$this->_event->num_rows = $wpdb->num_rows;
		$this->_event->reg_limit = '';
		$this->_event->allow_multiple = false;
		$this->_event->additional_limit = 0;
		$this->_event->is_active = true;
		$this->_event->event_status = 'A';
		$this->_event->display_desc = true;
		$this->_event->display_reg_form = true;
		$this->_event->alt_email = '';
		$this->_event->require_pre_approval = false;
		$this->_event->member_only = false;
		$this->_event->ticket_id = 0;
		$this->_event->certificate_id = 0;
		$this->_event->post_id = '';
		$this->_event->slug = '';
		$this->_event->venue_id = FALSE;
		$this->_event->venue_title = '';
		$this->_event->venue_url = '';
		$this->_event->venue_phone = '';
		$this->_event->venue_image = '';
		$this->_event = apply_filters('filter_hook_espresso_new_event_template', $this->_event);
		$this->_event->page_url = get_permalink($org_options['event_page_id']);
	}






	/**
	 * _set_edit_event_object
	 * this sets the _event property for the event details screen when adding.
	 *
	 * @access private
	 * @return void
	 */
	private function _set_edit_event_object() {
		global $wpdb, $org_options;
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');

		//check if we have an event_id if not then lets setup defaults for adding an event.
		if ( !isset($_REQUEST['EVT_ID']) ) {
			$this->_set_add_event_object();
			return;
		}

		$event_id = $_REQUEST['EVT_ID'];

		$sql = "SELECT e.*, ev.id as venue_id
		FROM " . EVENTS_DETAIL_TABLE . " e
		LEFT JOIN " . EVENTS_VENUE_REL_TABLE . " vr ON e.id = vr.event_id
		LEFT JOIN " . EVENTS_VENUE_TABLE . " ev ON vr.venue_id = ev.id
		WHERE e.id = %d";
		$this->_event = $wpdb->get_row($wpdb->prepare($sql, $event_id), OBJECT);

		//Debug
		//echo "<pre>".print_r($event,true)."</pre>";
		$this->_event->is_new = FALSE;
		$this->_event->event_name = stripslashes_deep($this->_event->event_name);
		$this->_event->event_desc = stripslashes_deep($this->_event->event_desc);
		$this->_event->phone = stripslashes_deep($this->_event->phone);
		$this->_event->externalURL = stripslashes_deep($this->_event->externalURL);
		$this->_event->early_disc = stripslashes_deep($this->_event->early_disc);
		$this->_event->early_disc_date = stripslashes_deep($this->_event->early_disc_date);
		$this->_event->early_disc_percentage = stripslashes_deep($this->_event->early_disc_percentage);
		$this->_event->event_identifier = stripslashes_deep($this->_event->event_identifier);
	//	$this->_event->start_time = isset($this->_event->start_time) ? $this->_event->start_time : '';
	//	$this->_event->end_time = isset($this->_event->end_time) ? $this->_event->end_time : '';
		$this->_event->status = array();
		$this->_event->status = event_espresso_get_is_active($this->_event->id);
		$this->_event->address = stripslashes_deep($this->_event->address);
		$this->_event->address2 = stripslashes_deep($this->_event->address2);
		$this->_event->city = stripslashes_deep($this->_event->city);
		$this->_event->state = stripslashes_deep($this->_event->state);
		$this->_event->zip = stripslashes_deep($this->_event->zip);
		$this->_event->country = stripslashes_deep($this->_event->country);
		$this->_event->submitted = $this->_event->submitted != '0000-00-00 00:00:00' ? (empty($this->_event->submitted) ? '' : event_date_display($this->_event->submitted, get_option('date_format')) ) : 'N/A';
		$this->_event->google_map_link = espresso_google_map_link(array('address' => $this->_event->address, 'city' => $this->_event->city, 'state' => $this->_event->state, 'zip' => $this->_event->zip, 'country' => $this->_event->country));
		$this->_event->question_groups = unserialize($this->_event->question_groups);
		$this->_event->event_meta = unserialize($this->_event->event_meta);

		$sql = "SELECT qg.* FROM " . EVENTS_QST_GROUP_TABLE . " qg JOIN " . EVENTS_QST_GROUP_REL_TABLE . " qgr ON qg.id = qgr.group_id ";
		$sql2 = apply_filters('filter_hook_espresso_event_editor_question_groups_sql', " WHERE wp_user = '0' OR wp_user = '1' ", $this->_event->id);
		$sql .= $sql2 . " GROUP BY qg.id ORDER BY qg.group_order";
		$sql = apply_filters('filter_hook_espresso_question_group_sql', $sql);
		//Debug:
		//echo $sql;
		$this->_event->q_groups = $wpdb->get_results($sql);
		$this->_event->num_rows = $wpdb->num_rows;
		$this->_event->page_url = get_permalink($org_options['event_page_id']);
	}





	/***************/
	/** METABOXES **/



	/**
	 * _register_event_editor_meta_boxes
	 * add all metaboxes related to the event_editor
	 * 
	 * @return [type] [description]
	 */
	protected function _register_event_editor_meta_boxes() {
		$this->_set_event_object();

		$this->_set_save_buttons(TRUE, array(), array(), EVENTS_ADMIN_URL);

		add_meta_box('espresso_event_editor_date_time', __('Dates &amp; Times', 'event_espresso'), array( $this, 'date_time_metabox' ), $this->_current_screen->id, 'normal', 'high');

		add_meta_box('espresso_event_editor_pricing', __('Event Pricing', 'event_espresso'), array( $this, 'pricing_metabox' ), $this->_current_screen->id, 'normal', 'core');

		add_meta_box('espresso_event_editor_venue', __('Venue Details', 'event_espresso'), array( $this, 'venue_metabox' ), $this->_current_screen->id, 'normal', 'core');

		add_meta_box('espresso_event_editor_email', __('Email Confirmation:', 'event_espresso'), array( $this, 'email_metabox' ), $this->_current_screen->id, 'advanced', 'core');

		add_meta_box('espresso_event_editor_quick_overview', __('Quick Overview', 'event_espresso'), array( $this, 'quick_overview_metabox' ), $this->_current_screen->id, 'side', 'high');

		add_meta_box('espresso_event_editor_primary_questions', __('Questions for Primary Attendee', 'event_espresso'), array( $this, 'primary_questions_group_meta_box' ), $this->_current_screen->id, 'side', 'core');

		add_meta_box('espresso_event_editor_categories', __('Event Category', 'event_espresso'), array( $this, 'categories_meta_box' ), $this->_current_screen->id, 'side', 'default');
	}







	/**
	 * _premium_event_editor_meta_boxes
	 * add all metaboxes related to the event_editor
	 *
	 * @access protected
	 * @return void 
	 */
	protected function _premium_event_editor_meta_boxes() {
		global $org_options;
		$this->_set_event_object();

		add_meta_box('espresso_event_editor_event_meta', __('Event Meta', 'event_espresso'), array( $this, 'event_meta_metabox'), $this->_current_screen->id, 'advanced', 'high');

		add_meta_box('espresso_event_editor_event_post', __('Create a Post', 'event_espresso'), array( $this, 'event_post_metabox'), $this->_current_screen->id, 'advanced', 'core');

		add_meta_box('espresso_event_editor_event_options', __('Event Options', 'event_espresso'), array( $this, 'event_options_meta_box' ), $this->_current_screen->id, 'side', 'high');

		add_meta_box('espresso_event_editor_additional_questions', __('Questions for Additional Attendees', 'event_espresso'), array( $this, 'additional_attendees_question_groups_meta_box' ), $this->_current_screen->id, 'side', 'core');

		add_meta_box('espresso_event_editor_promo_box', __('Event Promotions', 'event_espresso'), array( $this, 'promotions_meta_box' ), $this->_current_screen->id, 'side', 'core');

		add_meta_box('espresso_event_editor_featured_image_box', __('Featured Image', 'event_espresso'), array( $this, 'featured_image_meta_box' ), $this->_current_screen->id, 'side', 'default');

		if ($org_options['use_attendee_pre_approval']) {
			add_meta_box('espresso_event_editor_preapproval_box', __('Attendee Pre-Approval', 'event_espresso'), array( $this, 'preapproval_metabox' ), $this->_current_screen->id, 'side', 'default');
		}

		if ($org_options['use_personnel_manager']) {
			add_meta_box('espresso_event_editor_personnel_box', __('Event Staff / Speakers', 'event_espresso'), array( $this, 'personnel_metabox' ), $this->_current_screen->id, 'side', 'default');
		}
	}




	public function event_meta_metabox() {
		global $wpdb, $org_options;
		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
		global $espresso_premium;
		if ($espresso_premium != true)
			return;

		$event_meta = $this->_event->event_meta;
		?>
		<div class="inside">
		<?php
			$good_meta = array();
			$hiddenmeta = array("", "venue_id", "additional_attendee_reg_info", "add_attendee_question_groups", "date_submitted", "event_host_terms", "default_payment_status", "display_thumb_in_lists", "display_thumb_in_regpage", "display_thumb_in_calendar", "event_thumbnail_url", "originally_submitted_by", "enable_for_gmap", "orig_event_staff");
			$meta_counter = 1;

			$default_event_meta = array();
			$default_event_meta = apply_filters('filter_hook_espresso_filter_default_event_meta', $default_event_meta);

			$default_meta = $event_meta == '' ? $default_event_meta : array();
			$event_meta = $event_meta == '' ? array() : $event_meta;
			$event_meta = array_merge($event_meta, $default_meta);
			//print_r( $event_meta );
			$good_meta = $event_meta;
			//print_r( $good_meta );
			?>
			<p>
				<?php _e('Using Event Meta boxes', 'event_espresso'); ?> <?php echo apply_filters('filter_hook_espresso_help', 'event-meta-boxes'); ?>
			<ul id="dynamicMetaInput">
				<?php
				if ($event_meta != '') {
					foreach ($event_meta as $k => $v) {
						?>
						<?php
						if (in_array($k, $hiddenmeta)) {
							//echo "<input type='hidden' name='emeta[]' value='{$v}' />";
							unset($good_meta[$k]);
						} else {
							?>
							<li>
								<label>
						<?php _e('Key', 'event_espresso'); ?>
								</label>
								<select id="emeta[]" name="emeta[]">
									<?php foreach ($good_meta as $k2 => $v2) { ?>
										<option value="<?php echo $k2; ?>" <?php echo ($k2 == $k ? "SELECTED" : null); ?>><?php echo $k2; ?></option>
						<?php } ?>
								</select>
								<label for="meta-value">
						<?php _e('Value', 'event_espresso'); ?>
								</label>
								<input  size="20" type="text" value="<?php echo $v; ?>" name="emetad[]" id="emetad[]" />
								<?php
								echo '<img class="remove-item" title="' . __('Remove this meta box', 'event_espresso') . '" onclick="this.parentNode.parentNode.removeChild(this.parentNode);" src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/remove.gif" alt="' . __('Remove Meta', 'event_espresso') . '" />';
								?>
							</li>
							<?php
							$meta_counter++;
						}
						?>
						<?php
					}
					echo '<li><label for="emeta-box">' . __('Key', 'event_espresso');
					?>
				</label>
				<input id="emeta-box" size="20" type="text" value="" name="emeta[]" >
				<label for="emetaad[]">
				<?php _e('Value', 'event_espresso'); ?>
				</label>
				<input size="20" type="text" value="" name="emetad[]" id="emetad[]">
				<?php
				echo '<img class="remove-item" title="' . __('Remove this meta box', 'event_espresso') . '" onclick="this.parentNode.parentNode.removeChild(this.parentNode);" src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/remove.gif" alt="' . __('Remove Meta', 'event_espresso') . '" />' . '</li>';
			} else {
				echo '<li><label for="emeta[]">' . __('Key', 'event_espresso');
				?>
				</label>
				<input size="20" type="text" value="" name="emeta[]" id="emeta[]">
				<?php _e('Value', 'event_espresso'); ?>
				<input size="20" type="text" value="" name="emetad[]" id="emetad[]">
				<?php
				echo '<img class="remove-item" title="' . __('Remove this meta box', 'event_espresso') . '" onclick="this.parentNode.parentNode.removeChild(this.parentNode);" src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/remove.gif" alt="' . __('Remove Meta', 'event_espresso') . '" />' . '</li>';
				// $meta_counter++;
			}
			?>
			</ul>
			<p>
				<input type="button" class="button" value="<?php _e('Add A Meta Box', 'event_espresso'); ?>" onClick="addMetaInput('dynamicMetaInput');">
			</p>
			<script type="text/javascript">
				//Dynamic form fields
				var meta_counter = <?php echo $meta_counter > 1 ? $meta_counter - 1 : $meta_counter++; ?>;
				function addMetaInput(divName){
					var next_counter = counter_staticm(meta_counter);
					var newdiv = document.createElement('li');
					newdiv.innerHTML = "<label><?php _e('Key', 'event_espresso'); ?></label> <input size='20' type='text' value='' name='emeta[]' id='emeta[]'> <label><?php _e('Value', 'event_espresso'); ?></label> <input size='20' type='text' value='' name='emetad[]' id='emetad[]'><?php echo ' <img class=\"remove-item\" title=\"' . __('Remove this meta box', 'event_espresso') . '\" onclick=\"this.parentNode.parentNode.removeChild(this.parentNode);\" src=\"' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/icons/remove.gif\" alt=\"' . __('Remove Meta', 'event_espresso') . '\" />'; ?>";
					document.getElementById(divName).appendChild(newdiv);
					counter++;
				}

				function counter_staticm(meta_counter) {
					if ( typeof counter_static.counter == 'undefined' ) {

						counter_static.counter = meta_counter;
					}
					return ++counter_static.counter;
				}
			</script>
		</div>
		<?php
	}





	public function event_post_metabox() {
		$values = array(
			array('id' => true, 'text' => __('Yes', 'event_espresso')),
			array('id' => false, 'text' => __('No', 'event_espresso')));
		if (function_exists('espresso_member_data')) {
			global $espresso_manager;
			$is_admin = (espresso_member_data('role') == "administrator" || espresso_member_data('role') == 'espresso_event_admin') ? true : false;
			if (!$espresso_manager['event_manager_create_post'] && !$is_admin) {
				return;
			}
		}
		?>
		<div class="inside">
			<?php
			if (strlen($this->_event->post_id) > 1) {
				$create_post = true; //If a post was created previously, default to yes on the update post.
			} else {
				$create_post = false; //If a post was NOT created previously, default to no so we do not create a post on accident.
			}
			global $current_user;
			get_currentuserinfo();
			?>
			<table class="form-table">
				<tbody>
					<tr>
						<th class="middle">
							<label>
								<?php echo __('Add/Update post for this event?', 'event_espresso') ?>
							</label>
						</th>
						<td class="med">
							<?php
							echo EE_Form_Fields::select_input('create_post', $values, $create_post);
							if (strlen($this->_event->post_id) > 1) {
								echo '<p>' . __('If no, delete current post?', 'event_espresso');
								?>
								<input name="delete_post" type="checkbox" value="true" />
							<?php } ?>
							</p>
							<input type="hidden" name="post_id" value="<?php if (isset($this->_event->post_id)) echo $this->_event->post_id; ?>">
							<?php /* ?><p><?php _e('Category:', 'event_espresso'); ?> <?php wp_dropdown_categories(array('orderby'=> 'name','order' => 'ASC', 'selected' => $category, 'hide_empty' => 0 )); ?></p><?php */ ?>
						<td>
					</tr>
					<tr>
						<th class="middle">

							<?php
							if (!empty($this->_event->post_id)) {
								$post_data = get_post($this->_event->post_id);
								$tags = get_the_tags($this->_event->post_id);
								if ($tags) {
									foreach ($tags as $k => $v) {
										$tag[$k] = $v->name;
									}
									$tags = join(', ', $tag);
								}
							} else {
								$post_data = new stdClass();
								$post_data->ID = 0;
								$tags = '';
							}
							$box = array();

							$custom_post_array = array(array('id' => 'espresso_event', 'text' => __('Espresso Event', 'event_espresso')));
							$post_page_array = array(array('id' => 'post', 'text' => __('Post', 'event_espresso')), array('id' => 'page', 'text' => __('Page', 'event_espresso')));
							$post_page_array = !empty($org_options['template_settings']['use_custom_post_types']) ? array_merge($custom_post_array, $post_page_array) : $post_page_array;
							//print_r($post_page_array);

							$post_types = $post_page_array;
							?>

							<label>
								<?php _e('Author', 'event_espresso: '); ?>
							</label>
						</th>
						<td class="med">
							<?php wp_dropdown_users(array('who' => 'authors', 'selected' => $current_user->ID)); ?>
						</td>
					</tr>
					<tr>
						<th class="middle">
							<label>
								<?php _e('Post Type', 'event_espresso: '); ?>
							</label>
						</th>
						<td class="med">
							<?php echo EE_Form_Fields::select_input('post_type', $post_types, 'espresso_event') ?>
						</td>
					</tr>
					<tr>
						<th class="middle">
							<label>
								<?php _e('Tags', 'event_espresso: '); ?>
							</label>
						</th>
						<td class="med">
							<input id="post_tags" name="post_tags" size="20" type="text" value="<?php echo $tags; ?>" />
						</td>
					</tr>
				</tbody>
			</table>



			<p class="section-heading"><?php _e('Post Categories:', 'event_espresso'); ?> </p>
			<?php
			require_once( 'includes/meta-boxes.php');
			post_categories_meta_box($post_data, $box);
			?>

			<!-- if post templates installed, post template -->

		</div>
		<?php
	}





	public function event_options_meta_box() {
		$values = array(
			array('id' => true, 'text' => __('Yes', 'event_espresso')),
			array('id' => false, 'text' => __('No', 'event_espresso'))
		);
		$additional_attendee_reg_info_values = array(
				array('id' => '1', 'text' => __('No info required', 'event_espresso')),
				array('id' => '2', 'text' => __('Personal Information only', 'event_espresso')),
				array('id' => '3', 'text' => __('Full registration information', 'event_espresso'))
		);
		$event_status_values = array(
				array('id' => 'A', 'text' => __('Public', 'event_espresso')),
				array('id' => 'S', 'text' => __('Waitlist', 'event_espresso')),
				array('id' => 'O', 'text' => __('Ongoing', 'event_espresso')),
				array('id' => 'R', 'text' => __('Draft', 'event_espresso')),
				array('id' => 'D', 'text' => __('Deleted', 'event_espresso'))
		);
		$event_status_values = apply_filters('filter_hook_espresso_event_status_values', $event_status_values);

		$default_payment_status_values = array(
				array('id' => "", 'text' => 'No Change'),
				array('id' => 'Incomplete', 'text' => 'Incomplete'),
				array('id' => 'Pending', 'text' => 'Pending'),
				array('id' => 'Completed', 'text' => 'Completed')
		);
		?>
		<p class="inputundersmall">
			<label for="reg-limit">
				<?php _e('Attendee Limit: ', 'event_espresso'); ?>
			</label>
			<input id="reg-limit" name="reg_limit"  size="10" type="text" value="<?php echo $this->_event->reg_limit; ?>" /><br />
			<span>(<?php _e('leave blank for unlimited', 'event_espresso'); ?>)</span>
		</p>
		<p class="clearfix" style="clear: both;">
			<label for="group-reg"><?php _e('Allow group registrations? ', 'event_espresso'); ?></label>
			<?php echo EE_Form_Fields::select_input('allow_multiple', $values, $this->_event->allow_multiple, 'id="group-reg"', '', false); ?>
		</p>
		<p class="inputundersmall">
			<label for="max-registrants"><?php _e('Max Group Registrants: ', 'event_espresso'); ?></label>
			<input type="text" id="max-registrants" name="additional_limit" value="<?php echo $this->_event->additional_limit; ?>" size="4" />
		</p>
		<p class="inputunder">
			<label><?php _e('Additional Attendee Registration info?', 'event_espresso'); ?></label>
			<?php echo EE_Form_Fields::select_input('additional_attendee_reg_info', $additional_attendee_reg_info_values, $this->_event->event_meta['additional_attendee_reg_info']); ?>
		</p>
		<p>
			<label><?php _e('Event is Active', 'event_espresso'); ?></label>
			<?php echo EE_Form_Fields::select_input('is_active', $values, $this->_event->is_active); ?>
		</p>
		<p>
			<label><?php _e('Event Status', 'event_espresso'); ?>
				<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=status_types_info">
					<span class="question">[?]</span>
				</a>
			</label>
			<?php echo EE_Form_Fields::select_input('new_event_status', $event_status_values, $this->_event->event_status, '', '', false); ?>
		</p>
		<p>
			<label><?php _e('Display  Description', 'event_espresso'); ?></label>
			<?php echo EE_Form_Fields::select_input('display_desc', $values, $this->_event->display_desc); ?>
		</p>
		<p>
			<label>
				<?php _e('Display  Registration Form', 'event_espresso'); ?>
			</label>
			<?php echo EE_Form_Fields::select_input('display_reg_form', $values, $this->_event->display_reg_form, '', '', false); ?>
		</p>
		<p class="inputunder">
			<label>
				<?php _e('Default Payment Status', 'event_espresso'); ?>
				<a class="thickbox" href="#TB_inline?height=300&amp;width=400&amp;inlineId=payment_status_info">
					<span class="question">[?]</span>
				</a>
			</label>
			<?php echo EE_Form_Fields::select_input('default_payment_status', $default_payment_status_values, $this->_event->event_meta['default_payment_status']); ?>
		</p>
		<p class="inputunder">
			<label><?php _e('Alternate Registration Page', 'event_espresso'); ?>
				<a class="thickbox" href="#TB_inline?height=300&amp;width=400&amp;inlineId=external_URL_info">
					<span class="question">[?]</span>
				</a>
			</label>
			<input name="externalURL" size="20" type="text" value="<?php echo $this->_event->externalURL; ?>">
		</p>
		<p class="inputunder">
			<label><?php _e('Alternate Email Address', 'event_espresso'); ?>
				<a class="thickbox" href="#TB_inline?height=300&width=400&inlineId=alt_email_info">
					<span class="question">[?]</span>
				</a>
			</label>
			<input name="alt_email" size="20" type="text" value="<?php echo $this->_event->alt_email; ?>">
		</p>
		<?php
	}





	public function additional_attendees_question_groups_meta_box() {
		$add_attendee_question_groups = $this->_event->event_meta['add_attendee_question_groups'];
		?>
		<div class="inside">
			<p><strong>
					<?php _e('Question Groups', 'event_espresso'); ?>
				</strong><br />
				<?php _e('Add a pre-populated', 'event_espresso'); ?>
				<a href="admin.php?page=form_groups" target="_blank">
					<?php _e('group', 'event_espresso'); ?>
				</a>
				<?php _e('of', 'event_espresso'); ?>
				<a href="admin.php?page=form_builder" target="_blank">
					<?php _e('questions', 'event_espresso'); ?>
				</a>
				<?php _e('to your event. The personal information group is required for all events.', 'event_espresso'); ?>
			</p>
			<?php
			if ($this->_event->num_rows > 0) {
				reset($this->_event->q_groups);
				$html = '';
				foreach ($this->_event->q_groups as $question_group) {
					$question_group_id = $question_group->id;
					$group_name = $question_group->group_name;
					$checked = (is_array($add_attendee_question_groups) && array_key_exists($question_group_id, $add_attendee_question_groups)) || ($question_group->system_group == 1) ? ' checked="checked" ' : '';

					$visibility = $question_group->system_group == 1 ? 'style="visibility:hidden"' : '';

					$html .= '<p id="event-question-group-' . $question_group_id . '"><input value="' . $question_group_id . '" type="checkbox" ' . $visibility . ' name="add_attendee_question_groups[' . $question_group_id . ']" ' . $checked . ' /> <a href="admin.php?page=form_groups&amp;action=edit_group&amp;group_id=' . $question_group_id . '" title="edit" target="_blank">' . $group_name . "</a></p>";
				}
				if ($this->_event->num_rows > 10) {
					$top_div = '<div style="height:250px;overflow:auto;">';
					$bottom_div = '</div>';
				} else {
					$top_div = '';
					$bottom_div = '';
				}
				$html = $top_div . $html . $bottom_div;
				echo $html;
			} else {
				echo __('There seems to be a problem with your questions. Please contact support@eventespresso.com', 'event_espresso');
			}
			?>
		</div>
		<?php
	}





	public function promotions_meta_box() {
		$values = array(
			array('id' => true, 'text' => __('Yes', 'event_espresso')),
			array('id' => false, 'text' => __('No', 'event_espresso'))
		);
		global $wpdb;
		?>
		<div class="inside">

			<p><strong><?php _e('Early Registration Discount', 'event_espresso'); ?></strong></p>

			<p><label for="early_disc_date"><?php _e('End Date:', 'event_espresso'); ?></label><input type="text" class="datepicker" size="12" id="early_disc_date" name="early_disc_date" value="<?php echo isset($this->_event->early_disc_date) ? $this->_event->early_disc_date : ''; ?>"/> </p>

			<p class="promo-amnts">
				<label for="early_disc"><?php _e('Amount:', 'event_espresso'); ?></label><input type="text" size="3" id="early_disc" name="early_disc" value="<?php echo isset($this->_event->early_disc) ? $this->_event->early_disc : ''; ?>" /> <br /><span class="description"><?php _e('(Leave blank if not applicable)', 'event_espresso'); ?></span>
			</p>

			<p>
				<label><?php _e('Percentage:', 'event_espresso') ?></label>

				<?php echo EE_Form_Fields::select_input('early_disc_percentage', $values, !isset($this->_event->early_disc_percentage) ? '' : $this->_event->early_disc_percentage); ?>
			</p>

			<p><strong><?php _e('Promotion Codes', 'event_espresso'); ?></strong></p>
			<p class="disc-codes">
				<label><?php _e('Allow discount codes?', 'event_espresso'); ?> <?php echo apply_filters('filter_hook_espresso_help', 'coupon_code_info'); ?></label>
				<?php echo EE_Form_Fields::select_input('use_coupon_code', $values, !isset($this->_event->use_coupon_code) || $this->_event->use_coupon_code == '' ? false : $this->_event->use_coupon_code); ?>
			</p>

			<?php
			$sql = "SELECT * FROM " . EVENTS_DISCOUNT_CODES_TABLE;
			if (function_exists('espresso_member_data') && !empty($this->_event->event_id)) {
				$wpdb->get_results("SELECT wp_user FROM " . EVENTS_DETAIL_TABLE . " WHERE id = '" . $this->_event->event_id . "'");
				$this->_event->wp_user = $wpdb->last_result[0]->wp_user != '' ? $wpdb->last_result[0]->wp_user : espresso_member_data('id');
				$sql .= " WHERE ";
				if ($this->_event->wp_user == 0 || $this->_event->wp_user == 1) {
					$sql .= " (wp_user = '0' OR wp_user = '1') ";
				} else {
					$sql .= " wp_user = '" . $this->_event->wp_user . "' ";
				}
			}
			$event_discounts = $wpdb->get_results($sql);
			if (!empty($event_discounts)) {
				foreach ($event_discounts as $event_discount) {
					$discount_id = $event_discount->id;
					$coupon_code = $event_discount->coupon_code;

					$in_event_discounts = !empty($this->_event->event_id) ? $wpdb->get_results("SELECT * FROM " . EVENTS_DISCOUNT_REL_TABLE . " WHERE event_id='" . $this->_event->event_id . "' AND discount_id='" . $discount_id . "'") : array();
					$in_event_discount = '';
					foreach ($in_event_discounts as $in_discount) {
						$in_event_discount = $in_discount->discount_id;
					}
					echo '<p class="event-disc-code" id="event-discount-' . $discount_id . '"><label for="in-event-discount-' . $discount_id . '" class="selectit"><input value="' . $discount_id . '" type="checkbox" name="event_discount[]" id="in-event-discount-' . $discount_id . '"' . ($in_event_discount == $discount_id ? ' checked="checked"' : "" ) . '/> ' . $coupon_code . "</label></p>";
				}
			}

			echo '<p><a href="admin.php?page=discounts" target="_blank">' . __('Manage Promotional Codes ', 'event_espresso') . '</a></p>';
			?>
		</div>
		<?php
	}





	public function featured_image_meta_box() {
		$event_meta = $this->_event->event_meta;
		$values = array(
				array('id' => true, 'text' => __('Yes', 'event_espresso')),
				array('id' => false, 'text' => __('No', 'event_espresso')));
		?>
		<div class="inside">
			<div id="featured-image">
				<?php
				if (!empty($event_meta['event_thumbnail_url'])) {
					$event_thumb = $event_meta['event_thumbnail_url'];
				} else {
					$event_thumb = '';
				}
				?>
				<label for="upload_image">
					<?php _e('Add Featured Image', 'event_espresso'); ?>
				</label>
				<input id="upload_image" type="hidden" size="36" name="upload_image" value="<?php echo $event_thumb ?>" />
				<input id="upload_image_button" type="button" value="Upload Image" />
				<?php if ($event_thumb) { ?>
					<p class="event-featured-thumb"><img  src="<?php echo $event_thumb ?>" alt="" /></p>
					<a id='remove-image' href='#' title='Remove this image' onclick='return false;'>Remove Image</a>
				<?php } ?>
			</div>
			<p>
				<label>
					<?php _e('Enable image in event lists', 'event_espresso'); ?>
				</label>
				<?php echo EE_Form_Fields::select_input('show_thumb_in_lists', $values, isset($event_meta['display_thumb_in_lists']) ? $event_meta['display_thumb_in_lists'] : '', 'id="show_thumb_in_lists"'); ?> </p>
			<p>
				<label>
					<?php _e('Enable image in registration', 'event_espresso'); ?>
				</label>
				<?php echo EE_Form_Fields::select_input('show_thumb_in_regpage', $values, isset($event_meta['display_thumb_in_regpage']) ? $event_meta['display_thumb_in_regpage'] : '', 'id="show_thumb_in_regpage"'); ?> </p>
		</div>
		<?php
	}





	public function preapproval_metabox() {
		$pre_approval_values = array(
			array('id' => true, 'text' => __('Yes', 'event_espresso')),
			array('id' => false, 'text' => __('No', 'event_espresso')));
		?>
		<div class="inside">
			<p class="pre-approve">
				<label>
					<?php _e('Attendee pre-approval required?', 'event_espresso'); ?>
				</label>
				<?php
				echo EE_Form_Fields::select_input("require_pre_approval", $pre_approval_values, $this->_event->require_pre_approval);
				?>
			</p>
		</div>
		<?php
	}





	public function personnel_metabox() {
		$event_id = !empty($this->_event->id) ? $this->_event->id : 0;
		$originally_submitted_by = !empty($this->_event->event_meta['originally_submitted_by']) ? $this->_event->event_meta['originally_submitted_by'] : 0;
		$orig_event_staff = !empty($this->_event->event_meta['orig_event_staff']) ? $this->_event->event_meta['orig_event_staff'] : 0;
		?>
		<div class="inside">
			<?php echo espresso_personnel_cb($event_id, $originally_submitted_by, $orig_event_staff); ?>
		</div>
		<?php
	}





	



	public function date_time_metabox() {
		global $org_options, $espresso_premium;

		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');

	//	require_once(EVENT_ESPRESSO_INCLUDES_DIR . 'models/EEM_Ticket.model.php');
	//	$TKT_MDL = EEM_Ticket::instance();
	//	
	//	$all_event_tickets = $TKT_MDL->get_all_event_tickets( $event->id );

		require_once(EVENT_ESPRESSO_INCLUDES_DIR . 'models/EEM_Datetime.model.php');
		$DTM_MDL = EEM_Datetime::instance();

		global $times;
		// grab event times
		$times = $DTM_MDL->get_all_event_dates( $this->_event->id );
		// grab reg times
		//$reg_times = $DTM_MDL->get_all_reg_dates($this->_event->id);
		
		$datetime_IDs = array();
		
		//printr( $times, '$times' );
		?>

		
		<div id="event-datetimes-dv" class="" >

			<table id="event-dates-and-times">
				<thead>
					<tr valign="top">
						<td> <?php echo __('Event Starts on', 'event_espresso') ?> <?php echo apply_filters('filter_hook_espresso_help', 'event_date_info'); ?> </td>
						<td><?php echo __('Event Ends on', 'event_espresso') ?></td>
						<td><?php echo __('Registration Starts on', 'event_espresso') ?> <?php echo apply_filters('filter_hook_espresso_help', 'reg_date_info'); ?></td>
						<td><?php echo __('Registration Ends on', 'event_espresso') ?></td>					
						<?php /* DO NOT DELETE - NEW FEATURE IN PROGRESS <td><?php echo __('Max Reg Limit', 'event_espresso'); ?></td>*/ ?>
					</tr>
				</thead>
				
				<?php $row = 1; ?>
				
				<?php foreach ($times as $time) : ?>
					<tr valign="top" id="event-dates-and-times-row-<?php echo $row; ?>">
						<td>
							<div class="small-screen-table-label"><?php echo __('Event Starts on', 'event_espresso') ?> <?php echo apply_filters('filter_hook_espresso_help', 'event_date_info'); ?></div>
							<input id="event-start-<?php echo $row; ?>" name="event_datetimes[<?php echo $row; ?>][evt_start]" type="text" class="dtm-es-picker dtm-inp medium-text" value="<?php echo $time->start_date_and_time(  'Y-m-d '  ); ?>"/>
							<input name="event-start-row-<?php echo $row; ?>" type="hidden" value="<?php echo $row; ?>"/>
							<?php /* <input id="event-start-max-date-<?php echo $row; ?>" type="hidden" value=""/> */ ?>
							<?php if ($time->ID()) { ?>
							<?php $datetime_IDs[$row] = $time->ID(); ?>
							<input id="ID-<?php echo $row; ?>" name="event_datetimes[<?php echo $row; ?>][ID]" type="hidden" value="<?php echo $time->ID(); ?>"/>
							<?php } ?>						
							<input id="is-primary-<?php echo $row; ?>" name="event_datetimes[<?php echo $row; ?>][is_primary]" type="hidden" value="<?php echo $time->is_primary(); ?>" />
						</td>

						<td>
							<div class="small-screen-table-label"><?php echo __('Event Ends on', 'event_espresso') ?></div>
							<input id="event-end-<?php echo $row; ?>" name="event_datetimes[<?php echo $row; ?>][evt_end]" type="text" class="dtm-ee-picker dtm-inp medium-text" value="<?php echo $time->end_date_and_time(  'Y-m-d '  ); ?>"/>
							<input name="event-end-row_<?php echo $row; ?>" type="hidden" value="<?php echo $row; ?>"/>
							<?php /* <input id="event-end-min-date-<?php echo $row; ?>" type="hidden" value=""/> */ ?>
						</td>
						
						<td>
							<div class="small-screen-table-label"><?php echo __('Registration Starts on', 'event_espresso') ?></div>
							<input id="reg-start-<?php echo $row; ?>" name="event_datetimes[<?php echo $row; ?>][reg_start]" type="text" class="dtm-rs-picker dtm-inp medium-text" value="<?php echo $time->reg_start_date_and_time(  'Y-m-d '  ) ?>" />
							<input name="reg-start-row-<?php echo $row; ?>" type="hidden" value="<?php echo $row; ?>"/>
						</td>

						<td>
							<div class="small-screen-table-label"><?php echo __('Registration Ends on', 'event_espresso') ?></div>
							<input id="reg-end-<?php echo $row; ?>" name="event_datetimes[<?php echo $row; ?>][reg_end]" type="text" class="dtm-re-picker dtm-inp medium-text" value="<?php echo $time->reg_end_date_and_time(  'Y-m-d '  ) ?>" />
							<input name="reg-end-row_<?php echo $row; ?>" type="hidden" value="<?php echo $row; ?>"/>
						</td>
			
						<?php /* DO NOT DELETE - NEW FEATURE IN PROGRESS 
						<?php if ($org_options['time_reg_limit']) : ?>
							<td>
								<input type="text" id="reg-limit-<?php echo $row; ?>" name="event_datetimes[<?php echo $row; ?>][reg_limit]" class="small-text dtm-inp" style="text-align:right;" value="<?php echo $time->reg_limit(); ?>"/>
							</td>
						<?php endif; // time_reg_limit   ?>
						  */ ?>
						
	<!--					<td>
							<input type="text" id="tckts-left-<?php echo $row; ?>" name="event_datetimes[<?php echo $row; ?>][tckts_left]" class="small-text dtm-inp" style="text-align:right;" value="<?php echo $time->tckts_left(); ?>"/>
						</td>-->
																	
						<td>
							<div class="small-screen-table-label"><?php echo __('Actions', 'event_espresso') ?></div>
							<?php /* DO NOT DELETE - NEW FEATURE IN PROGRESS <a class='display-dtm-tickets-left-lnk display-ticket-manager' data-reveal-id="ticket-manager-dv" rel="<?php echo $time->ID(); ?>"  title='Display the Ticket Manager for this Date Time' style="position:relative; top:5px; margin:0 0 0 10px; font-size:.9em; cursor:pointer;" >
								<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/tickets-1-16x16.png" width="16" height="16" alt="<?php _e('tickets left', 'event_espresso'); ?>"/>
							</a> */ ?>
							<a class='clone-date-time dtm-inp-btn' rel='<?php echo $row; ?>' title='<?php _e('Clone this Event Date and Time', 'event_espresso'); ?>' style='position:relative; top:5px; margin:0 0 0 10px; font-size:.9em; cursor:pointer;'>
								<img src='<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/clone-trooper-16x16.png' width='16' height='16' alt='<?php _e('clone', 'event_espresso'); ?>'/>
							</a>
					<?php if ( $row != 1 ) : ?>
							<a class='remove-xtra-time dtm-inp-btn' rel='<?php echo $row; ?>' title='<?php _e('Remove this Event Date and Time', 'event_espresso'); ?>' style='position:relative; top:6px; margin:0 0 0 10px; font-size:.9em; cursor:pointer;'>
								<img src='<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/trash-16x16.png' width='16' height='16' alt='<?php _e('trash', 'event_espresso'); ?>'/>
							</a>
					<?php endif; ?>
						</td>
						
					</tr>
					<?php $row++; ?>
				<?php endforeach; // ($times as $time)  ?>
			</table>
			<br class="clear"/>
			<!--<input type="button" id="add-time" class="button dtm-inp-btn" value="<?php _e('Add Additional Time', 'event_espresso'); ?>" />-->
			<a id="add-new-date-time" class="button dtm-inp-btn" ><?php _e('Add New Dates &amp; Times', 'event_espresso'); ?></a>
			<br class="clear"/><br/>
		</div>

		
		<div id="timezones-datetimes-dv" class="">

			<?php if ((!isset($org_options['use_event_timezones']) || $org_options['use_event_timezones'] ) && $espresso_premium === TRUE) : ?>
				<span class="run-in"> <?php _e('Current Time:', 'event_espresso'); ?> </span>
				<span class="current-date"> <?php echo date(get_option('date_format')) . ' ' . date(get_option('time_format')); ?></span>
				<?php echo apply_filters('filter_hook_espresso_help', 'current_time_info'); ?>
				<a class="change-date-time" href="options-general.php" target="_blank"><?php _e('Change timezone and date format settings?', 'event_espresso'); ?></a>
			<?php endif; ?>

			<?php if (!empty($org_options['use_event_timezones']) && $espresso_premium === TRUE) : ?>
				<h6> <?php _e('Event Timezone:', 'event_espresso') ?> </h6>
				<?php echo eventespresso_ddtimezone($this->_event->id) ?>
			<?php endif; ?>

		</div>

		<input  type="hidden" name="datetime_IDs" value="<?php echo serialize( $datetime_IDs ); ?>"/>
		<input  type="hidden" id="process_datetimes" name="process_datetimes" value="1"/>


		<?php if ($espresso_premium) : ?>
			<script type="text/javascript">
				(function($) {
					var counter = <?php echo $row; ?>;

					$('#add-new-date-time').live('click', function(){
						var newRow = "<tr valign='top' id='event-dates-and-times-row-"+counter+"'><td><div class='small-screen-table-label'><?php echo __('Event Starts on', 'event_espresso') ?></div><input id='event-start-"+counter+"' name='event_datetimes["+counter+"][evt_start]' type='text' class='dtm-es-picker dtm-inp medium-text' value=''/><input name='event-start-row-<?php echo $row; ?>' type='hidden' value='"+counter+"'/><input id='is-primary-"+counter+"' name='event_datetimes["+counter+"][is_primary]' type='hidden' value='' /></td><td><div class='small-screen-table-label'><?php echo __('Event Ends on', 'event_espresso') ?></div><input id='event-end-"+counter+"' name='event_datetimes["+counter+"][evt_end]' type='text' class='dtm-ee-picker dtm-inp medium-text' value=''/><input name='event-end-row-<?php echo $row; ?>' type='hidden' value='"+counter+"'/></td><td><div class='small-screen-table-label'><?php echo __('Registration Starts on', 'event_espresso') ?></div><input id='reg-start-"+counter+"' name='event_datetimes["+counter+"][reg_start]' type='text' class='dtm-rs-picker dtm-inp medium-text' value='' /><input name='reg-start-row-<?php echo $row; ?>' type='hidden' value='"+counter+"'/></td><td><div class='small-screen-table-label'><?php echo __('Registration Ends on', 'event_espresso') ?></div><input id='reg-end-"+counter+"' name='event_datetimes["+counter+"][reg_end]' type='text' class='dtm-re-picker dtm-inp medium-text' value='' /><input name='reg-end-row-<?php echo $row; ?>' type='hidden' value='"+counter+"'/></td><?php /* DO NOT DELETE - NEW FEATURE IN PROGRESS <?php if ($org_options['time_reg_limit']) : ?><td><input type='text' id='reg-limit-"+counter+"' name='event_datetimes["+counter+"][reg_limit]' class='small-text dtm-inp' style='text-align:right;' value=''/></td><?php endif; // time_reg_limit   ?><td><input type='text' id='tckts-left-"+counter+"' name='event_datetimes["+counter+"][tckts_left]' class='small-text dtm-inp' style='text-align:right;' value=''/></td> */ ?><td><div class=small-screen-table-label><?php echo __('Actions', 'event_espresso') ?></div><a class='clone-date-time dtm-inp-btn' rel='"+counter+"' title='<?php _e('Clone this Event Date and Time', 'event_espresso'); ?>' style='position:relative; top:6px; margin:0 0 0 10px; font-size:.9em; cursor:pointer;'><img src='<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/clone-trooper-16x16.png' width='16' height='16' alt='<?php _e('clone', 'event_espresso'); ?>'/></a><a class='remove-xtra-time dtm-inp-btn' rel='"+counter+"' title='<?php _e('Remove this Event Time', 'event_espresso'); ?>' style='position:relative; top:6px; margin:0 0 0 10px; font-size:.9em; cursor:pointer;'><img src='<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/trash-16x16.png' width='16' height='16' alt='<?php _e('trash', 'event_espresso'); ?>'/></a></td></tr>";
						$('#event-dates-and-times tr:last').after( newRow );
						counter++;
					});
					

					$('.clone-date-time').live('click', function(){				
						var cloneRow = $(this).attr('rel');					
						var newRow = "<tr valign='top' id='event-dates-and-times-row-"+counter+"'><td><div class='small-screen-table-label'><?php echo __('Event Starts on', 'event_espresso') ?></div><input id='event-start-"+counter+"' name='event_datetimes["+counter+"][evt_start]' type='text' class='dtm-es-picker dtm-inp medium-text' value=''/><input name='event-start-row-<?php echo $row; ?>' type='hidden' value='"+counter+"'/><input id='is-primary-"+counter+"' name='event_datetimes["+counter+"][is_primary]' type='hidden' value='' /></td><td><div class='small-screen-table-label'><?php echo __('Event Ends on', 'event_espresso') ?></div><input id='event-end-"+counter+"' name='event_datetimes["+counter+"][evt_end]' type='text' class='dtm-ee-picker dtm-inp medium-text' value=''/><input name='event-end-row-<?php echo $row; ?>' type='hidden' value='"+counter+"'/></td><td><div class='small-screen-table-label'><?php echo __('Registration Starts on', 'event_espresso') ?></div><input id='reg-start-"+counter+"' name='event_datetimes["+counter+"][reg_start]' type='text' class='dtm-rs-picker dtm-inp medium-text' value='' /><input name='reg-start-row-<?php echo $row; ?>' type='hidden' value='"+counter+"'/></td><td><div class='small-screen-table-label'><?php echo __('Registration Ends on', 'event_espresso') ?></div><input id='reg-end-"+counter+"' name='event_datetimes["+counter+"][reg_end]' type='text' class='dtm-re-picker dtm-inp medium-text' value='' /><input name='reg-end-row-<?php echo $row; ?>' type='hidden' value='"+counter+"'/></td><?php /* DO NOT DELETE - NEW FEATURE IN PROGRESS <?php if ($org_options['time_reg_limit']) : ?><td><input type='text' id='reg-limit-"+counter+"' name='event_datetimes["+counter+"][reg_limit]' class='small-text dtm-inp' style='text-align:right;' value=''/></td><?php endif; // time_reg_limit   ?><td><input type='text' id='tckts-left-"+counter+"' name='event_datetimes["+counter+"][tckts_left]' class='small-text dtm-inp' style='text-align:right;' value=''/></td>  */ ?><td><div class=small-screen-table-label><?php echo __('Actions', 'event_espresso') ?></div><a class='clone-date-time dtm-inp-btn' rel='"+counter+"' title='<?php _e('Clone this Event Date and Time', 'event_espresso'); ?>' style='position:relative; top:6px; margin:0 0 0 10px; font-size:.9em; cursor:pointer;'><img src='<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/clone-trooper-16x16.png' width='16' height='16' alt='<?php _e('clone', 'event_espresso'); ?>'/></a><a class='remove-xtra-time dtm-inp-btn' rel='"+counter+"' title='<?php _e('Remove this Event Time', 'event_espresso'); ?>' style='position:relative; top:6px; margin:0 0 0 10px; font-size:.9em; cursor:pointer;'><img src='<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/trash-16x16.png' width='16' height='16' alt='<?php _e('trash', 'event_espresso'); ?>'/></a></td></tr>";
						$('#event-dates-and-times-row-'+cloneRow).after( newRow );
						$('#event-start-'+counter).val( $('#event-start-'+(cloneRow)).val() );
						$('#event-end-'+counter).val( $('#event-end-'+(cloneRow)).val() );
						$('#reg-start-'+counter).val( $('#reg-start-'+(cloneRow)).val() );
						$('#reg-end-'+counter).val( $('#reg-end-'+(cloneRow)).val() );
						<?php /* DO NOT DELETE - NEW FEATURE IN PROGRESS 
						$('#reg-limit-'+counter).val( $('#reg-limit-'+(cloneRow)).val() );
						$('#tckts-left-'+counter).val( $('#tckts-left-'+(cloneRow)).val() );
						  */ ?>
						counter++;
					});

					$('.remove-xtra-time').live("click", function(){
						var whichRow = '#event-dates-and-times-row-' + $(this).attr('rel');
						$(whichRow).remove();
						counter--;
					});

				})(jQuery);
			</script>
		<?php endif; // $espresso_premium 
	}





	public function pricing_metabox() {
		global $org_options;

		require_once(EVENT_ESPRESSO_INCLUDES_DIR . 'models/EEM_Price_Type.model.php');
		$PRT = EEM_Price_Type::instance();

		require_once(EVENT_ESPRESSO_INCLUDES_DIR . 'models/EEM_Price.model.php');
		$PRC = EEM_Price::instance();

		$show_no_event_price_msg = FALSE;		
		
		global $all_prices;
		if ( ! $all_prices = $PRC->get_all_event_prices_for_admin( $this->_event->id )) {
			$all_prices = array();
		}
		
		if ( empty( $all_prices[1] ) && empty( $all_prices[2] )) {
			$show_no_event_price_msg = TRUE;
		}
	//	 echo printr( $all_prices, '$all_prices' );

		foreach ($PRT->type as $type) {
			$all_price_types[] = array('id' => $type->ID(), 'text' => $type->name());
			if ( $type->is_global() ) {
				$global_price_types[ $type->ID() ] = $type;
			} else {
				$price_types[] = array('id' => $type->ID(), 'text' => $type->name());
			}						
		}
		//echo printr( $global_price_types, '$global_price_types' );
		
		$table_class = apply_filters('filter_hook_espresso_pricing_table_class_filter', 'event_editor_pricing');
		?>


		<div id="ticket-prices-dv" class="">

		<?php if ( $show_no_event_price_msg ) : ?>
			<div class="error">
				<p><?php _e('There are currently no Prices set for this Event. Please see the Event Pricing section for more details.', 'event_espresso'); ?></p>
			</div>	
			<div id="no-ticket-prices-msg-dv">
				<p><?php _e('Please enter at lease one Event Price for this Event, or one Default Event Price to ensure that this Event displays and functions properly. Default Event Prices can be set on the <a href="'. admin_url( 'admin.php?page=pricing' ) .'">Pricing Management</a> page.', 'event_espresso'); ?></p>
			</div>
		<?php endif; ?>

		<!--<h5 id="add-new-ticket-price-h5" ><?php _e('All Prices, Discounts and Surcharges that are Currently Active for This Event', 'event_espresso'); ?></h5>-->

		<table id="event_editor_pricing" width="100%" >
			<thead>
				<tr>
					<td class="event-price-tbl-hdr-type"><b><?php //_e('Type'); ?></b></td>
					<td class="event-price-tbl-hdr-order"><b><?php _e('Order', 'event_espresso'); ?></b></td>
					<td class="event-price-tbl-hdr-name"><b><?php _e('Name', 'event_espresso'); ?></b></td>
					<!--<td style="width:2.5%; text-align:center;"></td>-->
					<td class="event-price-tbl-hdr-amount"><b><?php _e('Amount', 'event_espresso'); ?></b></td>
					<!--<td style="width:1%; text-align:center;"></td>-->
					<td class="event-price-tbl-hdr-actions"></td>
					<td class="event-price-tbl-hdr-desc"></td>
				</tr>
			</thead>
			<?php 
		$counter = 1;
		foreach ( $all_prices as $price_type => $prices ) :
			foreach ( $prices as $price ) :
				if ( ! $price->deleted() ) :
					//echo printr( $price, '$price' );
					$disabled = ! $price->is_active() ? ' disabled="disabled"' : ''; 
					$disabled_class = ! $price->is_active() ? ' input-disabled' : ''; 
					$inactive = ! $price->is_active() ? '<span class="inactice-price">'.__('inactive price - edit advanced settings to reactivate', 'event_espresso').'</span>' : FALSE; 
					if ( $price->use_dates() ){
						$today = time();
						if ( $today < $price->start_date( FALSE ) ){
							$price_date_status = '<a title="'. __('This Event Price option is not yet active', 'event_espresso') . '"><img src="'.EVENT_ESPRESSO_PLUGINFULLURL.'images/icons/timer-pending-16x22.png" width="16" height="22" alt="'. __('This Event Price option is not yet active', 'event_espresso') . '" class="price-date-status-img"/></a>';					
						} elseif ( $today > $price->start_date( FALSE ) && $today < $price->end_date( FALSE ) ) {
							$price_date_status = '<a title="'. __('This Event Price option is currently active', 'event_espresso') . '"><img src="'.EVENT_ESPRESSO_PLUGINFULLURL.'images/icons/timer-active-16x22.png" width="16" height="22" alt="'. __('This Event Price option is currently active', 'event_espresso') . '" class="price-date-status-img"/></a>';					
						} else {
							$price_date_status = '<a title="'. __('This Event Price option has expired', 'event_espresso') . '"><img src="'.EVENT_ESPRESSO_PLUGINFULLURL.'images/icons/timer-expired-16x22.png" width="16" height="22" alt="'. __('This Event Price option has expired', 'event_espresso') . '" class="price-date-status-img"/></a>';
							$disabled = ' disabled="disabled"'; 
							$disabled_class = ' input-disabled'; 
							$inactive = '<span class="inactice-price">'.__('This Event Price option has expired - edit advanced settings to reactivate', 'event_espresso').'</span>';
						}
					} else {
						$price_date_status = '';
					}
					
			?>

				<tr>
					<td colspan="6">					
						<div id="edit-event-price-<?php echo $price->ID(); ?>" class="event-price-settings-dv hidden">

							<a class="cancel-event-price-btn" rel="<?php echo $price->ID(); ?>" ><?php _e('close', 'event_espresso'); ?></a>
							
							<h6><?php _e('Edit : ', 'event_espresso'); ?><?php echo $price->name(); ?></h6>
							<?php //echo printr( $price, '$price' ); ?>
							<table class="form-table" width="100%">
								<tbody>
								
									<tr valign="top">					
										<th><label for="edit-ticket-price-PRT_ID"><?php _e('Type', 'event_espresso'); ?></label></th>
										<td>
											<?php $select_name = 'edit_ticket_price['. $price->ID() .'][PRT_ID]'; ?>
											<?php echo EE_Form_Fields::select_input( $select_name, $all_price_types, $price->type(), 'id="edit-ticket-price-type-ID-'.$price->ID().'" style="width:auto;"', 'edit-ticket-price-input' ); ?>
											<span class="description">&nbsp;&nbsp;<?php _e('Whether this is an Event Price, Discount, or Surcharge.', 'event_espresso'); ?></span>
											<input name="edit_ticket_price[<?php echo $price->ID()?>][PRC_ID]" type="hidden" value="<?php echo $price->ID()?>"/>
											<input name="edit_ticket_price[<?php echo $price->ID()?>][EVT_ID]" type="hidden" value="<?php echo $this->_event->id?>"/>
											<?php $price_type = isset( $global_price_types[$price->type()] ) ? $global_price_types[$price->type()]->is_global() : FALSE; ?>
											<input name="edit_ticket_price[<?php echo $price->ID()?>][PRT_is_global]" type="hidden" value="<?php echo $price_type?>"/>
											<input name="edit_ticket_price[<?php echo $price->ID()?>][PRC_overrides]" type="hidden" value="<?php echo $price->overrides()?>"/>
											<input name="edit_ticket_price[<?php echo $price->ID()?>][PRC_deleted]" id="edit-ticket-price-PRC_deleted-<?php echo $price->ID(); ?>" type="hidden" value="<?php echo $price->deleted()?>"/>										
											<input name="edit_ticket_price[<?php echo $price->ID(); ?>][PRC_order]" id="edit-ticket-price-PRC_order-<?php echo $price->ID(); ?>" type="hidden"  value="<?php echo $PRT->type[$price->type()]->order(); ?>"/>										
											<input name="edit_ticket_price[<?php echo $price->ID()?>][use_quick_edit]" type="hidden" value="1"/>										
										</td>
									</tr>
									
									<tr valign="top">
										<th><label for="edit-ticket-price-PRC_name"><?php _e('Name', 'event_espresso'); ?></label></th>
										<td>
											<input class="edit-ticket-price-input regular-text" type="text" id="edit-ticket-price-PRC_name-<?php echo $price->ID(); ?>" name="edit_ticket_price[<?php echo $price->ID(); ?>][PRC_name]" value="<?php echo $price->name(); ?>"/>
											<span class="description">&nbsp;&nbsp;<?php _e('The name that site visitors will see for this Price.', 'event_espresso'); ?></span>
										</td>
									</tr>
									
									<tr valign="top">
										<th><label for="edit-ticket-price-PRC_desc"><?php _e('Description', 'event_espresso'); ?></label></th>
										<td>
											<input class="edit-ticket-price-input widefat" type="text" id="edit-ticket-price-PRC_desc-<?php echo $price->ID(); ?>" name="edit_ticket_price[<?php echo $price->ID(); ?>][PRC_desc]" value="<?php echo stripslashes( $price->desc() ); ?>"/><br/>
											<span class="description"><?php _e('A brief description for this Price. More for your benefit, as it is currently not displayed to site visitors.', 'event_espresso'); ?></span>
										</td>							
									</tr>
									
									<tr valign="top">
										<th><label for="edit-ticket-price-PRC_amount"><?php _e('Amount', 'event_espresso'); ?></label></th>
										<td>
											<?php $price_amount =  ($PRT->type[$price->type()]->is_percent()) ? number_format( $price->amount(), 1 ) : number_format( $price->amount(), 2 ); ?>
											<input class="edit-ticket-price-input small-text" type="text" id="edit-ticket-price-PRC_amount-<?php echo $price->ID(); ?>" name="edit_ticket_price[<?php echo $price->ID(); ?>][PRC_amount]" style="text-align:right;" value="<?php echo $price_amount; ?>"/>
											<span class="description">&nbsp;&nbsp;<?php _e('The dollar or percentage amount for this Price.', 'event_espresso'); ?></span>
										</td>
									</tr>
									
	<?php /* DO NOT DELETE - NEW FEATURE IN PROGRESS 		
									<tr valign="top">
										<th><label for="edit-ticket-price-PRC_reg_limit"><?php _e('Registration Limit', 'event_espresso'); ?></label></th>
										<td>
											<input type="text" id="edit-ticket-price-PRC_reg_limit-<?php echo $price->ID(); ?>" name="edit_ticket_price[<?php echo $price->ID(); ?>][PRC_reg_limit]" class="edit-ticket-price-input small-text" style="text-align:right;" value="<?php echo $price->reg_limit(); ?>"/>
											<span class="description">&nbsp;&nbsp;<?php _e('The maximum number of attendees that can be registratered at this Price Level. Leave blank for no limit.', 'event_espresso'); ?></span>
										</td>
									</tr>
									
									<tr valign="top">
										<th><label for="edit-ticket-price-PRC_tckts_left"><?php _e('Tickets Left', 'event_espresso'); ?></label></th>
										<td>
											<input type="text" id="edit-ticket-price-PRC_tckts_left-<?php echo $price->ID(); ?>" name="edit_ticket_price[<?php echo $price->ID(); ?>][PRC_tckts_left]" class="edit-ticket-price-input small-text" style="text-align:right;" value="<?php echo $price->tckts_left(); ?>"/>
											<span class="description">&nbsp;&nbsp;<?php _e('The number of tickets left, or available spaces, at this Price Level. This field is computed and any changes made to this quatity will have no affect. To change the number of Tickets LEft you will need to manually add Attendees via the Registrations Admin page.', 'event_espresso'); ?></span>
										</td>
									</tr>
	  */ ?>			
									
									<tr valign="top" class="edit-ticket-price-use-dates-tbl-row">
										<th><label><?php _e('Triggered by Date', 'event_espresso'); ?></label></th>
										<td>
											<?php $price_uses_dates = $price->use_dates();?>
											<label class="edit-ticket-price-radio-lbl">
												<?php $checked = $price_uses_dates == 1 ? ' checked="checked"' : '';?>
												<input name="edit_ticket_price[<?php echo $price->ID(); ?>][PRC_use_dates]" class="edit-ticket-price-use-dates-yes edit-ticket-price-input etp-radio" type="radio" value="1"<?php echo $checked;?> style="margin-right:5px;"/>
												<?php _e('Yes', 'event_espresso');?>
											</label>
											<label class="edit-ticket-price-radio-lbl">
												<?php $checked = $price_uses_dates == 0 ? ' checked="checked"' : '';?>
												<input name="edit_ticket_price[<?php echo $price->ID(); ?>][PRC_use_dates]" class="edit-ticket-price-use-dates-no edit-ticket-price-input etp-radio" type="radio" value="0"<?php echo $checked;?> style="margin-right:5px;"/>
												<?php _e('No', 'event_espresso');?>
											</label>
											<span class="description"><?php _e( 'If set to "Yes", then you will be able to set the dates for when this price will become active / inactive.', 'event_espresso' ); ?></span>
										</td>
									</tr>
				
									<tr valign="top">
										<th>
											<div class="edit-ticket-price-dates">
												<label for="edit_ticket_price[<?php echo $price->ID(); ?>][PRC_start_date]"><?php _e('Start Date', 'event_espresso'); ?></label>
											</div>
										</th>
										<td>
											<div class="edit-ticket-price-dates">
												<input id="edit-ticket-price-PRC_start_date-<?php echo $price->ID(); ?>" name="edit_ticket_price[<?php echo $price->ID(); ?>][PRC_start_date]" type="text" class="datepicker edit-ticket-price-input" value="<?php echo $price->start_date(); ?>" />
												<span class="description">&nbsp;&nbsp;<?php _e( sprintf( 'If the "Triggered by Date" field above is set to "Yes", then this is the date that this Event Price would become active and displayed.' ), 'event_espresso'); ?></span>
											</div>
										</td>
									</tr>
				
									<tr valign="top">
										<th>
											<div class="edit-ticket-price-dates">
											<label for="edit_ticket_price[<?php echo $price->ID(); ?>][PRC_end_date]"><?php _e('End Date', 'event_espresso'); ?></label>
											</div>
										</th>
										<td>
											<div class="edit-ticket-price-dates">
											<input id="edit-ticket-price-PRC_end_date-<?php echo $price->ID(); ?>" name="edit_ticket_price[<?php echo $price->ID(); ?>][PRC_end_date]" type="text" class="datepicker edit-ticket-price-input" value="<?php echo $price->end_date(); ?>" />
											<span class="description">&nbsp;&nbsp;<?php _e( sprintf( 'If "Triggered by Date" is set to "Yes", then this is the date that this Event Price would become inactive and no longer displayed.' ), 'event_espresso'); ?></span>
											</div>
										</td>
									</tr>			
									<?php if ( $counter > 1 ) : ?>
									<tr valign="top">
										<th><label><?php _e('Active', 'event_espresso'); ?></label></th>
										<td>
											<label class="edit-ticket-price-radio-lbl">
												<input class="edit-ticket-price-input" type="radio" name="edit_ticket_price[<?php echo $price->ID(); ?>][PRC_is_active]" value="1" style="margin-right:5px;" <?php echo $price->is_active() ? 'checked="checked"' : '' ?> />
												<?php _e('Yes', 'event_espresso');?>
											</label>
											<label class="edit-ticket-price-radio-lbl">
												<input class="edit-ticket-price-input" type="radio" name="edit_ticket_price[<?php echo $price->ID(); ?>][PRC_is_active]" value="0" style="margin-right:5px;" <?php echo ! $price->is_active() ? 'checked="checked"' : '' ?> />
												<?php _e('No', 'event_espresso');?>
											</label>
											<span class="description"><?php _e('Whether this Price is currently being used and displayed on the site.', 'event_espresso'); ?></span>
										</td>
									</tr>
									<?php else : ?>
											<input name="edit_ticket_price[<?php echo $price->ID()?>][PRC_is_active]" type="hidden" value="1"/>										
									<?php endif; ?>
								</tbody>
							</table>
				
						</div>
					</td>
				</tr>
				
				<tr>
					<td colspan="6">
						<div id="event-price-<?php echo $price->ID(); ?>" class="event-price-dv">
							<table class="ticket-price-quick-edit-tbl" width="100%">
								<tr>
								
									<td class="type-column ticket-price-quick-edit-column"> 
										<?php
										 //echo $PRT->type[$price->type()]->name(); 
										 //$select_name = 'edit_ticket_price['. $price->ID() .'][PRT_ID]'; 
										//echo EE_Form_Fields::select_input( $select_name, $all_price_types, $price->type(), 'id="quick-edit-ticket-price-type-ID" ', 'edit-ticket-price-input quick-edit' ); 
										?>
										<div class="small-screen-table-label"><?php echo __('Type', 'event_espresso') ?></div>
										<span><?php echo $PRT->type[$price->type()]->name() . ' ' . $price_date_status; ?></span>
									</td> 
									
									<td class="order-column ticket-price-quick-edit-column"> 
										<?php //echo $PRT->type[$price->type()]->order(); ?>
										<div class="small-screen-table-label"><?php echo __('Order', 'event_espresso') ?></div>
										<input class="edit-ticket-price-input quick-edit small-text jst-rght<?php echo $disabled_class;?>" type="text" id="quick-edit-ticket-price-PRC_order-<?php echo $price->ID(); ?>" name="quick_edit_ticket_price[<?php echo $price->ID(); ?>][PRC_order]" value="<?php echo $PRT->type[$price->type()]->order(); ?>"<?php echo $disabled; ?>/>							
									</td> 
									
									<td class="name-column ticket-price-quick-edit-column"> 
										<?php //echo $price->name(); ?>
										<div class="small-screen-table-label"><?php echo __('Name', 'event_espresso') ?></div>
										<input class="edit-ticket-price-input quick-edit regular-text<?php echo $disabled_class;?>" type="text" id="quick-edit-ticket-price-PRC_name-<?php echo $price->ID(); ?>" name="quick_edit_ticket_price[<?php echo $price->ID(); ?>][PRC_name]" value="<?php echo $price->name(); ?>" <?php echo $disabled; ?>/>
									</td> 
									
									<!--<td class="cur-sign-column ticket-price-quick-edit-column"> 
										<div class="small-screen-table-label"><?php echo __('Amount', 'event_espresso') ?></div>
										<?php echo ($PRT->type[$price->type()]->is_percent()) ?  '' : $org_options['currency_symbol']; ?>
									</td>--> 
									
									<td class="amount-column ticket-price-quick-edit-column"> 
										<div class="small-screen-table-label"><?php echo __('Amount', 'event_espresso') ?></div>
										<span class="cur-sign jst-rght"><?php echo ($PRT->type[$price->type()]->is_percent()) ?  '' : $org_options['currency_symbol']; ?></span>
										<?php $price_amount =  ($PRT->type[$price->type()]->is_percent()) ? number_format( $price->amount(), 1 ) : number_format( $price->amount(), 2 ); ?>
										<input class="edit-ticket-price-input quick-edit small-text jst-rght<?php echo $disabled_class;?>" type="text" id="quick-edit-ticket-price-PRC_amount-<?php echo $price->ID(); ?>" name="quick_edit_ticket_price[<?php echo $price->ID(); ?>][PRC_amount]" value="<?php echo $price_amount; ?>"<?php echo $disabled; ?>/>
										<span class="percent-sign jst-left"><?php echo ($PRT->type[$price->type()]->is_percent()) ? '%' : ''; ?></span>
									</td> 
									
									<!--<td class="percent-column ticket-price-quick-edit-column"> 
										<?php echo ($PRT->type[$price->type()]->is_percent()) ? '%' : ''; ?>
									</td> -->
									
	<?php /* DO NOT DELETE - NEW FEATURE IN PROGRESS
									<td class="tckts-left-column" style="width:7.5%; height:2.5em; text-align:right;"> 
										<input class="edit-tickets-left-input quick-edit" type="text" id="quick-edit-ticket-price[<?php echo $price->ID(); ?>][PRC_tckts_left]" name="quick_edit_ticket_price[<?php echo $price->ID(); ?>][PRC_tckts_left]" style="width:100%;text-align:right;" value="<?php echo $price->tckts_left(); ?>" disabled="disabled"/>
									</td> 
	 */ ?>
									
									<td class="edit-column ticket-price-quick-edit-column">
										<div class="small-screen-table-label"><?php echo __('Actions', 'event_espresso') ?></div>									
										<?php /* DO NOT DELETE - NEW FEATURE IN PROGRESS
										<a class='display-price-tickets-left-lnk display-ticket-manager' data-reveal-id="ticket-manager-dv" rel="<?php echo $price->ID(); ?>"  title='Display the Ticket Manager for this Event' style="cursor:pointer;" >
											<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/tickets-1-16x16.png" width="16" height="16" alt="<?php _e('tickets left', 'event_espresso'); ?>"/>
										</a>
										 */ ?>
										<a class='edit-event-price-lnk evt-prc-btn' rel="<?php echo $price->ID(); ?>"  title="Edit Advanced Settings for this Event Price">
											<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/settings-16x16.png" width="16" height="16" alt="<?php _e('edit', 'event_espresso'); ?>"/>
										</a>
										<a class='delete-event-price-lnk evt-prc-btn' rel="<?php echo $price->ID(); ?>" title="Delete this Event Price" >
											<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/trash-16x16.png" width="16" height="16" alt="<?php _e('trash', 'event_espresso'); ?>"/>
										</a>
									</td>

									
									<td class="desc-column ticket-price-quick-edit-column"> 
										<div class="small-screen-table-label"><?php echo __('Description', 'event_espresso') ?></div>		
										<?php //echo $price->desc(); ?>
										<!--<input class="edit-ticket-price-input quick-edit widefat" type="text" id="quick-edit-ticket-price[<?php echo $price->ID(); ?>][PRC_desc]" name="quick_edit_ticket_price[<?php echo $price->ID(); ?>][PRC_desc]" value="<?php echo $price->desc(); ?>" style="width:100%;"/>-->
										<span class="description"><?php echo $inactive ? $inactive : stripslashes( $price->desc() ); ?></span>
									</td> 
									

								</tr>
							</table>
						</div>
					</td>				
				</tr>

				<?php
				endif;
				$counter++;
			endforeach;
		endforeach;
			?>
			</table>
			<br/>

			<div id="add-new-ticket-price-dv" class="hidden">
		
				<h5 id="add-new-ticket-price-h5" ><?php _e('Add New Event Price', 'event_espresso'); ?></h5>
					
				<table class="form-table">
					<tbody>
					
						<tr valign="top">					
							<th><label for="new-ticket-price-PRT_ID"><?php _e('Type', 'event_espresso'); ?></label></th>
							<td>
								<?php echo EE_Form_Fields::select_input( 'new_ticket_price[PRT_ID]', $price_types, 2, 'id="new-ticket-price-type-ID"', 'add-new-ticket-price-input' ); ?>
								<span class="description">&nbsp;&nbsp;<?php _e('Whether this is an Event Price, Discount, or Surcharge.', 'event_espresso'); ?></span>
								<input id="new_ticket_price-EVT_ID" name="new_ticket_price[EVT_ID]" type="hidden" value="<?php echo $this->_event->id; ?>" />
							</td>
						</tr>
						
						<tr valign="top">
							<th><label for="new-ticket-price-PRC_name"><?php _e('Name', 'event_espresso'); ?></label></th>
							<td>
								<input class="add-new-ticket-price-input regular-text" type="text" id="new-ticket-price-PRC_name" name="new_ticket_price[PRC_name]" value=""/>
								<span class="description">&nbsp;&nbsp;<?php _e('The name that site visitors will see for this Price.', 'event_espresso'); ?></span>
							</td>
						</tr>
						
						<tr valign="top">
							<th><label for="new-ticket-price-PRC_desc"><?php _e('Description', 'event_espresso'); ?></label></th>
							<td>
								<textarea class="add-new-ticket-price-input regular-text" type="text" id="new-ticket-price[PRC_desc]" name="new_ticket_price[PRC_desc]" cols="100" rows="1" ></textarea><br/>
								<span class="description"><?php _e('A brief description for this Price. More for your benefit, as it is currently not displayed to site visitors.', 'event_espresso'); ?></span>
							</td>							
						</tr>
						
						<tr valign="top">
							<th><label for="new-ticket-price-PRC_amount"><?php _e('Amount', 'event_espresso'); ?></label></th>
							<td>
								<input class="add-new-ticket-price-input small-text" type="text" id="new-ticket-price[PRC_amount]" name="new_ticket_price[PRC_amount]" style="text-align:right;" value=""/>
								<span class="description">&nbsp;&nbsp;<?php _e('The dollar or percentage amount for this Price.', 'event_espresso'); ?></span>
							</td>
						</tr>

	<?php /* DO NOT DELETE - NEW FEATURE IN PROGRESS 
						<tr valign="top">
							<th><label for="new-ticket-price-PRC_amount"><?php _e('Registration Limit', 'event_espresso'); ?></label></th>
							<td>
								<input type="text" id="new_ticket_price[PRC_reg_limit]" name="new_ticket_price[PRC_reg_limit]" class="add-new-ticket-price-input small-text" style="text-align:right;" value=""/>
								<span class="description">&nbsp;&nbsp;<?php _e('The maximum number of attendees that can be registratered at this Price Level. Leave blank for no limit.', 'event_espresso'); ?></span>
							</td>
						</tr>
	*/ ?>
						
						<tr valign="top">
							<th><label><?php _e('Triggered by Date', 'event_espresso'); ?></label></th>
							<td>
								<label class="edit-ticket-price-radio-lbl">
									<input class="add-new-ticket-price-input" type="radio" name="new_ticket_price[PRC_use_dates]" value="1" style="margin-right:5px;">
									<?php _e('Yes', 'event_espresso');?>
								</label>
								<label class="edit-ticket-price-radio-lbl">
									<input class="add-new-ticket-price-input" type="radio" name="new_ticket_price[PRC_use_dates]" value="0" style="margin-right:5px;" checked="checked" />
									<?php _e('No', 'event_espresso');?>
								</label>
								<span class="description"><?php _e( 'If set to "Yes", then you will be able to set the dates for when this price will become active / inactive.', 'event_espresso' ); ?></span>
							</td>
						</tr>

						<tr valign="top">
							<th><label for="new_ticket_price[PRC_start_date]"><?php _e('Start Date', 'event_espresso'); ?></label></th>
							<td>
								<input id="new-ticket-price[PRC_start_date]" name="new_ticket_price[PRC_start_date]" type="text" class="datepicker add-new-ticket-price-input" value="" />
								<span class="description">&nbsp;&nbsp;<?php _e( sprintf( 'If the "Triggered by Date" field above is set to "Yes", then this is the date that this Event Price would become active and displayed for this Event.' ), 'event_espresso'); ?></span>
							</td>
						</tr>

						<tr valign="top">
							<th><label for="new_ticket_price[PRC_end_date]"><?php _e('End Date', 'event_espresso'); ?></label></th>
							<td>
								<input id="new-ticket-price[PRC_end_date]" name="new_ticket_price[PRC_end_date]" type="text" class="datepicker add-new-ticket-price-input" value="" />
								<span class="description">&nbsp;&nbsp;<?php _e( sprintf( 'If "Triggered by Date" is set to "Yes", then this is the date that this Event Price would become inactive and no longer displayed for this Event.' ), 'event_espresso'); ?></span>
							</td>
						</tr>			

						<tr valign="top">
							<th><label><?php _e('Active', 'event_espresso'); ?></label></th>
							<td>
								<label class="edit-ticket-price-radio-lbl">
									<input class="add-new-ticket-price-input" type="radio" name="new_ticket_price[PRC_is_active]" value="1" style="margin-right:5px;" checked="checked" />
									<?php _e('Yes', 'event_espresso');?>
								</label>
								<label class="edit-ticket-price-radio-lbl">
									<input class="add-new-ticket-price-input" type="radio" name="new_ticket_price[PRC_is_active]" value="0" style="margin-right:5px;" />
									<?php _e('No', 'event_espresso');?>
								</label>
								<span class="description"><?php _e('Whether this Price is currently being used and displayed on the site.', 'event_espresso'); ?></span>
							</td>
						</tr>
						
					</tbody>
				</table>
				<br/>

				<div>

					<div>
						<a id="hide-add-new-ticket-price" class="cancel-event-price-btn hidden" rel="add-new-ticket-price" style="left:230px;"><?php _e('cancel', 'event_espresso');?></a>
					</div>

				</div>
				
			</div>
			
			<a id="display-add-new-ticket-price" class="button-secondary display-the-hidden" rel="add-new-ticket-price">
				<?php _e('Add New Event Price', 'event_espresso'); ?>
			</a>
			<br class="clear"/><br/>
			
			<input id="edited-ticket-price-IDs" name="edited_ticket_price_IDs" type="hidden" value="" />
			
		</div>
		<?php
	}





	public function venue_metabox() {
		global $org_options, $espresso_premium;
		$values = array(
				array('id' => true, 'text' => __('Yes', 'event_espresso')),
				array('id' => false, 'text' => __('No', 'event_espresso'))
		);
		?>
		<div class="inside">
			<table class="form-table">
				<tr>
					<?php
					if (function_exists('espresso_venue_dd') && $org_options['use_venue_manager'] && $espresso_premium) {
						$ven_type = 'class="use-ven-manager"';
						?>
					<td valign="top" <?php echo $ven_type ?>><fieldset id="venue-manager">
								<legend><?php echo __('Venue Information', 'event_espresso') ?></legend>
								<?php if (!espresso_venue_dd()) : ?>
									<p class="info">
										<b><?php _e('You have not created any venues yet.', 'event_espresso'); ?></b>
									</p>
									<p><a href="admin.php?page=event_venues"><?php echo __('Add venues to the Venue Manager', 'event_espresso') ?></a></p>
								<?php else: ?>
									<?php echo espresso_venue_dd($this->_event->venue_id) ?>
								<?php endif; ?>
							</fieldset>
						</td>
						<?php
					} else {
						$ven_type = 'class="manual-venue"';
						?>
						<td valign="top" <?php echo $ven_type ?>>
							<fieldset>
								<legend>
									<?php _e('Physical Location', 'event_espresso'); ?>
								</legend>
								<p>
									<label for="phys-addr">
										<?php _e('Address:', 'event_espresso'); ?>
									</label>
									<input size="20" id="phys-addr" tabindex="100"  type="text"  value="<?php echo $this->_event->address ?>" name="address" />
									<label for="phys-addr-2"><?php _e('Address 2:', 'event_espresso'); ?></label>
									<input size="20" id="phys-addr-2" tabindex="101"  type="text"  value="<?php echo $this->_event->address2 ?>" name="address2" />
									<label for="phys-city">
										<?php _e('City:', 'event_espresso'); ?>
									</label>
									<input size="20" id="phys-city" tabindex="102"  type="text"  value="<?php echo $this->_event->city ?>" name="city" />
									<label for="phys-state">
										<?php _e('State:', 'event_espresso'); ?>
									</label>
									<input size="20" id="phys-state" tabindex="103"  type="text"  value="<?php echo $this->_event->state ?>" name="state" />
									<label for="zip-postal">
										<?php _e('Zip/Postal Code:', 'event_espresso'); ?>
									</label>
									<input size="20" id="zip-postal"  tabindex="104"  type="text"  value="<?php echo $this->_event->zip ?>" name="zip" />
									<label for="phys-country">
										<?php _e('Country:', 'event_espresso'); ?>
									</label>
									<input size="20" id="phys-country" tabindex="105"  type="text"  value="<?php echo $this->_event->country ?>" name="country" />
									<br/>
									<?php _e('Google Map Link (for email):', 'event_espresso'); ?>
									<br />
									<?php echo $this->_event->google_map_link; ?> </p>
							</fieldset>
						</td>
						<td valign="top" <?php echo $ven_type; ?>>

								<legend>
									<?php _e('Venue Information', 'event_espresso'); ?>
								</legend>
								<p>
									<label for="ven-title">
										<?php _e('Title:', 'event_espresso'); ?>
									</label>
									<input size="20"id="ven-title" tabindex="106"  type="text"  value="<?php echo stripslashes_deep($this->_event->venue_title) ?>" name="venue_title" />
								</p>
								<p>
									<label for="ven-website">
										<?php _e('Website:', 'event_espresso'); ?>
									</label>
									<input size="20" id="ven-website" tabindex="107"  type="text"  value="<?php echo stripslashes_deep($this->_event->venue_url) ?>" name="venue_url" />
								</p>
								<p>
									<label for="ven-phone">
										<?php _e('Phone:', 'event_espresso'); ?>
									</label>
									<input size="20" id="ven-phone" tabindex="108"  type="text"  value="<?php echo stripslashes_deep($this->_event->venue_phone) ?>" name="venue_phone" />
								</p>
								<p>
									<label for="ven-image">
										<?php _e('Image:', 'event_espresso'); ?>
									</label>
									<input size="20" id="ven-image" tabindex="110"  type="text"  value="<?php echo stripslashes_deep($this->_event->venue_image) ?>" name="venue_image" />
								</p>
							<?php } ?>
					</td>
					<td valign="top" <?php echo $ven_type ?>>
						<fieldset id="virt-location">
							<legend>
								<?php _e('Virtual Location', 'event_espresso'); ?>
							</legend>
							<p>
								<label for="virt-phone" style="display:inline-block; width:100px;">
									<?php _e('Phone:', 'event_espresso'); ?>
								</label>
								<input size="20" id="virt-phone" type="text" tabindex="111" value="<?php echo $this->_event->phone ?>" name="phone" />
							</p>
							<p>
								<label for="url-event" style="display:inline-block; width:100px; vertical-align:top;">
									<?php _e('URL of Event:', 'event_espresso'); ?>
								</label>
								<textarea id="url-event" cols="30" rows="4" tabindex="112"  name="virtual_url"><?php echo stripslashes_deep($this->_event->virtual_url) ?></textarea>
							</p>
							<p>
								<label for="call-in-num" style="display:inline-block; width:100px;">
									<?php _e('Call in Number:', 'event_espresso'); ?>
								</label>
								<input id="call-in-num" size="20" tabindex="113"  type="text"  value="<?php echo stripslashes_deep($this->_event->virtual_phone) ?>" name="virtual_phone" />
							</p>
						</fieldset>
					</td>
				</tr>

			</table>
			<p>
				<label for="enable_for_gmap">
					<?php _e('Enable event address in Google Maps? ', 'event_espresso') ?>
				</label>
				<?php echo EE_Form_Fields::select_input('enable_for_gmap', $values, isset($this->_event->event_meta['enable_for_gmap']) ? $this->_event->event_meta['enable_for_gmap'] : '', 'id="enable_for_gmap"') ?> </p>
		</div>
		<?php
	}





	public function email_metabox() {
		//todo: this needs to be moved into the the Messages_Admin_Page.core.php once the events_admin has been reworked to be in the new admin system.

		//let's get the active messengers (b/c messenger objects have the active message templates)
		$EEM_controller = new EE_Messages;
		$active_messengers = $EEM_controller->get_active_messengers();
		$tabs = array();

		//get content for active messengers
		foreach ( $active_messengers as $name => $messenger ) {
			$tabs[$name] = $messenger->get_messenger_admin_page_content('events', 'edit', array('event' => $this->_event) );
		}


		require_once EVENT_ESPRESSO_PLUGINFULLPATH . '/helpers/EE_Tabbed_Content.helper.php';
		//we want this to be tabbed content so let's use the EE_Tabbed_Content::display helper.
		$tabbed_content = EE_Tabbed_Content::display($tabs);
		if ( is_wp_error($tabbed_content) ) {
			$tabbed_content = $tabbed_content->get_error_message();
		}
		
		echo $tabbed_content;
	}





	public function quick_overview_metabox() {
		?>
		<div class="submitbox" id="submitpost">
			<div id="minor-publishing">
			
				<div id="minor-publishing-actions" class="clearfix">
					<div id="preview-action"> <a class="preview button" href="<?php echo espresso_reg_url($this->_event->id, $this->_event->slug); ?>" target="_blank" id="event-preview" tabindex="5">
							<?php _e('View Event', 'event_espresso'); ?>
						</a>
						<input type="hidden" name="event-preview" id="event-preview" value="" />
					</div>
					<div id="copy-action"> <a class="preview button" href="admin.php?page=events&amp;action=copy_event&event_id=<?php echo $this->_event->id ?>" id="post-copy" tabindex="4" onclick="return confirm('<?php _e('Are you sure you want to copy ' . $this->_event->event_name . '?', 'event_espresso'); ?>')">
							<?php _e('Duplicate Event', 'event_espresso'); ?>
						</a>
						<input  type="hidden" name="event-copy" id="event-copy" value="" />
					</div>
				</div>
				<!-- /minor-publishing-actions -->

				<div id="misc-publishing-actions">
					<div class="misc-pub-section curtime" id="visibility"> <span id="timestamp">
							<?php _e('Start Date', 'event_espresso'); ?>
							<b> <?php echo event_date_display($this->_event->start_date); ?></b> </span> </div>
					<div class="misc-pub-section">
						<label for="post_status">
							<?php _e('Current Status:', 'event_espresso'); ?>
						</label>
						<span id="post-status-display"> <?php echo $this->_event->status['display']; ?></span></div>

					<div class="misc-pub-section" id="visibility">
						<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/group.png" width="16" height="16" alt="<?php _e('View Attendees', 'event_espresso'); ?>" />
						<a href="admin.php?page=attendees&amp;event_admin_reports=list_attendee_payments&amp;event_id=' . $this->_event->id . '"><?php _e('Attendees', 'event_espresso'); ?></a>:
						<?php echo get_number_of_attendees_reg_limit($this->_event->id, 'num_attendees_slash_reg_limit'); ?>
					</div>

					<?php $class = apply_filters('filter_hook_espresso_event_editor_email_attendees_class', 'misc-pub-section'); ?>

					<div class="misc-pub-section <?php echo $class; ?>" id="visibility2">
						<a href="admin.php?page=attendees&amp;event_admin_reports=event_newsletter&amp;event_id=<?php echo $this->_event->id ?>" title="<?php _e('Email Event Attendees', 'event_espresso'); ?>">
							<img src="<?php echo EVENT_ESPRESSO_PLUGINFULLURL ?>images/icons/email_go.png" width="16" height="16" alt="<?php _e('Newsletter', 'event_espresso'); ?>" />
						</a>
						<a href="admin.php?page=attendees&amp;event_admin_reports=event_newsletter&amp;event_id=<?php echo $this->_event->id ?>" title="<?php _e('Email Event Attendees', 'event_espresso'); ?>">
							<?php _e('Email Event Attendees', 'event_espresso'); ?>
						</a>
					</div>
					<?php do_action('action_hook_espresso_event_editor_overview_add', $this->_event); ?>
				</div>			
				<!-- /misc-publishing-actions -->
			</div>
			<!-- /minor-publishing -->

			<div id="delete-action">
				<?php /*if ($event->recurrence_id > 0) : ?>
					<a class="submitdelete deletion" href="admin.php?page=events&amp;action=delete_recurrence_series&recurrence_id=<?php echo $event->recurrence_id ?>" onclick="return confirm('<?php _e('Are you sure you want to delete ' . $event->event_name . '?', 'event_espresso'); ?>')">
						<?php _e('Delete all events in this series', 'event_espresso'); ?>
					</a>
				<?php else:*/ ?>
					<a class="submitdelete deletion" href="admin.php?page=events&amp;action=delete&event_id=<?php echo $event->id ?>" onclick="return confirm('<?php _e('Are you sure you want to delete ' . $event->event_name . '?', 'event_espresso'); ?>')">
						<?php _e('Delete Event', 'event_espresso'); ?>
					</a>
				<?php //endif; ?>
			</div>
			<br/>
			<?php
				echo $this->_template_args['save_buttons'];
			?>
			

		</div>
		<!-- /submitpost -->
		<?php
	}





	public function primary_questions_group_meta_box() {
		$question_groups = $this->_event->question_groups;
		?>
		<div class="inside">
			<p><strong>
					<?php _e('Question Groups', 'event_espresso'); ?>
				</strong><br />
				<?php _e('Add a pre-populated', 'event_espresso'); ?>
				<a href="admin.php?page=form_groups" target="_blank">
					<?php _e('group', 'event_espresso'); ?>
				</a>
				<?php _e('of', 'event_espresso'); ?>
				<a href="admin.php?page=form_builder" target="_blank">
					<?php _e('questions', 'event_espresso'); ?>
				</a>
				<?php _e('to your event. The personal information group is required for all events.', 'event_espresso'); ?>
			</p>
			<?php
			if ($this->_event->num_rows > 0) {
				reset($this->_event->q_groups);
				$html = '';
				foreach ($this->_event->q_groups as $question_group) {
					$question_group_id = $question_group->id;
					$group_name = $question_group->group_name;
					$checked = (is_array($question_groups) && array_key_exists($question_group_id, $question_groups)) || $question_group->system_group == 1 ? ' checked="checked" ' : '';
					$visibility = $question_group->system_group == 1 ? 'style="visibility:hidden"' : '';
					$group_id = isset($group_id) ? $group_id : '';
					$html .= '<p id="event-question-group-' . $question_group_id . '"><input value="' . $question_group_id . '" type="checkbox" ' . $checked . $visibility . ' name="question_groups[' . $question_group_id . ']" ' . $checked . ' /> <a href="admin.php?page=form_groups&amp;action=edit_group&amp;group_id=' . $question_group_id . '" title="edit" target="_blank">' . $group_name . '</a></p>';
				}
				if ($this->_event->num_rows > 10) {
					$top_div = '<div style="height:250px;overflow:auto;">';
					$bottom_div = '</div>';
				} else {
					$top_div = '';
					$bottom_div = '';
				}
				$html = $top_div . $html . $bottom_div;
				echo $html;
			} else {
				echo __('There seems to be a problem with your questions. Please contact support@eventespresso.com', 'event_espresso');
			}
			do_action('action_hook_espresso_event_editor_questions_notice');
			?>
		</div>
		<?php
	}





	public function categories_meta_box() {
		$event_id = $this->_event->id;
		global $wpdb;
		?>
		<div class="inside">
			<?php
			$sql = "SELECT * FROM " . EVENTS_CATEGORY_TABLE;
			$sql = apply_filters('filter_hook_espresso_event_editor_categories_sql', $sql);
			$event_categories = $wpdb->get_results($sql);
			$num_rows = $wpdb->num_rows;
			if ($num_rows > 0) {
				if ($num_rows > 10) {
					echo '<div style="height:250px;overflow:auto;">';
				}
				foreach ($event_categories as $category) {
					$category_id = $category->id;
					$category_name = $category->category_name;

					$in_event_categories = $wpdb->get_results("SELECT * FROM " . EVENTS_CATEGORY_REL_TABLE . " WHERE event_id='" . $event_id . "' AND cat_id='" . $category_id . "'");
					foreach ($in_event_categories as $in_category) {
						$in_event_category = $in_category->cat_id;
					}
					if (empty($in_event_category))
						$in_event_category = '';
					?>
					<p id="event-category-<?php echo $category_id; ?>">
						<label for="in-event-category-<?php echo $category_id; ?>" class="selectit">
							<input value="<?php echo $category_id; ?>" type="checkbox" name="event_category[]" id="in-event-category-<?php echo $category_id; ?>"<?php echo ($in_event_category == $category_id ? ' checked="checked"' : "" ); ?>/>
							<?php echo $category_name; ?>
						</label>
					</p>
					<?php
				}
				if ($num_rows > 10) {
					echo '</div>';
				}
			} else {
				_e('No Categories', 'event_espresso');
			}
			?>
			<p>
				<a href="admin.php?page=event_categories" target="_blank">
					<?php _e('Manage Categories', 'event_espresso'); ?>
				</a>
			</p>
		</div>
		<?php
	}







	/** end metaboxes **/
	/*******************/





	/**
	 * _delete_event
	 * deletes a given event
	 *
	 * @access protected
	 * @return void 
	 */
	protected function _delete_event() {}





	/**
	 * _insert_or_update_event
	 * depending on argument, will handling inserting or updating event
	 *
	 * @access protected
	 * @param  bool $new_event true = insert, false = update
	 * @return void
	 */
	protected function _insert_or_update_event($new_event) {}




	/**
	 * _trash_or_restore_event
	 * depending on argument, will handle trashing or restoring event
	 *
	 * @access protected
	 * @param  bool $trash TRUE = trash, FALSE = restore
	 * @return void
	 * @todo: Currently the events table doesn't allow for trash/restore.  When we move to new events model we'll allow for it.
	 */
	protected function _trash_or_restore_event($trash) {}





	/**
	 * _view_report
	 * Shows the report page for events
	 * @return string html for the report page
	 */
	protected function _view_report() {
		$this->_admin_page_title .= $this->_get_action_link_or_button('add_event', 'add', array(), 'button add-new-h2');
		$this->_template_args['admin_page_content'] = 'in here';
		$this->display_admin_page_with_sidebar();
	}






	/**
	 * _events_export
	 * Will export all (or just the given event) to a Excel compatible file.
	 * 
	 * @access protected
	 * @return file 
	 */
	protected function _events_export() {

		//todo: I don't like doing this but it'll do until we modify EE_Export Class.
		$new_request_args = array(
			'export' => 'report',
			'action' => 'event',
			'event_id' => $_REQUEST['EVT_ID'],
			);
		$_REQUEST = array_merge( $_REQUEST, $new_request_args);
		if (file_exists(EVENT_ESPRESSO_INCLUDES_DIR . 'classes/EE_Export.class.php')) {
			require_once(EVENT_ESPRESSO_INCLUDES_DIR . 'classes/EE_Export.class.php');
			$EE_Export = EE_Export::instance();
			$EE_Export->export();
		}
	}




	/**
	 * _payment_export
	 * Will export payments for events to an excel file (or for given events)
	 * @return file?
	 */
	protected function _payment_export() {

		//todo: I don't like doing this but it'll do until we modify EE_Export Class.
		$new_request_args = array(
			'export' => 'report',
			'action' => 'payment',
			'type' => 'excel',
			'event_id' => $_REQUEST['EVT_ID'],
			);
		$_REQUEST = array_merge( $_REQUEST, $new_request_args );
		if (file_exists(EVENT_ESPRESSO_INCLUDES_DIR . 'classes/EE_Export.class.php')) {
			require_once(EVENT_ESPRESSO_INCLUDES_DIR . 'classes/EE_Export.class.php');
			$EE_Export = EE_Export::instance();
			$EE_Export->export();
		}

	}



	/**
	 * _import_events
	 * This handles displaying the screen and running imports for importing events.
	 * 	
	 * @return string html
	 */
	protected function _import_events() {

		//first check if we've got an incoming import
		if (isset($_REQUEST['import'])) {
			if (file_exists(EVENT_ESPRESSO_INCLUDES_DIR . 'classes/EE_Import.class.php')) {
				require_once(EVENT_ESPRESSO_INCLUDES_DIR . 'classes/EE_Import.class.php');
				$EE_Import = EE_Import::instance();
				$EE_Import->import();
			}
		}

		include( EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/functions/csv_uploader.php' );
		$import_what = 'Event Details';
		$import_intro = 'If you have a previously exported list of Event Details in a Comma Separated Value (CSV) file format, you can upload the file here: ';
		$page = 'events';
		$content = espresso_csv_uploader($import_what, $import_intro, $page);

		$this->_admin_page_title .= $this->_get_action_link_or_button('add_event', 'add', array(), 'button add-new-h2');
		$this->_template_args['admin_page_content'] = $content;	
		$this->display_admin_page_with_sidebar();
	}




	/**
	 * _get_events()
	 * This method simply returns all the events (for the given _view and paging)
	 *
	 * @access public
	 *
	 * @param int $per_page count of items per page (20 default);
	 * @param int $current_page what is the current page being viewed.
	 * @param bool $count if TRUE then we just return a count of ALL events matching the given _view.  If FALSE then we return an array of event objects that match the given _view and paging parameters.
	 * @return array an array of event objects.
	 */
	public function get_events($per_page = 10, $current_page = 1, $count = FALSE) {
		global $wpdb, $org_options;

		$offset = ($current_page-1)*$per_page; 
		$limit = $count ? '' : ' LIMIT ' . $offset . ',' . $per_page;
		$orderby = isset($_REQUEST['orderby']) ? " ORDER BY " . $_REQUEST['orderby'] : " ORDER BY e.event_name";
		$order = isset($_REQUEST['order']) ? " " . $_REQUEST['order'] : " DESC";

		if (isset($_REQUEST['month_range'])) {
			$pieces = explode(' ', $_REQUEST['month_range'], 3);
			$month_r = !empty($pieces[0]) ? $pieces[0] : '';
			$year_r = !empty($pieces[1]) ? $pieces[1] : '';
		}
		
		$sql = '';
		$sql = $count ? "SELECT COUNT(e.id) " : "SELECT e.id as event_id, e.event_name, e.slug, e.event_identifier, e.reg_limit, e.is_active, e.recurrence_id, e.event_meta, e.event_status, dtt.*";

		if ( !$count ) {

			//venue information
			if (isset($org_options['use_venue_manager']) && $org_options['use_venue_manager'] ) {
				$sql .= ", v.name AS venue_title, v.address AS venue_address, v.address2 AS venue_address2, v.city AS venue_city, v.state AS venue_state, v.zip AS venue_zip, v.country AS venue_country ";
			} else {
				$sql .= ", e.venue_title, e.phone, e.address, e.address2, e.city, e.state, e.zip, e.country ";
			}
		}

		$sql .= " FROM " . EVENTS_DETAIL_TABLE . " e ";

	
		$sql .= " LEFT JOIN " . ESP_DATETIME . " dtt ON dtt.EVT_ID = e.id ";

		if (isset($org_options['use_venue_manager']) && $org_options['use_venue_manager']) {
			$sql .= " LEFT JOIN " . EVENTS_VENUE_REL_TABLE . " vr ON vr.event_id = e.id ";
			$sql .= " LEFT JOIN " . EVENTS_VENUE_TABLE . " v ON v.id = vr.venue_id ";
		}
		


		if ( isset($_REQUEST['category_id']) && $_REQUEST['category_id'] != '') {
			$sql .= " LEFT JOIN " . EVENTS_CATEGORY_REL_TABLE . " cr ON cr.event_id = e.id ";
			$sql .= " LEFT JOIN " . EVENTS_CATEGORY_TABLE . " c ON c.id = cr.cat_id ";
		}

		$sql .= ' WHERE ';

		if ( !$count ) {
			$sql .= "dtt.DTT_is_primary = '1' AND ";
		}

		$sql .= ( isset($_REQUEST['event_status']) && ($_REQUEST['event_status'] != '') ) ? "e.event_status = '" . $_REQUEST['event_status'] . "' " : "e.event_status != 'D' ";
		$sql .= isset($_REQUEST['category_id']) && $_REQUEST['category_id'] != '' ? " AND c.id = '" . $_REQUEST['category_id'] . "' " : '';

		if ( isset($_REQUEST['month_range']) && $_REQUEST['month_range'] != '' ) {
			$sql .= " AND dtt.DTT_EVT_start BETWEEN '" . strtotime($year_r . '-' . $month_r . '-01') . "' AND '" . strtotime($year_r . '-' . $month_r . '-31') . "' ";
		} elseif (isset($_REQUEST['today']) && $_REQUEST['today'] == 'true') {
			$sql .= " AND dtt.DTT_EVT_start BETWEEN '" . strtotime(date('Y-m-d') . ' 0:00:00') . "' AND '" . strtotime(date('Y-m-d') . ' 23:59:59') . "' ";
		} elseif (isset($_REQUEST['this_month']) && $_REQUEST['this_month'] == 'true') {
			$sql .= " AND dtt.DTT_EVT_start BETWEEN '" . strtotime($this_year_r . '-' . $this_month_r . '-01') . "' AND '" . strtotime($this_year_r . '-' . $this_month_r . '-' . $days_this_month) . "' ";
		}

		$sql .= !$count ? " GROUP BY e.id " . $orderby . $order . $limit : '';

		//todo: This needs to be prepared to protect agains injection attacks... but really the whole stinking query could probably be better layed out.
		
		if ( $count ) {
			$events = $wpdb->get_var( $sql );
		} else {
			$events = $wpdb->get_results( $sql );
		}

		return $events;
	}



	


	/**
	 * espresso_event_months_dropdown			
	 * This is copied (and slightly modified) from the same named function in EE core legacy.
	 * 
	 * @param  string $current_value current month range value
	 * @return string                dropdown listing month/year selections for events.
	 */
	public function espresso_event_months_dropdown($current_value = '') {
		global $wpdb;
		$SQL = "SELECT DTT_EVT_start as e_date FROM " . $wpdb->prefix . "esp_datetime GROUP BY YEAR(FROM_UNIXTIME(DTT_EVT_start)), MONTH(FROM_UNIXTIME(DTT_EVT_start))";

		$dates = $wpdb->get_results($SQL);

		if ($wpdb->num_rows > 0) {
			echo '<select name="month_range" class="wide">';
			echo '<option value="">' . __('Select a Month/Year', 'event_espresso') . '</option>';
			foreach ($dates as $row) {
				$option_date = date_i18n( 'M Y', $row->e_date );
				echo '<option value="' . $option_date . '"';
				echo $option_date == $current_value ? ' selected="selected=selected"' : '';
				echo '>' . $option_date . '</option>' . "\n";
			}
			echo "</select>";
		} else {
			_e('No Results', 'event_espresso');
		}
	}

} //end class Events_Admin_Page