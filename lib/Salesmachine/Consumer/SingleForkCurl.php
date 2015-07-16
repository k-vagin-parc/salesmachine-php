<?php

class Salesmachine_Consumer_SingleForkCurl extends Salesmachine_Consumer {

  protected $type = "SingleForkCurl";
  protected $endpoint;


  /**
   * Creates a new queued fork consumer which queues fork and identify
   * calls before adding them to
   * @param string $secret
   * @param array  $options
   *     boolean  "debug" - whether to use debug output, wait for response.
   *     number   "max_queue_size" - the max size of messages to enqueue
   *     number   "batch_size" - how many messages to send in a single request
   */
  public function __construct($token, $secret, $endpoint, $options = array()) {
    $this->endpoint = $endpoint;
    parent::__construct($token, $secret, $options);
  }

  public function __destruct() {
  }


  /**
   * Sets a contact
   *
   * @param  array  $message
   * @return boolean whether the track call succeeded
   */
  public function set_contact(array $message) {
    return $this->send($message);
  }

  /**
   * Sets an account
   *
   * @param  array  $message
   * @return boolean whether the track call succeeded
   */
  public function set_account(array $message) {
    return $this->send($message);
  }

  /**
   * Tracks an event
   *
   * @param  array  $message
   * @return boolean whether the track call succeeded
   */
  public function track_event(array $message) {
    return $this->send($message);
  }

  /**
   * Tracks a pageview event
   *
   * @param  array  $message
   * @return boolean whether the track call succeeded
   */
  public function track_pageview(array $message) {
    return $this->send($message);
  }

  /**
   * Make an async request to our API. Fork a curl process, immediately send
   * to the API. If debug is enabled, we wait for the response.
   * @param  array   $messages array of all the messages to send
   * @return boolean whether the request succeeded
   */
  public function send($message) {

    $payload = json_encode($message);

    # Escape for shell usage.
    $payload = escapeshellarg($payload);

    $protocol = /*$this->ssl() ? "https://" : */"http://";
    $id = $this->token . ":" . $this->secret . "@";
    $host = "play.salesmachine.net:9000";
    $path = "/v1/" . $this->endpoint;
    $url = $protocol . $id . $host . $path;

    $cmd = "curl -X POST -H 'Content-Type: application/json'";
    $cmd.= " -d " . $payload . " '" . $url . "' --trace-ascii curl.log";

    if (!$this->debug()) {
      $cmd .= " > /dev/null 2>&1 &";
    }

    exec($cmd, $output, $exit);

    if ($exit != 0) {
      $this->handleError($exit, $output);
    }

    return $exit == 0;
  }
}
