<?php
/**
 * Hagfish - Controller 
 * 
 * Hagfish controllers are used to link routes to actions. A route can either
 * be a standard path or contain regular expressions for parameters.
 * 
 * Copyright (c) 2011 Phil Newton
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 *	
 *    1. Redistributions of source code must retain the above copyright 
 *       notice, this list of conditions and the following disclaimer.
 * 	
 *    2. Redistributions in binary form must reproduce the above copyright 
 *       notice, this list of conditions and the following disclaimer in the 
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 * 
 * @package    Hagfish
 * @subpackage Core
 * @author     Phil Newton <phil@sodaware.net>
 * @copyright  2011 Phil Newton <phil@sodaware.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since      File available since Release 1.0.0
 */


/**
 * The main controller for a Hagfish application. Add actions using addAction, 
 * set up the template folder and call using dispatch.
 */
class HagfishController
{

	// Action type constants
	const TYPE_HAGFISH_ACTION	= 1;
	const TYPE_CLASS			= 2;
	const TYPE_OBJECT			= 3;
	const TYPE_FUNCTION			= 4;
	const TYPE_CLOSURE			= 5;
	const TYPE_UNKNOWN			= 6;
	
	
	protected $_actionMap;				/**< Array of URI => (class => method) */
	protected $_templatePath;			/**< Path of the template directory */
	protected $_parameters;				/**< Array of parameter names => values */
	protected $_prefix;					/**< Rewrite prefix. User if in a subdirectory */
	
	
	// ----------------------------------------------------------------------
	// -- Setup
	// ----------------------------------------------------------------------
	
	/**
	 * Sets the path to the template directory.
	 * @param string $path Path of the template directory.
	 */
	public function setTemplatePath($path)
	{
		$this->_templatePath = $path;
	}
	
	/**
	 * Sets the prefix. This will be stripped from the front of the request before
	 * searching for actions. Use if running hagfish from a subdirectory.
	 * 
	 * For example, an app in example.org/my_app/ would use setPrefix->('my_app')
	 * 
	 * @param string $prefix Prefix to set.
	 */
	public function setPrefix($prefix)
	{
		
		// Add slash to beginning if not present
		if (substr($prefix, 0, 1) != '/') {
			$prefix = '/' . $prefix;
		}
		
		// Strip slash from end if present
		if (substr($prefix, strlen($prefix) - 1, 1) == '/') {
			$prefix = substr($prefix, 0, strlen($prefix) - 1); 
		}
		
		$this->_prefix = $prefix;
		
	}
	
	/**
	 * Add a single action to the action map.
	 * @param string $name Name of the action
	 * @param array Array of class => method.
	 */	
	public function addAction($name, $action)
	{
	//	if (!is_array($action)) {
	//		throw new Exception("Cannot register handler for $name - invalid handler");
	//	}
		
		$this->_actionMap[$name]	= $action;
	}
	
	/**
	 * Add a list of actions to the action map. Should be in the form of an array
	 * of arrays. For example:
	 * 
	 * addActions(array(
	 * 	'action-name'	=> array('class' => 'method')
	 * ))
	 */
	public function addActions()
	{
		$args = func_get_args();
		
		foreach ($args as $arg) {
			if (is_array($arg)) {
				$this->_actionMap = array_merge($this->_actionMap, $arg);			
			}
		}
	}
	
	/**
	 * Register a new database connection.
	 * 
	 * @param string $host Database host. Usually "localhost".
	 * @param string $name Name of the database to connect to.
	 * @param string $username Username to connect with.
	 * @param string $password Password to connect with.
	 * @param string $connection Optional name used to identifiy the connection with.
	 */
	public function addDbConnection($host, $name, $username, $password, $connection = 'default')
	{
		HagfishDatabase::addConnection($host, $name, $username, $password, $connection);
	}
	
	
	// ----------------------------------------------------------------------
	// -- Main execution
	// ----------------------------------------------------------------------
	
