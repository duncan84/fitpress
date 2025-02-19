<?php
/*
Plugin Name: FitPress
Version: 0.7-alpha
Description: Publish FitBit statistics on WordPress
Author: Duncan Bell, Daniel Walmsley
Author URI: https://duncanbell.ca
Plugin URI: https://github.com/duncan84/fitpress
Text Domain: fitpress
Domain Path: /languages
*/

define( 'FITPRESS_CLIENT_STATE_KEY', 'this should be replace prior to uploading' );

class FitPress {
	// singleton class pattern:
	protected static $instance = NULL;
	public static function get_instance() {
		NULL === self::$instance and self::$instance = new self;
		return self::$instance;
	}

	function __construct() {
		// hook activation and deactivation for the plugin
		add_action('init', array($this, 'init'));
	}

	function init() {
		add_action('admin_enqueue_scripts', array($this, 'fitpress_init_styles'));
		add_action('admin_menu', array($this, 'fitpress_settings_page'));
		add_action('admin_init', array($this, 'fitpress_register_settings'));
		add_action('show_user_profile', array($this, 'fitpress_linked_accounts_print'));
		add_action('admin_post_fitpress_auth', array($this, 'fitpress_auth'));
		add_action('admin_post_fitpress_auth_callback', array($this, 'fitpress_auth_callback'));
		add_action('admin_post_fitpress_auth_unlink', array($this, 'fitpress_auth_unlink'));
		// add_shortcode( 'fitbit', array($this, 'fitpress_shortcode') );
		add_shortcode( 'fitpress_profile', array($this, 'fitpress_linked_accounts') );
		add_shortcode( 'heartrate', array($this, 'fitpress_shortcode_heartrate') );
		add_shortcode( 'fp_profile', array($this, 'fitpress_shortcode_profile') );
		add_shortcode( 'fp_goals', array($this, 'fitpress_shortcode_goals') );
		add_shortcode( 'fp_badges', array($this, 'fitpress_shortcode_badges') );
		add_shortcode( 'fp_activities', array($this, 'fitpress_shortcode_activities') );
		add_shortcode( 'fp_nutrition_info', array($this, 'fitpress_shortcode_nutrition') );
		add_shortcode( 'fp_distance', array($this,'fitpress_shortcode_distance'));
		add_shortcode( 'fp_calories', array($this,'fitpress_shortcode_calories'));
		add_shortcode( 'fp_bmi', array($this,'fitpress_shortcode_bmi'));
		add_shortcode( 'fp_weight', array($this,'fitpress_shortcode_weight'));
		add_shortcode( 'fp_fat', array($this,'fitpress_shortcode_fat'));

		
		add_shortcode( 'steps', array($this, 'fitpress_shortcode_steps') );
		wp_register_script( 'jsapi', 'https://www.google.com/jsapi' );
		add_action( 'wp_enqueue_scripts', array($this, 'fitpress_scripts') );
	}
	function fitpress_scripts() {
		wp_enqueue_script( 'jsapi' );
        wp_register_style( 'fitpress', plugins_url( 'fitpress/fitpress.css' ) );
        wp_enqueue_style( 'fitpress' );
	}

	/**
	 * Shortcodes 
	 **/ 
	function fitpress_shortcode_profile( $atts ){
		if (is_user_logged_in()){
			$user_id = get_current_user_id();
			$fitpress_credentials = get_user_meta( $user_id, 'fitpress_credentials', true );
			if ($fitpress_credentials){
				$atts = $this->fitpress_shortcode_base( $atts );

				$fitbit = $this->get_fitbit_client();
				try {
					$output="";
					$result = $fitbit->get_current_user_info($date);
					$name=$result->fullName;
					$aboutMe=$result->aboutMe;
					$avatar=$result->avatar;
					$output = "<div class='fp-badge-container'>".
					"<span class='fp-title'>".$name."</span>";
					$output.="<div class='fp-badge'>";
					$output.="<img src='".$avatar."' alt='".$name."' />";
					$output.="<p class='fp-badge-title'>".$aboutMe."</p>";
					$output.="</div>";
					$output.="</div>";
					return $output;
				} catch(Exception $e) {
					return print_r($e->getMessage(), true);
				}
			}
		}
	}


