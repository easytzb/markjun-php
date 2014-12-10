<?php
class Price {
    private $url    = null;
    private $host   = null;            
    private $need_delete    = false;

    public function __construct() {
        if (empty($_GET['url'])) Common::error(ERROR_NO_URL, 'ERROR_NO_URL');

        $this->url = trim(urldecode($_GET['url']));

        if (substr($this->url, 0, 4) != 'http') Common::error(ERROR_NOT_VALID_URL, 'ERROR_NOT_VALID_URL');
    }


    public function getPush() {
        Common::db();
        $ip = Common::getIp();

        $now = time();
        //$now = 1369554659;

        //非商品名匹配型的push
        $sql	= "SELECT * FROM push WHERE $now BETWEEN btime AND etime AND (match='$ip' OR `type`='2')";
        $data	= Common::getDataBySql($sql, 'id');

        //查看是否有商品型push
        $sql	= "SELECT COUNT(0) cou FROM push WHERE $now BETWEEN  btime AND etime AND `type`=3";
        $productMatch = intval(Common::getOne($sql));

        $data[0] = $productMatch;
        Common::d(($data));
    }

    /**
     * 获得价格并发出响应
     */
    public function response() {
        require_once dirname(__FILE__) . '/curl.php';

        $this->host = parse_url( $this->url, PHP_URL_HOST);
        $this->host = strtolower($this->host);
        switch ($this->host) {
        case 'item.taobao.com':
            $this->getTaobao();
            break;
        case 'wt.taobao.com':
            $this->getTaobaoWt();
            break;
        case 'detail.tmall.com':
            $this->getTmall();
            break;
        case 'mvd.360buy.com':
        case 'book.360buy.com':
        case 'e.360buy.com':
        case 'www.360buy.com':
        case 'mvd.jd.com':
        case 'book.jd.com':
        case 'e.jd.com':
        case 'www.jd.com':
        case 'item.jd.com':
            $this->get360Buy();
            break;
        case 'item.vancl.com':
        case 'item.vt.vancl.com':
            $this->getVancl();
            break;

        case 'item.vjia.com':
            $this->getVjia();
            break;

        case 'www.amazon.cn':
            $this->getAmazon();
            break;

            //case 'product.suning.com':
            //case 'www.suning.com':
            //    $this->getSuning();
            //    break;

        case 'get.push':
            $this->getPush();
            break;
        default :
            Common::error(ERROR_NOT_SUPPORT_HOST, 'ERROR_NOT_SUPPORT_HOST');
        }
    }

    /*private function getSuningEmall() {
        $ch = new Curl($this->url);
        $ch->setOption(CURLOPT_RETURNTRANSFER, true);
        $detailHtml = $ch->exec();
        $ch->close();

        $isSoldout = 0;
    }

    private function getSuning() {
        $ch = new Curl($this->url);
        $ch->setOption(CURLOPT_RETURNTRANSFER, true);
        $detailHtml = $ch->exec();
        $ch->close();

        $isSoldout = 0;

        preg_match('/name="name" value="(.+?)">.+?name="img" value="(.+?)">/s', $detailHtml, $info);
        $img    = trim(array_pop($info));
        $title  = trim(array_pop($info));

        preg_match('/sn=sn.+"storeId":\'(\d+)\',"catalogId":\'(\d+)\'.+?partNumber":"(\d+)".+sn\.snVendorCode="(\d+)"/s', $detailHtml, $info);
        $detailHtml = null;
        $snVendorCode = trim(array_pop($info));
        $partNumber   = trim(array_pop($info));
        $catalogId    = trim(array_pop($info));
        $storeId      = trim(array_pop($info));
        $newUrl = "http://product.suning.com/emall/csl_{$storeId}_{$catalogId}_{$catalogId}_{$partNumber}_9264_.html";

        $ch->init($newUrl);
        $ch->setOption(CURLOPT_RETURNTRANSFER, true);
        $detailHtml = $ch->exec($ch);
        preg_match('/"productPrice":"(.*?)"/s', $detailHtml, $info);
        $price = array_pop($info);
        var_dump($detailHtml);die;

        preg_match_all('/"promoPrice":"(.*?)"/s', $detailHtml, $info);
        $priceArr = array_pop($info);
        foreach ($priceArr as $v) {            
            if ($v == '') continue;
            if (floatval($v) != $v) continue;
            if (floatval($v) < floatval($price)) $price = floatval($v);
        }
        if ($price == '') {
            $isSoldout = 1;
            $price = 0;
        }

        Common::d((array(
            'u'     => $this->url,
            'p'     => $price,
            'i'     => $img,
            't'     => $title,
            'o'     => $isSoldout
        )));
    }*/

