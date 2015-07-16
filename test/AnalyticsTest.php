<?php

require_once(dirname(__FILE__) . "/../lib/Salesmachine.php");

class AnalyticsTest extends PHPUnit_Framework_TestCase {

  function setUp() {
    date_default_timezone_set("UTC");
    Salesmachine::init("fWlU0N6jJKbcgW_OR6OidQ", "UZ8YjpEXXPBYmROvPnJ5jw", array("debug" => true));
  }

  function testContact() {
    $this->assertTrue(Salesmachine::set_contact("123456",
      array(
        "name" => "Martin Mystère"
      )
    ));
  }

  function testAccount() {
    $this->assertTrue(Salesmachine::set_account("1",
       array(
        "name" => "Jean Account"
      )
    ));
  }

  function testEvent() {
    $this->assertTrue(Salesmachine::track_event("7549", "user_registration",
      array(
        "account_uid" => "78910",
        "display_name" => "Registration"
      )
    ));
  }

  function testPageview() {
    $this->assertTrue(Salesmachine::track_pageview("75478",
      array(
        "account_uid" => "78910",
        "display_name" => "Registration"
      )
    ));
  }
}
?>