	/**
	 * Main method. Get action to execute from the request and execute it. Render
	 * output if a template is set.
	 */
	public function dispatch()
	{
		// Check everything is setup
		if (!count($this->_actionMap)) {
			throw new Exception('Cannot execute - no actions defined');
		}
		
		// Parse the URI
		$requestName	= $this->_getRequestAction();
		
		// Populate PHP _GET variable
		$this->_populateGet();
		
		// Find the action to execute
		$action	= $this->_getActionHandler($requestName);
		
		// Execute the action
		switch (HagfishController::getActionType($action)) {
			
			case HagfishController::TYPE_CLASS:
				$this->_executeClassAction($action); 
				break;
			
			case HagfishController::TYPE_HAGFISH_ACTION:
				$this->_executeHagfishAction($action); 
				break;
				
			case HagfishController::TYPE_OBJECT:
				$this->_executeObjectAction($action); 
				break;
			
			case HagfishController::TYPE_FUNCTION:
				$this->_executeFunctionAction($action);
				break;
				
			case HagfishController::TYPE_CLOSURE:
				$this->_executeClosureAction($action);
				break;
				
			case HagfishController::TYPE_UNKNOWN:
				throw new Exception('No valid handler for ' . $requestName);
				break;
		}
	}
	
	
	// ----------------------------------------------------------------------
	// -- Parameters API
	// ----------------------------------------------------------------------
	
	public function getParameter($paramName, $default = null)
	{
		return (array_key_exists(strtolower($paramName), $this->_parameters)) ?
			$this->_parameters[strtolower($paramName)] : $default;
	}
	
	public function setParameter($paramName, $paramValue)
	{
		$this->_parameters[strtolower($paramName)] = $paramValue;
	}
	
	
	// ----------------------------------------------------------------------
	// -- Execution helpers
	// ----------------------------------------------------------------------
	
	// TODO: Some of these can be joined and refactored
	
	/**
	 * Get the type of an action that will be executed, such as an object array
	 * or a class.
	 * @param mixed $action Action handler from _getActionHandler.
	 * @return int Action type constant (TYPE_*)
	 */
	public static function getActionType($action)
	{
		// Check for classes / objects
		if (is_array($action) && count($action) == 2) {
			
			// Check for class
			if (is_string($action[0]) && class_exists($action[0])) {
					
				if (method_exists(new $action[0], $action[1])) {
					return (is_subclass_of($action[0], 'HagfishAction')) ?
						HagfishController::TYPE_HAGFISH_ACTION : 
						HagfishController::TYPE_CLASS;			
				}
					
				if (method_exists(new $action[0], $action[1])) {
					return HagfishController::TYPE_HAGFISH_ACTION;			
				}
				
			} elseif (is_object($action[0])) {
				
				return HagfishController::TYPE_OBJECT;
				
			}
			
		// Check for functions / closures
		} elseif (!is_array($action)) {
				
			if (@function_exists($action)) {
				return HagfishController::TYPE_FUNCTION;
			} elseif (is_object($action) && strtolower(get_class($action)) == 'closure') {
				return HagfishController::TYPE_CLOSURE;
			}
			
		}
		
		return HagfishController::TYPE_UNKNOWN;
		
	}
	
	/**
	 * Executes an action that is handled by a class.
	 * @param array $action Array of (class_name, method_name) 
	 */
	protected function _executeClassAction($action)
	{
		if (!class_exists($action[0])) {
			throw new Exception('No class found: ' . $action[0]);
		}
		
		// Create new handler + execute
		$handler = new $action[0];
		
		// Execute
		echo call_user_func(array($handler, $action[1]), $this->_getRequest());
		
	}
	
	/**
	 * Executes an action that is handled by a class that extends HagfishAction.
	 * @param array $action Array of (class_name, method_name) 
	 */
	protected function _executeHagfishAction($action)
	{
		if (!class_exists($action[0])) {
			throw new Exception('No class found: ' . $action[0]);
		}
		
		// Create new handler
		$handler = new $action[0];
		
		// Setup vars
		$handler->setTemplatePath($this->_templatePath);
		
		// Execute
		call_user_func(array($handler, $action[1]), $this->_getRequest());
		
		// Render view
		echo $handler->render();
		
	}
	
