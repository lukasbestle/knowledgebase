<?php

// Require the library
require_once("knowledgebase.php");

// Initialize a new KB.
$kb = new KnowledgeBase("A KB can have a name!");

// Get the name of the KB.
echo "The name of the KB is:                      " . $kb->what_is_your_name() . "\n";
echo "You can also get it using the instance var: " . $kb->name . "\n";

// You can define names to get the KB name
$kb->defineNameGetter("tell_me_your_name");
echo "Name got using the new defined name getter: " . $kb->tell_me_your_name() . "\n\n";

// Let's add a person to it who can program and who's name is Lukas.
// He has this cool data - but as it says, that could be *anything* valid in PHP.
$lukasObj = array("this array" => "could be anything", "for example" => "an object", "or" => "a string");

// Let's tell the KB I'm here and I want to participate.
$kb->I_am_a("person")->my_name_is("Lukas")->and_I_can("program")->set($lukasObj);

// Let's get our data back out of it...
// There are two different ways to do that - depending on the language that makes more sense here
$lukasObj1 = $kb->I_want_a("person")->able_to("program")->with_the_name("Lukas")->gimme();
$lukasObj2 = $kb->I_want_a("person")->with_the_name("Lukas")->able_to("program")->gimme();

// Let's see: Does the KB remember me?
echo "Data before the KB took my data:\n";
print_r($lukasObj);

echo "\nData in query way number 1:\n";
print_r($lukasObj1);

echo "\nData in query way number 2:\n";
print_r($lukasObj2);

// Let's give the KB a computer with the ability to surf the web who's name is "Any".
// You see: You can also query write operations other way round: First ability, then name!
$kb->I_am_a("computer")->I_can("surf the web")->and_my_name_is("Any")->set("Some data about me!");

// Let's get the data of this computer back...
$computer1 = $kb->I_want_a("computer")->able_to("surf the web")->with_the_name("Any")->gimme();

// Now, let's ask for a computer which is not in the KB!
$computer2 = $kb->I_want_a("computer")->able_to("surf the web")->with_the_name("MacBook Pro")->gimme();

echo "\n\nOur computer able to surf the web: $computer1";

echo "\nData of something not in the KB:   ";
var_dump($computer2);

// Our computer does not want to stay in the KB!
$result = $kb->I_am_the("computer Any")->able_to("surf the web")->remove();

// $result contains if the deletion worked.

// Try to get him back...
echo "\nOur computer able to surf the web should not be in the KB anymore: ";
var_dump($kb->I_want_a("computer")->able_to("surf the web")->with_the_name("Any")->gimme());

// Now, we want to use some other phrases to access the KB. Let's tell it about that...
// This one defines that a phrase named "my_cool_name" defines the property $name.
$kb->definePhrase("people_think_I_am_very_good_at_writing", '$ability');

// Let us define another one
// Here, we need to remove the old phrase to do that
// You can do the same with setters, getters, removers and name getters!
$kb->undefinePhrase("I_am_a");
// You can define all formats fitting in a string. This is then matched by a RegEx.
$kb->definePhrase("I_am_a", '$type named $name');

// You could also write that like so:
$kb->undefinePhrase("I_am_a")->definePhrase("I_am_a", '$type named $name');

// Because the KB has to know if your phrases are setter, getter or remover, you have to append "set", "gimme" or "remove" to the query
// But you can change that!
$kb->defineSetter("make_me_available_for_hire");
$kb->defineGetter("what_is_his_information");
$kb->defineRemover("I_dont_want_to_have_me_in_the_KB");

// Let's test that!
// Note I use capitalization for the p in people - you can do that with the first char :)
$kb->People_think_I_am_very_good_at_writing("code")->I_am_a("student named Lukas Bestle")->make_me_available_for_hire("Some data about me!");
echo "\nHere I am again: " . $kb->I_want_a("student")->able_to("code")->with_the_name("Lukas Bestle")->what_is_his_information() . "\n";