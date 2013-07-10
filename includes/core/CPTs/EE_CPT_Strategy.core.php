<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
/**
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package			Event Espresso
 * @ author			Seth Shoultes
 * @ copyright		(c) 2008-2011 Event Espresso  All Rights Reserved.
 * @ license			http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link					http://www.eventespresso.com
 * @ version		 	4.0
 *
 * ------------------------------------------------------------------------
 *
 * CPT_Strategy
 *
 * @package			Event Espresso
 * @subpackage	/core/
 * @author				Brent Christensen 
 *
 * ------------------------------------------------------------------------
 */
class EE_CPT_Strategy extends EE_BASE {


	/**
	 * 	EE_Registry Object
	 *	@var 	EE_Registry	$EE	
	 * 	@access 	protected
	 */
	protected $EE = NULL;

	/**
	 * $CPT - the current page, if it utilizes CPTs
	 *	@var 	array	
	 * 	@access 	protected
	 */
	protected $CPT = NULL;

	/**
	 * 	@var 	array 	$_CPTs
	 *  @access 	protected
	 */
	protected $_CPTs = array();

	/**
	 * 	@var 	array 	$_CPT_endpoints
	 *  @access 	protected
	 */
	protected $_CPT_endpoints = array();

	/**
	 * $model_objects - array of objects instantiated via EE models
	 *	@var 	array	
	 * 	@access 	protected
	 */
	protected $model_objects = array();



	
	/**
	 * 	class constructor
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function __construct( EE_Registry $EE ) {
		// EE registry
		$this->EE = $EE;
		// get CPT data
		$this->_CPTs = EE_Register_CPTs::get_CPTs();
		$this->_CPT_endpoints = $this->_set_CPT_endpoints();
		// load EE_Request_Handler
		add_action( 'wp_loaded', array( $this, 'apply_CPT_Strategy' ), 2 );
	}



	/**
	 * 	_set_CPT_endpoints - add CPT "slugs" to array of default espresso "pages"
	 *
	 * 	@access private
	 * 	@return array
	 */
	private function _set_CPT_endpoints() {
		$_CPT_endpoints = array();
		//printr( $CPTs, '$CPTs  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
		if ( is_array( $this->_CPTs )) {
			foreach ( $this->_CPTs as $CPT_type => $CPT ) {
				$_CPT_endpoints [ $CPT['singular_slug'] ] = $CPT_type;
				$_CPT_endpoints [ $CPT['plural_slug'] ] = $CPT_type;
			}
		}
		return $_CPT_endpoints;
	}


	/**
	 * 	_get_espresso_CPT_endpoints 
	 *
	 * 	@access public
	 * 	@return array
	 */
	public function get_CPT_endpoints() {
		return $this->_CPT_endpoints;
	}	


	/**
	 * 	apply_CPT_Strategy
	 *
	 * 	@access public
	 * 	@return array
	 */
	public function apply_CPT_Strategy() {
		// if current page is espresso page, then this is it's post name
		if ( $espresso_page = $this->EE->REQ->is_espresso_page() ) {
			// but is this espresso page a CPT endpoint ?
			if ( isset( $this->_CPT_endpoints[ $espresso_page ] )) {
				
				$this->CPT = $this->_CPTs[ $this->_CPT_endpoints[ $espresso_page ] ];
				$this->CPT['post_type'] = $this->_CPT_endpoints[ $espresso_page ];
				$this->CPT['requested_page'] = $espresso_page;
//				printr( $this->CPT, '$this->CPT  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
				if ( isset( $this->CPT['singular_name'] )) {
					// get list of CPTS via CPT Model
					$CPT_Model = 'EEM_' . $this->CPT['singular_name'];
					$this->CPT['tables'] = $CPT_Model::instance()->get_tables();
					// is there a Meta Table for this CPT?
					$this->CPT['meta_table'] = isset( $this->CPT['tables'][ $this->CPT['singular_name'] . '_Meta' ] ) ? $this->CPT['tables'][ $this->CPT['singular_name'] . '_Meta' ] : FALSE;
					// creates classname like:  EE_CPT_Event_Strategy
					$CPT_Strategy_class_name = 'EE_CPT_' . $this->CPT['singular_name'] . '_Strategy';
					 $this->EE->load_file ( EE_CORE . 'CPTs' . DS, $CPT_Strategy_class_name, 'core', FALSE, FALSE );
					// instantiate
					$CPT_Strategy = new $CPT_Strategy_class_name( $this->EE, $this->CPT );
//					printr( $CPT_Strategy, '$CPT_Strategy  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
					add_filter( 'posts_fields', array( $this, 'posts_fields' ));
					add_filter( 'posts_join',	array( $this, 'posts_join' ));
					add_filter( 'get_' . $this->CPT['post_type'] . '_metadata', array( $CPT_Strategy, 'get_EE_post_type_metadata' ), 1, 4 );

				}				
			}
		}
	}



