<?php

namespace APIRouter;

class Route {
	/**
	 * URL of this Route
	 * @var string
	 */
	private $url;

	/**
	 * Accepted HTTP methods for this route.
	 *
	 * @var string[]
	 */
	private $methods = array('get', 'post', 'put', 'delete', 'patch');

	/**
	 * Target for this route, can be anything.
	 * @var mixed
	 */
	private $target;

	/**
	 * The name of this route, used for reversed routing
	 * @var string
	 */
	private $name;

	/**
	 * Custom parameter filters for this route
	 * @var array
	 */
	private $filters = array();

	/**
	 * Array containing parameters passed through request URL
	 * @var array
	 */
	private $parameters = array();

	/**
	 * @var array
	 */
	private $config;

	/**
	 * @param       $resource
	 * @param array $config
	 */
	public function __construct($resource, array $config) {
		$this->url = $resource;
		$this->config  = $config;
		if ( isset($config['method']) ) {
			$this->methods = $config['method'];
		}

		$this->target = isset($config['target']) ? $config['target'] : NULL;
	}

	public function getUrl() {
		return $this->url;
	}

	public function setUrl($url) {
		$url = (string) $url;

		// make sure that the URL is suffixed with a forward slash
		if ( substr($url, -1) !== '/' ) {
			$url .= '/';
		}

		$this->url = $url;
	}

	public function getTarget() {
		return $this->target;
	}

	public function setTarget($target) {
		$this->target = $target;
	}

	public function getMethods() {
		return $this->methods;
	}

	public function setMethods(array $methods) {
		$this->methods = $methods;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = (string) $name;
	}

	public function setFilters(array $filters) {
		$this->filters = $filters;
	}

	public function getRegex() {
		return preg_replace_callback('/:(\w+)/', array(&$this, 'substituteFilter'), $this->url);
	}

	private function substituteFilter($matches) {
		if ( isset($matches[1]) && isset($this->filters[$matches[1]]) ) {
			return $this->filters[$matches[1]];
		}

		return '([\w-%]+)';
	}

	public function getParameters() {
		return $this->parameters;
	}

	public function setParameters(array $parameters) {
		$this->parameters = $parameters;
	}

	public function dispatch() {
		$action = explode('#', $this->config['_controller']);
		if ( !is_subclass_of($action[0], 'APIRouter\Controller') ) {
			throw new \Exception;
		};
		$instance = new $action[0];
		$params = array($action[1], array($this->parameters));
		call_user_func_array(array($instance, '__callInvoke'), $params);
	}
}