    private function getAmazon() {
        $ch = new Curl($this->url);
        $ch->setOption(CURLOPT_RETURNTRANSFER, true);
        $detailHtml = $ch->exec();
        $ch->close();

        $isSoldout = 0;
        if (strpos($detailHtml, ',"title":"') !== false) {
            if (strpos($detailHtml, '>目前无货<') !== false) 
                $isSoldout = 1;

            if (!$isSoldout) {
                preg_match('/id="priceblock_ourprice".+?￥?([0-9.,]+?)( |\<)/s', $detailHtml, $price);
                array_pop($price);
                $price = str_replace(',', '', trim(array_pop($price)));
            } else $price = 0;

            preg_match('/var data = \{.*?"main":\{"(.*?)"/s', $detailHtml, $img);
            $img = trim(array_pop($img));

            preg_match('/\,"title":"(.*?)"/s', $detailHtml, $title);
            $title = trim(array_pop($title));
        } elseif (strpos($detailHtml, 'fbt_x_img') !== false) {
            preg_match('/<td id="fbt_x_img">.*?src\="(.*?)" width.*?alt="(.*?)"/s', $detailHtml, $info);
            $title	= trim(array_pop($info));
            $img	= trim(array_pop($info));

            preg_match('/<span class="price">￥ (.*?)\</s', $detailHtml, $price);
            $price = str_replace(',', '', trim(array_pop($price)));
        } else {
            preg_match('/"btAsinTitle">(.+?)</s', $detailHtml, $info);
            $title	= trim(array_pop($info));

            preg_match('/largeImage = "(.+?)"/s', $detailHtml, $info);
            $img = trim(array_pop($info));

            preg_match('/kitsunePrice">￥ (.+?)\</s', $detailHtml, $info);
            $price = trim(array_pop($info));
        }

        Common::d((array(
            'u'     => $this->url,
            'p'	    => $price,
            'i'	    => $img,
            't'	    => $title,
            'o'     => $isSoldout 
        )));
    }

    private function getVjia() {
        $ch = new Curl($this->url);
        $ch->setOption(CURLOPT_RETURNTRANSFER, true);
        $detailHtml = $ch->exec();
        $ch->close();

        $isSoldout = strpos($detailHtml, 'ComdNull') === false?0:1;

        preg_match('/<div class\="sp\-bigImg".*?<img title="(.+?)" src="(.+?)"/s', $detailHtml, $info);
        $img	= trim(array_pop($info));
        $title	= trim(array_pop($info));

        preg_match('/id\="SpecialPrice"\>(.+?)\<\/span\>/s', $detailHtml, $price);
        $price = str_replace(',', '', trim(array_pop($price)));

        Common::d((array(
            'u'     => $this->url,
            'p'	    => $price,
            'i'	    => $img,
            't'	    => $title,
            'o'     => $isSoldout 
        )));
    }
    private function getVancl() {
        $ch = new Curl($this->url);
        $ch->setOption(CURLOPT_RETURNTRANSFER, true);
        $detailHtml = $ch->exec();
        $ch->close();

        $isSoldout = strpos($detailHtml, 'buhuo') === false?0:1;

        preg_match('/midimg.+?src="(.+?)".+?title="(.+?)".+?cuxiaoPrice.+?(\d+\.\d+)/s', $detailHtml, $info);

        Common::d((array(
            'u'     => $this->url,
            'p'	    => $info[3],
            'i'	    => $info[1],
            't'	    => $info[2],
            'o'     => $isSoldout
        )));
    }            

