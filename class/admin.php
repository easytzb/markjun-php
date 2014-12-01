<?php

class Admin {

    /**
     * GET值
     * @var string
     */
    private $t = '';

    /**
     * 数据库连接
     * @var object 
     */
    private $db = null;

    /**
     *  操作类型
     * @var array
     */
    private $opeType = array(
        100 => 'install',
        101 => 'add by button',
        102 => 'add by menu',
        103 => 'remove by button',
        104 => 'remove in popup',
        105 => 'remove by menu',
        106 => 'popup',
        107 => 'click img in popup',
        108 => 'click GO',
        109 => 'has notify',
        110 => 'click OPEN ALL',
        111 => 'click IGNORE ALL',
        112 => 'user close notify',
        113 => 'refresh in pop',


        1 => 'add',
        2 => 'remove in c',
        3 => 'remove in popup',
        4 => 'popup',
        5 => 'share',
        6 => 'click X in notify',
        7 => 'click prod in notify',
        8 => 'click prod in popup',
        9 => 'test notify',
        10 => 'notify by user',
        11 => 'notify by Mark Jun',
        12 => 'close notify',
        13 => 'install',
        14 => 'click GO',
        15 => 'timeSortutime',
        16 => 'timeSortctime',
        17 => 'priceFilterup',
        18 => 'priceFilterdown',
        19 => 'priceFilterall',
        20 => 'siteFiltertaobao',
        21 => 'siteFiltertmall',
        22 => 'siteFiltervancl',
        23 => 'siteFilter360buy',
        24 => 'siteFilterall'

    );

    public function __construct() {
        $this->db = Common::db();

        $this->d = date('Y-m-d', trim($_GET['d']));

    }

    public function __destruct() {
        if ($this->db) $this->db->close();
    }

    public function response() {
        if ($_GET['a'] == 'getIp') $this->ipToLocal ();
        elseif ($_GET['a'] == 'global') $this->index ();
        elseif ($_GET['a'] == 'del' ) $this->delTestData();
        elseif ($_GET['a'] == 'savePush' ) $this->savePush();
        elseif ($_GET['a'] == 'listPush' ) $this->listPush();
        elseif ($_GET['a'] == 'delPush' ) $this->delPush();
    }

    private function e($str) {
        if (!get_magic_quotes_gpc()) $str = addslashes(trim(urldecode($str)));
        return $this->db->real_escape_string($str);
    }

    /**
     * 删除通知
     */
    private function delPush() {
        $sql = "DELETE FROM push WHERE id=" . intval($_GET['id']);
        $res = $this->db->query($sql);
        if ($res) die('true');
        die('false');
    }

    private function savePush() {
        $dataArr = array();
        $dataArr['title']	= $this->e($_GET['title']);
        $dataArr['btime']	= strtotime($this->e($_GET['btime']));
        $dataArr['etime']	= strtotime($this->e($_GET['etime']));
        $dataArr['status']	= $this->e($_GET['status']);
        $dataArr['link']	= $this->e($_GET['link']);
        $dataArr['match']	= $this->e($_GET['match']);
        $dataArr['is_test']	= intval($_GET['test']);

        //push类型
        if (filter_var($dataArr['match'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            $dataArr['type'] = 1;//IP
        } elseif ($dataArr['match'] == '*') {
            $dataArr['type'] = 2;//IP
        } else $dataArr['type'] = 3;//商品名称匹配

        if (!empty($_GET['id'])) {
            $tmpSetArr = array();

            $id = intval($_GET['id']);

            foreach ($dataArr as $k => $v) $tmpSetArr[] = " `$k`='$v'";

            $sql = "UPDATE push SET " . implode(',', $tmpSetArr) . " WHERE id=$id";

            $this->db->query($sql);

            die(trim($id));
        }

        $sql = 'INSERT INTO push(`' . join('`,`', array_keys($dataArr)) . 
            '`) VALUES("' . join('","', $dataArr) . '")';

        $this->db->query($sql);		
        die(trim($this->db->insert_id));
    }

    private function listPush() {
        $sql = "SELECT * FROM push order by id DESC";
        $data = Common::getDataBySql($sql);
        foreach ($data as $k => $v) {
            $v['btime'] = date('Y-m-d H:i:s', $v['btime']);
            $v['etime'] = date('Y-m-d H:i:s', $v['etime']);
            $v['ctime'] = $v['ctime'];
            $data[$k] = $v;
        }
        die(json_encode($data));
    }

    private function delTestData()	{
        $conArr = array();
        foreach ($GLOBALS['adminIp'] as $v) {
            if (strpos($v, '%') === false) 
                $conArr[] = "ip='$v'";
            else $conArr[] = "ip LIKE '$v'";
        }
        if ($conArr) {
            $sql = "delete from stat where " . join(' OR ', $conArr);
            $this->db->query($sql);
        }
        die;		
    }

    private function index() {

        //今日IP数
        $sql = "SELECT COUNT(DISTINCT ip) AS cou
            FROM `stat` 
            WHERE FROM_UNIXTIME(TIME) > '{$this->d} 00:00:00' 
            AND FROM_UNIXTIME(TIME) < '{$this->d} 23:59:59'";
        $ipNum = Common::getDataBySql($sql, 'cou', 'cou');
        $ipNum = array_pop($ipNum);

        //当日新增IP数
        $sql = "SELECT ip FROM  `stat` 
            GROUP BY ip 
            HAVING MIN( FROM_UNIXTIME( TIME ) ) >=  '{$this->d} 00:00:00' 
            AND MIN( FROM_UNIXTIME( TIME ) ) <=  '{$this->d} 23:59:59'";
        $newIp = Common::getDataBySql($sql, 'ip', 'ip');

        //当日新增IP数
        $newIpNum = count($newIp);

        //当天的操作顺序表
        $sql = "SELECT type AS ope,FROM_UNIXTIME( TIME ) AS ntime,ip,type
            FROM `stat` 
            WHERE FROM_UNIXTIME(TIME) >= '" . $this->d . " 00:00:00' AND FROM_UNIXTIME(TIME) <= '" . $this->d . " 23:59:59'
            ORDER BY time DESC";
        $operate = Common::getDataBySql($sql);
        
        //当天各类型操作数
        $stat = array();
        foreach ($operate as $v) {
            empty($stat[$v['type']]) && $stat[$v['type']] = 0;
            $stat[$v['type']]++;
        }
        
        //显示次数
        //require_once "BaeCounter.class.php";
        //$cr = new BaeCounter();
        
        //收藏总数
        
        $stat['ip']			= $ipNum;
        $stat['newIp']		= $newIpNum;
        $stat['notifyNum']	= 1;//$cr->get('c1');
        $stat['addNum']		= 1;//$cr->get('c2');
        
        die(json_encode(array(
            'stat'		=> $stat, 
            'ope'		=> $operate, 
            'new'		=> $newIp,
            'opeType'	=> $this->opeType
        )));
    }

    public function ipToLocal() {		
        if (empty($_GET['ip'])) die;
        $ip = trim($_GET['ip']);
        if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip)) die;

        $url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php';
        $ch = new Curl($url);
        $ch->setOption(CURLOPT_RETURNTRANSFER, true);
        $ch->setOption(CURLOPT_POST, 0);
        $ch->setOption(CURLOPT_POSTFIELDS, array(
            'format' => 'json',
            'ip'	 => $ip
        ));
        $detailHtml = $ch->exec();
        if (empty($detailHtml)) die;
        die($detailHtml);
    }
}