	 //Activities
	function fitpress_shortcode_activities( $atts ){
		if (is_user_logged_in()){
			$user_id = get_current_user_id();
			$fitpress_credentials = get_user_meta( $user_id, 'fitpress_credentials', true );
			if ($fitpress_credentials){
				$atts = $this->fitpress_shortcode_base( $atts );

				$fitbit = $this->get_fitbit_client();
				try {
					$output="";
					$date = date('Y-m-d');
					$result = $fitbit->get_activities($date);
					$output = "<table>";
					$output .= "<tr><th>Calories Burned</th><td width='10'></td>".
								"<td>".$result->summary->caloriesBMR."</td></tr>";
					$output .= "<tr><th>Floors</th><td width='10'></td>".
								"<td>".$result->summary->floors."</td></tr>";
					$output .= "<tr><th>Steps</th><td width='10'></td>".
								"<td>".$result->summary->steps."</td></tr>";
					$output .= "<tr><th>Resting Heartrate</th><td width='10'></td>".
								"<td>".$result->summary->restingHeartRate."</td></tr>";
					$output .= "<tr><th>Distance</th><td width='10'></td>".
								"<td>".$result->summary->distances[0]->distance."</td></tr>";
					$output .= '</table>';
					return $output;
				} catch(Exception $e) {
					return print_r($e->getMessage(), true);
				}
			}
		}
	}

	 //badges
	function fitpress_shortcode_badges( $atts ){
		if (is_user_logged_in()){
			
			$user_id = get_current_user_id();
			$fitpress_credentials = get_user_meta( $user_id, 'fitpress_credentials', true );
			if ($fitpress_credentials){
				$atts = $this->fitpress_shortcode_base( $atts );

				$fitbit = $this->get_fitbit_client();
				try {
					$result = $fitbit->get_badges();
					$output="<div class='fp-badge-container'>".
							"<span class='fp-title'>Badges</span>";
					$total=count($result);
					if ($total>0){
						for ($i=0;$i<count($result);$i++){
							$row=$result[$i];
							$output.="<div class='fp-badge'>";
							$output.="<img src='".$row->image125px."' alt='".$row->description."' />";
							$output.="<p class='fp-badge-title'>".$row->name."</p>";
							$output.="</div>";
						}
					}
					else{
						$output.="<span class='fp-title'>No badges... yet!</span>";
					}
					$output.="</div>";
					return $output;
				} catch(Exception $e) {
					return print_r($e->getMessage(), true);
				}
			}
		}
	}
	 
	//[fp_goals]
	function fitpress_shortcode_goals( $atts ){
		if (is_user_logged_in()){
			
			$user_id = get_current_user_id();
			$fitpress_credentials = get_user_meta( $user_id, 'fitpress_credentials', true );
			if ($fitpress_credentials){
				$atts = $this->fitpress_shortcode_base( $atts );

				$fitbit = $this->get_fitbit_client();
				try {
					$result = $fitbit->get_goals("daily");
					$resultWeekly = $fitbit->get_goals("weekly");
					$output = "<table><tr><td></td><td width='10' rowspan='6'></td>".
					"<th>Daily Goals:</th><td width='10' rowspan='6'></td><th>Weekly Goals:</th></tr>";
					$output .= "<tr><th>Active Minutes</th><td>".$result->activeMinutes." minutes</td>";
					$output .= "<td>".$resultWeekly->activeMinutes." minutes</td></tr>";
					$output .= "<tr><th>Calories Out</th><td>".$result->caloriesOut."</td>";
					$output .= "<td>".$resultWeekly->caloriesOut."</td></tr>";
					$output .= "<tr><th>Distance</th><td>".$result->distance." mi</td>";
					$output .= "<td>".$resultWeekly->distance." mi</td></tr>";
					$output .= "<tr><th>Floors</th><td>".$result->floors." floors</td>";
					$output .= "<td>".$resultWeekly->floors." floors</td></tr>";
					$output .= "<tr><th>Steps</th><td>".$result->steps." steps</td>";
					$output .= "<td>".$resultWeekly->steps." steps</td></tr>";
					$output .= '</table>';
					return $output;
				} catch(Exception $e) {
					return print_r($e->getMessage(), true);
				}
			}
		}
	}

