<?php
/**
 * hagfish_controller.tests.php
 */

error_reporting(E_ALL | E_STRICT);
require_once dirname(dirname(__FILE__)) . '/hagfish.php';

class HagfishControllerTests extends PHPUnit_Framework_TestCase
{
	function setUp() {
			
	}

	function tearDown() {
		
	}
	
	// ----------------------------------------------------------------------
	// -- HagfishController::getActionType
	// ----------------------------------------------------------------------

	function testGetActionTypeWithEmptyArg() {
		$this->assertEquals(HagfishController::getActionType(''), HagfishController::TYPE_UNKNOWN, '::getActionType returns TYPE_UNKNOWN for invalid args');
	}
	
	function testGetActionTypeWithNoneExistantFunction() {
		$this->assertEquals(HagfishController::getActionType('none_existant'), HagfishController::TYPE_UNKNOWN, '::getActionType returns TYPE_UNKNOWN for non-existant function');
	}

	function testGetActionTypeWithClassButNoMethod() {
		$this->assertEquals(HagfishController::getActionType(array('TestAction')), HagfishController::TYPE_UNKNOWN, '::getActionType returns TYPE_UNKNOWN for class with no method specified');
	}

	function testGetActionTypeWithInvalidClass() {
		$this->assertEquals(HagfishController::getActionType(array('InvalidClass', 'noFunction')), HagfishController::TYPE_UNKNOWN, '::getActionType returns TYPE_UNKNOWN for invalid class with no method specified');
	}

	function testGetActionTypeWithClassWithInvalidMethod() {
		$this->assertEquals(HagfishController::getActionType(array('TestAction', 'noFunction')), HagfishController::TYPE_UNKNOWN, '::getActionType returns TYPE_UNKNOWN for class with invalid method');
	}
	
	function testGetActionTypeForHagfishAction() {
		$this->assertEquals(HagfishController::getActionType(array('TestAction', 'testFunction')), HagfishController::TYPE_HAGFISH_ACTION, '::getActionType returns TYPE_HAGFISH_ACTION for valid HagfishAction arg');
	}

	function testGetActionTypeForFunction() {
		$this->assertEquals(HagfishController::getActionType('exampleFunction'), HagfishController::TYPE_FUNCTION, '::getActionType returns TYPE_FUNCTION for valid function arg');
	}

	function testGetActionTypeForClosure() {
		$this->assertEquals(HagfishController::getActionType(function() { }), HagfishController::TYPE_CLOSURE, '::getActionType returns TYPE_CLOSURE for valid closure arg');
	}
	
	
}

function exampleFunction() { }

class TestAction extends HagfishAction
{
	function testFunction() { }
}