    /**
     * 获得京东信息
     */
    private function get360Buy() {
        preg_match('/(\d+)\.html/i', $this->url, $info);
        $url = 'http://d.360buy.com/fittingInfo/get?skuId=' . $info[1] . '&callback=Recommend.cbRecoFittings';
        $ch = new Curl($url);
        $ch->setOption(CURLOPT_RETURNTRANSFER, true);
        $detailHtml = $ch->exec();
        $ch->close();

        //preg_match('/master:{"wid":"\d+?","wmaprice":".+?","wmeprice":"(.+?)","name":"(.+?)","imgurl":"(.+?)"/i', $detailHtml, $info);
        $isOffLine = intval(strpos($detailHtml, '"fittings":[],"fittingType":[]') !== false);
        preg_match('/"master":({.+?}),/i', $detailHtml, $info);
        $info = json_decode($info[1]);
        $isOffLine = intval($info->price == '0.00');

        Common::d((array(
            'u'   => $this->url,
            'p'   => $info->price,
            'i'   => 'http://img14.360buyimg.com/n1/' . $info->pic,     //二级域名范围是img10-img14
            't'   => $info->name,
            'o'   => $isOffLine
        )));
    }

    private function getTmall() {

        $ch = new Curl($this->url);
        $ch->setOption(CURLOPT_RETURNTRANSFER, true);
        $ch->setOption(CURLOPT_ENCODING, 'gzip,deflate');
        $ch->setOption(CURLOPT_FOLLOWLOCATION, true); //对于301/2关键在这里
        $detailHtml = $ch->exec();
        $ch->close();

        //获取价格api地址
        $reg = '/"initApi":"(.+?)"/';
        preg_match($reg, $detailHtml, $newUrl);
        if (empty($newUrl) || empty($newUrl[1])) $newUrl = null;
        else {
            $newUrl = array_pop($newUrl);
            if (strpos($newUrl, 'http') === false) $newUrl = null;
        }

        if ($newUrl) {
            $this->tmallBase($detailHtml);
            unset($detailHtml);
            $ch->init($newUrl);
            $ch->setOption(CURLOPT_RETURNTRANSFER, true);
            $ch->setOption(CURLOPT_HTTPHEADER, array('Referer: ' . $this->url));
            $detailHtml	= $ch->exec($ch);
            $ch->close();
            //preg_match_all('/,"price":"([0-9.]+)","promotionList/', $detailHtml, $price);
            //if (isset($price[1])) $price = floatval(is_array($price[1])?min($price[1]):$priceBak);
            preg_match_all('/,"price":"([0-9.]+)","prom/', $detailHtml, $priceVip);
            if (isset($priceVip[1])) $this->priceVip = floatval(is_array($priceVip[1])?min($priceVip[1]):0);

        } else $this->getTaobaoWt($detailHtml);
        unset($detailHtml);
        $this->aliResponse();
    }

    private function getTaobaoWt($detailHtml='')	{
        if (empty($detailHtml)) {
            $ch = new Curl($this->url);
            $ch->setOption(CURLOPT_RETURNTRANSFER, true);
            $ch->setOption(CURLOPT_ENCODING, 'gzip,deflate');
            $ch->setOption(CURLOPT_FOLLOWLOCATION, true); //对于301/2关键在这里
            $detailHtml = $ch->exec();
            $ch->close();
        }

        //图片信息
        preg_match('/id="J_mainpic" src="(.+?)"/i', $detailHtml, $pic);
        $pic = trim(array_pop($pic));

        //商品名
        preg_match('/\<h3\>\<a href="" target="\_blank"\>(.+?)<\/a>/i', $detailHtml, $title);
        $title = trim(array_pop($title));
        $title = iconv('GBK', 'UTF-8', $title);

        //价格
        preg_match('/class="J_basePrice".*?(\d+\.\d\d) /i', $detailHtml, $price);
        $price = floatval(array_pop($price));

        //VIP价格
        preg_match('/J_promoPrice.*?(\d+\.\d\d).*?</s', $detailHtml, $priceVip);
        $priceVip = floatval(array_pop($priceVip));

        Common::d((array(
            'u' => $this->url,
            'p'	=> $price,
            'v'	=> $priceVip,
            'i'	=> $pic,
            't'	=> $title,
            'o' => 0
        )));
    }

