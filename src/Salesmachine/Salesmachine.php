<?php

namespace Salesmachine;

class Salesmachine
{

  // mandatory api credentials
  static $api_version     = '1';
  static $api_url         = 'api.salesmachine.io';
  static $api_token       = null;
  static $api_secret      = null;
  static $unique_user_id  = null;
  // options
  static $use_https       = true;
  static $encode          = 'none';
  static $use_buffer      = false;
  static $log_dir         = '/logs/';
  static $log_file_buffer = 'salesmachine_buffer.log';
  static $log_file_debug  = 'salesmachine_debug.log';
  static $debug           = false;
  static $prod_env        = false;
  static $epoch           = null;

  /**
   * Init the Salesmachine client library. See readme file for explanation of options.
   * @param type $api_token
   * @param type $api_secret
   * @param type $options
   * @return type
   * @throws Exception
   */
  static function init($api_token, $api_secret, $options = array())
  {
    self::$api_token = $api_token;
    self::$api_secret = $api_secret;

    self::$use_https = self::option_or_default('use_https', $options, self::$use_https);
    self::$encode = self::option_or_default('encode', $options, self::$encode);
    self::$use_buffer = self::option_or_default('use_buffer', $options, self::$use_buffer);
    self::$log_dir = self::option_or_default('log_dir', $options, self::$log_dir);
    self::$log_file_buffer = self::option_or_default('log_file_buffer', $options, self::$log_file_buffer);
    self::$log_file_debug = self::option_or_default('log_file_debug', $options, self::$log_file_debug);
    self::$debug = self::option_or_default('debug', $options, self::$debug);
    self::$prod_env = self::option_or_default('prod_env', $options, self::$prod_env);
    self::$epoch = self::option_or_default('epoch', $options, self::$epoch);

    // check if everything we need is installed and working
    if ((self::$use_buffer || self::$debug) && !is_writable(self::$log_dir)) {
      throw new Exception('Unable to write to log dir ' . self::$log_dir);
    }
    if (!function_exists('curl_init')) {
      throw new Exception('Salesmachine needs the CURL PHP extension.');
    }
    if (!function_exists('json_decode')) {
      throw new Exception('Salesmachine needs the JSON PHP extension.');
    }

    return self::is_initialized();
  }

  /**
   * Identifies the user we want to track. 
   * @param type $unique_user_id A unique string identifying your user
   */
  static function identify($unique_user_id)
  {
    self::$unique_user_id = $unique_user_id;
  }

  /**
   * Set the parameters of the identified user. Typical values are name, email, user group, etc.
   * @param array $params
   * @return type
   */
  static function set($params = array())
  {
    if (!self::is_identified()) {
      return;
    }

    if (!$params || !is_array($params)) {
      $params = array();
    }

    $message = array(
      'unique_id'  => self::$unique_user_id,
      'created_at' => self::get_time(),
      'params'     => $params,
    );

    self::store_or_send('user', 'POST', $message);
  }

