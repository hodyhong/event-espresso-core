<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
/**
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package			Event Espresso
 * @ author				Seth Shoultes
 * @ copyright		(c) 2008-2011 Event Espresso  All Rights Reserved.
 * @ license			http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link					http://www.eventespresso.com
 * @ version		 	4.0
 *
 * ------------------------------------------------------------------------
 *
 * Question Group Model
 *
 * @package			Event Espresso
 * @subpackage		includes/models/
 * @author				Michael Nelson
 *
 * ------------------------------------------------------------------------
 */
require_once ( EVENT_ESPRESSO_INCLUDES_DIR . 'models/EEM_Soft_Delete_Base.model.php' );

class EEM_Question_Option extends EEM_Soft_Delete_Base {

  	// private instance of the Attendee object
	private static $_instance = NULL;

	/**
	 *		This funtion is a singleton method used to instantiate the EEM_Attendee object
	 *
	 *		@access public
	 *		@return EEM_Question_Option instance
	 */	
	public static function instance(){
	
		// check if instance of EEM_Attendee already exists
		if ( self::$_instance === NULL ) {
			// instantiate Espresso_model 
			self::$_instance = new self();
		}
		// EEM_Attendee object
		return self::$_instance;
	}

	protected function __construct(){
		$this->singular_item = __('Question Option','event_espresso');
		$this->plural_item = __('Question Options','event_espresso');
		
//		$this->_fields_settings=array(
//				'QSO_ID'=>new EE_Model_Field('Question Option ID', 'primary_key', false, null, null, null),
//				'QSO_value'=>new EE_Model_Field('Question Option Key Value', 'plaintext', false, '', null, null),
//				'QSO_text'=>new EE_Model_Field('Question Option Display Text', 'simplehtml', false, '', null, null),
//				'QST_ID'=>new EE_Model_Field('Related Question ID', 'foreign_key', false, null, null, 'Question'),
//				'QSO_deleted'=>new EE_Model_Field('Whether the option has been deleted', 'deleted_flag', false, false, null, null)
//			);
//		$this->_related_models=array(
//				'Question'=>new EE_Model_Relation('belongsTo', 'Question', 'QST_ID')
//			);
		$this->_tables = array(
			'Question_Option'=>new EE_Primary_Table('esp_question_option','QSG_ID')
		);
		$this->_fields = array(
			'Question_Option'=>array(
					'QSO_ID'=>new EE_Primary_Key_Int_Field('QSO_ID', 'Question OPtion ID', false, 0),
					'QSO_name'=>new EE_Simple_HTML_Field('QSO_ID', 'Question Option Name', false, ''),
					'QST_ID'=>new EE_Foreign_Key_Int_Field('QST_ID', 'Question ID', false, 0, 'Question'),
					'QSO_deleted'=>new EE_Trashed_Flag_Field('QSO_deleted', 'Flag indicating Option was trashed', false, false)
				)
		);
		$this->_model_relations = array(
			'Question'=>new EE_Belongs_To_Relation()
		);
		
		parent::__construct();
	}
}
// End of file EEM_Question_Option.model.php
// Location: /includes/models/EEM_Question_Option.model.php
