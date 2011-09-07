<?php
/**
 * Hagfish - Request
 * 
 * A HagfishRequest object will be passed to every action handler. Contains
 * information about the request, such as the URL passed in and the value of 
 * any parameters extracted.
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


class HagfishRequest
{
	
	protected $_parameters;				/**< Array of parameter names => values */
	
	
	// ----------------------------------------------------------------------
	// -- Querying parameters
	// ----------------------------------------------------------------------
	
	/**
	 * Get the value of a parameter .
	 * 
	 * @param string $paramName The name of the parameter to retrieve.
	 * @param mixed $default Optional value to return if parameter not present. Default is null.
	 * @return mixed Parameter value, or $default if not found.
	 */
	public function getParameter($paramName, $default = null)
	{
		return (array_key_exists(strtolower($paramName), $this->_parameters)) ?
			$this->_parameters[strtolower($paramName)] : $default;
	}
	
	/**
	 * Set a parameter.
	 * 
	 * @param string $paramName Parameter name.
	 * @param mixed $paramValue Value of the parameter.
	 * @return HagfishRequest This request.
	 */
	public function setParameter($paramName, $paramValue)
	{
		$this->_parameters[strtolower($paramName)] = $paramValue;
		return $this;
	}
	
	
	// ----------------------------------------------------------------------
	// -- Construction
	// ----------------------------------------------------------------------
	
	/**
	 * Create a new request.
	 * @param array $args Array of parameter names => values.
	 */
	public function __construct($args = array())
	{
		if (is_array($args)) {
			$this->_parameters = $args;
		}
	}
	
	
}
