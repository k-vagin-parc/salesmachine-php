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


  function testAccount() {
    $this->assertTrue($this->client->set_account("1",
      array(
        "name" => "Jean Account"
      )
    ));
    $this->checkWritten("account");
  }

   function testContact() {
    $this->assertTrue($this->client->set_contact("1",
      array(
        "email" => "Test post",
        "display_name" => "coucou",
        "name" => "Jean Contact",
        "account_uid" => "1"
      )
    ));
    $this->checkWritten("contact");
  }

  function testEvent() {
    $this->assertTrue($this->client->track_event("7549", "user_registration",
      array(
        "account_uid" => "78910",
        "display_name" => "Registration"
      )
    ));
    $this->checkWritten("event");
  }

  function testPageview() {
    $this->assertTrue($this->client->track_pageview("75478",
      array(
        "account_uid" => "78910",
        "display_name" => "Registration"
      )
    ));
    $this->checkWritten("pageview");
  }

  function testSend(){
    for ($i = 0; $i < 200; $i++)
      $this->client->set_contact("1");

    exec("php --define date.timezone=UTC send.php --secret oq0vdlg7yi --file /home/sealk/projs/salesmachine-api/php/analytics-php/test/analytics.log", $output);
    $this->assertEquals("sent 200 from 200 requests successfully", trim($output[0]));
    $this->assertFalse(file_exists($this->filename));
  }

  function testProductionProblems() {
    # Open to a place where we should not have write access.
    $client = new Salesmachine_Client("fWlU0N6jJKbcgW_OR6OidQ", "UZ8YjpEXXPBYmROvPnJ5jw",
                          array("consumer" => "file",
                                "filename" => "/dev/xxxxxxx" ));

    $tracked = $client->set_contact("41258");
    $this->assertFalse($tracked);
  }

  function checkWritten($type) {
    exec("wc -l " . $this->filename, $output);
    $out = trim($output[0]);
    $this->assertEquals($out, "1 " . $this->filename);
    $str = file_get_contents($this->filename);
    $json = json_decode(trim($str));
    unlink($this->filename);
  }

}
?>
