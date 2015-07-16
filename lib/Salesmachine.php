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
    self::$client = new Salesmachine_Client($token, $secret, $options);
  }

  /**
   * Sets a contact
   *
   * @param  array $message
   * @return boolean whether the track call succeeded
   */
  public static function set_contact(array $message) {
    self::checkClient();
    self::validate($message);
    return self::$client->set_contact($message);
  }

  /**
   * Sets an account
   *
   * @param  array $message
   * @return boolean whether the track call succeeded
   */
  public static function set_account(array $message) {
    self::checkClient();
    self::validate($message);
    return self::$client->set_account($message);
  }

  /**
   * Tracks an event
   *
   * @param  array $message
   * @return boolean whether the track call succeeded
   */
  public static function track_event(array $message) {
    self::checkClient();
    self::validate($message);
    return self::$client->track_event($message);
  }

  /**
   * Tracks a pageview
   *
   * @param  array $message
   * @return boolean whether the track call succeeded
   */
  public static function track_pageview(array $message) {
    self::checkClient();
    self::validate($message);
    return self::$client->track_pageview($message);
  }

  /**
   * Validate common properties.
   *
   * @param array $msg
   * @param string $type
   */
  public static function validate($msg){
    $userId = !empty($msg["contact_uid"]);
    self::assert($userId, "Salesmachine::* requires contact_uid");
  }

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
