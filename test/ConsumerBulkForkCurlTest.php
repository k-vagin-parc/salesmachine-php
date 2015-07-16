<?php

require_once(dirname(__FILE__) . "/../lib/Salesmachine/Client.php");

class ConsumerBulkForkCurlTest extends PHPUnit_Framework_TestCase {

  private $client;

  function setUp() {
    date_default_timezone_set("UTC");
    $this->client = new Salesmachine_Client("fWlU0N6jJKbcgW_OR6OidQ", "UZ8YjpEXXPBYmROvPnJ5jw",
                          array("consumer" => "bulk_fork_curl",
                                "debug"    => true));
  }

  function testAccount() {
    $this->assertTrue($this->client->set_account(array(
      "contact_uid" => "1",
      "name" => "Jean Account"
    )));
  }

   function testContact() {
    $this->assertTrue($this->client->set_contact(array(
      "contact_uid" => "1",
      "email" => "Test post",
      "display_name" => "coucou",
      "name" => "Jean Contact",
      "account_uid" => "1"
    )));
  }

  function testEvent() {
    $this->assertTrue($this->client->track_event(array(
      "contact_uid" => "7549"
    )));
  }

  function testPageview() {
    $this->assertTrue($this->client->track_pageview(array(
      "contact_uid" => "75478"
    )));
  }

  function testFlush() {
    $this->assertTrue($this->client->flush());
  }
}
?>