    /**
     * 获取淘宝、天猫信息
     */
    private function getTaobao() {
        $ch = new Curl($this->url);
        $ch->setOption(CURLOPT_RETURNTRANSFER, true);
        $ch->setOption(CURLOPT_ENCODING, 'gzip,deflate');
        $ch->setOption(CURLOPT_FOLLOWLOCATION, true); //对于301/2关键在这里
        $detailHtml = $ch->exec();
        $ch->close();

        //判断是否已移除
        $this->need_delete = (strpos($detailHtml, 'error-notice-text') !== false);
        if ($this->need_delete) {
            //已移除的商品，啥也不用做了
            $this->title = '';
            $this->price = 0;
            $this->priceVip = 0;
            $this->pic = '';
            $this->offSale = 1;
            $this->aliResponse();
        }

        //获取价格api地址
        $reg = '/var b="(.+?)"/';
        preg_match($reg, $detailHtml, $newUrl);
        if (empty($newUrl) || empty($newUrl[1])) $newUrl = null;
        else {
            $newUrl = array_pop($newUrl);
            if (strpos($newUrl, 'http') === false) $newUrl = null;
        }

        $this->taobaoBase($detailHtml);
        unset($detailHtml);

        if ($newUrl) {			
            $ch->init($newUrl);
            $ch->setOption(CURLOPT_RETURNTRANSFER, true);
            $ch->setOption(CURLOPT_HTTPHEADER, array('Referer: ' . $this->url));
            $detailHtml	= $ch->exec($ch);
            $ch->close();

            $reg = '/price:"([0-9.]+)"/';
            preg_match_all($reg, $detailHtml, $priceVip);
            if (empty($priceVip[1])) {
                preg_match_all('/(?:low|high):([0-9.]+)/', $detailHtml, $priceVip);
            }
            if (isset($priceVip[1]) && ($priceVip = array_pop($priceVip)))
                $this->priceVip = floatval(is_array($priceVip)?min($priceVip):$priceVip);
            else $this->priceVip = 0;
        }
        $this->aliResponse();
    }

    private function tmallBase($detailHtml) {

        //判断是否下架
        $this->offSale = ((strpos($detailHtml, 'tb-key-off-sale') !== false) || (strpos($detailHtml, 'J_Sold-out-recommend') !== false));

        preg_match('/\<title\>(.*)\-tmall\.com/', $detailHtml, $info);
        $this->title = trim($info[1]);

        preg_match('/id="J_ImgBooth".+?src="(.*?)"/', $detailHtml, $info);		
        if (empty($info)) 
            preg_match('/url\((.*?)\)" id="J_ImgBooth"/', $detailHtml, $info);
        $this->pic = trim($info[1]);

        preg_match('/"defaultItemPrice":"([0-9\.]+?)[" ]/', $detailHtml, $info);
        $this->price = trim($info[1]);
    }

    private function taobaoBase($detailHtml) {

        //判断是否下架
        $this->offSale = ((strpos($detailHtml, 'tb-key-off-sale') !== false) || (strpos($detailHtml, 'J_Sold-out-recommend') !== false));

        preg_match('/<div id="J_itemViewed" catId="\d+" data\-value=\'(.+?)\'><\/div>/', $detailHtml, $info);
        $info = trim(array_pop($info));
        unset($detailHtml);

        //图片信息
        preg_match('/"pic":"(.+?)"/', $info, $pic);
        $this->pic = trim(array_pop($pic));

        //商品名
        preg_match('/"title":"(.+?)"/', $info, $title);
        $this->title = trim(array_pop($title));

        //价格
        preg_match('/"price":"(.+?)"/', $info, $price);
        $this->price = floatval(array_pop($price)/100);		
    }

    private function aliResponse() {
        if (isset($this->priceVip) && $this->priceVip == $this->price) 
            $this->priceVip = 0;

        if (empty($this->priceVip))
            $this->priceVip = empty($this->priceVip)?0:$this->priceVip;

        if ($this->pic 
            && strpos($this->pic, 'http') === false) {
            $this->pic = 'http://img01.taobaocdn.com/bao/uploaded/' . $this->pic;
        }

        $this->title = iconv('GBK', 'UTF-8', $this->title);

        Common::d((array(
            'u' => $this->url,
            'p'	=> $this->price,
            'v'	=> $this->priceVip,
            'i'	=> $this->pic,
            't'	=> $this->title,
            'o' => intval($this->offSale),
            'd' => $this->need_delete
        )));
    }
}