	//[heartrate]
	function fitpress_shortcode_heartrate( $atts ){
		if (is_user_logged_in()){
			$user_id = get_current_user_id();
			$fitpress_credentials = get_user_meta( $user_id, 'fitpress_credentials', true );
			if ($fitpress_credentials){
				$atts = $this->fitpress_shortcode_base( $atts );

				$fitbit = $this->get_fitbit_client();

				try {

					if (array_key_exists('date',$atts) && $atts['date']){
						if (is_string($atts['date'])){
							$result = $fitbit->get_heart_rate($atts['date']);
						}
						else{
							$result = $fitbit->get_heart_rate($atts['date']->format('Y-m-d'));
						}
					}
					else{
						$output.='generated';
						$result = $fitbit->get_heart_rate(date("Y-m-d"));
					}
					$output = "<table><tr><th>Heart Rate Zone</th><td width='10'</td><th>Minutes</th></tr>";
					foreach ($result->value->heartRateZones as $heartRateZone) {
						$name = $heartRateZone->name;
						$minutes = $heartRateZone->minutes;
						$output .= "<tr><td>{$name}</td><td></td><td>{$minutes} minutes</td></tr>";
					}
					$output .= '</table>';
					return $output;
				} catch(Exception $e) {
					return print_r($e->getMessage(), true);
				}
			}
		}
	}
	
	function fitpress_shortcode_nutrition($atts){
		if (is_user_logged_in()){
			$user_id=get_current_user_id();
			$fitpress_credentials = get_user_meta( $user_id, 'fitpress_credentials', true );
			if ($fitpress_credentials){
				$atts = $this->fitpress_shortcode_base( $atts );
				$fitbit = $this->get_fitbit_client();
				$date; 
				$data_type="caloriesIn";
				if (array_key_exists('data_type',$atts) && $atts['data_type']){
					$data_type=$atts['data_type'];
				}
				if (array_key_exists('date',$atts) && $atts['date']){
					if (is_string($atts['date'])){
						$date=$atts['date'];
					}
					else{
						$date=$atts['date']->format('Y-m-d');
					}
				}
				try{
					$nutrition_info = $fitbit->get_nutrition_series_log($date,$data_type);
					array_walk($nutrition_info, function (&$v, $k) { $v = array($v->dateTime, intval($v->value)); });
					$column="Calories";
					$title="'Calories per day'";
					$y_title="'Calories'";

					if ($data_type=="water"){
						$column="Water";
						$title="'Water per day'";
						$y_title="'Water'";

					}


					// add header
					array_unshift($nutrition_info, array('Date', $column));

					$nutrition_info_json = json_encode($nutrition_info);

					$output .= <<<ENDHTML
		<script type="text/javascript">
			google.load('visualization', '1.0', {'packages':['corechart', 'bar']});
			google.setOnLoadCallback(function() {
				var data = google.visualization.arrayToDataTable({$nutrition_info_json});
				var options = {
					title: {$title},
					hAxis: {
					title: 'Date',
					format: 'Y-m-d'
					},
					vAxis: {
					title: {$y_title}
					}
				};
				var chart = new google.visualization.ColumnChart(document.getElementById('chart_div_{$data_type}'));
				chart.draw(data, options);
			});

		</script>
		<div id="chart_div_{$data_type}"></div>
		ENDHTML;

					// $output = print_r($steps, true);
					return $output;
				} catch(Exception $e) {
					return print_r($e->getMessage(), true);
				}
			}
		}
	}
	//Body base request 

	function fitpress_load_body_series($atts, $data_type){

		if (is_user_logged_in()){
			$user_id = get_current_user_id();
			$fitpress_credentials = get_user_meta( $user_id, 'fitpress_credentials', true );
			if ($fitpress_credentials){
				$atts = $this->fitpress_shortcode_base( $atts );

				$fitbit = $this->get_fitbit_client();
				$data_title=ucwords($data_type);
				try {
					$output = '';

					if (array_key_exists('date',$atts) && $atts['date']){
						if (is_string($atts['date'])){
							$result = $fitbit->get_body_time_series($data_type, $atts['date'], '7d');
						}
						else{
							$result = $fitbit->get_body_time_series($data_type, $atts['date']->format('Y-m-d'), '7d');
						}
					}
					else{
						$output.='generated';
						$result = $fitbit->get_body_time_series($data_type, date("Y-m-d"), '7d');
					}
					array_walk($result, function (&$v, $k) { $v = array($v->dateTime, intval($v->value)); });

					// add header
					array_unshift($result, array('Date', $data_title));

					$result_json = json_encode($result);

					$output .= <<<ENDHTML
		<script type="text/javascript">
			google.load('visualization', '1.0', {'packages':['corechart', 'bar']});
			google.setOnLoadCallback(function() {
				var data = google.visualization.arrayToDataTable({$result_json});
				var options = {
					title: '{$data_title} per day',
					hAxis: {
					title: 'Date',
					format: 'Y-m-d'
					},
					vAxis: {
					title: '{$data_title}'
					}
				};
				var chart = new google.visualization.ColumnChart(document.getElementById('chart_div_body_{$data_type}'));
				chart.draw(data, options);
			});

		</script>
		<div id="chart_div_body_{$data_type}"></div>
		ENDHTML;

					// $output = print_r($distance, true);
					return $output;
				} catch(Exception $e) {
					return print_r($e->getMessage(), true);
				}
			}
		}
	}
	//[fp_bmi]
	function fitpress_shortcode_bmi($atts){
		return $this->fitpress_load_body_series($atts,"bmi");
	}
	//[fp_fat]
	function fitpress_shortcode_fat($atts){
		return $this->fitpress_load_body_series($atts,"fat");
	}
	//[fp_weight]
	function fitpress_shortcode_weight($atts){
		return $this->fitpress_load_body_series($atts,"weight");
	}

