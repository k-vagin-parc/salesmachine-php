<?php

require(__DIR__ . '/Consumer.php');
require(__DIR__ . '/QueueConsumer.php');
require(__DIR__ . '/Consumer/File.php');
require(__DIR__ . '/Consumer/BatchForkCurl.php');
require(__DIR__ . '/Consumer/SingleForkCurl.php');
require(__DIR__ . '/Consumer/Socket.php');

class Salesmachine_Client {

  /**
   * VERSION
   */

  const VERSION = "1.0.0";

  private $consumer_contact;
  private $consumer_about;
  private $consumer_event;
  private $consumer;

  private $token;
  private $mode;

  /**
   * Create a new analytics object with your app's secret
   * key
   *
   * @param string $secret
   * @param array  $options array of consumer options [optional]
   * @param string Consumer constructor to use, socket by default.
   */
  public function __construct($token, $secret, $options = array()) {

    $consumers = array(
      "socket"     => "Salesmachine_Consumer_Socket",
      "file"       => "Salesmachine_Consumer_File",
      "batch_fork_curl"  => "Salesmachine_Consumer_BatchForkCurl",
      "single_fork_curl"  => "Salesmachine_Consumer_SingleForkCurl"
    );

    # Use our curl single-request consumer by default
    $consumer_type = isset($options["consumer"]) ? $options["consumer"] :
                                                   "single_fork_curl";
    $Consumer = $consumers[$consumer_type];

    if ($Consumer == "Salesmachine_Consumer_SingleForkCurl") {
      $this->mode = "single";
      # Create a consumer by endpoint
      $this->consumer_contact = new $Consumer($token, $secret, "contact", $options);
      $this->consumer_account = new $Consumer($token, $secret, "account", $options);
      $this->consumer_event = new $Consumer($token, $secret, "track/event", $options);
    } else {
      $this->mode = "batch";
      $this->consumer = new $Consumer($token, $secret, "batch", $options);
    }

    $this->token = $token;
  }

  public function __destruct() {
    if ($this->mode == "single") {
      $this->consumer_contact->__destruct();
      $this->consumer_account->__destruct();
      $this->consumer_event->__destruct();
    } else {
      $this->consumer->__destruct();
    }
  }

  /**
   * Sets a contact
   *
   * @param  array $message
   * @return [boolean] whether the track call succeeded
   */
  public function set_contact($contact_uid, array $message = array()) {
    $data = array();
    $data['contact_uid'] = $contact_uid;
    $data['params'] = $message;

    if ($this->mode == "single") {
      return $this->consumer_contact->set_contact($this->message($data));
    } else {
      $data['method'] = 'contact';
      return $this->consumer->set_contact($this->message($data));
    }
  }

  /**
   * Sets an account
   *
   * @param  array $message
   * @return [boolean] whether the track call succeeded
   */
  public function set_account($account_uid, array $message = array()) {
    $data = array();
    $data['account_uid'] = $account_uid;
    $data['params'] = $message;

    if ($this->mode == "single") {
      return $this->consumer_account->set_account($this->message($data));
    } else {
      $data['method'] = 'account';
      return $this->consumer->set_account($this->message($data));
    }
  }

  /**
   * Tracks an event
   *
   * @param  array $message
   * @return [boolean] whether the track call succeeded
   */
  public function track_event($contact_uid, $event_uid, array $message = array()) {
    $data = array();
    $data['contact_uid'] = $contact_uid;
    $data['event_uid'] = $event_uid;
    $data['params'] = $message;

    if ($this->mode == "single") {
      return $this->consumer_event->track_event($this->message($data));
    } else {
      $data['method'] = 'event';
      return $this->consumer->track_event($this->message($data));
    }
  }

  /**
   * Tracks a pageview
   *
   * @param  array $message
   * @return [boolean] whether the track call succeeded
   */
  public function track_pageview($contact_uid, array $message = array()) {
    //$message["type"] = "track";
    $data = array();
    $data['contact_uid'] = $contact_uid;
    $data['event_uid'] = "pageview";
    $data['params'] = $message;

    if ($this->mode == "single") {
      return $this->consumer_event->track_event($this->message($data));
    } else {
      $data['method'] = 'event';
      return $this->consumer->track_event($this->message($data));
    }
  }


  /**
   * Flush any async consumers
   */
  public function flush() {
    if (!method_exists($this->consumer, 'flush')) return false;
    return $this->consumer->flush();
  }

  /**
   * Formats a timestamp by making sure it is set
   * and converting it to iso8601.
   *
   * The timestamp can be time in seconds `time()` or `microseconds(true)`.
   * any other input is considered an error and the method will return a new date.
   *
   * Note: php's date() "u" format (for microseconds) has a bug in it
   * it always shows `.000` for microseconds since `date()` only accepts
   * ints, so we have to construct the date ourselves if microtime is passed.
   *
   * @param  time $timestamp - time in seconds (time())
   */
  private function formatTime($ts) {
    // time()
    if ($ts == null) $ts = time();
    if (is_integer($ts)) return date("c", $ts);

    // anything else return a new date.
    if (!is_float($ts)) return date("c");

    // fix for floatval casting in send.php
    $parts = explode(".", (string)$ts);
    if (!isset($parts[1])) return date("c", (int)$parts[0]);

    // microtime(true)
    $sec = (int)$parts[0];
    $usec = (int)$parts[1];
    $fmt = sprintf("Y-m-d\TH:i:s%sP", $usec);
    return date($fmt, (int)$sec);
  }

  /**
   * Add common fields to the given `message`
   *
   * @param array $msg
   * @param string $def
   * @return array
   */

  private function message($msg, $def = ""){
    /* To define later eventually*/
    //$created_at = $this->formatTime(null);
    return $msg;
  }

  /**
   * Generate a random messageId.
   *
   * https://gist.github.com/dahnielson/508447#file-uuid-php-L74
   *
   * @return string
   */

  private static function messageId(){
    return sprintf("%04x%04x-%04x-%04x-%04x-%04x%04x%04x"
      , mt_rand(0, 0xffff)
      , mt_rand(0, 0xffff)
      , mt_rand(0, 0xffff)
      , mt_rand(0, 0x0fff) | 0x4000
      , mt_rand(0, 0x3fff) | 0x8000
      , mt_rand(0, 0xffff)
      , mt_rand(0, 0xffff)
      , mt_rand(0, 0xffff));
  }

  /**
   * Add the Salesmachine.io context to the request
   * @return array additional context
   */
  private function getContext () {
    return array(
      "library" => array(
        "name" => "analytics-php",
        "version" => self::VERSION
      )
    );
  }
}
