layui.define(['layer', 'table'], function (exports) {
    var $ = layui.jquery;
    var layer = layui.layer;
    var table = layui.table;

    var instances = [];

    var pearTreetable = {

        render: function (param) {
            param.method = param.method?param.method:"GET";
            if (!pearTreetable.checkParam(param)) {
                return;
            }
            if (param.data) {
                pearTreetable.init(param, param.data);
            } else {
                if(param.method === 'post' || param.method === 'POST') {
                    $.post(param.url, param.where, function(res){
                        if(param.parseData){
                            res = param.parseData(res);
                            param.data = res.data;
                        }
                        pearTreetable.init(param, res.data);
                    });
                } else {
                    $.get(param.url, param.where, function(res){
                        if(param.parseData){
                            res = param.parseData(res);
                            param.data = res.data;
                        }
                        pearTreetable.init(param, res.data);
                    });
                }
            }
        },
        
        // 根据元素获取当前行数据
        getRowData: function(elem, tableId) {
            var $tr = $(elem).closest('tr');
            var index = $tr.data('index');
            return table.cache[tableId] ? table.cache[tableId][index] : null;
        },
        
        // 根据id获取行数据
        getRowById: function(elem, id) {
            var instance = null;
            instances.forEach(function(item) {
                if (item.key === elem) {
                    instance = item;
                }
            });
            if (!instance || !instance.value.data) return null;
            for (var i = 0; i < instance.value.data.length; i++) {
                if (instance.value.data[i].id == id) {
                    return instance.value.data[i];
                }
            }
            return null;
        },
        
        // 更新行数据
        updateRowData: function(elem, id, field, value) {
            var instance = null;
            instances.forEach(function(item) {
                if (item.key === elem) {
                    instance = item;
                }
            });
            if (!instance || !instance.value.data) return false;
            for (var i = 0; i < instance.value.data.length; i++) {
                if (instance.value.data[i].id == id) {
                    instance.value.data[i][field] = value;
                    return true;
                }
            }
            return false;
        },
        // 渲染表格
        init: function (param, data) {
            var mData = [];
            var doneCallback = param.done;
            var tNodes = data;
            for (var i = 0; i < tNodes.length; i++) {
                var tt = tNodes[i];
                if (!tt.id) {
                    tt.id = tt[param.treeIdName];
                }
                if (!tt.pid) {
                    tt.pid = tt[param.treePidName] || 0; //变更
                }
            }

            /*var sort = function (s_pid, data) {
                for (var i = 0; i < data.length; i++) {
                    if (data[i].pid == s_pid) {
                        var len = mData.length;
                        if (len > 0 && mData[len - 1].id == s_pid) {
                            mData[len - 1].isParent = true;
                        }
                        mData.push(data[i]);
                        sort(data[i].id, data);
                    }
                }
            };
            sort(param.treeSpid, tNodes);*/

            var map = {}; // 变更
            for (var k in data) {
                map[data[k].id] = data[k];
            }
            for (var j in map) {
                if(map[j].pid && map[map[j].pid]) {
                    var parent = map[map[j].pid];
                    if (!parent.children) {
                        parent.children = [];
                        parent.isParent = true;
                    }
                    parent.children.push(map[j]);
                }
            }
            var tree = [];
            for (var l in map) {
                if (!map[l].pid || !map[map[l].pid]) {
                    map[l].isRoot = true;
                    tree.push(map[l]);
                }
            }
            function travel(item)
            {
                mData.push(item);
                if (item.children) {
                    for (var g in item.children) {
                        travel(item.children[g]);
                    }
                }
            }
            for (var h in tree) {
                travel(tree[h]);
            }

            param.prevUrl = param.url;
            param.url = undefined;
            param.data = mData;
            param.page = {
                count: param.data.length,
                limit: param.data.length
            };
            param.cols[0][param.treeColIndex].templet = function (d) {
                var mId = d.id;
                var mPid = d.pid;
                var isDir = d.isParent;
                var emptyNum = pearTreetable.getEmptyNum(mPid, mData);
                var iconHtml = '';
                for (var i = 0; i < emptyNum; i++) {
                    iconHtml += '<span class="treeTable-empty"></span>';
                }
                if (isDir) {
                    iconHtml += '<i class="layui-icon layui-icon-triangle-d"></i> <i class="layui-icon layui-icon-layer"></i>';
                } else {
                    iconHtml += '<i class="layui-icon layui-icon-file"></i>';
                }
                iconHtml += '&nbsp;&nbsp;';
                var ttype = isDir ? 'dir' : 'file';
                var vg = '<span class="treeTable-icon open" lay-tid="' + mId + '" lay-tpid="' + mPid + '" lay-ttype="' + ttype + '">';
                return vg + iconHtml + d[param.cols[0][param.treeColIndex].field] + '</span>'
            };

            param.done = function (res, curr, count) {
                $(param.elem).next().addClass('treeTable');
                $('.treeTable .layui-table-page').css('display', 'none');
                $(param.elem).next().attr('treeLinkage', param.treeLinkage);
                if (param.treeDefaultClose) {
                    pearTreetable.foldAll(param.elem);
                }
                if (doneCallback) {
                    doneCallback(res, curr, count);
                }
            };
            
            // 确保有表格ID用于编辑事件
            if (!param.id) {
                param.id = param.elem.replace('#', '') + '-table';
            }

            // 渲染表格
            table.render(param);
            
            // 注册编辑事件（如果配置了 onEdit 回调）
            if (param.onEdit) {
                table.on('edit(' + param.elem.replace('#', '') + ')', function(obj) {
                    param.onEdit({
                        value: obj.value,
                        data: obj.data,
                        field: obj.field,
                        update: obj.update,
                        tr: obj.tr
                    });
                });
            }
            
            var result = instances.some(item=>item.key===param.elem);
            if(!result){
                instances.push({key:param.elem,value:param});
            }
        },
        reload: function(elem) {
            instances.forEach(function(item){
                if(item.key === elem) {
                    $(elem).next().remove();
                    item.value.data = undefined;
                    item.value.url = item.value.prevUrl;
                    pearTreetable.render(item.value);
                }
            })
        },
		search: function(elem,keyword) {
			var $tds = $(elem).next('.treeTable').find('.layui-table-body tbody tr td');
			if (!keyword) {
			    $tds.css('background-color', 'transparent');
			    layer.msg("请输入关键字", {icon: 5});
			    return;
			}
			var searchCount = 0;
			$tds.each(function () {
			    $(this).css('background-color', 'transparent');
			    if ($(this).text().indexOf(keyword) >= 0) {
			        $(this).css('background-color', 'rgba(250,230,160,0.5)');
			        if (searchCount == 0) {
			            $('body,html').stop(true);
			            $('body,html').animate({scrollTop: $(this).offset().top - 150}, 500);
			        }
			        searchCount++;
			    }
			});
			if (searchCount == 0) {
			    layer.msg("没有匹配结果", {icon: 5});
			} else {
			    pearTreetable.expandAll(elem);
			}
		},
        getEmptyNum: function (pid, data) {
            var num = 0;
            if (!pid) {
                return num;
            }
            var tPid;
            for (var i = 0; i < data.length; i++) {
                if (pid == data[i].id) {
                    num += 1;
                    tPid = data[i].pid;
                    break;
                }
            }
            return num + pearTreetable.getEmptyNum(tPid, data);
        },
        // 展开/折叠行
        toggleRows: function ($dom, linkage) {
            var type = $dom.attr('lay-ttype');
            if ('file' == type) {
                return;
            }
            var mId = $dom.attr('lay-tid');
            var isOpen = $dom.hasClass('open');
            if (isOpen) {
                $dom.removeClass('open');
            } else {
                $dom.addClass('open');
            }
            $dom.closest('tbody').find('tr').each(function () {
                var $ti = $(this).find('.treeTable-icon');
                var pid = $ti.attr('lay-tpid');
                var ttype = $ti.attr('lay-ttype');
                var tOpen = $ti.hasClass('open');
                if (mId == pid) {
                    if (isOpen) {
                        $(this).hide();
                        if ('dir' == ttype && tOpen == isOpen) {
                            $ti.trigger('click');
                        }
                    } else {
                        $(this).show();
                        if (linkage && 'dir' == ttype && tOpen == isOpen) {
                            $ti.trigger('click');
                        }
                    }
                }
            });
        },
        // 检查参数
        checkParam: function (param) {
            /*if (!param.treeSpid && param.treeSpid != 0) {
                layer.msg('参数treeSpid不能为空', {icon: 5});
                return false;
            }*/

            if (!param.treeIdName) {
                layer.msg('参数treeIdName不能为空', {icon: 5});
                return false;
            }

            if (!param.treePidName) {
                layer.msg('参数treePidName不能为空', {icon: 5});
                return false;
            }

            if (!param.treeColIndex && param.treeColIndex != 0) {
                layer.msg('参数treeColIndex不能为空', {icon: 5});
                return false;
            }
            return true;
        },
        // 展开所有
        expandAll: function (dom) {
            $(dom).next('.treeTable').find('.layui-table-body tbody tr').each(function () {
                var $ti = $(this).find('.treeTable-icon');
                var ttype = $ti.attr('lay-ttype');
                var tOpen = $ti.hasClass('open');
                if ('dir' == ttype && !tOpen) {
                    $ti.trigger('click');
                }
            });
        },
        // 折叠所有
        foldAll: function (dom) {
            $(dom).next('.treeTable').find('.layui-table-body tbody tr').each(function () {
                var $ti = $(this).find('.treeTable-icon');
                var ttype = $ti.attr('lay-ttype');
                var tOpen = $ti.hasClass('open');
                if ('dir' == ttype && tOpen) {
                    $ti.trigger('click');
                }
            });
        }
    };

    // 给图标列绑定事件
    $('body').on('click', '.treeTable .treeTable-icon', function () {
        var treeLinkage = $(this).parents('.treeTable').attr('treeLinkage');
        if ('true' == treeLinkage) {
            pearTreetable.toggleRows($(this), true);
        } else {
            pearTreetable.toggleRows($(this), false);
        }
    });

    exports('pearTreetable', pearTreetable);
});