	/**
	 * Executes an action that is handled by an instance of a class.
	 * @param array $action Array of (object, method_name) 
	 */
	protected function _executeObjectAction($action)
	{
		// Call (may be a hagfish object)
		echo call_user_func(array($action[0], $action[1]), $this->_getRequest());		
	}
	
	/**
	 * Executes an action that is handled by a function name.
	 * @param string $action Function name to execute. 
	 */
	protected function _executeFunctionAction($action)
	{
		echo call_user_func($action, $this->_getRequest());
	}
	
	/**
	 * Executes an action that is handled by a closure.
	 * @param Closure $action Closure to execute. 
	 */
	protected function _executeClosureAction($action)
	{
		echo $action->__invoke($this->_getRequest());
	}
	
	
	// ----------------------------------------------------------------------
	// -- Internal helpers
	// ----------------------------------------------------------------------
	
	protected function _getRequest()
	{
		return new HagfishRequest($this->_parameters);
	}
	
	/**
	 * Get the action handler for this particular request.
	 */
	protected function _getActionHandler($requestName)
	{
		// If no request sent, display an error
		if (!$requestName) {
			throw new Exception('No request made');
		}
			
		// Check for actions with no pattern in the URL
		if (array_key_exists($requestName, $this->_actionMap)) {
			return $this->_actionMap[$requestName];
		}
		
		// Check for URLs with a pattern
		foreach ($this->_actionMap as $actionPattern => $action) {
			
			if (strpos($actionPattern, '%') !== false) {
				
				// Get arguments
				preg_match_all('/%(.*?)%/', $actionPattern, $patterns);
				
				// Create the regex to evaluate this 
				$regex = str_replace('/', '\\/', $actionPattern);
				foreach ($patterns[1] as $pattern) {
					$regex = str_replace('%' . $pattern . '%', '(\\' . substr($pattern, 0, 1) . '*)', $regex);
				}
				
				$matches = preg_match_all('/' . $regex . '/', $requestName, $args);
				if ($matches) {
					// Set arguments + call
					for ($i = 0; $i < count($patterns[1]); $i++) {
						$this->setParameter(substr($patterns[1][$i], 2), $args[$i + 1][0]);
					}
					return $action;
				}
				
			}
			
		}
		
		// TODO: Handle 404 errors better!
		if (!$requestName || !array_key_exists($requestName, $this->_actionMap)) {
			throw new Exception('No action registered for request: ' . $requestName);
		}
		
		return $this->_actionMap[$requestName];
	}
	
	/**
	 * Populates the _GET global.
	 */
	protected function _populateGet()
	{
		$uriPieces = parse_url($_SERVER['REQUEST_URI']);
		if (array_key_exists('query', $uriPieces)) {
			parse_str($uriPieces['query'], $_GET);
		}
	}
	
	/**
	 * Get the name of the action to execute.
	 * @return string Action name.
	 */
	protected function _getRequestAction()
	{
		$requestName	= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		
		// Strip trailing slashes
		if (substr($requestName, 0, 1) == '/') {
			$requestName = substr($requestName, 1);	
		}
		
		if (substr($requestName, strlen($requestName) - 1, 1) == '/') {
			 $requestName = substr($requestName, 0, strlen($requestName) - 1);
		}
		
		// Strip prefix if set
		if (strpos($requestName, $this->_prefix) == 0) {
			$requestName = substr($requestName, strlen($this->_prefix));
		}
		
		return (!$requestName) ? 'default' : $requestName;
	}
	
	
	// ----------------------------------------------------------------------
	// -- Construction
	// ----------------------------------------------------------------------
	
	public function __construct()
	{
		$this->_actionMap	= array();
		$this->_parameters	= array();
	}
	
}

