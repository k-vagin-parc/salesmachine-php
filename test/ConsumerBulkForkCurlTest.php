<?php

require_once(dirname(__FILE__) . "/../lib/Salesmachine/Client.php");

class ConsumerBulkForkCurlTest extends PHPUnit_Framework_TestCase {

  private $client;

  function setUp() {
    date_default_timezone_set("UTC");
    $this->client = new Salesmachine_Client("fWlU0N6jJKbcgW_OR6OidQ", "UZ8YjpEXXPBYmROvPnJ5jw",
                          array("consumer" => "bulk_fork_curl",
                                "host" => "play.salesmachine.net:9000",
                                "ssl" => false,
                                "debug"    => true));
  }

  function testAccount() {
    $this->assertTrue($this->client->set_account("1",
      array(
        "name" => "Jean Account"
      )
    ));
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
  }

  function testEvent() {
    $this->assertTrue($this->client->track_event("7549", "user_registration",
      array(
        "account_uid" => "78910",
        "display_name" => "Registration"
      )
    ));
  }

  function testPageview() {
    $this->assertTrue($this->client->track_pageview("75478",
      array(
        "account_uid" => "78910",
        "display_name" => "Registration"
      )
    ));
  }

  function testFlush() {
    $this->assertTrue($this->client->track_event("7549", "user_registration",
      array(
        "account_uid" => "78910",
        "display_name" => "Registration"
      )
    ));
    $this->assertTrue($this->client->track_pageview("75478",
      array(
        "account_uid" => "78910",
        "display_name" => "Registration"
      )
    ));
    $this->assertTrue($this->client->flush());
  }
}
?>
