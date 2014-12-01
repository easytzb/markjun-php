<?php
class Common {
    private static $ip = '';

    public static $db = null;

    /**
     * 错误响应
     * @param int $errorNo
     * @param string $logMsg the message of Log
     */
    public function error($errorNo, $logMsg = '') {
        if ($logMsg) {
            require_once "BaeLog.class.php";
            $logger=BaeLog::getInstance();
            $logger ->logWrite(4, $logMsg);
        }
        die('{' . $errorNo . '}');
    }

    /**
     * 来访者IP地址
     * @return string
     */
    public function getIp() {
        if (self::$ip) return self::$ip;

        $ip		=  isset($_SERVER["HTTP_X_FORWARDED_FOR"])?$_SERVER["HTTP_X_FORWARDED_FOR"]:(isset($_SERVER["HTTP_CLIENT_IP"])?$_SERVER["HTTP_CLIENT_IP"]:$_SERVER["REMOTE_ADDR"]);
        $pos	= strpos($ip, ',');
        if ($pos !== FALSE)  $ip = substr($ip, 0, $pos);

        return self::$ip = $ip;
    }

    public function randIp() {
        return rand(100,200) . '.' . rand(1,255) . '.' . rand(1,255) . '.' . rand(1,255);
    }

    public function isAdmin() {
        $ip = self::getIp();
        foreach ($GLOBALS['adminIp'] as $v) {
            $v = str_replace(array('.', '%'), array('\.', '.+'), $v);
            if (preg_match('/' . $v . '/', $ip)) return true;
        }
        return false;
    }

    public function db(){
        if (self::$db ==  null) {
            self::$db = new mysqli(DB_HOST, DB_USER, DB_PWD, DB_NAME, DB_PORT) or
                self::error(ERROR_DB_CONNECT_ERROR, self::$db->connect_error);

            self::$db->query("SET NAMES UTF8");
        }

        return self::$db;
    }

    /**
     * 根据SQL语句获得数据
     * @param string $sql
     * @param string $key
     * @param string $val
     * @return array
     */
    public function getDataBySql( $sql, $key = '', $val = '' ) {

        self::db();

        if (!($res = self::$db->query($sql))) 
            self::error(ERROR_SQL_EXEC, self::$db->connect_error);
        $data = array();
        while ($row = $res->fetch_assoc()) {			
            if ($val) $v = $row[$val];
            else $v = $row;

            if ($key) $data[$row[$key]] = $v;
            else $data[] = $v;
        }
        return $data;
    }

    /**
     * 获得一条记录
     * @param string $sql
     * @param string $key
     * @param string $val 
     * @return array 
     */
    public function getRow($sql, $key = '', $val = '') {
        $res = self::getDataBySql($sql, $key, $val);
        if (!is_array($res) || empty($res)) return array();
        else return array_pop($res);
    }

    /**
     * 获得一个字段
     * @param string $sql
     * @param string $key
     * @param string $val 
     * @return string|null
     */
    public function getOne($sql, $key = '', $val = '') {
        $res = self::getRow($sql, $key, $val);
        if (!is_array($res) || empty($res)) return null;
        else return array_pop($res);
    }

    /**
     * 输出json格式数据 
     * @param array $arr
     */
    public function d($arr) {

        //是否是淘宝或天猫
        $is_tmall = strpos($arr['u'], 'tmall') !== false;
        $is_tb    = $is_tmall || (strpos($arr['u'], 'taobao') !== false);
        if (!$is_tb) die(json_encode($arr));

        if (!preg_match('/\d+/', $arr['u'], $match)) return '';
        $id = intval(array_pop($match));
        if (!$id) die(json_encode($arr));

        $url = "http://pub.alimama.com/common/code/getAuctionCode.json" . 
            "?auctionid=$id" . 
            "&adzoneid=27674237" . 
            "&siteid=8182453" . 
            "&t=" . (microtime(true) * 1000) . 
            "&_tb_token_=" . TB_TOKEN . 
            "&_input_charset=utf-8";
        $ch = new Curl($url);
        $ch->setOption(CURLOPT_RETURNTRANSFER, true);
        $ch->setOption(CURLOPT_ENCODING, 'gzip,deflate');
        //$ch->setOption(CURLOPT_FOLLOWLOCATION, true); //对于301/2关键在这里
        $ch->setOption(CURLOPT_COOKIE, TB_COOKIE); //cookie
        $detailHtml = $ch->exec();
        $ch->close();
        $detailHtml = json_decode($detailHtml);
        if (!isset($detailHtml->data->eliteUrl))
            die(json_encode($arr));

        $ch->init($detailHtml->data->eliteUrl);
        $ch->setOption(CURLOPT_RETURNTRANSFER, true);
        $ch->setOption(CURLOPT_ENCODING, 'gzip,deflate');
        $ch->setOption(CURLOPT_FOLLOWLOCATION, true); //对于301/2关键在这里
        $detailHtml = $ch->exec();
        if (!$detailHtml) die(json_encode($arr));

        $reg = '/featured\-btn go\-to\-buy" href="(.+?)"/';
        if (preg_match($reg, $detailHtml, $match)) {
            $arr['r'] = array_pop($match);
        }

        die(json_encode($arr));
    }
}
