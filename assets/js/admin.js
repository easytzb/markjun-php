$(function() {
    Stat.init();
})
var Stat = {
    init: function() {
        this.initDatepicker();
        this.getGlobalData();
        this.eventAdd();
    },
    initDatepicker: function() {
        $('#data-value').val((new Date()).pattern("yyyy-MM-dd"));
    },

    eventAdd: function() {
        $('#filter').live('keyup', function() {
            var v = $.trim(this.value);
            if (v == '') {
                $('.ipFrom').parent().show();
                $('#opeNum').html($('.ipFrom:visible').size());
                return;
            }
            if (v.match(/^\d{1,3}(\.(\d{1,3}(\.(\d{1,3}(\.\d{1,3}?)?)?)?)?)?$/) == null) return;
            $('.ipFrom').each(function() {
                var ip = $(this).attr('data');
                var p = $(this).parent();
                if (ip.indexOf(v) === -1) p.hide();
                else p.show();
            });
            $('#opeNum').html($('.ipFrom:visible').size());
        });
        $('tr').live('mouseover', function() {
            $(this).addClass('color');
        }).live('mouseout', function() {
            $(this).removeClass('color');
        });

        $('#del').click(function() {
            $.ajax({
                url: 'index.php',
                data: Stat.getParams({
                    a: 'del'
                }),
                success: function(res) {
                    if (res == '' && $('.flowList:visible').size())
                        Stat.getGlobalData();
                }
            });
            return false;
        });

        $('.ipFrom').live('click', function() {
            var ip = $.trim($(this).attr('data'));
            if ($.trim($('#filter').val()) == ip) $('#filter').val('');
            else $('#filter').val(ip);
            $('#filter').trigger("keyup");
            return false;
        });


        $('#data-value').change(function() {
            Stat.getGlobalData();
        });

        $('.button').live('click', function() {
            $('.button').not(this).css('font-weight', 'normal');
            $(this).css('font-weight', 'bold');
            $('.content').not('.' + this.id).hide();
            if ($('.' + this.id).html() != '' && $('.' + this.id + ':hidden').size())
                $('.' + this.id).show(1000);
            return false;
        });


        $('#save').live('click', function() {
            if ($('#form')[0].checkValidity() === false) {
                return true;
            }
            var data = {};
            data.a = 'savePush';
            data.id = $('#push_id').val();
            data.title = encodeURIComponent($('#title').val());
            data.link = encodeURIComponent($('#link').val());
            data.btime = encodeURIComponent($('#btime').val());
            data.etime = encodeURIComponent($('#etime').val());
            data.status = encodeURIComponent($('#status').val());
            data.match = encodeURIComponent($('#match').val());
            data.test = parseInt($('.test:checked').val());
            console.log(data);
            $(this).attr('disabled', true);

            $.ajax({
                url: 'index.php',
                data: Stat.getParams(data),
                success: function(res) {
                    $('#cancel').trigger('click');
                }
            });
            return false;
        });

        $('#cancel').live('click', function() {
            $('#save').attr('disabled', false);
            $('#title,#link,#status,#match,#push_id').val('');
            $('#btime,#etime').val('0000-00-00 00:00:00');
            $('#pushList').trigger('click');
            return false;
        });

        $('.delete').live('click', function() {
            var id = JSON.parse($(this).attr('data'));
            var data = {
                a: 'delPush',
                id: id.id
            };
            var _this = this;
            $.ajax({
                url: 'index.php',
                data: Stat.getParams(data),
                success: function(res) {
                    if (res == 'true') $(_this).parent().parent().hide();
                    else console.log('delete failed!');
                }
            });
            return false;
        });

        $('.edit').live('click', function() {
            var data = JSON.parse($(this).attr('data'));
            $('.pushAdd').show();
            $('#push_id').val(data.id);
            $('#title').val(data.title);
            $('#link').val(data.link);
            $('#btime').val(data.btime.replace(' ', 'T'));
            $('#etime').val(data.etime.replace(' ', 'T'));
            $('#status').val(data.status);
            $('#match').val(data.match);
            $('.test[value="' + data.is_test + '"]').attr('checked', true);
            return false;
        });

        $('#pushList').live('click', function() {
            $.ajax({
                url: 'index.php',
                data: Stat.getParams({
                    a: 'listPush'
                }),
                success: function(res) {

                    if (!res) return;

                    if (!(res = JSON.parse(res))) return;

                    var string = '<table border="1">';

                    for (var k in res) {
                        string += '<tr><td>' + res[k].id + '</td>';
                        string += '<td><a href="' + res[k].link +
                            '" target="_blank">' + res[k].title + '</a></td>';
                        string += '<td title="开始时间">' + res[k].btime + '</td>';
                        string += '<td title="结束时间">' + res[k].etime + '</td>';
                        string += '<td title="状态描述">' + res[k].status + '</td>';
                        string += '<td title="范围匹配">' + res[k].match + '</td>';
                        string += '<td title="创建时间">' + res[k].ctime + '</td>';
                        string += '<td title="是否为测试">' + res[k].is_test + '</td>';
                        string += '<td><a href="test" data=\'' + JSON.stringify(res[k]) + '\' class="delete" title="删除"><img src="assets/img/close.png" /></a>&nbsp;&nbsp;<a href="test" data=\'' + JSON.stringify(res[k]) + '\' class="edit" title="编辑"><img src="assets/img/edit.png" /></a></td></tr>';
                    }
                    string += '</table>';
                    $('.pushList').html(string).show(1000);
                }
            });
            return false;
        });
    },
    getGlobalData: function() {
        $.ajax({
            url: 'index.php',
            data: Stat.getParams({
                a: 'global'
            }),
            success: function(res) {
                res = JSON.parse(res);

                var string = '<table border="1">';
                var opeNum = '';
                for (var k in res.opeType) {
                    var tmpStr = '';
                    tmpStr += "<tr>";
                    tmpStr += "<td>" + k + "</td>";
                    tmpStr += "<td>" + res.opeType[k] + "</td>";
                    tmpStr += "<td>" + (res.stat[k] || 0) + "</td>";
                    tmpStr += "</tr>";
                    delete res.stat[k];
                    opeNum = tmpStr + opeNum;
                }

                string += opeNum;
                opeNum = null;

                for (var k in res.stat) {
                    string += "<tr>";
                    string += "<td>&nbsp;</td>";
                    string += "<td>" + k + "</td>";
                    string += "<td>" + (res.stat[k] || 0) + "</td>";
                    string += "</tr>";
                    delete res.stat[k];
                }
                string += '</table>';
                $('#opeTypeTotal').html(string);

                string = '<table border="1"><tr><td colspan="4" id="opeNum"></td></tr>';
                var i = 0;
                for (var k in res.ope) {
                    var v = res.ope[k];
                    string += "<tr>";
                    string += "<td>" + v.ntime + "</td>";
                    string += '<td>' + v.ope + '.' + res.opeType[v.ope] + '</td>';
                    string += "<td class='ipFrom' data='" + v.ip + "'>" + v.ip + "</td>";
                    string += "<td>&nbsp;</td>";
                    string += "</tr>";
                    i++;
                }
                string += '</table>';
                $('.flowList').html(string).find('#opeNum').html(i);
                $('#filter').trigger("keyup");
                Stat.ipToLocal();
            }
        });
    },

    getParams: function(ope) {
        ope.d = parseInt(new Date($('#data-value').val()).valueOf() / 1000);
        ope._ = (new Date()).valueOf();
        return ope;
    },
    ipToLocal: function() {

        $('.ipFrom').each(function(i) {
            var info = '';
            var ip = $(this).attr('data');
            if ((info = localStorage.getItem(ip)) != null) {
                $(this).next().html(info);
                return;
            }
            var _this = this;
            $.ajax({
                _this: _this,
                url: 'index.php',
                ip: ip,
                data: Stat.getParams({
                    a: 'getIp',
                    ip: ip
                }),
                dataType: 'json',
                success: function(res) {
                    if (!res) return;
                    var info = "";
                    if (res.country) info += res.country;
                    if (res.province) info += res.province;
                    if (res.city) info += res.city;
                    if (info == "") return;
                    localStorage.setItem(this.ip, info)
                    $(this._this).next().html(info);
                }
            });
        });
    }
};
