<?php

class Stat {

	/**
	 * GET值
	 * @var string
	 */
	private $t = '';

	/**
	 * 数据库连接
	 * @var string
	 */
	private $link = '';

	public function __construct() {
		$this->t = trim(($_GET['t']));		

		$this->link = Common::db();
		
		if (isset($_GET['appid']) && strlen(trim($_GET['appid'])) == 32 ) {
			$sql = "REPLACE INTO appid(`id`) VALUES('" . trim($_GET['appid']) . "')";
			$this->link->query($sql);
		}		
	}
	
	public function response() {
		if (($t = intval($this->t)) &&  $this->t == $t) {
			$sql = 'insert INTO `stat` VALUES(' . $t . ', ' . time() . ',"' . Common::getIp() . '", 0)';
			if (!$this->link->query($sql)) {
				$error = $this->link ->connect_error;
				$this->link->close();
				Common::error(ERROR_SQL_EXEC, $error);
			}
			$this->link->close();
		} else Common::error(ERROR_NOT_VALID_T, 'ERROR_NOT_VALID_T' . $_GET['t']);
		
		die('0');
	}
}