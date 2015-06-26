<?php

/**
 * Contains the most low level methods & helpers in FFL Commerce
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade FFL Commerce to newer
 * versions in the future. If you wish to customise FFL Commerce core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             FFLCommerce
 * @category            Core
 * @author              Tampa Bay Tactical Supply, Inc.
 * @copyright           Copyright © 2011-2014 Tampa Bay Tactical Supply, Inc. & Jigoshop.
 * @license             GNU General Public License v3
 */
class fflcommerce extends FFLCommerce_Singleton
{
	const SHOP_LARGE_W = '300';
	const SHOP_LARGE_H = '300';
	const SHOP_SMALL_W = '150';
	const SHOP_SMALL_H = '150';
	const SHOP_TINY_W = '36';
	const SHOP_TINY_H = '36';
	const SHOP_THUMBNAIL_W = '90';
	const SHOP_THUMBNAIL_H = '90';

	public static $errors = array();
	public static $messages = array();
	public static $plugin_url;
	public static $plugin_path;

	/** constructor */
	protected function __construct()
	{
		self::$errors = (array)fflcommerce_session::instance()->errors;
		self::$messages = (array)fflcommerce_session::instance()->messages;

		// uses fflcommerce_base_class to provide class address for the filter
		self::add_filter('wp_redirect', 'redirect', 1, 2);
	}

	/**
	 * Get the current path to FFL Commerce
	 *
	 * @deprecated Use FFLCOMMERCE_DIR constant.
	 * @return  string  local filesystem path with trailing slash
	 */
	public static function fflcommerce_path()
	{
		return FFLCOMMERCE_DIR;
	}

	/**
	 * Get the current version of FFL Commerce
	 *
	 * @deprecated Please use FFLCOMMERCE_VERSION constant instead - it is much faster.
	 * @return  string  current fflcommerce version
	 */
	public static function fflcommerce_version()
	{
		return FFLCOMMERCE_VERSION;
	}

	/**
	 * Get the assets url
	 * Provide a filter to allow asset location elsewhere such as on a CDN
	 *
	 * @deprecated Use FFLCOMMERCE_URL constant instead.
	 * @param null $file
	 * @return  string  url
	 */
	public static function assets_url($file = null)
	{
		return apply_filters('fflcommerce_assets_url', FFLCOMMERCE_URL.'/'.$file);
	}

	/**
	 * Get the plugin url
	 *
	 * @deprecated Use FFLCOMMERCE_URL constant instead.
	 * @param null $file
	 * @return  string  url
	 */
	public static function plugin_url($file = null)
	{
		return FFLCOMMERCE_URL.'/'.$file;
	}

	/**
	 * Get the plugin path
	 *
	 * @note plugin_dir_path() does this
	 * @deprecated Use FFLCOMMERCE_DIR constant instead.
	 * @return  string  url
	 */
	public static function plugin_path()
	{
		if (!empty(self::$plugin_path)) {
			return self::$plugin_path;
		}

		return self::$plugin_path = dirname(dirname(__FILE__));
	}

	/**
	 * Return the URL with https if SSL is on
	 *
	 * @param $url
	 * @return string url
	 */
	public static function force_ssl($url)
	{
		if (is_ssl()) $url = str_replace('http:', 'https:', $url);

		return $url;
	}

	/**
	 * Get a var
	 * Variable is filtered by fflcommerce_get_var_{var name}
	 *
	 * @param string $var Variable name to fetch.
	 * @return string Variable value.
	 */
	public static function get_var($var)
	{
		$return = null;

		switch ($var) {
			case 'shop_large_w' :
				$return = self::SHOP_LARGE_W;
				break;
			case 'shop_large_h' :
				$return = self::SHOP_LARGE_H;
				break;
			case 'shop_small_w' :
				$return = self::SHOP_SMALL_W;
				break;
			case 'shop_small_h' :
				$return = self::SHOP_SMALL_H;
				break;
			case 'shop_tiny_w' :
				$return = self::SHOP_TINY_W;
				break;
			case 'shop_tiny_h' :
				$return = self::SHOP_TINY_H;
				break;
			case 'shop_thumbnail_w' :
				$return = self::SHOP_THUMBNAIL_W;
				break;
			case 'shop_thumbnail_h' :
				$return = self::SHOP_THUMBNAIL_H;
				break;
		}

		return apply_filters('fflcommerce_get_var_'.$var, $return);
	}

