<?php

// Require the library
require_once("knowledgebase.php");

// Initialize a new KB.
$kb = new KnowledgeBase("A KB can have a name!");

// Get the name of the KB.
echo "The name of the KB is:                      " . $kb->what_is_your_name() . "\n";
echo "You can also get it using the instance var: " . $kb->name . "\n\n";

// Let's add a person to it who can program and who's name is Lukas.
// He has this cool data - but as it says, that could be *anything* valid in PHP.
$lukasObj = array("this array" => "could be anything", "for example" => "an object", "or" => "a string");

// Let's tell the KB I'm here and I want to participate.
$kb->I_am_a("person")->my_name_is("Lukas")->and_I_can("program")->here_I_am($lukasObj);

// Let's get our data back out of it...
// There are two different ways to do that - depending on the language that makes more sense here
$lukasObj1 = $kb->I_want_a("person")->able_to("program")->with_the_name("Lukas");
$lukasObj2 = $kb->I_want_a("person")->with_the_name("Lukas")->able_to("program");

// Let's see: Does the KB remember me?
echo "Data before the KB took my data:\n";
print_r($lukasObj);

echo "\nData in query way number 1:\n";
print_r($lukasObj1);

echo "\nData in query way number 2:\n";
print_r($lukasObj2);

// Let's give the KB a computer with the ability to surf the web who's name is "Any".
// You see: You can also query write operations other way round: First ability, then name!
$kb->I_am_a("computer")->I_can("surf the web")->and_my_name_is("Any")->here_I_am("Some data about me!");

// Let's get the data of this computer back...
$computer1 = $kb->I_want_a("computer")->able_to("surf the web")->with_the_name("Any");

// Now, let's ask for a computer which is not in the KB!
$computer2 = $kb->I_want_a("computer")->able_to("surf the web")->with_the_name("MacBook Pro");

echo "\n\nOur computer able to surf the web: $computer1";

echo "\nData of something not in the KB:   ";
var_dump($computer2);

// Our computer does not want to stay in the KB!
$result = $kb->I_am_the("computer Any")->able_to("surf the web")->please_delete_me();

// $result contains if the deletion worked.

// Try to get him back...
echo "\nOur computer able to surf the web should not be in the KB anymore: ";
var_dump($kb->I_want_a("computer")->able_to("surf the web")->with_the_name("Any"));