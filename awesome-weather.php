<?php
/*
Plugin Name: Awesome Weather Widget
Plugin URI: http://halgatewood.com/awesome-weather
Description: A weather widget that actually looks cool
Author: Hal Gatewood
Author URI: http://www.halgatewood.com
Version: 1.3


FILTERS AVAILABLE:
awesome_weather_cache 						= How many seconds to cache weather: default 3600 (one hour).
awesome_weather_error 						= Error message if weather is not found.
awesome_weather_sizes 						= array of sizes for widget
awesome_weather_extended_forecast_text 		= Change text of footer link


SHORTCODE USAGE
[awesome-weather location="Oklahoma City, OK" units="F"]
[awesome-weather location="London, UK" units="C" width=220]
*/


// SETTINGS
$awesome_weather_sizes = apply_filters( 'awesome_weather_sizes' , array( 'tall', 'wide' ) );
        


// SETUP
function awesome_weather_setup()
{
	load_plugin_textdomain( 'awesome-weather', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action('plugins_loaded', 'awesome_weather_setup', 99999);



// HAS SHORTCODE - by pippin
function awesome_weather_wp_head( $posts ) 
{
	wp_enqueue_style( 'awesome-weather', plugins_url( '/awesome-weather.css', __FILE__ ) );
}
add_action('wp_enqueue_scripts', 'awesome_weather_wp_head');


//THE SHORTCODE
add_shortcode( 'awesome-weather', 'awesome_weather_shortcode' );
function awesome_weather_shortcode( $atts )
{
	return awesome_weather_logic( $atts );	
}


// THE LOGIC
function awesome_weather_logic( $atts )
{
	global $awesome_weather_sizes;
	
	$rtn 				= "";
	$weather_data		= array();
	$location 			= isset($atts['location']) ? $atts['location'] : false;
	$size 				= (isset($atts['size']) AND $atts['size'] == "tall") ? 'tall' : 'wide';
	$units 				= (isset($atts['units']) AND strtoupper($atts['units']) == "C") ? "metric" : "imperial";
	$units_display		= $units == "metric" ? __('C', 'awesome-weather') : __('F', 'awesome-weather');
	$override_title 	= isset($atts['override_title']) ? $atts['override_title'] : false;
	$days_to_show 		= isset($atts['forecast_days']) ? $atts['forecast_days'] : 5;
	$show_stats 		= (isset($atts['hide_stats']) AND $atts['hide_stats'] == 1) ? 0 : 1;
	$show_link 			= (isset($atts['show_link']) AND $atts['show_link'] == 1) ? 1 : 0;
	$background			= isset($atts['background']) ? $atts['background'] : false;
	$locale				= 'en';

	$sytem_locale = get_locale();
	$available_locales = array( 'en', 'sp', 'fr', 'it', 'de', 'pt', 'ro', 'pl', 'ru', 'ua', 'fi', 'nl', 'bg', 'se', 'tr', 'zh_tw', 'zh_cn' ); 

	
    // CHECK FOR LOCALE
    if( in_array( $sytem_locale , $available_locales ) )
    {
    	$locale = $sytem_locale;
    }
    
    // CHECK FOR LOCALE BY FIRST TWO DIGITS
    if( in_array(substr($sytem_locale, 0, 2), $available_locales ) )
    {
    	$locale = substr($sytem_locale, 0, 2);
    }


	// NO LOCATION, ABORT ABORT!!!1!
	if( !$location ) { return awesome_weather_error(); }
	
	
	//FIND AND CACHE CITY ID
	$city_name_slug 				= sanitize_title( $location );
	$weather_transient_name 		= 'awesome-weather-' . $units . '-' . $city_name_slug . "-". $locale;


	// TWO APIS USED (VERSION 2.5)
	//http://api.openweathermap.org/data/2.5/weather?q=London,uk&units=metric&cnt=7&lang=fr
	//http://api.openweathermap.org/data/2.5/forecast/daily?q=London&units=metric&cnt=7&lang=fr

	
	
	// GET WEATHER DATA
	if( get_transient( $weather_transient_name ) )
	{
		$weather_data = get_transient( $weather_transient_name );
	}
	else
	{
		// NOW
		$now_ping = "http://api.openweathermap.org/data/2.5/weather?q=" . $city_name_slug . "&lang=" . $locale . "&units=" . $units;
		$now_ping_get = wp_remote_get( $now_ping );
	
		if( is_wp_error( $now_ping_get ) ) 
		{
			return awesome_weather_error( $now_ping_get->get_error_message()  ); 
		}	
	
		$city_data = json_decode( $now_ping_get['body'] );
		
		if( isset($city_data->cod) AND $city_data->cod == 404 )
		{
			return awesome_weather_error( $city_data->message ); 
		}
		else
		{
			$weather_data['now'] = $city_data;
		}
		
		
		// FORECAST
		if( $days_to_show != "hide" )
		{
			$forecast_ping = "http://api.openweathermap.org/data/2.5/forecast/daily?q=" . $city_name_slug . "&lang=" . $locale . "&units=" . $units ."&cnt=7";
			$forecast_ping_get = wp_remote_get( $forecast_ping );
		
			if( is_wp_error( $forecast_ping_get ) ) 
			{
				return awesome_weather_error( $forecast_ping_get->get_error_message()  ); 
			}	
			
			$forecast_data = json_decode( $forecast_ping_get['body'] );
			
			if( isset($forecast_data->cod) AND $forecast_data->cod == 404 )
			{
				return awesome_weather_error( $forecast_data->message ); 
			}
			else
			{
				$weather_data['forecast'] = $forecast_data;
			}
		}	
		
		
		if($weather_data['now'] AND $weather_data['forecast'])
		{
			// SET THE TRANSIENT, CACHE FOR AN HOUR
			set_transient( $weather_transient_name, $weather_data, apply_filters( 'awesome_weather_cache', 3600 ) ); 
		}
	}



	// NO WEATHER
	if( !$weather_data OR !isset($weather_data['now'])) { return awesome_weather_error(); }
	
	
	// TODAYS TEMPS
	$today 			= $weather_data['now'];
	$today_temp 	= round($today->main->temp);
	$today_high 	= round($today->main->temp_max);
	$today_low 		= round($today->main->temp_min);
	
	
	// COLOR OF WIDGET
	$bg_color = "temp1";
	if($units_display == "F")
	{
		if($today_temp > 31 AND $today_temp < 40) $bg_color = "temp2";
		if($today_temp >= 40 AND $today_temp < 50) $bg_color = "temp3";
		if($today_temp >= 50 AND $today_temp < 60) $bg_color = "temp4";
		if($today_temp >= 60 AND $today_temp < 80) $bg_color = "temp5";
		if($today_temp >= 80 AND $today_temp < 90) $bg_color = "temp6";
		if($today_temp >= 90) $bg_color = "temp7";
	}
	else
	{
		if($today_temp > 1 AND $today_temp < 4) $bg_color = "temp2";
		if($today_temp >= 4 AND $today_temp < 10) $bg_color = "temp3";
		if($today_temp >= 10 AND $today_temp < 15) $bg_color = "temp4";
		if($today_temp >= 15 AND $today_temp < 26) $bg_color = "temp5";
		if($today_temp >= 26 AND $today_temp < 32) $bg_color = "temp6";
		if($today_temp >= 32) $bg_color = "temp7";
	}
	
	
	// DATA
	$header_title = $override_title ? $override_title : $today->name;
	
	$today->main->humidity 		= round($today->main->humidity);
	$today->wind->speed 		= round($today->wind->speed);
	
	$wind_label = array ( 
							__('N', 'awesome-weather'),
							__('NNE', 'awesome-weather'), 
							__('NE', 'awesome-weather'),
							__('ENE', 'awesome-weather'),
							__('E', 'awesome-weather'),
							__('ESE', 'awesome-weather'),
							__('SE', 'awesome-weather'),
							__('SSE', 'awesome-weather'),
							__('S', 'awesome-weather'),
							__('SSW', 'awesome-weather'),
							__('SW', 'awesome-weather'),
							__('WSW', 'awesome-weather'),
							__('W', 'awesome-weather'),
							__('WNW', 'awesome-weather'),
							__('NW', 'awesome-weather'),
							__('NNW', 'awesome-weather')
						);
						
	$wind_direction = $wind_label[ fmod((($today->wind->deg + 11) / 22.5),16) ];
	
	$show_stats_class = ($show_stats) ? "awe_with_stats" : "awe_without_stats";
	
	if($background) $bg_color = "darken";
	
	// DISPLAY WIDGET	
	$rtn .= "
	
		<div id=\"awesome-weather-{$city_name_slug}\" class=\"awesome-weather-wrap awecf {$bg_color} {$show_stats_class} awe_{$size}\">
	";


	if($background) 
	{ 
		$rtn .= "<div class=\"awesome-weather-cover\" style='background-image: url($background);'>";
		$rtn .= "<div class=\"awesome-weather-darken\">";
	}

	$rtn .= "
			<div class=\"awesome-weather-header\">{$header_title}</div>
			
			<div class=\"awesome-weather-current-temp\">
				$today_temp<sup>{$units_display}</sup>
			</div> <!-- /.awesome-weather-current-temp -->
	";	
	
	if($show_stats)
	{
		$speed_text = ($units == "metric") ? __('km/h', 'awesome-weather') : __('mph', 'awesome-weather');
	
	
		$rtn .= "
				
				<div class=\"awesome-weather-todays-stats\">
					<div class=\"awe_desc\">{$today->weather[0]->description}</div>
					<div class=\"awe_humidty\">" . __('humidity:', 'awesome-weather') . " {$today->main->humidity}% </div>
					<div class=\"awe_wind\">" . __('wind:', 'awesome-weather') . " {$today->wind->speed}" . $speed_text . " {$wind_direction}</div>
					<div class=\"awe_highlow\"> "  .__('H', 'awesome-weather') . " {$today_high} &bull; " . __('L', 'awesome-weather') . " {$today_low} </div>	
				</div> <!-- /.awesome-weather-todays-stats -->
		";
	}

	if($days_to_show != "hide")
	{
		$rtn .= "<div class=\"awesome-weather-forecast awe_days_{$days_to_show} awecf\">";
		$c = 1;
		$dt_today = date('Ymd');
		$forecast = $weather_data['forecast'];
		$days_to_show = (int) $days_to_show;
		
		foreach( (array) $forecast->list as $forecast )
		{
			if( $dt_today >= date('Ymd', $forecast->dt)) continue;
			
			$forecast->temp = (int) $forecast->temp->day;
			$day_of_week = date('D', $forecast->dt);
			$rtn .= "
				<div class=\"awesome-weather-forecast-day\">
					<div class=\"awesome-weather-forecast-day-temp\">{$forecast->temp}<sup>{$units_display}</sup></div>
					<div class=\"awesome-weather-forecast-day-abbr\">$day_of_week</div>
				</div>
			";
			if($c == $days_to_show) break;
			$c++;
		}
		$rtn .= " </div> <!-- /.awesome-weather-forecast -->";
	}
	
	if($show_link AND isset($today->id))
	{
		$show_link_text = apply_filters('awesome_weather_extended_forecast_text' , __('extended forecast', 'awesome-weather'));

		$rtn .= "<div class=\"awesome-weather-more-weather-link\">";
		$rtn .= "<a href=\"http://openweathermap.org/city/{$today->id}\" target=\"_blank\">{$show_link_text}</a>";		
		$rtn .= "</div> <!-- /.awesome-weather-more-weather-link -->";
	}
	
	if($background) 
	{ 
		$rtn .= "</div> <!-- /.awesome-weather-cover -->";
		$rtn .= "</div> <!-- /.awesome-weather-darken -->";
	}
	
	
	$rtn .= "</div> <!-- /.awesome-weather-wrap -->";
	return $rtn;
}


// RETURN ERROR
function awesome_weather_error( $msg = false )
{
	if(!$msg) $msg = __('No weather information available', 'awesome-weather');
	return apply_filters( 'awesome_weather_error', "<!-- AWESOME WEATHER ERROR: " . $msg . " -->" );
}



// TEXT BLOCK WIDGET
class AwesomeWeatherWidget extends WP_Widget 
{
	function AwesomeWeatherWidget() { parent::WP_Widget(false, $name = 'Awesome Weather Widget'); }

    function widget($args, $instance) 
    {	
        extract( $args );
        
        $location 			= isset($instance['location']) ? $instance['location'] : false;
        $override_title 	= isset($instance['override_title']) ? $instance['override_title'] : false;
        $units 				= isset($instance['units']) ? $instance['units'] : false;
        $size 				= isset($instance['size']) ? $instance['size'] : false;
        $forecast_days 		= isset($instance['forecast_days']) ? $instance['forecast_days'] : false;
        $hide_stats 		= (isset($instance['hide_stats']) AND $instance['hide_stats'] == 1) ? 1 : 0;
        $show_link 			= (isset($instance['show_link']) AND $instance['show_link'] == 1) ? 1 : 0;
        $background			= isset($instance['background']) ? $instance['background'] : false;

		echo $before_widget;
		echo awesome_weather_logic( array( 'location' => $location, 'override_title' => $override_title, 'size' => $size, 'units' => $units, 'forecast_days' => $forecast_days, 'hide_stats' => $hide_stats, 'show_link' => $show_link, 'background' => $background ));
		echo $after_widget;
    }
 
    function update($new_instance, $old_instance) 
    {		
		$instance = $old_instance;
		$instance['location'] 			= strip_tags($new_instance['location']);
		$instance['override_title'] 	= strip_tags($new_instance['override_title']);
		$instance['units'] 				= strip_tags($new_instance['units']);
		$instance['size'] 				= strip_tags($new_instance['size']);
		$instance['forecast_days'] 		= strip_tags($new_instance['forecast_days']);
		$instance['hide_stats'] 		= strip_tags($new_instance['hide_stats']);
		$instance['show_link'] 			= strip_tags($new_instance['show_link']);
		$instance['background'] 		= strip_tags($new_instance['background']);
        return $instance;
    }
 
    function form($instance) 
    {	
    	global $awesome_weather_sizes;
    	
        $location 			= isset($instance['location']) ? esc_attr($instance['location']) : "";
        $override_title 	= isset($instance['override_title']) ? esc_attr($instance['override_title']) : "";
        $selected_size 		= isset($instance['size']) ? esc_attr($instance['size']) : "wide";
        $units 				= (isset($instance['units']) AND strtoupper($instance['units']) == "C") ? "C" : "F";
        $forecast_days 		= isset($instance['forecast_days']) ? esc_attr($instance['forecast_days']) : 5;
        $hide_stats 		= (isset($instance['hide_stats']) AND $instance['hide_stats'] == 1) ? 1 : 0;
        $show_link 			= (isset($instance['show_link']) AND $instance['show_link'] == 1) ? 1 : 0;
        $background			= isset($instance['background']) ? esc_attr($instance['background']) : "";
	?>
        <p>
          <label for="<?php echo $this->get_field_id('location'); ?>">
          	<?php _e('Location:', 'awesome-weather'); ?><br />
          	<small><?php _e('(i.e: London,UK or New York City,NY)', 'awesome-weather'); ?></small>
          </label> 
          <input class="widefat" style="margin-top: 4px;" id="<?php echo $this->get_field_id('location'); ?>" name="<?php echo $this->get_field_name('location'); ?>" type="text" value="<?php echo $location; ?>" />
        </p>
                
        <p>
          <label for="<?php echo $this->get_field_id('override_title'); ?>"><?php _e('Override Title:', 'awesome-weather'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('override_title'); ?>" name="<?php echo $this->get_field_name('override_title'); ?>" type="text" value="<?php echo $override_title; ?>" />
        </p>
                
        <p>
          <label for="<?php echo $this->get_field_id('units'); ?>"><?php _e('Units:', 'awesome-weather'); ?></label>  &nbsp;
          <input id="<?php echo $this->get_field_id('units'); ?>" name="<?php echo $this->get_field_name('units'); ?>" type="radio" value="F" <?php if($units == "F") echo ' checked="checked"'; ?> /> F &nbsp; &nbsp;
          <input id="<?php echo $this->get_field_id('units'); ?>" name="<?php echo $this->get_field_name('units'); ?>" type="radio" value="C" <?php if($units == "C") echo ' checked="checked"'; ?> /> C
        </p>
        
		<p>
          <label for="<?php echo $this->get_field_id('size'); ?>"><?php _e('Size:', 'awesome-weather'); ?></label> 
          <select class="widefat" id="<?php echo $this->get_field_id('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>">
          	<?php foreach($awesome_weather_sizes as $size) { ?>
          	<option value="<?php echo $size; ?>"<?php if($selected_size == $size) echo " selected=\"selected\""; ?>><?php echo $size; ?></option>
          	<?php } ?>
          </select>
		</p>
        
		<p>
          <label for="<?php echo $this->get_field_id('forecast_days'); ?>"><?php _e('Forecast:', 'awesome-weather'); ?></label> 
          <select class="widefat" id="<?php echo $this->get_field_id('forecast_days'); ?>" name="<?php echo $this->get_field_name('forecast_days'); ?>">
          	<option value="5"<?php if($forecast_days == 5) echo " selected=\"selected\""; ?>>5 Days</option>
          	<option value="4"<?php if($forecast_days == 4) echo " selected=\"selected\""; ?>>4 Days</option>
          	<option value="3"<?php if($forecast_days == 3) echo " selected=\"selected\""; ?>>3 Days</option>
          	<option value="2"<?php if($forecast_days == 2) echo " selected=\"selected\""; ?>>2 Days</option>
          	<option value="1"<?php if($forecast_days == 1) echo " selected=\"selected\""; ?>>1 Days</option>
          	<option value="hide"<?php if($forecast_days == 'hide') echo " selected=\"selected\""; ?>>Don't Show</option>
          </select>
		</p>
		
        <p>
          <label for="<?php echo $this->get_field_id('background'); ?>"><?php _e('Background Image:', 'awesome-weather'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('background'); ?>" name="<?php echo $this->get_field_name('background'); ?>" type="text" value="<?php echo $background; ?>" />
        </p>
		
        <p>
          <label for="<?php echo $this->get_field_id('hide_stats'); ?>"><?php _e('Hide Stats:', 'awesome-weather'); ?></label>  &nbsp;
          <input id="<?php echo $this->get_field_id('hide_stats'); ?>" name="<?php echo $this->get_field_name('hide_stats'); ?>" type="checkbox" value="1" <?php if($hide_stats) echo ' checked="checked"'; ?> />
        </p>
		
        <p>
          <label for="<?php echo $this->get_field_id('show_link'); ?>"><?php _e('Link to OpenWeatherMap:', 'awesome-weather'); ?></label>  &nbsp;
          <input id="<?php echo $this->get_field_id('show_link'); ?>" name="<?php echo $this->get_field_name('show_link'); ?>" type="checkbox" value="1" <?php if($show_link) echo ' checked="checked"'; ?> />
        </p> 
       
		
        <?php 
    }
}

add_action( 'widgets_init', create_function('', 'return register_widget("AwesomeWeatherWidget");') );