	/**
	 * Add a message
	 *
	 * @param string $message A message
	 */
	public static function add_message($message)
	{
		self::$messages[] = $message;
	}

	/**
	 * Output the errors and messages
	 */
	public static function show_messages()
	{
		if (self::has_errors()) {
			echo '<div class="fflcommerce_error">';
			foreach (self::$errors as $error) {
				echo '<span>'.$error.'</span><br />';
			}
			echo '</div>';
		}

		if (self::has_messages()) {
			echo '<div class="fflcommerce_message">';
			foreach (self::$messages as $message) {
				echo '<span>'.$message.'</span><br />';
			}
			echo '</div>';
		}

		self::clear_messages();
	}

	public static function has_errors()
	{
		return !empty(self::$errors);
	}

	public static function has_messages()
	{
		return !empty(self::$messages);
	}

	/** Clear messages and errors from the session data */
	public static function clear_messages()
	{
		self::$errors = self::$messages = array();
		unset(fflcommerce_session::instance()->messages);
		unset(fflcommerce_session::instance()->errors);
	}

	public static function nonce_field($action, $referrer = true, $echo = true)
	{
		$name = '_n';
		$action = 'fflcommerce-'.$action;

		$name = esc_attr($name);
		$nonce_field = '<input type="hidden" name="'.$name.'" value="'.wp_create_nonce($action).'" />';

		if ($referrer) {
			$nonce_field .= wp_referer_field(false);
		}

		if ($echo) {
			echo $nonce_field;
		}

		return $nonce_field;
	}

	public static function nonce_url($action, $url = '')
	{
		$name = '_n';
		$action = 'fflcommerce-'.$action;

		return add_query_arg($name, wp_create_nonce($action), $url);
	}

	/**
	 * Check a nonce and sets FFL Commerce error in case it is invalid
	 * To fail silently, set the error_message to an empty string
	 *
	 * @param  string $action then nonce action
	 * @return   bool
	 */
	public static function verify_nonce($action)
	{
		$name = '_n';
		$action = 'fflcommerce-'.$action;
		$request = array_merge($_GET, $_POST);

		if (!wp_verify_nonce($request[$name], $action)) {
			fflcommerce::add_error(__('Action failed. Please refresh the page and retry.', 'fflcommerce'));

			return false;
		}

		return true;
	}

	/**
	 * Add an error
	 *
	 * @param string $error An error
	 */
	public static function add_error($error)
	{
		self::$errors[] = $error;
	}

	/**
	 * Redirection hook which stores messages into session data
	 *
	 * @param $location
	 * @param $status
	 * @return string Location
	 */
	public static function redirect($location, $status = null)
	{
		fflcommerce_session::instance()->errors = self::$errors;
		fflcommerce_session::instance()->messages = self::$messages;

		return apply_filters('fflcommerce_session_location_filter', $location);
	}

