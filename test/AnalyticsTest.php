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

  /*function testGroup(){
    $this->assertTrue(Segment::group(array(
      "groupId" => "group-id",
      "userId" => "user-id",
      "traits" => array(
        "plan" => "startup"
      )
    )));
  }

  function testMicrotime(){
    $this->assertTrue(Segment::page(array(
      "anonymousId" => "anonymous-id",
      "name" => "analytics-php-microtime",
      "category" => "docs",
      "timestamp" => microtime(true),
      "properties" => array(
        "path" => "/docs/libraries/php/",
        "url" => "https://segment.io/docs/libraries/php/"
      )
    )));
  }

  function testPage(){
    $this->assertTrue(Segment::page(array(
      "anonymousId" => "anonymous-id",
      "name" => "analytics-php",
      "category" => "docs",
      "properties" => array(
        "path" => "/docs/libraries/php/",
        "url" => "https://segment.io/docs/libraries/php/"
      )
    )));
  }

  function testBasicPage(){
    $this->assertTrue(Segment::page(array(
      "anonymousId" => "anonymous-id"
    )));
  }

  function testScreen(){
    $this->assertTrue(Segment::screen(array(
      "anonymousId" => "anonymous-id",
      "name" => "2048",
      "category" => "game built with php :)",
      "properties" => array(
        "points" => 300
      )
    )));
  }

  function testBasicScreen(){
    $this->assertTrue(Segment::screen(array(
      "anonymousId" => "anonymous-id"
    )));
  }

  function testIdentify() {
    $this->assertTrue(Segment::identify(array(
      "userId" => "doe",
      "traits" => array(
        "loves_php" => false,
        "birthday" => time()
      )
    )));
  }

  function testEmptyTraits() {
    $this->assertTrue(Segment::identify(array(
      "userId" => "empty-traits"
    )));

    $this->assertTrue(Segment::group(array(
      "userId" => "empty-traits",
      "groupId" => "empty-traits"
    )));
  }

  function testEmptyArrayTraits() {
    $this->assertTrue(Segment::identify(array(
      "userId" => "empty-traits",
      "traits" => array()
    )));

    $this->assertTrue(Segment::group(array(
      "userId" => "empty-traits",
      "groupId" => "empty-traits",
      "traits" => array()
    )));
  }

  function testEmptyProperties() {
    $this->assertTrue(Segment::track(array(
      "userId" => "user-id",
      "event" => "empty-properties"
    )));

    $this->assertTrue(Segment::page(array(
      "category" => "empty-properties",
      "name" => "empty-properties",
      "userId" => "user-id"
    )));
  }

  function testEmptyArrayProperties(){
    $this->assertTrue(Segment::track(array(
      "userId" => "user-id",
      "event" => "empty-properties",
      "properties" => array()
    )));

    $this->assertTrue(Segment::page(array(
      "category" => "empty-properties",
      "name" => "empty-properties",
      "userId" => "user-id",
      "properties" => array()
    )));
  }

  function testAlias() {
    $this->assertTrue(Segment::alias(array(
      "previousId" => "previous-id",
      "userId" => "user-id"
    )));
  }*/
}
?>
