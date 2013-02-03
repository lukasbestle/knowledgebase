<?php

/**
 * KnowledgeBase.php - an object storage producing *real* speaking code implemented in PHP.
 *
 * @version 1.1
 * @author Lukas Bestle <lukas@lu-x.me>
 * @link http://lu-x.me
 * @copyright Copyright 2012-2012 Lukas Bestle
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

class KnowledgeBase implements Iterator {
	// Name of the KB instance
	public  $name;
	
	// The data storage
	private $wiseMen     = array();
	
	// Phrases to set information about the stuff the caller wants to set or get
	// These are already valid RegExes, but the definePhrase() function lets you use $type, $ability and $name instead
	private $phrases     = array(
		"I_am_a"         => '(?<type>.+?)',
		"I_want_a"       => '(?<type>.+?)',
		"all_of_type"    => '(?<type>.+?)',
		
		"I_can"          => '(?<ability>.+?)',
		"and_I_can"      => '(?<ability>.+?)',
		"able_to"        => '(?<ability>.+?)',
		
		"with_the_name"  => '(?<name>.+?)',
		"my_name_is"     => '(?<name>.+?)',
		"and_my_name_is" => '(?<name>.+?)',
		
		"I_am_the"       => '(?<type>.+?) (?<name>.+?)'
	);
	
	// Functions called at the end of the queue to do something
	private $setters     = array("set");
	private $getters     = array("gimme");
	private $removers    = array("remove");
	
	// Functions to get the name of the KB instance
	private $nameGetters = array("what_is_your_name");
	
	// Stuff for the instance to know what to do
	private $parent;
	private $callData    = array();
	
	// Iteration array
	private $_           = array();
	
	public function __construct($name = "", $parent = false, $data = false) {
		// Set the name
		$this->name = $name;
		
		// Set the topmost parent (the instance the user created)
		if($parent) {
			$this->parent = $parent;
		} else {
			$this->parent = $this;
		}
		
		// Set call data (intern usage)
		if($data) {
			$this->callData = $data;
		}
	}
	
	// =============================
	// All the magic!
	// =============================
	public function __call($name_orig, $arguments) {
		// Capitalization
		foreach(array($name_orig, lcfirst($name_orig)) as $name) {
			if(isset($this->parent->phrases[$name])) {
				// There's a phrase for that!
				
				// We need the argument for any phrase
				if(!isset($arguments[0])) throw new InvalidArgumentException("No argument given.");
				
				// Get the correct phrase
				$phrase = $this->parent->phrases[$name];
				
				// Match the phrase to extract information
				if(preg_match("{^$phrase$}", $arguments[0], $matches)) {
					// Get the current set of data
					$data = $this->callData;
					
					// Add the new information
					if(isset($matches["name"])) $data["name"] = $matches["name"];
					if(isset($matches["ability"])) $data["ability"] = $matches["ability"];
					if(isset($matches["type"])) $data["type"] = $matches["type"];
					
					// Get the class name to know which class to spawn
					$className = get_class($this);
					
					// Create a new instance of itself and give it some information what to do
					return new $className($this->parent->name, $this->parent, $data);
				} else {
					// Couldn't match
					throw new InvalidArgumentException("Input did not match phrase $name.");
				}
			} else if(in_array($name, $this->parent->nameGetters)) {
				// User wants to get the name of the KB
				// We use that one from the topmost instance - it could have changed already since that instance spawned
				return $this->parent->name;
			} else if(in_array($name, $this->parent->setters) || in_array($name, $this->parent->getters) || in_array($name, $this->parent->removers)) {
				// The last element of the chain
				
				// We need at least the name set by phrases to do anything here
				if(isset($this->callData["name"])) {
					// Set some fallback data
					$queryType = (isset($this->callData["type"]))? $this->callData["type"] : false;
					$queryAbility = (isset($this->callData["ability"]))? $this->callData["ability"] : false;
					$queryName = $this->callData["name"];
					
					if(in_array($name, $this->parent->setters)) {
						// It is a setter
						
						// We need the argument (contains the data to set)
						if(!isset($arguments[0])) {
							throw new InvalidArgumentException("No argument given.");
						}
						
						// Set the data
						return $this->parent->setObj($queryType, $queryAbility, $queryName, $arguments[0]);
					} else if(in_array($name, $this->parent->getters)) {
						// It is a getter
						
						// Get the data and return it
						return $this->parent->getObj($queryType, $queryAbility, $queryName);
					} else if(in_array($name, $this->parent->removers)) {
						// It is a remover
						
						// Remove the data
						return $this->parent->removeObj($queryType, $queryAbility, $queryName);
					} else {
						// The KB does not know what to do (no fitting task defined)
						// Should never happen - then it would be a bug in here
						throw new FatalException("Could not find a task. This is a bug in KB.php, please report it!");
					}
				} else {
					// Not all data available
					throw new MissingException("You must first define at least a name in the queue to call that.");
				}
			}
		}
		
		// The KB does not know what to do (no fitting task defined)
		throw new BadMethodCallException("There is no task doing that what you wanted.");
	}
	
	// =============================
	// Internal functions
	// Used to access the data store
	// =============================
	public function setObj($type, $ability, $name, $data) {
		if(!isset($this->wiseMen[$type])) $this->wiseMen[$type]                     = array();
		if(!isset($this->wiseMen[$type][$ability])) $this->wiseMen[$type][$ability] = array();
		
		$this->wiseMen[$type][$ability][$name] = new WiseMan($type, $ability, $name, $data, $this);
		
		return true;
	}
	
	public function getObj($type, $ability, $name) {
		if(!isset($this->wiseMen[$type][$ability][$name])) {
			throw new OutOfBoundsException("Could not find the element you requested.");
		}
		
		return $this->wiseMen[$type][$ability][$name];
	}
	
	public function removeObj($type, $ability, $name) {
		if(!isset($this->wiseMen[$type][$ability][$name])) {
			throw new OutOfBoundsException("Could not find the element you requested.");
		}
		
		unset($this->wiseMen[$type][$ability][$name]);
		
		if($this->wiseMen[$type][$ability] == array()) unset($this->wiseMen[$type][$ability]);
		if($this->wiseMen[$type] == array()) unset($this->wiseMen[$type]);
		
		return true;
	}
	
	// =============================
	// Helper functions (definers)
	// =============================
	public function defineNameGetter($name) {
		if(array_search($name, $this->parent->nameGetters)) throw new DuplicateEntryException("This name getter '$name' was already defined!");
		
		$this->parent->nameGetters[] = $name;
		
		return $this->parent;
	}
	
	public function definePhrase($name, $phrase) {
		// Parse the phrase and make it a valid RegEx
		$phrase = str_replace('$name', '(?<name>.+?)', $phrase);
		$phrase = str_replace('$ability', '(?<ability>.+?)', $phrase);
		$phrase = str_replace('$type', '(?<type>.+?)', $phrase);
		
		if(isset($this->parent->phrases[$name])) throw new DuplicateEntryException("The phrase '$name' was already defined!");
		
		$this->parent->phrases[$name] = $phrase;
		
		return $this->parent;
	}
	
	public function defineSetter($name) {
		if(array_search($name, $this->parent->setters)) throw new DuplicateEntryException("The setter '$name' was already defined!");
		
		$this->parent->setters[] = $name;
		
		return $this->parent;
	}
	
	public function defineGetter($name) {
		if(array_search($name, $this->parent->getters)) throw new DuplicateEntryException("The getter '$name' was already defined!");
		
		$this->parent->getters[] = $name;
		
		return $this->parent;
	}
	
	public function defineRemover($name) {
		if(array_search($name, $this->parent->removers)) throw new DuplicateEntryException("The remover '$name' was already defined!");
		
		$this->parent->removers[] = $name;
		
		return $this->parent;
	}
	
	// =============================
	// Helper functions (undefiners)
	// =============================
	public function undefineNameGetter($name) {
		if(array_search($name, $this->parent->nameGetters)) {
			unset($this->parent->nameGetters[array_search($name, $this->parent->nameGetters)]);
		} else {
			throw new OutOfBoundsException("The name getter '$name' was not defined!");
		}
		
		return $this->parent;
	}
	
	public function undefinePhrase($name) {
		if(isset($this->parent->phrases[$name])) {
			unset($this->parent->phrases[$name]);
		} else {
			throw new OutOfBoundsException("The phrase '$name' was not defined!");
		}
		
		return $this->parent;
	}
	
	public function undefineSetter($name) {
		if(array_search($name, $this->parent->setters)) {
			unset($this->parent->setters[array_search($name, $this->parent->setters)]);
		} else {
			throw new OutOfBoundsException("The setter '$name' was not defined!");
		}
		
		return $this->parent;
	}
	
	public function undefineGetter($name) {
		if(array_search($name, $this->parent->getters)) {
			unset($this->parent->getters[array_search($name, $this->parent->getters)]);
		} else {
			throw new OutOfBoundsException("The getter '$name' was not defined!");
		}
		
		return $this->parent;
	}
	
	public function undefineRemover($name) {
		if(array_search($name, $this->parent->removers)) {
			unset($this->parent->removers[array_search($name, $this->parent->removers)]);
		} else {
			throw new OutOfBoundsException("The remover '$name' was not defined!");
		}
		
		return $this->parent;
	}
	
	// =============================
	// Iteration
	// =============================
	public function rewind() {
		$this->refreshArray();
		
		reset($this->_);
	}
	
	public function current() {
		return current($this->_);
	}
	
	public function key() {
		return key($this->_);
	}
	
	public function next() {
		return next($this->_);
	}
	
	public function valid() {
		return $var = $this->current() !== false;
	}
	
	// Set the array to the correct data
	private function refreshArray() {
		// The final array
		$result = array();
		
		foreach($this->parent->wiseMen as $typename => $type) {
			// Filter items by type
			if(isset($this->callData["type"]) && $this->callData["type"] != $typename) continue;
			
			foreach($type as $abilityname => $ability) {
				// Filter items by ability
				if(isset($this->callData["ability"]) && $this->callData["ability"] != $abilityname) continue;
				
				foreach($ability as $name => $data) {
					// Filter items by name
					if(isset($this->callData["name"]) && $this->callData["name"] != $name) continue;
					
					$result[] = $data;
				}
			}
		}
		
		// Set the array to the result
		$this->_ = $result;
	}
	
	// =============================
	// String representation
	// =============================
	public function __toString() {
		return $this->parent->name;
	}
}

// =============================
// The item object
// =============================
class WiseMan {
	// Hidden information
	private $_ = array(
		"type" => "",
		"ability" => "",
		"name" => ""
	);
	
	// The KB object to change data
	private $kb;
	
	// The real data in the moment the object was created
	public $staticData;
	
	public function __construct($type, $ability, $name, $data, $kb) {
		$this->kb           = $kb;
		
		$this->_["type"]    = $type;
		$this->_["ability"] = $ability;
		$this->_["name"]    = $name;
		
		$this->staticData   = $data;
	}
	
	// Get meta data out of the hidden object
	public function __get($item) {
		if($item == "data") {
			return $this->kb->getObj($this->_["type"], $this->_["ability"], $this->_["name"])->staticData;
		}
		
		if(!isset($this->_[$item])) return false;
		
		return $this->_[$item];
	}
	
	// Set the meta data
	public function __set($item, $value) {
		if($item == "data") {
			$this->kb->setObj($this->_["type"], $this->_["ability"], $this->_["name"], $value);
			return true;
		}
		
		if(!isset($this->_[$item])) return false;
		
		// Get the current data
		$data = $this->kb->getObj($this->_["type"], $this->_["ability"], $this->_["name"])->staticData;
		
		// Remove the old item
		$this->kb->removeObj($this->_["type"], $this->_["ability"], $this->_["name"]);
		
		// Set the new data
		$this->_[$item] = $value;
		
		// Add the new item again
		$this->kb->setObj($this->_["type"], $this->_["ability"], $this->_["name"], $data);
		
		return true;
	}
	
	public function __toString() {
		return print_r($this->__get("data"), true);
	}
}

// =============================
// Exceptions
// =============================
class DuplicateEntryException extends Exception {}
class FatalException extends Exception {}
class MissingException extends Exception {}