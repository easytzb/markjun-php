<?php
class Autoloader {	
	
	/**
	 * 加载文件的路径
	 * @var string 
	 */
	private $path =  '';

	public function __construct() {
		$this->path = dirname(__FILE__) . '/';
		spl_autoload_register(array($this, 'loader'));
	}

	private function loader($className) {
		//echo 'Trying to load ', $className, ' via ', __METHOD__, "()\n";
		include_once   $this->path . strtolower($className . '.php');
	}
}