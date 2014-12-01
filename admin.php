<?php if (!defined('isAdmin'))  { header('HTTP/1.1 404 Not Found'); die; }?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head _wxlaelhepjgkhpnfpeodbobgikmbjecnne_="shake_1.0">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="assets/css/jquery.datepick.css">
<script type="text/javascript" src="assets/js/jquery.min.js"></script>
<script type="text/javascript" src="assets/js/admin.js"></script>
<title>Mark 君</title>
</head>
<body>
<div id="left">
  <div id="opeTypeTotal"></div>
</div>
<div id="content">
  <div class="pushAdd content">
  <form id="form" action="donothing" method="get">
    <table border="1">
      <tr>
        <td>标题</td>
        <td><input id="title" type="text" size="40" required="required" /></td>
        <td>URL</td>
        <td><input id="link" type="url" size="40" required="required" title="如：http://www.baidu.com/" /></td>
      </tr>
      <tr>
        <td>开始时间</td>
        <td><input id="btime" type="datetime-local" required="required" title="请指定开始时间" /></td>
        <td>结束时间</td>
        <td><input id="etime" type="datetime-local" required="required" title="请指定结束时间" /></td>
      </tr>
      <tr>
        <td>状态描述</td>
        <td><input id="status" type="text" size="40" placeholder="eg:10分钟后/已经/即将开始(也可不填)" /></td>
        <td>推送范围</td>
        <td><input id="match" type="text" size="40" required="required" title="请指定推送范围" placeholder="* 或 具体IP 或 关键词(可多个)" /></td>
      </tr>
      <tr>
        <td>测试而已</td>
        <td>
            <label><input class="test" type="radio" checked="checked" name="test" value="1" />是</label>
            <label><input class="test" type="radio" name="test" value="0" />否</label>
        </td>
        <td colspan="2">
            <input id="save" type="submit" value="保存" />
            <input id="push_id" type="hidden" value="" />
            <input id="cancel" type="submit" value="取消" />
        </td>
      </tr>
    </table>
    </form>
  </div>
  <div class="flowList content"></div>
  <div class="pushList content"></div>
</div>
<div id="right">
  <input id="data-value" type="date" />
  <input id="filter" type="text" size="30" />
  <!--a id="del" class="button" href="?a=del">del test data</a-->
  <a id="pushList" class="button" href="?a=列表页面">list push</a>
  <a id="pushAdd" class="button" href="?a=添加push">add push</a>
  <a id="flowList" class="button" href="?a=flow list">flow list</a>
</div>
</body>
</html>
