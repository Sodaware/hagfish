<?php
/**
 * Hagfish - Action
 * 
 * Action classes do the real work in Hagfish. An action takes care of all of
 * the processing that needs to be done, and controls what output will be
 * generated (either directly or through the use of templates).
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
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @since      File available since Release 1.0.0
 */


/**
 * Action handler. Maps URI paths to class => method. Use Controller->addAction
 * method to register an action.
 * 
 * All action methods can set the template and should register variables that
 * will be needed in the view.
 */
class HagfishAction
{
	protected $_templateName;			/**< The name of the template */
	protected $_templatePath;			/**< Folder templates are stored in */
	protected $_variables;				/**< Array of variable names => values */
	
	
	// ----------------------------------------------------------------------
	// -- Setup
	// ----------------------------------------------------------------------
	
	/**
	 * Sets the directory that templates are found in.
	 * @param string $path Location of template directory.
	 */
	public function setTemplatePath($path)
	{
		$this->_templatePath = $path;
	}
	
	/**
	 * Set the name of the template to display. Leave blank for no output. Do
	 * not include directory or ".template.php" - this will be added automatically.
	 * @param string $template Name of template.
	 */
	public function setTemplateName($template)
	{
		$this->_templateName = $template;
	}
	
	/**
	 * Register variables with the template. Use an array of name => value
	 * pairs. They can then be called from within the template.
	 * @param array $variables The variables to register.
	 */
	public function registerVariables($variables)
	{
		if (is_array($variables)) {
			$this->_variables = array_merge($this->_variables, $variables);
		}
	}
	
	
	// ----------------------------------------------------------------------
	// -- Display
	// ----------------------------------------------------------------------
	
	/**
	 * Render the template using registered variables and return it as a string.
	 * @return string Rendered view.
	 */
	public function render()
	{
		if (!$this->_templateName) {
			return;
		}
		
		// Include template
		$path = $this->_templatePath . '/' . $this->_templateName . '.template.php';
		
		// Render
		if (file_exists($path)) {
			@extract($this->_variables);
			ob_start();
			include $path;
			$contents	= ob_get_contents();
			ob_end_clean();
			return $contents;
		}
		
		throw new Exception('Template not found: ' . $this->_templateName);
		
	}
	
	
	// ----------------------------------------------------------------------
	// -- Construction
	// ----------------------------------------------------------------------

	public function __construct()
	{
		$this->_variables	= array();
		$this->_parameters	= array();
	}
	
}