  /**
   * Track a pageview for the identified user
   * @param type $location
   * @param type $user_ip
   * @param type $user_agent
   * @return type
   */
  static function pageview($location, $user_ip = '', $user_agent = '')
  {
    if (!self::is_identified()) {
      return;
    }
    
    /* Try to find the client IP if not provided */
    if (!$user_ip) {
      foreach (array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP', 'REMOTE_ADDR') as $header)
			{
        $user_ip = array_pop(explode(',', $_SERVER[$header]));
        if (filter_var($user_ip, FILTER_VALIDATE_IP)){
          break;
        }
      }
    }
    /* Get the user agent if not provided */
    if (!$user_agent) {
      $user_agent = $_SERVER['HTTP_USER_AGENT'];
    }
    
    $message = array(
      'unique_id'  => self::$unique_user_id,
      'created_at' => self::get_time(),
      'event'      => 'pageview',
      'params'     => array(
        'location'   => $location,
        'user_ip'    => $user_ip,
        'user_agent' => $user_agent
      ),
    );
    self::store_or_send('pageview', 'POST', $message);
  }

  /**
   * Track an event for an identified user
   * @param type $title
   * @param type $params
   * @return type
   */
  static function event($title, $params = array())
  {
    if (!self::is_identified()) {
      return;
    }

    if (!$params || !is_array($params)) {
      $params = array();
    }

    $message = array(
      'unique_id'  => self::$unique_user_id,
      'event'      => $title,
      'created_at' => self::get_time(),
      'params'     => $params,
    );

    self::store_or_send('event', 'POST', $message);
  }

  /**
   * Creates or updates a data element in Salesmachine. A data element can be 
   * linked to to a user using user_id inside the parameters.
   * @param type $unique_id
   * @param array $params
   * @return type
   */
  static function element($unique_id, $dataset, $params = array())
  {
    if (!self::is_identified()) {
      return;
    }

    if (!$params || !is_array($params)) {
      $params = array();
    }

    $params['dataset'] = $dataset;

    $message = array(
      'unique_id'  => $unique_id,
      'created_at' => self::get_time(),
      'params'     => $params
    );

    self::store_or_send('pageview', 'POST', $message);
  }

  /**
   * Used for processing the locally stored buffer of requests. 
   * Reads all previously stored requests and sends them to salesmachine.
   * Call this functions inside a cron job with sufficient runtime length.
   * @return boolean
   */
  static function send_buffer()
  {
    self::log_debug('Start sending buffer ...');

    $requests_sent   = 0;
    $buffer_file     = self::$log_dir . self::$log_file_buffer;
    $buffer_file_tmp = self::$log_dir . self::$log_file_buffer . '.' . rand(1, 100000) . '.tmp';

    try {

      if (!is_file($buffer_file)) {
        self::log_debug('Buffer file does not exists, nothing found to sent.');
        return false;
      }

      // take the current buffer and put it inside a unique tmp buffer
      rename($buffer_file, $buffer_file_tmp);

      // read and process the tmp buffer
      $fh = fopen($buffer_file_tmp, "r");
      if ($fh) {
        while (!feof($fh)) {
          $data_serialized = fgets($fh);

          if ($data_serialized == false) {
            continue;
          }

          $data = unserialize($data_serialized);
          
          if (self::send($data['e'], $data['m'], $data['d'])) {
            $requests_sent++;
          }
        }
        fclose($fh);
      }
      unlink($buffer_file_tmp);

      self::log_debug('Queue was sent successfully! Sent ' . $requests_sent . ' requests.');
    } catch (Exception $e) {
      self::log_error($e->getMessage());
    }
  }

  static protected function store_or_send($endpoint, $method, $data)
  {
    if (self::$use_buffer) {
      self::store_in_buffer($endpoint, $method, $data);
    } else {
      self::send($endpoint, $method, $data);
    }
  }

  static protected function store_in_buffer($endpoint, $method, $data)
  {
    $data_json = serialize(array(
      'e' => $endpoint,
      'm' => $method,
      'd' => $data));

    $fh = fopen(self::$log_dir . self::$log_file_buffer, 'a');
    if ($fh) {
      fputs($fh, $data_json . "\n");
      fclose($fh);
    } else {
      self::log_error('Queue file not writeable');
      return false;
    }
  }

  static protected function is_initialized()
  {
    if (self::$api_token) {
      return true;
    }

    self::log_error("Salesmachine not initialized. Call Salesmachine::init(<token>,<secret>) at least once.");
  }

  static protected function is_identified()
  {
    if (self::$unique_user_id) {
      return true;
    }

    self::log_error("No user identified. Call Salesmachine::identify(<unique_id>) at least once.");
  }

  static function set_time($time)
  {
    self::$epoch = $time;
  }

  static protected function get_time()
  {
    return !is_null(self::$epoch) ? self::$epoch : time();
  }

  static protected function log_error($msg)
  {
    self::log_debug($msg, 'error');
    if (!self::$prod_env) {
      throw new Exception($msg);
    } else {
      error_log("[SALESMACHINE] " . $msg);
    }
  }

  static protected function log_debug($msg, $type = 'debug')
  {
    if (!self::$debug) {
      return;
    }

    try {
      $fh = fopen(self::$log_dir . self::$log_file_debug, 'a');
      if ($fh) {
        fputs($fh, '[' . date('c') . '] [' . strtoupper($type) . '] ' . $msg . "\r\n");
        fclose($fh);
      }
    } catch (Exception $e) {
      // just ignore
      return false;
    }
  }

  static protected function endpoint_url($method)
  {
    $base_url = (self::$use_https ? 'https://' : 'http://') . self::$api_url;
    if (self::$api_version == '1') {
      $endpoints = array(
        'user'     => $base_url . '/v1/user',
        'element'  => $base_url . '/v1/element',
        'event'    => $base_url . '/v1/track/event',
        'pageview' => $base_url . '/v1/track/pageview',
      );
    }

    if (!isset($endpoints[$method])) {
      self::log_error('Salesmachine endpoint does not exist.');
      return false;
    }
    return $endpoints[$method];
  }

  static protected function option_or_default($key, $option_array, $default)
  {
    return array_key_exists($key, $option_array) ? $option_array[$key] : $default;
  }

  static protected function send($endpoint, $method, $data)
  {
    if (!self::is_initialized()) {
      return;
    }

    if (!is_array($data) || count($data) < 1) {
      self::log_error('No data for Salesmachine request define');
      return;
    }

    $url = self::endpoint_url($endpoint);
    if ($url === false) {
      self::log_error('Endpoint not valid');
      return;
    }

    $message = array(
      'api_token' => self::$api_token,
      'encode'    => 'none',
      'data'      => json_encode($data)
    );

    $headers = array(
      "Accept: application/json",
      "AcceptEncoding: gzip, deflate"
    );

    $process = curl_init($url);
    if (self::$debug) {
      curl_setopt($process, CURLOPT_VERBOSE, true);
    }
    curl_setopt($process, CURLOPT_HEADER, 1);
    curl_setopt($process, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($process, CURLOPT_USERPWD, self::$api_token . ":" . self::$api_secret);
    curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
    if ($method == 'POST') {
      curl_setopt($process, CURLOPT_POSTFIELDS, $message);
      curl_setopt($process, CURLOPT_POST, true);
    } elseif ($method == 'PUT') {
      curl_setopt($process, CURLOPT_CUSTOMREQUEST, 'PUT');
      curl_setopt($process, CURLOPT_POSTFIELDS, $message);
      $headers[] = 'Content-Length: ' . strlen($message);
    } elseif ($method != 'GET') {
      curl_setopt($process, CURLOPT_CUSTOMREQUEST, $method);
    }
    curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($process, CURLOPT_TIMEOUT, 30);
    curl_setopt($process, CURLOPT_POST, 1);
    curl_setopt($process, CURLOPT_POSTFIELDS, $message);
    curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
    $return = curl_exec($process);

    if (self::$debug) {
      self::log_debug('URL ' . $url);
      self::log_debug('MESSAGE ' . print_r($message, 1));
      $return_array = curl_getinfo($process);
      self::log_debug('RETURN CODE ' . $return_array['http_code]']);
      self::log_debug('RETURN ' . $return);
    }

    curl_close($process);
    return true;
  }

}
