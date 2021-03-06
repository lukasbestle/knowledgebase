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
echo "Name got using the new defined name getter: " . $kb->tell_me_your_name() . "\n";

// You can also just print out the object
echo "The same using the object itself:           " . $kb . "\n\n";

// Let's add a person to it who can program and who's name is Lukas.
// He has this cool data - but as it says, that could be *anything* valid in PHP.
$lukasObj = array("this array" => "could be anything", "for example" => "an object", "or" => "a string");

// Let's tell the KB I'm here and I want to participate.
$kb->I_am_a("student")->my_name_is("Lukas")->and_I_can("program")->set($lukasObj);

// Let's get our data back out of it...
// There are a lot different ways to do that - depending on the language that makes more sense here
$lukasObj1 = $kb->I_want_a("student")->able_to("program")->with_the_name("Lukas")->gimme();
$lukasObj2 = $kb->I_want_a("student")->with_the_name("Lukas")->able_to("program")->gimme();

// You can now get the data and meta information out of it
$lukasObj1Data = $lukasObj1->data;
$lukasObj2Data = $lukasObj2->data;
$name = $lukasObj1->name;

// But that is cool: You can even change the information in that object direcly:
$lukasObj1->name = "My name is not Lukas anymore!";

// Let's try to get the data using the new name:
$lukasObjNewData = $kb->I_want_a("student")->able_to("program")->with_the_name("My name is not Lukas anymore!")->gimme()->data;

// And here's the data:
echo "Data before the KB took my data:\n";
print_r($lukasObj);

echo "\nData in query way number 1:\n";
print_r($lukasObj1Data);

echo "\nData in query way number 2:\n";
print_r($lukasObj2Data);

echo "\nData from the magically changed object:\n";
print_r($lukasObjNewData);

// Let's give the KB a computer with the ability to surf the web who's name is "Any".
// You see: You can also query write operations other way round: First ability, then name!
$kb->I_am_a("computer")->I_can("surf the web")->and_my_name_is("Any")->set("Some data about me!");

// Let's get the data of this computer back...
$computer1 = $kb->I_want_a("computer")->able_to("surf the web")->with_the_name("Any")->gimme()->data;
echo "\n\nOur computer able to surf the web: $computer1\n\n";

// Now, let's ask for a computer which is not in the KB!
echo "Data of something not in the KB:\n";
try {
	var_dump($kb->I_want_a("computer")->able_to("surf the web")->with_the_name("MacBook Pro")->gimme()->data);
} catch(Exception $e) {
	echo 'Caught the exception "' . $e->getMessage() . "\"\n";
}

// Our computer does not want to stay in the KB!
$result = $kb->I_am_the("computer Any")->able_to("surf the web")->remove();

// $result contains if the deletion worked.

// Try to get him back...
echo "\nOur computer able to surf the web should not be in the KB anymore:\n";
try {
	var_dump($kb->I_want_a("computer")->able_to("surf the web")->with_the_name("Any")->gimme());
} catch(Exception $e) {
	echo 'Caught the exception "' . $e->getMessage() . "\"\n";
}

// Now, we want to use some other phrases to access the KB. Let's tell it about that...
// This one defines that a phrase named "my_cool_name" defines the property $name.
$kb->definePhrase("people_think_I_am_very_good_at_writing", '$ability');

// Let us define another one
// Here, we need to remove the old phrase to do that
// You can do the same with setters, getters, removers and name getters!
$kb->undefinePhrase("I_am_the");
// You can define all formats fitting in a string. This is then matched by a RegEx.
$kb->definePhrase("I_am_the", '$type named $name');

// You could also write that like so:
$kb->undefinePhrase("I_am_the")->definePhrase("I_am_the", '$type named $name');

// Because the KB has to know if your phrases are setter, getter or remover, you have to append "set", "gimme" or "remove" to the query
// But you can change that!
$kb->defineSetter("make_me_available_for_hire");
$kb->defineGetter("what_is_his_information");
$kb->defineRemover("I_dont_want_to_have_me_in_the_KB");

// Let's test that!
// Note I use capitalization for the p in people - you can do that with the first char :)
$kb->People_think_I_am_very_good_at_writing("code")->I_am_the("student named Lukas Bestle")->make_me_available_for_hire("Some data about Lukas Bestle!");
echo "\nHere I am again: " . $kb->I_want_a("student")->able_to("code")->with_the_name("Lukas Bestle")->what_is_his_information()->data . "\n\n";

// Add some new items
$kb->People_think_I_am_very_good_at_writing("code")->I_am_the("student named luX")->make_me_available_for_hire("Some data about luX!");
$kb->I_am_a("computer")->I_can("surf the web")->and_my_name_is("MacBook Air")->set("I have a lot information!");
$kb->I_am_a("dinner")->I_can("make people well-fed")->and_my_name_is("I don't know, actually...")->set("Yay! I'm in the KB");

// This item does not know what type it is (it does, but just as an example)
// You can basiclly omit anything besides the name
$kb->I_can("produce *real* speaking code")->my_name_is("KnowledgeBase")->set($kb);

// Let us get all students from the KB and iterate (you can do that with any queue object!)
echo "Iterate through some items:\n";
foreach($kb->All_of_type("student") as $item) {
	echo 'The student named "' . $item->name . '" can "' . $item->ability .'" and has the data "' . $item->data . "\"\n";
}

// Get all items from the KB and iterate
echo "\nIterate through all items:\n";
foreach($kb as $item) {
	echo 'The "' . $item->type .'" named "' . $item->name . '" can "' . $item->ability .'" and has the data "' . $item->data . "\"\n";
}