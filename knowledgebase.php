<?php

/**
 * KnowledgeBase.php - an object storage producing *real* speaking code implemented in PHP.
 *
 * @version 1.0
 * @author Lukas Bestle <lukas@lu-x.me>
 * @link http://lu-x.me
 * @copyright Copyright 2012-2012 Lukas Bestle
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

class KnowledgeBase {
	public  $name;
	private $wiseMen = array();
	
	public function __construct($name = "") {
		$this->name = $name;
	}
	
/*===================================
  Internal stuff
  =================================== */
	public function add($category, $ability, $name, $obj) {
		if(!isset($this->data[$category])) $this->data[$category] = array();
		if(!isset($this->data[$category][$ability])) $this->data[$category][$ability] = array();
		
		$this->wiseMen[$category][$ability][$name] = $obj;
		
		return true;
	}
	
	public function gimme($category, $ability, $name) {
		if(isset($this->wiseMen[$category][$ability][$name])) return $this->wiseMen[$category][$ability][$name];
		
		return false;
	}
	
/*===================================
  Data about the KB
  =================================== */
	public function what_is_your_name() {
		return $this->name;
	}

/*===================================
  Putting
  =================================== */
	public function I_am_a($type) {
		return new secondStage($this, "add", array($type));
	}
	
/*===================================
  Getting
  =================================== */
	public function I_want_a($type) {
		return new secondStage($this, "return", array($type));
	}
}

class secondStage {
	private $parent;
	private $type;
	private $data;
	
	public function __construct($parent, $type, $data) {
		$this->parent = $parent;
		$this->type   = $type;
		$this->data   = $data;
	}

/*===================================
  Adding
  =================================== */
	public function my_name_is($name) {
		if($this->type != "add") return false;
		
		return new thirdStage($this->parent, "add", array_merge($this->data, array($name)));
	}
	
	public function I_can($ability) {
		if($this->type != "add") return false;
		
		return new thirdStage($this->parent, "add", array_merge($this->data, array($ability)));
	}
	
/*===================================
  Getting
  =================================== */
	public function with_the_name($name) {
		if($this->type != "return") return false;
		
		return new thirdStage($this->parent, "return", array_merge($this->data, array($name)));
	}
	
	public function able_to($ability) {
		if($this->type != "return") return false;
		
		return new thirdStage($this->parent, "return", array_merge($this->data, array($ability)));
	}
}

class thirdStage {
	private $parent;
	private $type;
	private $data;
	
	public function __construct($parent, $type, $data) {
		$this->parent = $parent;
		$this->type   = $type;
		$this->data   = $data;
	}
	
/*===================================
  Adding
  =================================== */
	public function and_I_can($ability) {
		if($this->type != "add") return false;
		
		return new fourthStage($this->parent, "add", array_merge($this->data, array($ability)), true);
	}
	
	public function and_my_name_is($name) {
		if($this->type != "add") return false;
		
		return new fourthStage($this->parent, "add", array_merge($this->data, array($name)), false);
	}
	
/*===================================
  Getting
  =================================== */
	public function with_the_name($name) {
		if($this->type != "return") return false;
		
		return $this->parent->gimme($this->data[0], $this->data[1], $name);
	}
	
	public function able_to($ability) {
		if($this->type != "return") return false;
		
		return $this->parent->gimme($this->data[0], $ability, $this->data[1]);
	}
}

class fourthStage {
	private $parent;
	private $type;
	private $data;
	private $swapped;
	
	public function __construct($parent, $type, $data, $swapped) {
		$this->parent  = $parent;
		$this->type    = $type;
		$this->data    = $data;
		$this->swapped = $swapped;
	}
	
/*===================================
  Adding
  =================================== */
	public function here_I_am($obj) {
		if($this->type != "add") return false;
		
		if($this->swapped) {
			return $this->parent->add($this->data[0], $this->data[2], $this->data[1], $obj);
		} else {
			return $this->parent->add($this->data[0], $this->data[1], $this->data[2], $obj);
		}
	}
}