	// http://www.xe.com/symbols.php
	public static function currency_symbols()
	{
		$symbols = array(
			'AED' => '&#1583;&#46;&#1573;', /*'United Arab Emirates dirham'*/
			'AFN' => '&#1547;', /*'Afghanistan Afghani'*/
			'ALL' => 'Lek', /*'Albania Lek'*/
			'ANG' => '&fnof;', /*'Netherlands Antilles Guilder'*/
			'ARS' => '$', /*'Argentina Peso'*/
			'AUD' => '$', /*'Australia Dollar'*/
			'AWG' => '&fnof;', /*'Aruba Guilder'*/
			'AZN' => '&#1084;&#1072;&#1085;', /*'Azerbaijan New Manat'*/
			'BAM' => 'KM', /*'Bosnia and Herzegovina Convertible Marka'*/
			'BBD' => '$', /*'Barbados Dollar'*/
			'BGN' => '&#1083;&#1074;', /*'Bulgaria Lev'*/
			'BMD' => '$', /*'Bermuda Dollar'*/
			'BND' => '$', /*'Brunei Darussalam Dollar'*/
			'BOB' => '$b', /*'Bolivia Boliviano'*/
			'BRL' => '&#82;&#36;', /*'Brazil Real'*/
			'BSD' => '$', /*'Bahamas Dollar'*/
			'BWP' => 'P', /*'Botswana Pula'*/
			'BYR' => 'p.', /*'Belarus Ruble'*/
			'BZD' => 'BZ$', /*'Belize Dollar'*/
			'CAD' => '$', /*'Canada Dollar'*/
			'CHF' => 'CHF', /*'Switzerland Franc'*/
			'CLP' => '$', /*'Chile Peso'*/
			'CNY' => '&yen;', /*'China Yuan Renminbi'*/
			'COP' => '$', /*'Colombia Peso'*/
			'CRC' => '&#8353;', /*'Costa Rica Colon'*/
			'CUP' => '&#8369;', /*'Cuba Peso'*/
			'CZK' => 'K&#269;', /*'Czech Republic Koruna'*/
			'DKK' => 'kr', /*'Denmark Krone'*/
			'DOP' => 'RD$', /*'Dominican Republic Peso'*/
			'EEK' => 'kr', /*'Estonia Kroon'*/
			'EGP' => '&pound;', /*'Egypt Pound'*/
			'EUR' => '&euro;', /*'Euro Member Countries'*/
			'FJD' => '$', /*'Fiji Dollar'*/
			'FKP' => '&pound;', /*'Falkland Islands'*/
			'GBP' => '&pound;', /*'United Kingdom Pound'*/
			'GEL' => 'ლ', /*'Georgia Lari'*/
			'GGP' => '&pound;', /*'Guernsey Pound'*/
			'GHC' => '&cent;', /*'Ghana Cedis'*/
			'GIP' => '&cent;', /*'Gibraltar Pound'*/
			'GTQ' => 'Q', /*'Guatemala Quetzal'*/
			'GYD' => '$', /*'Guyana Dollar'*/
			'HKD' => '$', /*'Hong Kong Dollar'*/
			'HNL' => 'L', /*'Honduras Lempira'*/
			'HRK' => 'kn', /*'Croatia Kuna'*/
			'HUF' => '&#70;&#116;', /*'Hungary Forint'*/
			'IDR' => '&#82;&#112;', /*'Indonesia Rupiah'*/
			'ILS' => '&#8362;', /*'Israel Shekel'*/
			'IMP' => '&pound;', /*'Isle of Man Pound'*/
			'INR' => '&#8360;', /*'India Rupee'*/
			'IRR' => '&#65020;', /*'Iran Rial'*/
			'ISK' => 'kr', /*'Iceland Krona'*/
			'JEP' => '&pound;', /*'Jersey Pound'*/
			'JMD' => 'J$', /*'Jamaica Dollar'*/
			'JPY' => '&yen;', /*'Japan Yen'*/
			'KGS' => '&#1083;&#1074;', /*'Kyrgyzstan Som'*/
			'KHR' => '&#6107;', /*'Cambodia Riel'*/
			'KPW' => '&#8361;', /*'North Korea Won'*/
			'KRW' => '&#8361;', /*'South Korea Won'*/
			'KYD' => '$', /*'Cayman Islands Dollar'*/
			'KZT' => '&#1083;&#1074;', /*'Kazakhstan Tenge'*/
			'LAK' => '&#8365;', /*'Laos Kip'*/
			'LBP' => '&pound;', /*'Lebanon Pound'*/
			'LKR' => '&#8360;', /*'Sri Lanka Rupee'*/
			'LRD' => '$', /*'Liberia Dollar'*/
			'LTL' => 'Lt', /*'Lithuania Litas'*/
			'LVL' => 'Ls', /*'Latvia Lat'*/
			'MAD' => '&#1583;.&#1605;.', /*'Moroccan Dirham'*/
			'MKD' => '&#1076;&#1077;&#1085;', /*'Macedonia Denar'*/
			'MNT' => '&#8366;', /*'Mongolia Tughrik'*/
			'MUR' => '&#8360;', /*'Mauritius Rupee'*/
			'MXN' => '&#36;', /*'Mexico Peso'*/
			'MYR' => 'RM', /*'Malaysia Ringgit'*/
			'MZN' => 'MT', /*'Mozambique Metical'*/
			'NAD' => '$', /*'Namibia Dollar'*/
			'NGN' => '&#8358;', /*'Nigeria Naira'*/
			'NIO' => 'C$', /*'Nicaragua Cordoba'*/
			'NOK' => 'kr', /*'Norway Krone'*/
			'NPR' => '&#8360;', /*'Nepal Rupee'*/
			'NZD' => '$', /*'New Zealand Dollar'*/
			'OMR' => '&#65020;', /*'Oman Rial'*/
			'PAB' => 'B/.', /*'Panama Balboa'*/
			'PEN' => 'S/.', /*'Peru Nuevo Sol'*/
			'PHP' => '&#8369;', /*'Philippines Peso'*/
			'PKR' => '&#8360;', /*'Pakistan Rupee'*/
			'PLN' => '&#122;&#322;', /*'Poland Zloty'*/
			'PYG' => 'Gs', /*'Paraguay Guarani'*/
			'QAR' => '&#65020;', /*'Qatar Riyal'*/
			'RON' => '&#108;&#101;&#105;', /*'Romania New Leu'*/
			'RSD' => 'РСД', /*'Serbia Dinar'*/
			'RUB' => '&#1088;&#1091;&#1073;', /*'Russia Ruble'*/
			'SAR' => '&#65020;', /*'Saudi Arabia Riyal'*/
			'SBD' => '$', /*'Solomon Islands Dollar'*/
			'SCR' => '&#8360;', /*'Seychelles Rupee'*/
			'SEK' => 'kr', /*'Sweden Krona'*/
			'SGD' => '$', /*'Singapore Dollar'*/
			'SHP' => '&pound;', /*'Saint Helena Pound'*/
			'SOS' => 'S', /*'Somalia Shilling'*/
			'SRD' => '$', /*'Suriname Dollar'*/
			'SVC' => '$', /*'El Salvador Colon'*/
			'SYP' => '&pound;', /*'Syria Pound'*/
			'THB' => '&#3647;', /*'Thailand Baht'*/
			'TRL' => '&#8356;', /*'Turkey Lira'*/
			'TRY' => 'TL', /*'Turkey Lira'*/
			'TTD' => 'TT$', /*'Trinidad and Tobago Dollar'*/
			'TVD' => '$', /*'Tuvalu Dollar'*/
			'TWD' => 'NT$', /*'Taiwan New Dollar'*/
			'UAH' => '&#8372;', /*'Ukraine Hryvna'*/
			'USD' => '$', /*'United States Dollar'*/
			'UYU' => '$U', /*'Uruguay Peso'*/
			'UZS' => '&#1083;&#1074;', /*'Uzbekistan Som'*/
			'VEF' => 'Bs', /*'Venezuela Bolivar Fuerte'*/
			'VND' => '&#8363;', /*'Viet Nam Dong'*/
			'XCD' => '$', /*'East Caribbean Dollar'*/
			'YER' => '&#65020;', /*'Yemen Rial'*/
			'ZAR' => 'R', /*'South Africa Rand'*/
			'ZWD' => 'Z$', /*'Zimbabwe Dollar'*/
		);

		ksort($symbols);

		return $symbols;
	}