	//Activity base request
	function fitpress_load_activity_series($atts, $data_type){

		if (is_user_logged_in()){
			$user_id = get_current_user_id();
			$fitpress_credentials = get_user_meta( $user_id, 'fitpress_credentials', true );
			if ($fitpress_credentials){
				$atts = $this->fitpress_shortcode_base( $atts );

				$fitbit = $this->get_fitbit_client();
				$data_title=ucwords($data_type);
				try {
					$output = '';

					if (array_key_exists('date',$atts) && $atts['date']){
						if (is_string($atts['date'])){
							$result = $fitbit->get_time_series($data_type, $atts['date'], '7d');
						}
						else{
							$result = $fitbit->get_time_series($data_type, $atts['date']->format('Y-m-d'), '7d');
						}
					}
					else{
						$output.='generated';
						$result = $fitbit->get_time_series($data_type, date("Y-m-d"), '7d');
					}
					array_walk($result, function (&$v, $k) { $v = array($v->dateTime, intval($v->value)); });

					// add header
					array_unshift($result, array('Date', $data_title));

					$result_json = json_encode($result);

					$output .= <<<ENDHTML
		<script type="text/javascript">
			google.load('visualization', '1.0', {'packages':['corechart', 'bar']});
			google.setOnLoadCallback(function() {
				var data = google.visualization.arrayToDataTable({$result_json});
				var options = {
					title: '{$data_title} per day',
					hAxis: {
					title: 'Date',
					format: 'Y-m-d'
					},
					vAxis: {
					title: '{$data_title}'
					}
				};
				var chart = new google.visualization.ColumnChart(document.getElementById('chart_div_{$data_type}'));
				chart.draw(data, options);
			});

		</script>
		<div id="chart_div_{$data_type}"></div>
		ENDHTML;

					// $output = print_r($distance, true);
					return $output;
				} catch(Exception $e) {
					return print_r($e->getMessage(), true);
				}
			}
		}
	}

	//[steps]
	function fitpress_shortcode_steps( $atts ){
		return $this->fitpress_load_activity_series($atts,"steps");
	}
	
	//[distance]
	function fitpress_shortcode_distance( $atts ){
		return $this->fitpress_load_activity_series($atts, 'distance');
	}

	//[calories]
	function fitpress_shortcode_calories( $atts ){
		return $this->fitpress_load_activity_series($atts, 'calories');
	}

	// common functionality for shortcodes
	function fitpress_shortcode_base( $atts ) {
		$atts = shortcode_atts( array(
		    'date' => null
			, 'data' => null
			, 'data_type' => null
		), $atts );

		// we only compute this if not supplied because it's expensive to compute
		if ( $atts['date'] == null ) {
			$post = get_post(get_the_ID());
			$atts['date'] = new DateTime($post->post_date);
		}

		return $atts;
	}


	/**
	 * CSS and javascript
	 **/

	function fitpress_init_styles() {
		wp_enqueue_style('fitpress-style', plugin_dir_url( __FILE__ ) . 'fitpress.css', array());
	}

	function fitpress_get_must_login() {
		return "<div><p>User Must Be Logged In To View This Content</p></div>";
	}

	function fitpress_linked_accounts_print(){
		echo $this->fitpress_linked_accounts();
	}

	/**
	 * User profile buttons
	 **/