	/**
	 * 	posts_fields
	 *
	 *  @access 	public
	 *  @return 	string
	 */
	public function posts_fields( $SQL ) {
		// does this CPT have a meta table ?
		if ( isset( $this->CPT['meta_table'] )) {
			global $wpdb;
			$SQL .= ', ' . $this->CPT['meta_table']->get_table_name() . '.* ' ;
		}
		return $SQL;
	}



	/**
	 * 	posts_join
	 *
	 *  @access 	public
	 *  @return 	string
	 */
	public function posts_join( $SQL ) {
		// does this CPT have a meta table ?
		if ( isset( $this->CPT['meta_table'] )) {
			global $wpdb;
			$SQL .= ' LEFT JOIN ' . $this->CPT['meta_table']->get_table_name() . ' ON (' . $this->CPT['meta_table']->get_table_name() . '.' . $this->CPT['meta_table']->get_fk_on_table() . ' = ' . $wpdb->posts . '.ID) ';
		}
		return $SQL;
	}






}






/**
 * ------------------------------------------------------------------------
 *
 * EE_CPT_Default_Strategy
 *
 * @package			Event Espresso
 * @subpackage	/core/
 * @author				Brent Christensen 
 *
 * ------------------------------------------------------------------------
 */
class EE_CPT_Default_Strategy {


	/**
	 * 	EE_Registry Object
	 *	@var 	EE_Registry	$EE	
	 * 	@access 	protected
	 */
	protected $EE = NULL;

	/**
	 * $CPT - the current page, if it utilizes CPTs
	 *	@var 	object	
	 * 	@access 	protected
	 */
	protected $CPT = NULL;



	
	/**
	 * 	class constructor
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function __construct( EE_Registry $EE, $CPT ) {
		$this->EE = $EE;
		$this->CPT = $CPT;
		//printr( $this->CPT, '$this->CPT  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
		add_filter( 'pre_get_posts', array( $this, 'pre_get_posts' ), 999 );
		add_action( 'loop_start', array( $this, 'loop_start' ), 1 );
	}





	/**
	 * 	pre_get_posts
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function pre_get_posts(  $WP_Query  ) {
		//printr( $WP_Query, '$WP_Query  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
		if ( ! $WP_Query->is_main_query() && ! $WP_Query->is_archive() ) {
			return;
		}
//		$WP_Query->set( 'post_type', array( $this->CPT['post_type'] ));
//		$WP_Query->set( 'fields', 'ids' );
		return $WP_Query;
	}





	/**
	 * 	wp
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function loop_start( $WP_Query ) {
//		$EVT = $this->EE->load_model( 'Event' );
//		$EVT_IDs = array();
//		foreach( $WP_Query->posts as $WP_Post ) {
//			$EVT_IDs[] = $WP_Post->ID;
//		}
//		$events = $EVT->get_all( array( 0 =>array( 'EVT_ID' => array( 'IN', $EVT_IDs ), 'Event_Datetime.EVD_primary' => 1 ), 'force_join' =>array( 'Datetime' )));
//		printr( $WP_Query, '$WP_Query  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
//		printr( $EVT_IDs, '$EVT_IDs  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
//		printr( $events, '$events  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );

	}




	/**
	 * 	get_EE_post_type_metadata
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function get_EE_post_type_metadata( $meta_value = NULL, $post_id, $meta_key, $single ) {

		return $meta_value;

	}


}






// End of file EE_CPT_Strategy.core.php
// Location: /core/EE_CPT_Strategy.core.php