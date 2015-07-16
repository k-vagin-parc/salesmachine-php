<?php

require_once(dirname(__FILE__) . "/../lib/Salesmachine.php");

class AnalyticsTest extends PHPUnit_Framework_TestCase {

  function setUp() {
    date_default_timezone_set("UTC");
    Salesmachine::init("fWlU0N6jJKbcgW_OR6OidQ", "UZ8YjpEXXPBYmROvPnJ5jw", array("debug" => true));
  }

  function testContact() {
    $this->assertTrue(Salesmachine::set_contact(array(
      "contact_uid" => "123456",
      "params" => array(
        "name" => "Martin Mystère"
      )
    )));
  }

  function testAccount() {
    $this->assertTrue(Salesmachine::set_account(array(
      "contact_uid" => "1",
      "params" => array(
        "name" => "Jean Account"
      )
    )));
  }

  function testEvent() {
    $this->assertTrue(Salesmachine::track_event(array(
      "contact_uid" => "7549",
      "event_uid" => "user_registration",
      "params" => array(
        "account_uid" => "78910",
        "display_name" => "Registration"
      )
    )));
  }

  function testPageview() {
    $this->assertTrue(Salesmachine::track_pageview(array(
      "contact_uid" => "75478",
      "params" => array(
        "account_uid" => "78910",
        "display_name" => "Registration"
      )
    )));
  }
}
?>
