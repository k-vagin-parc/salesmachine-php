<?php

require_once(dirname(__FILE__) . "/../lib/Salesmachine/Client.php");

class ConsumerFileTest extends PHPUnit_Framework_TestCase {

  private $client;
  private $filename = "/home/sealk/projs/salesmachine-api/php/analytics-php/test/analytics.log";

  function setUp() {
    date_default_timezone_set("UTC");
    if (file_exists($this->filename)) {
      var_dump("IT EXISTS !");
      unlink($this->filename);
    }

    $this->client = new Salesmachine_Client("fWlU0N6jJKbcgW_OR6OidQ", "UZ8YjpEXXPBYmROvPnJ5jw",
                          array("consumer" => "file",
                                "filename" => $this->filename));

  }

  function tearDown(){
    if (file_exists($this->filename))
      unlink($this->filename);
  }

  function testContact() {
    $this->assertTrue($this->client->set_contact(array(
      "contact_uid" => "754",
      "name" => "Jean Contact"
    )));
    $this->checkWritten("contact");
  }

  function testAccount() {
    $this->assertTrue($this->client->set_account(array(
      "contact_uid" => "7547",
      "name" => "Jean Account"
    )));
    $this->checkWritten("track");
  }

  function testEvent() {
    $this->assertTrue($this->client->track_event(array(
      "contact_uid" => "7549"
    )));
    $this->checkWritten("track");
  }

  function testPageview() {
    $this->assertTrue($this->client->track_pageview(array(
      "contact_uid" => "754",
    )));
    $this->checkWritten("track");
  }

  /*function testSend(){
    for ($i = 0; $i < 200; $i++) {
      $this->client->track(array(
        "userId" => "userId",
        "event" => "event"
      ));
    }
    exec("php --define date.timezone=UTC send.php --secret oq0vdlg7yi --file /tmp/analytics.log", $output);
    $this->assertEquals("sent 200 from 200 requests successfully", trim($output[0]));
    $this->assertFalse(file_exists($this->filename));
  }*/

  function testProductionProblems() {
    # Open to a place where we should not have write access.
    $client = new Salesmachine_Client("fWlU0N6jJKbcgW_OR6OidQ", "UZ8YjpEXXPBYmROvPnJ5jw",
                          array("consumer" => "file",
                                "filename" => "/dev/xxxxxxx" ));

    $tracked = $client->set_contact(array("contact_uid" => "741258"));
    $this->assertFalse($tracked);
  }

  function checkWritten($type) {
    exec("wc -l " . $this->filename, $output);
    $out = trim($output[0]);
    $this->assertEquals($out, "1 " . $this->filename);
    $str = file_get_contents($this->filename);
    var_dump($str);
    $json = json_decode(trim($str));
    unlink($this->filename);
  }

}
?>
