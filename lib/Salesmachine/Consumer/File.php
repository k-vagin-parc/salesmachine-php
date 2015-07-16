<?php

class Salesmachine_Consumer_File extends Salesmachine_Consumer {

  private $file_handle;
  protected $type = "File";

  /**
   * The file consumer writes track and identify calls to a file.
   * @param string $secret
   * @param array  $options
   *     string "filename" - where to log the analytics calls
   */
  public function __construct($token, $secret, $endpoint, $options = array()) {

    if (!isset($options["filename"]))
      $options["filename"] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "analytics.log";

    parent::__construct($secret, $options);

    try {
      $this->file_handle = fopen($options["filename"], "a");
      chmod($options["filename"], 0777);
    } catch (Exception $e) {
      $this->handleError($e->getCode(), $e->getMessage());
    }
  }

  public function __destruct() {
    if ($this->file_handle &&
        get_resource_type($this->file_handle) != "Unknown") {
      fclose($this->file_handle);
    }
  }

  /**
   * Sets a contact
   *
   * @param  array $message
   * @return [boolean] whether the track call succeeded
   */
  public function set_contact(array $message) {
    return $this->write($message);
  }

  /**
   * Sets  an account
   *
   * @param  array $message
   * @return [boolean] whether the track call succeeded
   */
  public function set_account(array $message) {
    return $this->write($message);
  }

  /**
   * Tracks an event
   *
   * @param  array $message
   * @return [boolean] whether the track call succeeded
   */
  public function track_event(array $message) {
    return $this->write($message);
  }

  /**
   * Tracks a pageview
   *
   * @param  array $message
   * @return [boolean] whether the track call succeeded
   */
  public function track_pageview(array $message) {
    return $this->write($message);
  }

  /**
   * Writes the API call to a file as line-delimited json
   * @param  [array]   $body post body content.
   * @return [boolean] whether the request succeeded
   */
  private function write($body) {

    if (!$this->file_handle)
      return false;

    $content = json_encode($body);
    $content.= "\n";

    return fwrite($this->file_handle, $content) == strlen($content);
  }
}
