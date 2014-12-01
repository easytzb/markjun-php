<?php
/**
 * CURLOPT_RETURNTRANSFER	TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
 * CURLOPT_NOBODY			TRUE to exclude the body from the output. Request method is then set to HEAD. Changing this to FALSE does not change it to GET. 
 * CURLOPT_POST				启用时会发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样。
 * CURLOPT_POSTFIELDS		全部数据使用HTTP协议中的"POST"操作来发送。要发送文件，在文件名前面加上@前缀并使用完整路径。这个参数可以通过urlencoded后的字符串类
 * 							似'para1=val1&para2=val2&...'或使用一个以字段名为键值，字段数据为值的数组。如果value是一个数组，Content-Type头将会被设置成multipart/form-data。
 *
 */
class Curl{

    private $ch		= null;
    public $url	= null;
    private $option	= null;

    public function __construct( $url ) {
        $this->init($url);
    }
    
    public function __destruct() {
        $this->close();
    }

    public function init($url) {
        $this->close();
        $this->url	= $url;
        $this->ch	= curl_init( $this->url );
    }

    public function setOption( $option, $value ) {
        curl_setopt($this->ch, $option, $value );
        return $this;
    }

    public function setOptions( $options ) {
        if (!is_array( $options ))exit('The params for Gather::setOptions is not array.');
        foreach ( $options as $key => $val ) {
            $this->setOption( $key, $val );
        }
        return $this;
    }

    public function exec() {
        return curl_exec( $this->ch );
    }

    public function close() {
        if ( $this->ch ) {
			curl_close( $this->ch );
			$this->ch = null;
		}
    }
}