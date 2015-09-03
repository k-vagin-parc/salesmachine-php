<?php

if (!function_exists('json_encode')) {
    throw new Exception('Salesmachine needs the JSON PHP extension.');
}

require(dirname(__FILE__) . '/Salesmachine/Client.php');


class Salesmachine {

  private static $client;

  /**
   * Initializes the default client to use. Uses the socket consumer by default.
   * @param  string $secret   your project's secret key
   * @param  array  $options  passed straight to the client
   */
  public static function init($token, $secret, $options = array()) {
    self::assert($token, "Salesmachine::init() requires token");
    self::assert($secret, "Salesmachine::init() requires secret");

    if (isset($options['use_buffer'])) {
      $options['batch_size'] = $options['use_buffer'] ? 1000 : 1;
    } else {
      $options['batch_size'] = 1000;
    }

    self::$client = new Salesmachine_Client($token, $secret, $options);
  }

  /**
   * Sets a contact
   *
   * @param  array $message
   * @return boolean whether the track call succeeded
   */
  public static function set_contact($contact_uid, array $message = array()) {
    self::checkClient();
    //self::validate($message);
    return self::$client->set_contact($contact_uid, $message);
  }

  /**
   * Sets an account
   *
   * @param  array $message
   * @return boolean whether the track call succeeded
   */
  public static function set_account($account_uid, array $message = array()) {
    self::checkClient();
    //self::validate($message);
    return self::$client->set_account($account_uid, $message);
  }

  /**
   * Tracks an event
   *
   * @param  array $message
   * @return boolean whether the track call succeeded
   */
  public static function track_event($contact_uid, $event_uid, array $message = array()) {
    self::checkClient();
    //self::validate($message);
    return self::$client->track_event($contact_uid, $event_uid, $message);
  }

  /**
   * Tracks a pageview
   *
   * @param  array $message
   * @return boolean whether the track call succeeded
   */
  public static function track_pageview($contact_uid, array $message = array()) {
    self::checkClient();
    //self::validate($message);
    return self::$client->track_pageview($contact_uid, $message);
  }

  /**
   * Validate common properties.
   *
   * @param array $msg
   * @param string $type

  public static function validate($msg){
    $userId = !empty($msg["contact_uid"]);
    self::assert($userId, "Salesmachine requires contact_uid for any request.");
  }*/

  /**
   * Flush the client
   */

  public static function flush(){
    self::checkClient();
    return self::$client->flush();
  }

  /**
   * Check the client.
   *
   * @throws Exception
   */
  private static function checkClient(){
    if (null != self::$client) return;
    throw new Exception("Salesmachine::init() must be called before any other tracking method.");
  }

  /**
   * Assert `value` or throw.
   *
   * @param array $value
   * @param string $msg
   * @throws Exception
   */
  private static function assert($value, $msg){
    if (!$value) throw new Exception($msg);
  }

}
