<?php

require_once('knowledgebase.php');

class KnowledgeBaseTest extends PHPUnit_Framework_TestCase {
	private $kb;
	
	private $lukasObj = array("this array" => "could be anything", "for example" => "an object", "or" => "a string");
	
	public function __construct() {
		$this->kb = new KnowledgeBase("A KB can have a name!");
	}
	
	public function testShouldTellItsName() {
		$this->assertEquals("A KB can have a name!", $this->kb->what_is_your_name());
		$this->assertEquals("A KB can have a name!", $this->kb->name);
		$this->assertEquals("A KB can have a name!", $this->kb);
	}
	
	public function testShouldSetAName() {
		$this->assertEquals($this->kb, $this->kb->defineNameGetter("tell_me_your_name"));
		$this->assertEquals("A KB can have a name!", $this->kb->tell_me_your_name());
	}
	
	public function testShouldSetData() {
		$this->assertEquals(true, $this->kb->I_am_a("student")->my_name_is("Lukas")->and_I_can("program")->set($this->lukasObj));
		
		$this->assertEquals($this->lukasObj, $this->kb->I_want_a("student")->able_to("program")->with_the_name("Lukas")->gimme()->data);
		$this->assertEquals($this->lukasObj, $this->kb->I_want_a("student")->with_the_name("Lukas")->able_to("program")->gimme()->data);
		
		$this->assertEquals("Lukas", $this->kb->I_want_a("student")->with_the_name("Lukas")->able_to("program")->gimme()->name);
		$this->assertEquals("program", $this->kb->I_want_a("student")->with_the_name("Lukas")->able_to("program")->gimme()->ability);
		$this->assertEquals("student", $this->kb->I_want_a("student")->with_the_name("Lukas")->able_to("program")->gimme()->type);
	}
	
	public function testShouldChangeData() {
		$this->kb->I_am_a("student")->my_name_is("Lukas")->and_I_can("program")->set($this->lukasObj);
		$obj = $this->kb->I_want_a("student")->able_to("program")->with_the_name("Lukas")->gimme();
		
		$obj->name = "Blah";
		$obj->ability = "code";
		$obj->type = "person";
		
		$this->assertEquals($this->lukasObj, $this->kb->I_want_a($obj->type)->able_to($obj->ability)->with_the_name($obj->name)->gimme()->data);
	}
	
	/**
   * @expectedException OutOfBoundsException
   */
	public function testShouldHaveDeletedOldItem() {
		$this->kb->I_want_a("student")->able_to("program")->with_the_name("Lukas")->gimme();
	}
	
	/**
   * @expectedException OutOfBoundsException
   */
	public function testShouldThrowExceptionForNonexistent() {
		$this->kb->I_want_a("computer")->able_to("surf the web")->with_the_name("MacBook Pro")->gimme();
	}
	
	/**
   * @expectedException OutOfBoundsException
   */
	public function testShouldBeRemovable() {
		$this->kb->I_am_a("computer")->I_can("surf the web")->and_my_name_is("Any")->set("Some data about me!");
		$this->assertEquals("Some data about me!", $this->kb->I_want_a("computer")->able_to("surf the web")->with_the_name("Any")->gimme());
		
		$this->assertEquals(true, $this->kb->I_am_the("computer Any")->able_to("surf the web")->remove());
		
		$this->kb->I_want_a("computer")->able_to("surf the web")->with_the_name("Any")->gimme();
	}
	
	public function testShouldDefinePhrase() {
		$this->assertEquals($this->kb, $this->kb->definePhrase("all_in_one", '$ability $type $name'));
		
		$this->assertEquals(true, $this->kb->all_in_one("ability type name")->set("Data"));
		$this->assertEquals("Data", $this->kb->I_want_a("type")->able_to("ability")->with_the_name("name")->gimme()->data);
	}
	
	/**
   * @expectedException DuplicateEntryException
   */
	public function testShouldNotOverridePhrase() {
		$this->assertEquals($this->kb, $this->kb->definePhrase("all_in_one", '$ability $type $name'));
		$this->kb->definePhrase("all_in_one", '$ability and some cool text');
	}
	
	public function testShouldDefineEndpoints() {
		$this->kb->definePhrase("people_think_I_am_very_good_at_writing", '$ability');
		$this->assertEquals($this->kb, $this->kb->defineSetter("make_me_available_for_hire"));
		$this->assertEquals($this->kb, $this->kb->defineGetter("what_is_his_information"));
		$this->assertEquals($this->kb, $this->kb->defineRemover("I_dont_want_to_have_me_in_the_KB"));
		
		$this->kb->undefinePhrase("I_am_the")->definePhrase("I_am_the", '$type named $name');
		
		$this->kb->People_think_I_am_very_good_at_writing("code")->I_am_the("student named Lukas Bestle")->make_me_available_for_hire("Some data about Lukas Bestle!");
		$this->assertEquals("Some data about Lukas Bestle!", $this->kb->I_want_a("student")->able_to("code")->with_the_name("Lukas Bestle")->what_is_his_information()->data);
	}
	
	public function testShouldIterate() {
		$this->kb->definePhrase("people_think_I_am_very_good_at_writing", '$ability');
		$this->assertEquals($this->kb, $this->kb->defineSetter("make_me_available_for_hire"));
		$this->assertEquals($this->kb, $this->kb->defineGetter("what_is_his_information"));
		$this->assertEquals($this->kb, $this->kb->defineRemover("I_dont_want_to_have_me_in_the_KB"));
		
		$this->kb->People_think_I_am_very_good_at_writing("code")->I_am_the("student named luX")->make_me_available_for_hire("Some data about luX!");
		$this->kb->I_am_a("computer")->I_can("surf the web")->and_my_name_is("MacBook Air")->set("I have a lot information!");
		$this->kb->I_am_a("dinner")->I_can("make people well-fed")->and_my_name_is("I don't know, actually...")->set("Yay! I'm in the KB");
		$this->kb->I_can("produce *real* speaking code")->my_name_is("KnowledgeBase")->set($this->kb);
		
		foreach($this->kb->All_of_type("student") as $student) {
			$this->assertEquals("Some data about luX!", $student->data);
			
			break;
		}
		
		foreach($this->kb as $item) {
			$this->assertEquals("Some data about luX!", $item->data);
			
			break;
		}
	}
}