	function fitpress_linked_accounts() {
		if (is_user_logged_in()){
			$user_id = get_current_user_id();

			$fitpress_credentials = get_user_meta($user_id, 'fitpress_credentials', true);
			$last_error = get_user_meta($user_id, 'fitpress_last_error', true);
			$out="";
			// list the wpoa_identity records:
			$out.="<div id='fitpress-linked-accounts'>";
			$out.="<h3>FitBit Account</h3>";
			if ( ! $fitpress_credentials ) {
				$out.= "<p>You have not linked your FitBit account.</p>";
				$out.= $this->fitpress_login_button();
			} else {
				$unlink_url = admin_url('admin-post.php?action=fitpress_auth_unlink');
				$name = $fitpress_credentials['name'];
				$out.= "<p>Linked account {$name} - <a href='{$unlink_url}'>Unlink</a>";
			}
			if ( $last_error ) {
				$out.="<p>There was an error connecting your account: {$last_error}</p>";
			}

			$out.="</div>";
		}
		else{
			$out=$this->fitpress_get_must_login();
			//fitpress_get_must_login();
		}
		return $out;
	}

	private function get_fitbit_oauth2_client() {
		require_once('fitpress-oauth2-client.php');
		$user_id = get_current_user_id();
		
		$redirect_url = admin_url('admin-post.php?action=fitpress_auth_callback');
		return  new FitBit_OAuth2_Client(get_option('fitpress_api_id'), get_option('fitpress_api_secret'), $redirect_url, FITPRESS_CLIENT_STATE_KEY);
	}

	function get_fitbit_client( $access_token = null ) {
		require_once('fitpress-oauth2-client.php');
		$user_id = get_current_user_id();
		$fitpress_credentials = get_user_meta( $user_id, 'fitpress_credentials', true );

		if ( ! $access_token && $fitpress_credentials ) {
			$access_token = $fitpress_credentials['token'];
		}

		$client = new FitBit_API_Client( $access_token );

		return $client;
	}

	//redirect out to FitBit authorization URL
	function fitpress_auth() {
		$oauth_client = $this->get_fitbit_oauth2_client();
		$auth_url = $oauth_client->generate_authorization_url( get_current_user_id() );
		wp_redirect( $auth_url );
		
		exit;
	}

	//delete stored fitbit token
	function fitpress_auth_unlink() {
		$user_id = get_current_user_id();
		delete_user_meta( $user_id, 'fitpress_credentials' );
		$this->redirect_to_user( $user_id );
	}

	function fitpress_auth_callback() {
		$user_id = get_current_user_id();
		$oauth_client = $this->get_fitbit_oauth2_client();
		$auth_response = $oauth_client->process_authorization_grant_request( $user_id );
		
		if ( is_wp_error( $auth_response ) ) {
			die(print_r( $auth_response, true ));
		}

		$access_token = $auth_response->access_token;
		$user_info = $this->get_fitbit_client( $access_token )->get_current_user_info();

		update_user_meta( get_current_user_id(), 'fitpress_credentials', array( 'token' => $access_token, 'name' => $user_info->fullName ) );

		$this->redirect_to_user( $user_id );
	}

	function fitpress_login_button() {
		$url = admin_url('admin-post.php?action=fitpress_auth');

		// generates and returns a login button for FitPress:
		$html = "";
		$html .= "<a id='fitpress-login-fitbit' class='fitpress-login-button' href='{$url}'>";
		$html .= "Link my FitBit account";
		$html .= "</a>";
		return $html;
	}

	/**
	 * Plugin settings
	 **/

	// registers all settings that have been defined at the top of the plugin:
	function fitpress_register_settings() {
		register_setting('fitpress_settings', 'fitpress_api_id');
		register_setting('fitpress_settings', 'fitpress_api_secret');
		register_setting('fitpress_settings', 'fitpress_redirect');
		register_setting('fitpress_settings', 'fitpress_token_override');
	}

	// add the main settings page:
	function fitpress_settings_page() {
		add_options_page( 'FitPress Options', 'FitPress', 'manage_options', 'FitPress', array($this, 'fitpress_settings_page_content') );
	}

	// render the main settings page content:
	function fitpress_settings_page_content() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		$blog_url = rtrim(site_url(), "/") . "/";
		include 'fitpress-settings.php';
	}

	/**
	 * Private functions
	 */

	private function redirect_to_user( $user_id ) {
		//wp_redirect( get_edit_user_link( $user_id ), 301 );
		$redirect_url = get_option('fitpress_redirect');
		wp_redirect($redirect_url);
		exit;
	}
}

FitPress::get_instance();
?>
