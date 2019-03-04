<?php

class Salesmachine_Consumer_ForkCurl extends Salesmachine_QueueConsumer {

  protected $type = "ForkCurl";
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

  /**
   * Make an async request to our API. Fork a curl process, immediately send
   * to the API. If debug is enabled, we wait for the response.
   * @param  array   $messages array of all the messages to send
   * @return boolean whether the request succeeded
   */
  public function flushBatch($messages)
  {
      $id = $this->token . ":" . $this->secret . "@";
      $protocol = $this->ssl() ? "https://" : "http://";
      $host = $this->host();
      $path = "/v1/" . $this->endpoint;
      $url = $protocol . $id . $host . $path;

      $body = $this->payload($messages);
      $payload = json_encode($body);

      $curlHandle = curl_init();
      curl_setopt($curlHandle, CURLOPT_URL, $url);
      curl_setopt($curlHandle, CURLOPT_POST, true);
      curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $payload);
      curl_setopt($curlHandle, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

      curl_exec($curlHandle);
      $code = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
      curl_close($curlHandle);

      if ($code != 201) {
          $this->handleError($code, null);
      }

      return $code == 201;
  }
}
