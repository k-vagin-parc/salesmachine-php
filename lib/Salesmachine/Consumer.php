<?php
abstract class Salesmachine_Consumer {

  protected $type = "Consumer";

  protected $options;
  protected $token;
  protected $secret;

  /**
   * Store our secret and options as part of this consumer
   * @param string $secret
   * @param array  $options
   */
  public function __construct($token, $secret, $options = array()) {
    $this->token = $token;
    $this->secret = $secret;
    $this->options = $options;
  }


  /**
   * Sets a contact
   *
   * @param  array  $message
   * @return boolean whether the track call succeeded
   */
  abstract public function set_contact(array $message);

  /**
   * Sets an account
   *
   * @param  array  $message
   * @return boolean whether the track call succeeded
   */
  abstract public function set_account(array $message);

  /**
   * Track an event
   *
   * @param  array  $message
   * @return boolean whether the track call succeeded
   */
  abstract public function track_event(array $message);

  /**
   * Track a pageview
   *
   * @param  array  $message
   * @return boolean whether the track call succeeded
   */
  abstract public function track_pageview(array $message);

  /**
   * Check whether debug mode is enabled
   * @return boolean
   */
  protected function debug() {
    return isset($this->options["debug"]) ? $this->options["debug"] : false;
  }

  /**
   * Check whether we should connect to the API using SSL. This is enabled by
   * default with connections which make batching requests. For connections
   * which can save on round-trip times, you may disable it.
   * @return boolean
   */
  protected function ssl() {
    return isset($this->options["ssl"]) ? $this->options["ssl"] : true;
  }


  /**
   * On an error, try and call the error handler, if debugging output to
   * error_log as well.
   * @param  string $code
   * @param  string $msg
   */
  protected function handleError($code, $msg) {

    if (isset($this->options['error_handler'])) {
      $handler = $this->options['error_handler'];
      $handler($code, $msg);
    }

    if ($this->debug()) {
      error_log("[Analytics][" . $this->type . "] " . $msg);
    }
  }
}