	public static function currency_countries()
	{
		$countries = array(
			'AED' => __('United Arab Emirates dirham', 'fflcommerce'),
			'AFN' => __('Afghanistan Afghani', 'fflcommerce'),
			'ALL' => __('Albania Lek', 'fflcommerce'),
			'ANG' => __('Netherlands Antilles Guilder', 'fflcommerce'),
			'ARS' => __('Argentina Peso', 'fflcommerce'),
			'AUD' => __('Australia Dollar', 'fflcommerce'),
			'AWG' => __('Aruba Guilder', 'fflcommerce'),
			'AZN' => __('Azerbaijan New Manat', 'fflcommerce'),
			'BAM' => __('Bosnia and Herzegovina Convertible Marka', 'fflcommerce'),
			'BBD' => __('Barbados Dollar', 'fflcommerce'),
			'BGN' => __('Bulgaria Lev', 'fflcommerce'),
			'BMD' => __('Bermuda Dollar', 'fflcommerce'),
			'BND' => __('Brunei Darussalam Dollar', 'fflcommerce'),
			'BOB' => __('Bolivia Boliviano', 'fflcommerce'),
			'BRL' => __('Brazil Real', 'fflcommerce'),
			'BSD' => __('Bahamas Dollar', 'fflcommerce'),
			'BWP' => __('Botswana Pula', 'fflcommerce'),
			'BYR' => __('Belarus Ruble', 'fflcommerce'),
			'BZD' => __('Belize Dollar', 'fflcommerce'),
			'CAD' => __('Canada Dollar', 'fflcommerce'),
			'CHF' => __('Switzerland Franc', 'fflcommerce'),
			'CLP' => __('Chile Peso', 'fflcommerce'),
			'CNY' => __('China Yuan Renminbi', 'fflcommerce'),
			'COP' => __('Colombia Peso', 'fflcommerce'),
			'CRC' => __('Costa Rica Colon', 'fflcommerce'),
			'CUP' => __('Cuba Peso', 'fflcommerce'),
			'CZK' => __('Czech Republic Koruna', 'fflcommerce'),
			'DKK' => __('Denmark Krone', 'fflcommerce'),
			'DOP' => __('Dominican Republic Peso', 'fflcommerce'),
			'EEK' => __('Estonia Kroon', 'fflcommerce'),
			'EGP' => __('Egypt Pound', 'fflcommerce'),
			'EUR' => __('Euro Member Countries', 'fflcommerce'),
			'FJD' => __('Fiji Dollar', 'fflcommerce'),
			'FKP' => __('Falkland Islands', 'fflcommerce'),
			'GBP' => __('United Kingdom Pound', 'fflcommerce'),
			'GEL' => __('Georgian Lari', 'fflcommerce'),
			'GGP' => __('Guernsey Pound', 'fflcommerce'),
			'GHC' => __('Ghana Cedis', 'fflcommerce'),
			'GIP' => __('Gibraltar Pound', 'fflcommerce'),
			'GTQ' => __('Guatemala Quetzal', 'fflcommerce'),
			'GYD' => __('Guyana Dollar', 'fflcommerce'),
			'HKD' => __('Hong Kong Dollar', 'fflcommerce'),
			'HNL' => __('Honduras Lempira', 'fflcommerce'),
			'HRK' => __('Croatia Kuna', 'fflcommerce'),
			'HUF' => __('Hungary Forint', 'fflcommerce'),
			'IDR' => __('Indonesia Rupiah', 'fflcommerce'),
			'ILS' => __('Israel Shekel', 'fflcommerce'),
			'IMP' => __('Isle of Man Pound', 'fflcommerce'),
			'INR' => __('India Rupee', 'fflcommerce'),
			'IRR' => __('Iran Rial', 'fflcommerce'),
			'ISK' => __('Iceland Krona', 'fflcommerce'),
			'JEP' => __('Jersey Pound', 'fflcommerce'),
			'JMD' => __('Jamaica Dollar', 'fflcommerce'),
			'JPY' => __('Japan Yen', 'fflcommerce'),
			'KGS' => __('Kyrgyzstan Som', 'fflcommerce'),
			'KHR' => __('Cambodia Riel', 'fflcommerce'),
			'KPW' => __('North Korea Won', 'fflcommerce'),
			'KRW' => __('South Korea Won', 'fflcommerce'),
			'KYD' => __('Cayman Islands Dollar', 'fflcommerce'),
			'KZT' => __('Kazakhstan Tenge', 'fflcommerce'),
			'LAK' => __('Laos Kip', 'fflcommerce'),
			'LBP' => __('Lebanon Pound', 'fflcommerce'),
			'LKR' => __('Sri Lanka Rupee', 'fflcommerce'),
			'LRD' => __('Liberia Dollar', 'fflcommerce'),
			'LTL' => __('Lithuania Litas', 'fflcommerce'),
			'LVL' => __('Latvia Lat', 'fflcommerce'),
			'MAD' => __('Moroccan Dirham', 'fflcommerce'),
			'MKD' => __('Macedonia Denar', 'fflcommerce'),
			'MNT' => __('Mongolia Tughrik', 'fflcommerce'),
			'MUR' => __('Mauritius Rupee', 'fflcommerce'),
			'MXN' => __('Mexico Peso', 'fflcommerce'),
			'MYR' => __('Malaysia Ringgit', 'fflcommerce'),
			'MZN' => __('Mozambique Metical', 'fflcommerce'),
			'NAD' => __('Namibia Dollar', 'fflcommerce'),
			'NGN' => __('Nigeria Naira', 'fflcommerce'),
			'NIO' => __('Nicaragua Cordoba', 'fflcommerce'),
			'NOK' => __('Norway Krone', 'fflcommerce'),
			'NPR' => __('Nepal Rupee', 'fflcommerce'),
			'NZD' => __('New Zealand Dollar', 'fflcommerce'),
			'OMR' => __('Oman Rial', 'fflcommerce'),
			'PAB' => __('Panama Balboa', 'fflcommerce'),
			'PEN' => __('Peru Nuevo Sol', 'fflcommerce'),
			'PHP' => __('Philippines Peso', 'fflcommerce'),
			'PKR' => __('Pakistan Rupee', 'fflcommerce'),
			'PLN' => __('Polish Zloty', 'fflcommerce'),
			'PYG' => __('Paraguay Guarani', 'fflcommerce'),
			'QAR' => __('Qatar Riyal', 'fflcommerce'),
			'RON' => __('Romania New Leu', 'fflcommerce'),
			'RSD' => __('Serbia Dinar', 'fflcommerce'),
			'RUB' => __('Russia Ruble', 'fflcommerce'),
			'SAR' => __('Saudi Arabia Riyal', 'fflcommerce'),
			'SBD' => __('Solomon Islands Dollar', 'fflcommerce'),
			'SCR' => __('Seychelles Rupee', 'fflcommerce'),
			'SEK' => __('Sweden Krona', 'fflcommerce'),
			'SGD' => __('Singapore Dollar', 'fflcommerce'),
			'SHP' => __('Saint Helena Pound', 'fflcommerce'),
			'SOS' => __('Somalia Shilling', 'fflcommerce'),
			'SRD' => __('Suriname Dollar', 'fflcommerce'),
			'SVC' => __('El Salvador Colon', 'fflcommerce'),
			'SYP' => __('Syria Pound', 'fflcommerce'),
			'THB' => __('Thailand Baht', 'fflcommerce'),
			'TRL' => __('Turkey Lira', 'fflcommerce'),
			'TRY' => __('Turkey Lira', 'fflcommerce'),
			'TTD' => __('Trinidad and Tobago Dollar', 'fflcommerce'),
			'TVD' => __('Tuvalu Dollar', 'fflcommerce'),
			'TWD' => __('Taiwan New Dollar', 'fflcommerce'),
			'UAH' => __('Ukraine Hryvna', 'fflcommerce'),
			'USD' => __('United States Dollar', 'fflcommerce'),
			'UYU' => __('Uruguay Peso', 'fflcommerce'),
			'UZS' => __('Uzbekistan Som', 'fflcommerce'),
			'VEF' => __('Venezuela Bolivar Fuerte', 'fflcommerce'),
			'VND' => __('Viet Nam Dong', 'fflcommerce'),
			'XCD' => __('East Caribbean Dollar', 'fflcommerce'),
			'YER' => __('Yemen Rial', 'fflcommerce'),
			'ZAR' => __('South Africa Rand', 'fflcommerce'),
			'ZWD' => __('Zimbabwe Dollar', 'fflcommerce'),
		);

		asort($countries);

		return $countries;
	}
}
