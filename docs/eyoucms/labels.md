# EyouCMS 企业站标签兼容指引

生成日期：2026-11-03T06:45:53+08:00。基线：本地 EyouCMS v1.7.7；官方文档：https://www.eyoucms.com/doc/label/

## 阅读规则

2026-11-07：本手册保留原版 EyouCMS 标签定义。Webman 模式共用这些属性及语义，将可执行标签命名空间与真实根变量分别写作 `webman`、`$webman`；不可直接混用。安装选择、literal 示例、缓存隔离及错误规则见[双标签体系](template-modes.md)。字段名与局部循环变量保持原名。

- 本文件记录源码事实和官方文档索引，不能代替运行验证。所有尚未执行的行为测试保持 `unverified`。
- 源码属性解析表达式保留 `isset`、`empty`、别名优先级和数值转换；表达式中的最后一个值通常是默认值，但嵌套条件必须按源码理解。
- 标签中的默认循环变量通常为 `$field`，嵌套循环应使用不同 `id`；具体默认值见各条源码表达式。
- 商城、会员工作流关闭；原版模板里的相应标签仍须按原版功能关闭时的语义解析，不能作为未知标签丢弃。
- 普通产品展示、参数、图集、下载、视频、招聘、单页、留言、自定义字段、语言与可视化不因排除商城会员而自动排除。
- 官方文档可能已更新；`observed_attributes`是页面示例里观察到的属性，不代表1.7.7已实现。完整规则以源码注册表和测试为准。
- `include`、变量点语法、`$Request`、过滤器管道、PHP表达式及原生控制标签需要独立语法测试。

## 覆盖总览

官方文档 79 条；源码主标签 102 个；模板语料 218 个。

## 标签与源码默认行为

### `php`

- 名称/别名：`php`
- 范围：`template_syntax`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:31`。
- 注册属性：无固定属性。
- 官方来源：https://www.eyoucms.com/doc/label/all/8905.html；上游更新时间：2025-03-21 16:49:55。

### `channel`

- 名称/别名：`channel`, `models`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:32`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagChannel.php`。
- 注册属性：`modelid`、`channelid`、`typeid`、`notypeid`、`type`、`row`、`loop`、`currentstyle`、`currentclass`、`id`、`name`、`key`、`empty`、`mod`、`titlelen`、`offset`、`limit`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$modelid = isset($tag['modelid']) ? $tag['modelid'] : (isset($tag['channelid']) ? $tag['channelid'] : ''); // L583
$typeid = !empty($tag['typeid']) ? $tag['typeid'] : ''; // L586
$notypeid = !empty($tag['notypeid']) ? $tag['notypeid'] : ''; // L589
$name = !empty($tag['name']) ? $tag['name'] : ''; // L592
$type = !empty($tag['type']) ? $tag['type'] : 'son'; // L593
$currentclass = !empty($tag['currentclass']) ? $tag['currentclass'] : ''; // L594
$currentclass = !empty($tag['currentstyle']) ? $tag['currentstyle'] : ''; // L596
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L598
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L599
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L600
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L602
$titlelen = !empty($tag['titlelen']) && is_numeric($tag['titlelen']) ? intval($tag['titlelen']) : 100; // L603
$offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0; // L604
$row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 100; // L606
```

### `channelartlist`

- 名称/别名：`channelartlist`, `modelsartlist`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:33`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagChannelartlist.php`。
- 注册属性：`modelid`、`channelid`、`typeid`、`type`、`row`、`loop`、`id`、`key`、`empty`、`titlelen`、`mod`、`currentstyle`、`currentclass`
- 官方来源：https://www.eyoucms.com/doc/label/all/493.html；上游更新时间：2023-10-23 17:25:52。

属性默认值及优先级（源码事实）：

```php
$modelid = isset($tag['modelid']) ? $tag['modelid'] : (isset($tag['channelid']) ? $tag['channelid'] : ''); // L689
$typeid = !empty($tag['typeid']) ? $tag['typeid'] : ''; // L692
$type = !empty($tag['type']) ? $tag['type'] : 'self'; // L695
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L697
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L698
$row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 10; // L701
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L702
$titlelen = !empty($tag['titlelen']) && is_numeric($tag['titlelen']) ? intval($tag['titlelen']) : 100; // L703
$currentclass = isset($tag['currentclass']) ? $tag['currentclass'] : ''; // L704
$currentclass = !empty($tag['currentstyle']) ? $tag['currentstyle'] : ''; // L706
```

### `arclist`

- 名称/别名：`arclist`, `artlist`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:34`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagArclist.php`。
- 注册属性：`modelid`、`channelid`、`typeid`、`notypeid`、`keyword`、`row`、`loop`、`offset`、`titlelen`、`limit`、`orderby`、`ordermode`、`orderway`、`noflag`、`flag`、`bodylen`、`infolen`、`empty`、`mod`、`name`、`id`、`key`、`addfields`、`tagid`、`pagesize`、`thumb`、`joinaid`、`arcrank`、`release`、`idlist`、`idrange`、`aid`、`type`、`siteall`
- 编译器另有读取：`orderWay`；注册属性列表不是完整白名单。
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$typeid = !empty($tag['typeid']) ? $tag['typeid'] : ''; // L762
$notypeid = !empty($tag['notypeid']) ? $tag['notypeid'] : ''; // L765
$modelid = isset($tag['modelid']) ? $tag['modelid'] : (isset($tag['channelid']) ? $tag['channelid'] : ''); // L768
$addfields = isset($tag['addfields']) ? $tag['addfields'] : ''; // L771
$joinaid = isset($tag['joinaid']) ? $tag['joinaid'] : ''; // L774
$keyword = isset($tag['keyword']) ? $tag['keyword'] : ''; // L777
$release = isset($tag['release']) ? $tag['release'] : 'off'; // L780
$idlist = isset($tag['idlist']) ? $tag['idlist'] : ''; // L783
$idrange = isset($tag['idrange']) ? $tag['idrange'] : ''; // L786
$aid = isset($tag['aid']) ? $tag['aid'] : ''; // L789
$name = !empty($tag['name']) ? $tag['name'] : ''; // L792
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L793
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L794
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L795
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L797
$orderby = isset($tag['orderby']) ? $tag['orderby'] : ''; // L798
$ordermode = !empty($tag['orderway']) ? $tag['orderway'] : $ordermode; // L806
$flag = isset($tag['flag']) ? $tag['flag'] : ''; // L809
$noflag = isset($tag['noflag']) ? $tag['noflag'] : ''; // L810
$tagid = isset($tag['tagid']) ? $tag['tagid'] : ''; // L811
$pagesize = !empty($tag['pagesize']) && is_numeric($tag['pagesize']) ? intval($tag['pagesize']) : 0; // L812
$thumb = !empty($tag['thumb']) ? $tag['thumb'] : 'on'; // L813
$titlelen = !empty($tag['titlelen']) && is_numeric($tag['titlelen']) ? intval($tag['titlelen']) : 100; // L814
$bodylen = !empty($tag['bodylen']) && is_numeric($tag['bodylen']) ? intval($tag['bodylen']) : 160; // L815
$bodylen = !empty($tag['infolen']) && is_numeric($tag['infolen']) ? intval($tag['infolen']) : 160; // L817
$offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0; // L819
$row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 10; // L821
$arcrank = !empty($tag['arcrank']) ? $tag['arcrank'] : 'off'; // L827
$type = !empty($tag['type']) ? $tag['type'] : ''; // L828
$siteall = isset($tag['siteall']) ? $tag['siteall'] : null; // L829
```

### `arcpagelist`

- 名称/别名：`arcpagelist`, `artpagelist`
- 范围：`list_search`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:35`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagArcpagelist.php`。
- 注册属性：`tagid`、`pagesize`、`id`、`tips`、`loading`、`callback`
- 官方来源：https://www.eyoucms.com/doc/label/list/6279.html；上游更新时间：2023-12-27 10:22:02。

属性默认值及优先级（源码事实）：

```php
$tagid = !empty($tag['tagid']) ? $tag['tagid'] : ''; // L1058
$pagesize = !empty($tag['pagesize']) && is_numeric($tag['pagesize']) ? intval($tag['pagesize']) : 0; // L1059
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L1060
$tips = isset($tag['tips']) ? $tag['tips'] : ''; // L1061
$callback = isset($tag['callback']) ? $tag['callback'] : ''; // L1062
$loading = isset($tag['loading']) ? $tag['loading'] : ''; // L1063
```

官方基础示例（具体版本差异仍需测试）：

```html
{eyou:artpagelist tagid='与artlist标签的tagid名称一致' pagesize='3' tips='没有数据了'}
    <a href="javascript:void(0);" {$field.onclick}>点击加载更多</a>
{/eyou:artpagelist}
```

### `list`

- 名称/别名：`list`
- 范围：`list_search`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:36`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagList.php`。
- 注册属性：`modelid`、`channelid`、`typeid`、`notypeid`、`pagesize`、`row`、`loop`、`keyword`、`titlelen`、`orderby`、`ordermode`、`orderway`、`noflag`、`flag`、`bodylen`、`infolen`、`empty`、`mod`、`id`、`key`、`addfields`、`thumb`、`arcrank`、`idlist`、`idrange`、`siteall`
- 编译器另有读取：`orderWay`；注册属性列表不是完整白名单。
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$typeid = !empty($tag['typeid']) ? $tag['typeid'] : ''; // L922
$notypeid = !empty($tag['notypeid']) ? $tag['notypeid'] : ''; // L925
$modelid = isset($tag['modelid']) ? $tag['modelid'] : (isset($tag['channelid']) ? $tag['channelid'] : ''); // L928
$addfields = isset($tag['addfields']) ? $tag['addfields'] : ''; // L931
$keyword = isset($tag['keyword']) ? $tag['keyword'] : ''; // L934
$idlist = isset($tag['idlist']) ? $tag['idlist'] : ''; // L937
$idrange = isset($tag['idrange']) ? $tag['idrange'] : ''; // L940
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L943
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L944
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L945
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L947
$pagesize = !empty($tag['pagesize']) && is_numeric($tag['pagesize']) ? intval($tag['pagesize']) : 10; // L952
$thumb = !empty($tag['thumb']) ? $tag['thumb'] : 'on'; // L953
$orderby = isset($tag['orderby']) ? $tag['orderby'] : ''; // L954
$ordermode = !empty($tag['orderway']) ? $tag['orderway'] : $ordermode; // L962
$flag = isset($tag['flag']) ? $tag['flag'] : ''; // L965
$noflag = isset($tag['noflag']) ? $tag['noflag'] : ''; // L966
$titlelen = !empty($tag['titlelen']) && is_numeric($tag['titlelen']) ? intval($tag['titlelen']) : 100; // L967
$bodylen = !empty($tag['bodylen']) && is_numeric($tag['bodylen']) ? intval($tag['bodylen']) : 160; // L968
$bodylen = !empty($tag['infolen']) && is_numeric($tag['infolen']) ? intval($tag['infolen']) : 160; // L970
$arcrank = !empty($tag['arcrank']) ? $tag['arcrank'] : 'off'; // L972
$siteall = isset($tag['siteall']) ? $tag['siteall'] : null; // L973
```

### `pagelist`

- 名称/别名：`pagelist`
- 范围：`list_search`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:37`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagPagelist.php`。
- 注册属性：`listitem`、`listsize`
- 官方来源：https://www.eyoucms.com/doc/label/list/497.html；上游更新时间：2023-10-23 18:04:30。

属性默认值及优先级（源码事实）：

```php
$listitem = !empty($tag['listitem']) ? $tag['listitem'] : ''; // L1035
$listsize = !empty($tag['listsize']) ? intval($tag['listsize']) : ''; // L1036
```

官方基础示例（具体版本差异仍需测试）：

```html
{eyou:pagelist listitem='index,pre,pageno,next,end,info' listsize='2' /}
```

### `position`

- 名称/别名：`position`, `crumb`
- 范围：`global_enterprise`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:38`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagPosition.php`。
- 注册属性：`symbol`、`style`
- 编译器另有读取：`typeid`；注册属性列表不是完整白名单。
- 官方来源：https://www.eyoucms.com/doc/label/all/500.html；上游更新时间：2023-10-23 17:22:20。

属性默认值及优先级（源码事实）：

```php
$typeid = !empty($tag['typeid']) ? $tag['typeid'] : ''; // L1098
$symbol = isset($tag['symbol']) ? $tag['symbol'] : ''; // L1101
$style = !empty($tag['style']) ? $tag['style'] : ''; // L1102
```

### `type`

- 名称/别名：`type`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:39`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagType.php`。
- 注册属性：`typeid`、`type`、`empty`、`dirname`、`id`、`addfields`、`addtable`
- 官方来源：https://www.eyoucms.com/doc/label/all/494.html；上游更新时间：2023-10-23 17:23:32。

属性默认值及优先级（源码事实）：

```php
$typeid = isset($tag['typeid']) ? $tag['typeid'] : ''; // L1213
$type = !empty($tag['type']) ? $tag['type'] : 'self'; // L1216
$empty = !empty($tag['empty']) ? $tag['empty'] : ''; // L1217
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L1219
$addfields = isset($tag['addfields']) ? $tag['addfields'] : ''; // L1221
```

### `arcview`

- 名称/别名：`arcview`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:40`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagArcview.php`。
- 注册属性：`aid`、`empty`、`id`、`addfields`、`joinaid`
- 官方来源：https://www.eyoucms.com/doc/label/all/498.html；上游更新时间：2023-10-23 17:22:38。

属性默认值及优先级（源码事实）：

```php
$aid_tmp = isset($tag['aid']) ? $tag['aid'] : '0'; // L1275
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L1278
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L1280
$addfields = isset($tag['addfields']) ? $tag['addfields'] : ''; // L1281
$joinaid = isset($tag['joinaid']) ? $tag['joinaid'] : ''; // L1284
```

### `arcclick`

- 名称/别名：`arcclick`
- 范围：`content`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:41`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagArcclick.php`。
- 注册属性：`aid`、`value`、`type`
- 官方来源：https://www.eyoucms.com/doc/label/arc/499.html；上游更新时间：2023-10-24 11:05:17。

属性默认值及优先级（源码事实）：

```php
$aid_tmp = isset($tag['aid']) ? $tag['aid'] : 0; // L2367
$value = isset($tag['value']) ? $tag['value'] : ''; // L2370
$type = isset($tag['type']) ? $tag['type'] : ''; // L2373
```

### `downcount`

- 名称/别名：`downcount`
- 范围：`content`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:42`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagDowncount.php`。
- 注册属性：`aid`
- 官方来源：https://www.eyoucms.com/doc/label/arc/8392.html；上游更新时间：2023-10-24 10:57:15。

属性默认值及优先级（源码事实）：

```php
$aid_tmp = isset($tag['aid']) ? $tag['aid'] : 0; // L2405
```

### `collectnum`

- 名称/别名：`collectnum`
- 范围：`disabled_member_commerce`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:43`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagCollectnum.php`。
- 注册属性：`aid`
- 官方来源：https://www.eyoucms.com/doc/label/arc/11186.html；上游更新时间：2023-10-24 09:22:31。

属性默认值及优先级（源码事实）：

```php
$aid_tmp = isset($tag['aid']) ? $tag['aid'] : 0; // L2438
```

### `freebuynum`

- 名称/别名：`freebuynum`
- 范围：`disabled_member_commerce`；闭合：单标签；验证：`unverified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:44`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagFreebuynum.php`。
- 注册属性：`aid`、`modelid`、`channelid`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$aid_tmp = isset($tag['aid']) ? $tag['aid'] : 0; // L2471
$modelid = isset($tag['modelid']) ? $tag['modelid'] : (isset($tag['channelid']) ? $tag['channelid'] : 0); // L2473
```

### `load`

- 名称/别名：`load`, `import`, `css`, `js`
- 范围：`template_syntax`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:45`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagLoad.php`。
- 注册属性：`file`、`href`、`type`、`value`、`basepath`
- 编译器另有读取：`ver`；注册属性列表不是完整白名单。
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$file = isset($tag['file']) ? $tag['file'] : $tag['href']; // L508
$type = isset($tag['type']) ? strtolower($tag['type']) : ''; // L509
$ver = !empty($tag['ver']) ? $tag['ver'] : 'on'; // L510
```

### `guestbookform`

- 名称/别名：`guestbookform`, `form`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:46`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagGuestbookform.php`。
- 注册属性：`typeid`、`type`、`formid`、`empty`、`id`、`mod`、`key`、`before`、`beforeSubmit`
- 编译器另有读取：`o`；注册属性列表不是完整白名单。
- 官方来源：https://www.eyoucms.com/doc/label/arc/502.html；上游更新时间：2026-04-24 14:25:09。
- 官方来源：https://www.eyoucms.com/doc/label/arc/29138.html；上游更新时间：2026-04-07 15:17:59。

属性默认值及优先级（源码事实）：

```php
$typeid = !empty($tag['formid']) ? $tag['formid'] : 0; // L1811
$typeid = !empty($tag['typeid']) ? $tag['typeid'] : 0; // L1816
$type = !empty($tag['type']) ? $tag['type'] : 'default'; // L1820
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L1821
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L1822
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L1823
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L1824
$beforeSubmit = !empty($tag['before']) ? $tag['before'] : ''; // L1826
$beforeSubmit = !empty($tag['beforeSubmit']) ? $tag['beforeSubmit'] : ''; // L1828
```

官方基础示例（具体版本差异仍需测试）：

```html
{eyou:guestbookform type='default'}
<form method="POST" action="{$field.action}"  {$field.formhidden} onsubmit="{$field.submit}" >
<input id="attr_1" type="text" value="" name="{$field.attr_1}" placeholder="{$field.itemname_1}">
<input id="attr_2" type="text" value="" name="{$field.attr_2}" placeholder="{$field.itemname_2}">
<textarea id="attr_3" cols="40" rows="3" name="{$field.attr_3}" placeholder="{$field.itemname_3}"></textarea>
<input type="submit" value="提交">
{$field.hidden}
</form>
{/eyou:guestbookform}
```

### `formreply`

- 名称/别名：`formreply`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:47`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagFormreply.php`。
- 注册属性：`aid`、`key`、`limit`、`row`
- 编译器另有读取：`empty`、`id`、`mod`、`ordermode`、`page`、`pagesize`、`typeid`；注册属性列表不是完整白名单。
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$typeid = !empty($tag['typeid']) ? $tag['typeid'] : ''; // L4680
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L4683
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L4684
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L4686
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L4687
$ordermode = !empty($tag['ordermode']) ? $tag['ordermode'] : 'desc'; // L4688
$pagesize = !empty($tag['pagesize']) ? trim($tag['pagesize']) : 10; // L4689
$page = !empty($tag['page']) ? trim($tag['page']) : 1; // L4690
```

### `assign`

- 名称/别名：`assign`
- 范围：`template_syntax`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:48`。
- 注册属性：`name`、`value`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

### `empty`

- 名称/别名：`empty`
- 范围：`template_syntax`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:49`。
- 注册属性：`name`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

### `notempty`

- 名称/别名：`notempty`
- 范围：`template_syntax`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:50`。
- 注册属性：`name`
- 官方来源：https://www.eyoucms.com/doc/label/all/505.html；上游更新时间：2025-07-07 10:05:40。

### `foreach`

- 名称/别名：`foreach`
- 范围：`template_syntax`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:51`。
- 注册属性：`name`、`id`、`item`、`key`、`offset`、`length`、`mod`
- 编译器另有读取：`empty`、`expression`、`index`；注册属性列表不是完整白名单。
- 官方来源：https://www.eyoucms.com/doc/label/all/506.html；上游更新时间：2023-10-23 17:32:50。

属性默认值及优先级（源码事实）：

```php
$key = !empty($tag['key']) ? $tag['key'] : 'key'; // L2036
$item = !empty($tag['id']) ? $tag['id'] : $tag['item']; // L2037
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L2038
$offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0; // L2040
$length = !empty($tag['length']) && is_numeric($tag['length']) ? intval($tag['length']) : 'null'; // L2041
```

### `volist`

- 名称/别名：`volist`, `iterate`
- 范围：`template_syntax`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:52`。
- 注册属性：`name`、`id`、`offset`、`length`、`key`、`mod`、`limit`、`row`、`loop`
- 编译器另有读取：`empty`；注册属性列表不是完整白名单。
- 官方来源：https://www.eyoucms.com/doc/label/all/507.html；上游更新时间：2023-10-23 17:30:05。

属性默认值及优先级（源码事实）：

```php
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L2274
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L2275
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L2277
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L2278
$offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0; // L2279
$length = !empty($tag['length']) && is_numeric($tag['length']) ? intval($tag['length']) : 'null'; // L2280
$length = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 'null'; // L2283
```

### `if`

- 名称/别名：`if`
- 范围：`template_syntax`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:53`。
- 注册属性：`condition`
- 编译器另有读取：`expression`；注册属性列表不是完整白名单。
- 官方来源：https://www.eyoucms.com/doc/label/all/508.html；上游更新时间：2023-10-23 17:30:30。

属性默认值及优先级（源码事实）：

```php
$condition = !empty($tag['expression']) ? $tag['expression'] : $tag['condition']; // L2109
```

### `elseif`

- 名称/别名：`elseif`
- 范围：`template_syntax`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:54`。
- 注册属性：`condition`
- 编译器另有读取：`expression`；注册属性列表不是完整白名单。
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$condition = !empty($tag['expression']) ? $tag['expression'] : $tag['condition']; // L2126
```

### `else`

- 名称/别名：`else`
- 范围：`template_syntax`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:55`。
- 注册属性：无固定属性。
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

### `switch`

- 名称/别名：`switch`
- 范围：`template_syntax`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:56`。
- 注册属性：`name`
- 编译器另有读取：`expression`；注册属性列表不是完整白名单。
- 官方来源：https://www.eyoucms.com/doc/label/all/509.html；上游更新时间：2023-10-23 17:32:20。

属性默认值及优先级（源码事实）：

```php
$name = !empty($tag['expression']) ? $tag['expression'] : $tag['name']; // L2174
```

### `case`

- 名称/别名：`case`
- 范围：`template_syntax`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:57`。
- 注册属性：`value`、`break`
- 编译器另有读取：`expression`；注册属性列表不是完整白名单。
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$value = !empty($tag['expression']) ? $tag['expression'] : $tag['value']; // L2189
$isBreak = isset($tag['break']) ? $tag['break'] : ''; // L2204
```

### `default`

- 名称/别名：`default`
- 范围：`template_syntax`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:58`。
- 注册属性：无固定属性。
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

### `compare`

- 名称/别名：`compare`, `eq`, `equal`, `notequal`, `neq`, `gt`, `lt`, `egt`, `elt`, `heq`, `nheq`
- 范围：`template_syntax`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:59`。
- 注册属性：`name`、`value`、`type`
- 官方来源：https://www.eyoucms.com/doc/label/all/510.html；上游更新时间：2023-10-23 17:31:29。

属性默认值及优先级（源码事实）：

```php
$name = isset($tag['name']) ? $tag['name'] : ''; // L2236
$value = isset($tag['value']) ? $tag['value'] : ''; // L2237
$type = isset($tag['type']) ? $tag['type'] : 'eq'; // L2238
```

### `ad`

- 名称/别名：`ad`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:60`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagAd.php`。
- 注册属性：`aid`、`id`
- 官方来源：https://www.eyoucms.com/doc/label/all/836.html；上游更新时间：2023-10-23 17:18:30。

属性默认值及优先级（源码事实）：

```php
$aid = isset($tag['aid']) ? $tag['aid'] : '0'; // L1641
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L1644
```

### `adv`

- 名称/别名：`adv`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:61`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagAdv.php`。
- 注册属性：`pid`、`row`、`loop`、`orderby`、`where`、`id`、`empty`、`key`、`mod`、`currentstyle`、`currentclass`
- 官方来源：https://www.eyoucms.com/doc/label/all/511.html；上游更新时间：2023-10-23 17:33:02。

属性默认值及优先级（源码事实）：

```php
$pid = !empty($tag['pid']) ? $tag['pid'] : '0'; // L1682
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L1684
$orderby = !empty($tag['orderby']) ? $tag['orderby'] : ''; // L1685
$row = !empty($tag['row']) ? $tag['row'] : 10; // L1687
$where = !empty($tag['where']) ? $tag['where'] : ''; // L1688
$key = !empty($tag['key']) ? $tag['key'] : 'key'; // L1689
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L1690
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L1691
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L1693
$currentclass = !empty($tag['currentclass']) ? $tag['currentclass'] : ''; // L1694
$currentclass = !empty($tag['currentstyle']) ? $tag['currentstyle'] : ''; // L1696
```

### `global`

- 名称/别名：`global`
- 范围：`global_enterprise`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:62`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagGlobal.php`。
- 注册属性：`name`
- 官方来源：https://www.eyoucms.com/doc/label/all/513.html；上游更新时间：2023-10-23 17:29:18。

### `static`

- 名称/别名：`static`
- 范围：`global_enterprise`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:63`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagStatic.php`。
- 注册属性：`file`、`lang`、`href`、`code`、`site`、`version`
- 官方来源：https://www.eyoucms.com/doc/label/all/11919.html；上游更新时间：2025-07-07 17:15:45。

属性默认值及优先级（源码事实）：

```php
$file = isset($tag['file']) ? $tag['file'] : ''; // L545
$href = isset($tag['href']) ? $tag['href'] : ''; // L547
$lang = !empty($tag['lang']) ? $tag['lang'] : ''; // L549
$site = !empty($tag['site']) ? $tag['site'] : ''; // L551
$code = !empty($tag['code']) ? $tag['code'] : ''; // L553
$version = !empty($tag['version']) && $tag['version'] !== 'false' ? true : false; // L554
```

### `prenext`

- 名称/别名：`prenext`, `beafter`
- 范围：`content`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:64`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagPrenext.php`。
- 注册属性：`get`、`titlelen`、`id`、`empty`
- 官方来源：https://www.eyoucms.com/doc/label/arc/518.html；上游更新时间：2023-10-25 16:00:52。

属性默认值及优先级（源码事实）：

```php
$get = !empty($tag['get']) ? $tag['get'] : 'pre'; // L1747
$titlelen = !empty($tag['titlelen']) && is_numeric($tag['titlelen']) ? intval($tag['titlelen']) : 100; // L1748
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L1749
$empty = isset($tag['empty']) ? $tag['empty'] : '暂无'; // L1758
```

### `field`

- 名称/别名：`field`
- 范围：`global_enterprise`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:65`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagField.php`。
- 注册属性：`name`、`addfields`、`aid`
- 官方来源：https://www.eyoucms.com/doc/label/all/519.html；上游更新时间：2023-10-23 17:28:50。

属性默认值及优先级（源码事实）：

```php
$aid_tmp = isset($tag['aid']) ? $tag['aid'] : '0'; // L1871
$name = isset($tag['name']) ? $tag['name'] : ''; // L1874
$addfields = isset($tag['addfields']) ? $tag['addfields'] : ''; // L1875
```

### `searchurl`

- 名称/别名：`searchurl`
- 范围：`global_enterprise`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:66`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagSearchurl.php`。
- 注册属性：无固定属性。
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

### `searchform`

- 名称/别名：`searchform`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:67`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagSearchform.php`。
- 注册属性：`channel`、`modelid`、`channelid`、`typeid`、`notypeid`、`flag`、`noflag`、`type`、`empty`、`id`、`mod`、`key`
- 官方来源：https://www.eyoucms.com/doc/label/all/521.html；上游更新时间：2024-08-14 16:08:22。

属性默认值及优先级（源码事实）：

```php
$modelid = isset($tag['modelid']) ? $tag['modelid'] : (isset($tag['channelid']) ? $tag['channelid'] : ''); // L1155
$modelid = !empty($tag['channel']) ? $tag['channel'] : ''; // L1157
$typeid = !empty($tag['typeid']) ? $tag['typeid'] : ''; // L1160
$notypeid = !empty($tag['notypeid']) ? $tag['notypeid'] : ''; // L1162
$flag = !empty($tag['flag']) ? $tag['flag'] : ''; // L1164
$noflag = !empty($tag['noflag']) ? $tag['noflag'] : ''; // L1166
$type = !empty($tag['type']) ? $tag['type'] : 'default'; // L1168
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L1169
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L1170
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L1171
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L1172
```

### `tag`

- 名称/别名：`tag`, `tags`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:68`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagTag.php`。
- 注册属性：`aid`、`name`、`row`、`loop`、`id`、`key`、`mod`、`typeid`、`getall`、`sort`、`empty`、`style`、`type`
- 官方来源：https://www.eyoucms.com/doc/label/all/520.html；上游更新时间：2022-08-04 17:31:56。

属性默认值及优先级（源码事实）：

```php
$aid = !empty($tag['aid']) ? $tag['aid'] : '0'; // L1341
$typeid = !empty($tag['typeid']) ? $tag['typeid'] : ''; // L1343
$getall = !empty($tag['getall']) ? $tag['getall'] : '0'; // L1345
$style = !empty($tag['style']) ? $tag['style'] : ''; // L1346
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L1347
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L1348
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L1349
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L1351
$row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 100; // L1353
$sort = !empty($tag['sort']) ? $tag['sort'] : 'new'; // L1354
$type = !empty($tag['type']) ? $tag['type'] : ''; // L1355
```

### `tagarclist`

- 名称/别名：`tagarclist`, `tagsartlist`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:69`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagTagarclist.php`。
- 注册属性：`keyword`、`row`、`loop`、`offset`、`titlelen`、`limit`、`orderby`、`ordermode`、`orderway`、`noflag`、`flag`、`bodylen`、`infolen`、`empty`、`mod`、`name`、`id`、`key`、`addfields`、`tagid`、`pagesize`、`thumb`、`arcrank`
- 编译器另有读取：`orderWay`；注册属性列表不是完整白名单。
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$addfields = isset($tag['addfields']) ? $tag['addfields'] : ''; // L1408
$keyword = isset($tag['keyword']) ? $tag['keyword'] : ''; // L1411
$name = !empty($tag['name']) ? $tag['name'] : ''; // L1414
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L1415
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L1416
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L1417
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L1419
$orderby = isset($tag['orderby']) ? $tag['orderby'] : ''; // L1420
$ordermode = !empty($tag['orderway']) ? $tag['orderway'] : $ordermode; // L1428
$flag = isset($tag['flag']) ? $tag['flag'] : ''; // L1431
$noflag = isset($tag['noflag']) ? $tag['noflag'] : ''; // L1432
$tagid = isset($tag['tagid']) ? $tag['tagid'] : ''; // L1433
$pagesize = !empty($tag['pagesize']) && is_numeric($tag['pagesize']) ? intval($tag['pagesize']) : 0; // L1435
$thumb = !empty($tag['thumb']) ? $tag['thumb'] : 'on'; // L1436
$titlelen = !empty($tag['titlelen']) && is_numeric($tag['titlelen']) ? intval($tag['titlelen']) : 100; // L1437
$bodylen = !empty($tag['bodylen']) && is_numeric($tag['bodylen']) ? intval($tag['bodylen']) : 160; // L1438
$bodylen = !empty($tag['infolen']) && is_numeric($tag['infolen']) ? intval($tag['infolen']) : 160; // L1440
$arcrank = !empty($tag['arcrank']) ? $tag['arcrank'] : 'off'; // L1442
$limit = !empty($tag['limit']) ? trim($tag['limit']) : 20; // L1443
$limit = !empty($tag['row']) ? intval($tag['row']) : $limit; // L1445
```

### `flink`

- 名称/别名：`flink`, `links`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:70`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagFlink.php`。
- 注册属性：`type`、`groupid`、`row`、`loop`、`id`、`key`、`mod`、`titlelen`、`empty`、`limit`
- 官方来源：https://www.eyoucms.com/doc/label/all/578.html；上游更新时间：2023-10-23 17:20:48。

属性默认值及优先级（源码事实）：

```php
$type = !empty($tag['type']) ? $tag['type'] : 'text'; // L1499
$groupid = !empty($tag['groupid']) ? $tag['groupid'] : ""; // L1500
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L1502
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L1503
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L1504
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L1506
$titlelen = !empty($tag['titlelen']) && is_numeric($tag['titlelen']) ? intval($tag['titlelen']) : 100; // L1507
$row = !empty($tag['row']) ? intval($tag['row']) : 0; // L1509
$limit = !empty($tag['limit']) ? $tag['limit'] : ''; // L1510
```

### `language`

- 名称/别名：`language`
- 范围：`language`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:71`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagLanguage.php`。
- 注册属性：`type`、`row`、`loop`、`id`、`key`、`mod`、`titlelen`、`empty`、`limit`、`currentstyle`、`currentclass`
- 官方来源：https://www.eyoucms.com/doc/label/language/6058.html；上游更新时间：2023-10-24 12:05:05。

属性默认值及优先级（源码事实）：

```php
$type = !empty($tag['type']) ? $tag['type'] : ''; // L1553
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L1554
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L1555
$currentclass = !empty($tag['currentclass']) ? $tag['currentclass'] : ''; // L1556
$currentclass = !empty($tag['currentstyle']) ? $tag['currentstyle'] : ''; // L1558
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L1560
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L1562
$titlelen = !empty($tag['titlelen']) && is_numeric($tag['titlelen']) ? intval($tag['titlelen']) : 100; // L1563
$row = !empty($tag['row']) ? intval($tag['row']) : 0; // L1565
$limit = !empty($tag['limit']) ? $tag['limit'] : ''; // L1566
```

### `lang`

- 名称/别名：`lang`
- 范围：`language`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:72`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagLang.php`。
- 注册属性：`name`、`const`
- 官方来源：https://www.eyoucms.com/doc/label/language/6059.html；上游更新时间：2023-10-25 16:01:02。

属性默认值及优先级（源码事实）：

```php
$name = isset($tag['name']) ? $tag['name'] : ''; // L1608
$const = isset($tag['const']) ? $tag['const'] : ''; // L1611
```

### `ui`

- 名称/别名：`ui`
- 范围：`visual_editor`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:73`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagUi.php`。
- 注册属性：`open`
- 官方来源：https://www.eyoucms.com/doc/label/ui/571.html；上游更新时间：2023-10-24 11:58:04。

属性默认值及优先级（源码事实）：

```php
$open = !empty($tag['open']) ? $tag['open'] : ''; // L244
```

### `uitext`

- 名称/别名：`uitext`
- 范围：`visual_editor`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:74`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagUitext.php`。
- 注册属性：`e-id`、`e-page`、`id`
- 官方来源：https://www.eyoucms.com/doc/label/ui/572.html；上游更新时间：2023-10-24 11:58:13。

属性默认值及优先级（源码事实）：

```php
$e_id = isset($tag['e-id']) ? $tag['e-id'] : ''; // L458
$e_page = isset($tag['e-page']) ? $tag['e-page'] : ''; // L459
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L460
```

### `uihtml`

- 名称/别名：`uihtml`
- 范围：`visual_editor`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:75`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagUihtml.php`。
- 注册属性：`e-id`、`e-page`、`id`
- 官方来源：https://www.eyoucms.com/doc/label/ui/573.html；上游更新时间：2023-10-24 11:58:31。

属性默认值及优先级（源码事实）：

```php
$e_id = isset($tag['e-id']) ? $tag['e-id'] : ''; // L378
$e_page = isset($tag['e-page']) ? $tag['e-page'] : ''; // L379
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L380
```

### `uiupload`

- 名称/别名：`uiupload`
- 范围：`visual_editor`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:76`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagUiupload.php`。
- 注册属性：`e-id`、`e-page`、`id`
- 官方来源：https://www.eyoucms.com/doc/label/ui/574.html；上游更新时间：2023-10-24 11:58:40。

属性默认值及优先级（源码事实）：

```php
$e_id = isset($tag['e-id']) ? $tag['e-id'] : ''; // L263
$e_page = isset($tag['e-page']) ? $tag['e-page'] : ''; // L264
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L265
```

### `uitype`

- 名称/别名：`uitype`
- 范围：`visual_editor`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:77`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagUitype.php`。
- 注册属性：`e-id`、`e-page`、`id`、`typeid`
- 官方来源：https://www.eyoucms.com/doc/label/ui/575.html；上游更新时间：2023-10-24 11:59:55。

属性默认值及优先级（源码事实）：

```php
$typeid = isset($tag['typeid']) ? $tag['typeid'] : ''; // L319
$e_id = isset($tag['e-id']) ? $tag['e-id'] : ''; // L320
$e_page = isset($tag['e-page']) ? $tag['e-page'] : ''; // L321
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L322
```

### `uiarclist`

- 名称/别名：`uiarclist`, `uiartlist`
- 范围：`visual_editor`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:78`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagUiarclist.php`。
- 注册属性：`e-id`、`e-page`、`id`、`typeid`
- 官方来源：https://www.eyoucms.com/doc/label/ui/576.html；上游更新时间：2023-10-24 12:01:34。

属性默认值及优先级（源码事实）：

```php
$typeid = isset($tag['typeid']) ? $tag['typeid'] : ''; // L348
$e_id = isset($tag['e-id']) ? $tag['e-id'] : ''; // L349
$e_page = isset($tag['e-page']) ? $tag['e-page'] : ''; // L350
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L351
```

### `uichannel`

- 名称/别名：`uichannel`, `uimodels`
- 范围：`visual_editor`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:79`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagUichannel.php`。
- 注册属性：`e-id`、`e-page`、`id`、`typeid`
- 官方来源：https://www.eyoucms.com/doc/label/ui/577.html；上游更新时间：2023-10-24 12:01:23。

属性默认值及优先级（源码事实）：

```php
$typeid = isset($tag['typeid']) ? $tag['typeid'] : ''; // L289
$e_id = isset($tag['e-id']) ? $tag['e-id'] : ''; // L290
$e_page = isset($tag['e-page']) ? $tag['e-page'] : ''; // L291
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L292
```

### `uimap`

- 名称/别名：`uimap`
- 范围：`visual_editor`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:80`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagUimap.php`。
- 注册属性：`e-id`、`e-page`、`id`、`width`、`height`
- 官方来源：https://www.eyoucms.com/doc/label/ui/11541.html；上游更新时间：2023-10-24 11:06:08。

属性默认值及优先级（源码事实）：

```php
$e_id = isset($tag['e-id']) ? $tag['e-id'] : ''; // L404
$e_page = isset($tag['e-page']) ? $tag['e-page'] : ''; // L405
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L406
$width = !empty($tag['width']) ? $tag['width'] : '100%'; // L407
$height = !empty($tag['height']) ? $tag['height'] : 350; // L408
```

### `uicode`

- 名称/别名：`uicode`
- 范围：`visual_editor`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:81`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagUicode.php`。
- 注册属性：`e-id`、`e-page`、`id`
- 官方来源：https://www.eyoucms.com/doc/label/ui/11518.html；上游更新时间：2023-10-24 11:08:31。

属性默认值及优先级（源码事实）：

```php
$e_id = isset($tag['e-id']) ? $tag['e-id'] : ''; // L432
$e_page = isset($tag['e-page']) ? $tag['e-page'] : ''; // L433
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L434
```

### `uibackground`

- 名称/别名：`uibackground`
- 范围：`visual_editor`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:82`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagUibackground.php`。
- 注册属性：`e-id`、`e-page`、`id`、`e-img`
- 官方来源：https://www.eyoucms.com/doc/label/ui/11540.html；上游更新时间：2023-10-24 11:08:15。

属性默认值及优先级（源码事实）：

```php
$e_id = isset($tag['e-id']) ? $tag['e-id'] : ''; // L485
$e_page = isset($tag['e-page']) ? $tag['e-page'] : ''; // L486
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L487
$e_img = isset($tag['e-img']) ? $tag['e-img'] : ''; // L488
```

### `sql`

- 名称/别名：`sql`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:83`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagSql.php`。
- 注册属性：`sql`、`key`、`id`、`mod`、`cachetime`、`empty`
- 官方来源：https://www.eyoucms.com/doc/label/all/579.html；上游更新时间：2023-10-23 17:20:27。

属性默认值及优先级（源码事实）：

```php
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L196
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L197
$id = !empty($tag['id']) ? $tag['id'] : 'field'; // L198
$cachetime = !empty($tag['cachetime']) ? $tag['cachetime'] : ''; // L199
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L200
```

### `weapp`

- 名称/别名：`weapp`
- 范围：`extension_contract`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:84`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagWeapp.php`。
- 注册属性：`type`
- 官方来源：https://www.eyoucms.com/doc/label/all/580.html；上游更新时间：2023-10-23 17:19:45。

属性默认值及优先级（源码事实）：

```php
$type = isset($tag['type']) ? $tag['type'] : 'default'; // L2521
```

### `range`

- 名称/别名：`range`, `in`, `notin`, `between`, `notbetween`
- 范围：`template_syntax`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:85`。
- 注册属性：`name`、`value`、`type`
- 官方来源：https://www.eyoucms.com/doc/label/all/581.html；上游更新时间：2023-10-23 17:19:28。

属性默认值及优先级（源码事实）：

```php
$type = isset($tag['type']) ? $tag['type'] : 'in'; // L2546
```

### `present`

- 名称/别名：`present`
- 范围：`template_syntax`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:86`。
- 注册属性：`name`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

### `notpresent`

- 名称/别名：`notpresent`
- 范围：`template_syntax`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:87`。
- 注册属性：`name`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

### `defined`

- 名称/别名：`defined`
- 范围：`template_syntax`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:88`。
- 注册属性：`name`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

### `notdefined`

- 名称/别名：`notdefined`
- 范围：`template_syntax`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:89`。
- 注册属性：`name`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

### `define`

- 名称/别名：`define`
- 范围：`template_syntax`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:90`。
- 注册属性：`name`、`value`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

### `for`

- 名称/别名：`for`
- 范围：`template_syntax`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:91`。
- 注册属性：`start`、`end`、`name`、`comparison`、`step`
- 官方来源：https://www.eyoucms.com/doc/label/all/582.html；上游更新时间：2023-10-23 17:19:12。

### `url`

- 名称/别名：`url`
- 范围：`template_syntax`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:92`。
- 注册属性：`link`、`vars`、`suffix`、`domain`、`seo_pseudo`、`seo_pseudo_format`、`seo_inlet`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$link = isset($tag['link']) ? $tag['link'] : ''; // L2680
$vars = isset($tag['vars']) ? $tag['vars'] : ''; // L2681
$suffix = isset($tag['suffix']) ? $tag['suffix'] : 'true'; // L2682
$domain = isset($tag['domain']) ? $tag['domain'] : 'false'; // L2683
$seo_pseudo = isset($tag['seo_pseudo']) ? $tag['seo_pseudo'] : 'null'; // L2684
$seo_pseudo_format = isset($tag['seo_pseudo_format']) ? $tag['seo_pseudo_format'] : 'null'; // L2685
$seo_inlet = isset($tag['seo_inlet']) ? $tag['seo_inlet'] : 'null'; // L2686
```

### `function`

- 名称/别名：`function`
- 范围：`template_syntax`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:93`。
- 注册属性：`name`、`vars`、`use`、`call`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$name = !empty($tag['name']) ? $tag['name'] : 'func'; // L2709
$vars = !empty($tag['vars']) ? $tag['vars'] : ''; // L2710
$call = !empty($tag['call']) ? $tag['call'] : ''; // L2711
```

### `diyfield`

- 名称/别名：`diyfield`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:94`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagDiyfield.php`。
- 注册属性：`name`、`id`、`key`、`mod`、`type`、`empty`、`limit`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L2738
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L2739
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L2740
$type = isset($tag['type']) ? $tag['type'] : 'default'; // L2741
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L2742
```

### `attribute`

- 名称/别名：`attribute`
- 范围：`content`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:95`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagAttribute.php`。
- 注册属性：`aid`、`type`、`row`、`loop`、`limit`、`empty`、`id`、`mod`、`key`
- 编译器另有读取：`attrid`、`offset`；注册属性列表不是完整白名单。
- 官方来源：https://www.eyoucms.com/doc/label/all/6394.html；上游更新时间：2023-10-23 17:30:16。
- 官方来源：https://www.eyoucms.com/doc/label/arc/10108.html；上游更新时间：2025-03-21 16:46:06。

属性默认值及优先级（源码事实）：

```php
$aid = !empty($tag['aid']) ? $tag['aid'] : ''; // L2902
$attrid = !empty($tag['attrid']) ? $tag['attrid'] : ''; // L2904
$type = !empty($tag['type']) ? $tag['type'] : 'default'; // L2906
$id = isset($tag['id']) ? $tag['id'] : 'attr'; // L2907
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L2908
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L2909
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L2910
$offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0; // L2912
$row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 100; // L2914
```

### `attr`

- 名称/别名：`attr`
- 范围：`content`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:96`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagAttr.php`。
- 注册属性：`aid`、`name`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$aid = !empty($tag['aid']) ? $tag['aid'] : ''; // L2967
$name = isset($tag['name']) ? $tag['name'] : ''; // L2969
```

### `user`

- 名称/别名：`user`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:97`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagUser.php`。
- 注册属性：`type`、`id`、`key`、`mod`、`empty`、`currentstyle`、`currentclass`、`img`、`txt`、`txtid`、`afterhtml`、`viewfile`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$type = !empty($tag['type']) ? $tag['type'] : 'default'; // L3004
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L3005
$key = isset($tag['key']) ? $tag['key'] : 'i'; // L3006
$txt = !empty($tag['txt']) ? $tag['txt'] : ''; // L3007
$txtid = !empty($tag['txtid']) ? $tag['txtid'] : ''; // L3009
$img = !empty($tag['img']) ? $tag['img'] : 'off'; // L3010
$currentclass = !empty($tag['currentclass']) ? $tag['currentclass'] : ''; // L3011
$currentclass = !empty($tag['currentstyle']) ? $tag['currentstyle'] : ''; // L3013
$afterhtml = !empty($tag['afterhtml']) ? $tag['afterhtml'] : ''; // L3015
$viewfile = !empty($tag['viewfile']) ? $tag['viewfile'] : ''; // L3017
```

### `weapplist`

- 名称/别名：`weapplist`
- 范围：`extension_contract`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:98`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagWeapplist.php`。
- 注册属性：`type`、`id`、`key`、`mod`、`empty`、`currentstyle`、`currentclass`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$type = isset($tag['type']) ? $tag['type'] : 'default'; // L3047
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L3048
$key = isset($tag['key']) ? $tag['key'] : 'i'; // L3049
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L3050
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L3051
$currentclass = !empty($tag['currentclass']) ? $tag['currentclass'] : ''; // L3052
$currentclass = !empty($tag['currentstyle']) ? $tag['currentstyle'] : ''; // L3054
```

### `usermenu`

- 名称/别名：`usermenu`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:99`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagUsermenu.php`。
- 注册属性：`row`、`loop`、`id`、`empty`、`key`、`mod`、`currentstyle`、`currentclass`、`limit`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L3094
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L3095
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L3096
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L3098
$currentclass = !empty($tag['currentclass']) ? $tag['currentclass'] : ''; // L3099
$currentclass = !empty($tag['currentstyle']) ? $tag['currentstyle'] : ''; // L3101
$row = !empty($tag['row']) ? intval($tag['row']) : 0; // L3104
$limit = !empty($tag['limit']) ? $tag['limit'] : ''; // L3105
```

### `sppurchase`

- 名称/别名：`sppurchase`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:101`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagSppurchase.php`。
- 注册属性：`row`、`loop`、`id`、`key`、`mod`、`empty`、`currentstyle`、`currentclass`
- 编译器另有读取：`img`、`txt`、`type`；注册属性列表不是完整白名单。
- 官方来源：https://www.eyoucms.com/doc/label/arc/31362.html；上游更新时间：2024-05-07 16:51:15。

属性默认值及优先级（源码事实）：

```php
$type = !empty($tag['type']) ? $tag['type'] : 'default'; // L3146
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L3147
$key = isset($tag['key']) ? $tag['key'] : 'i'; // L3148
$txt = !empty($tag['txt']) ? $tag['txt'] : ''; // L3149
$img = !empty($tag['img']) ? $tag['img'] : 'off'; // L3151
$currentclass = !empty($tag['currentclass']) ? $tag['currentclass'] : ''; // L3152
$currentclass = !empty($tag['currentstyle']) ? $tag['currentstyle'] : ''; // L3154
```

官方基础示例（具体版本差异仍需测试）：

```html
<!--商品购买组件start-->
{eyou:sppurchase id='field' currentstyle='btn-danger'}
    <!-- 价格 标签开始 这里列出所有，请按需删除 -->
    <div class="ey-price">
        <!-- 原价 -->
        <span>￥{$field.old_price}</span> 
    </div>
    <div class="ey-price">
        <!-- 规格价 -->
        <span>￥{$field.spec_price}</span> 
    </div>
    <div class="ey-price">
        <!-- 会员价 设置会员折扣时会规格价+原价-->
        <span>￥{$field.users_price}</span> 
    </div>
    <div class="ey-price">
        <!-- 划线价 -->
        <span>￥{$field.crossed_price}</span> 
    </div>
    <div class="ey-price">
        <!-- 商品总价 -->
        <span>￥{$field.totol_price}</span> 
    </div>
    <!-- 价格 标签结束 -->
    <!-- 规格 标签开始 -->
    <div class="ey-spec">
    {eyou:volist name="$field.ReturnData" id='field2'}
        <div class="row m-t-15">
            <label class="form-control-label col-sm-7">{$field2.spec_name}</label>
            <div class="col-sm-10">
            {eyou:volist name="$field2.spec_value" id='field3'}
                <a href="JavaScript:void(0);" {$field3.SpecData} class="btn btn-default btn-selected {$field3.SpecClass}">
                    <!-- 规格图片 标签开始 -->
                    {notempty name="$field3.spec_image"}
                        <img src="{$field3.spec_image}" style="width: 20px; height: 20px;">
                    {/notempty}
                    <!-- 规格图片 标签结束 -->
                    {$field3.spec_value}
                </a>
            {/eyou:volist} 
            </div>
        </div>
    {/eyou:volist}
    </div>
    <!-- 规格 标签结束 -->
    <!-- 数量操作 标签开始 -->
    <div class="ey-number">
        <label>数量</label>
        <div class="btn-input">
            <button class="layui-btn" {$field.ReduceQuantity}>-</button>
            <input type="text" class="layui-input" {$field.UpdateQuantity}>
            <button class="layui-btn" {$field.IncreaseQuantity}>+</button>
        </div>
    </div>
    <!-- 数量操作 标签结束 -->
    <!-- 库存量 标签开始 -->
    <span {$field.stock_show}>库存量：{$field.stock_count} 件</span>
    <!-- 库存量 标签结束 -->
    <!-- 购买按钮 标签开始 -->
    <div class="ey-buyaction">
        <a class="ey-joinin" href="JavaScript:void(0);" {$field.ShopAddCart}>加入购物车</a>
        <a class="ey-joinbuy" href="JavaScript:void(0);" {$field.BuyNow}>立即购买</a>
    </div>
    <!-- 购买按钮 标签结束 -->
    {$field.hidden}
{/eyou:sppurchase}
<!--购物车组件end-->
```

### `spcart`

- 名称/别名：`spcart`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:103`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagSpcart.php`。
- 注册属性：`row`、`loop`、`id`、`key`、`mod`、`empty`、`limit`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L3188
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L3189
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L3190
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L3192
$row = !empty($tag['row']) ? intval($tag['row']) : 0; // L3194
$limit = !empty($tag['limit']) ? $tag['limit'] : ''; // L3195
```

### `sporder`

- 名称/别名：`sporder`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:105`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagSporder.php`。
- 注册属性：`row`、`loop`、`id`、`key`、`mod`、`empty`、`limit`
- 编译器另有读取：`name`、`order_id`；注册属性列表不是完整白名单。
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$name = isset($tag['name']) ? $tag['name'] : ''; // L3233
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L3234
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L3235
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L3236
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L3238
$row = !empty($tag['row']) ? intval($tag['row']) : 0; // L3240
$limit = !empty($tag['limit']) ? $tag['limit'] : ''; // L3241
$order_id = !empty($tag['order_id']) ? $tag['order_id'] : ''; // L3242
```

### `spsubmitorder`

- 名称/别名：`spsubmitorder`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:107`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagSpsubmitorder.php`。
- 注册属性：`row`、`loop`、`id`、`key`、`mod`、`empty`、`limit`
- 编译器另有读取：`name`；注册属性列表不是完整白名单。
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$name = isset($tag['name']) ? $tag['name'] : ''; // L3280
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L3281
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L3282
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L3283
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L3285
$row = !empty($tag['row']) ? intval($tag['row']) : 0; // L3287
$limit = !empty($tag['limit']) ? $tag['limit'] : ''; // L3288
```

### `sporderlist`

- 名称/别名：`sporderlist`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:109`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagSporderlist.php`。
- 注册属性：`row`、`loop`、`id`、`key`、`mod`、`empty`、`limit`、`pagesize`
- 编译器另有读取：`name`；注册属性列表不是完整白名单。
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$name = isset($tag['name']) ? $tag['name'] : ''; // L3325
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L3326
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L3327
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L3328
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L3330
$row = !empty($tag['row']) ? intval($tag['row']) : 0; // L3332
$limit = !empty($tag['limit']) ? $tag['limit'] : ''; // L3333
$pagesize = !empty($tag['pagesize']) && is_numeric($tag['pagesize']) ? intval($tag['pagesize']) : 10; // L3334
```

### `spaddress`

- 名称/别名：`spaddress`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:111`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagSpaddress.php`。
- 注册属性：`type`、`row`、`loop`、`id`、`key`、`mod`、`empty`、`limit`
- 编译器另有读取：`name`；注册属性列表不是完整白名单。
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$name = isset($tag['name']) ? $tag['name'] : ''; // L3486
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L3487
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L3488
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L3489
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L3491
$row = !empty($tag['row']) ? intval($tag['row']) : 0; // L3493
$limit = !empty($tag['limit']) ? $tag['limit'] : ''; // L3494
$opt = !empty($tag['type']) ? $tag['type'] : 'data'; // L3495
```

### `spordergoods`

- 名称/别名：`spordergoods`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:113`。
- 注册属性：`row`、`loop`、`id`、`key`、`mod`、`empty`、`limit`、`name`、`titlelen`
- 编译器另有读取：`offset`；注册属性列表不是完整白名单。
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$name = isset($tag['name']) ? $tag['name'] : ''; // L3405
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L3406
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L3407
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L3408
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L3410
$row = !empty($tag['row']) ? intval($tag['row']) : 0; // L3412
$titlelen = !empty($tag['titlelen']) && is_numeric($tag['titlelen']) ? intval($tag['titlelen']) : 100; // L3413
$offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0; // L3414
$row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 1000; // L3415
```

### `spstatus`

- 名称/别名：`spstatus`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`unverified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:115`。
- 注册属性：`row`、`loop`、`id`、`key`、`mod`、`empty`、`limit`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L3679
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L3680
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L3681
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L3683
$row = !empty($tag['row']) ? intval($tag['row']) : 0; // L3685
```

### `sppageorder`

- 名称/别名：`sppageorder`
- 范围：`disabled_member_commerce`；闭合：单标签；验证：`unverified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:117`。
- 注册属性：`listitem`、`listsize`、`pre_text`、`next_text`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$listitem = !empty($tag['listitem']) ? $tag['listitem'] : ''; // L3376
$listsize = !empty($tag['listsize']) ? intval($tag['listsize']) : ''; // L3377
$pre_text = !empty($tag['pre_text']) ? $tag['pre_text'] : ''; // L3378
$next_text = !empty($tag['next_text']) ? $tag['next_text'] : ''; // L3379
```

### `spsearch`

- 名称/别名：`spsearch`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:119`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagSpsearch.php`。
- 注册属性：`empty`、`id`、`mod`、`key`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L3533
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L3534
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L3535
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L3536
```

### `sppayapilist`

- 名称/别名：`sppayapilist`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:121`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagSppayapilist.php`。
- 注册属性：`id`、`key`、`mod`、`empty`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L3576
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L3577
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L3578
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L3579
```

### `screening`

- 名称/别名：`screening`
- 范围：`list_search`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:124`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagScreening.php`。
- 注册属性：`empty`、`id`、`mod`、`key`、`currentstyle`、`currentclass`、`addfields`、`addfieldids`、`alltxt`、`typeid`、`anchor`
- 官方来源：https://www.eyoucms.com/doc/label/list/7881.html；上游更新时间：2023-10-23 17:50:40。

属性默认值及优先级（源码事实）：

```php
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L3619
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L3620
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L3621
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L3622
$anchor = !empty($tag['anchor']) ? $tag['anchor'] : ''; // L3624
$currentclass = !empty($tag['currentclass']) ? $tag['currentclass'] : ''; // L3627
$currentclass = !empty($tag['currentstyle']) ? $tag['currentstyle'] : ''; // L3629
$addfields = isset($tag['addfields']) ? $tag['addfields'] : ''; // L3633
$addfieldids = isset($tag['addfieldids']) ? $tag['addfieldids'] : ''; // L3637
$alltxt = isset($tag['alltxt']) ? $tag['alltxt'] : ''; // L3641
$typeid = isset($tag['typeid']) ? $tag['typeid'] : ''; // L3645
```

### `memberlist`

- 名称/别名：`memberlist`, `userslist`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`unverified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:126`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagMemberlist.php`。
- 注册属性：`row`、`loop`、`titlelen`、`limit`、`empty`、`mod`、`id`、`key`、`orderby`、`ordermode`、`orderway`、`js`
- 官方来源：https://www.eyoucms.com/doc/label/all/29780.html；上游更新时间：2024-12-20 16:42:05。

属性默认值及优先级（源码事实）：

```php
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L3716
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L3717
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L3718
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L3720
$titlelen = !empty($tag['titlelen']) && is_numeric($tag['titlelen']) ? intval($tag['titlelen']) : 100; // L3721
$row = !empty($tag['row']) ? intval($tag['row']) : 20; // L3723
$limit = !empty($tag['limit']) ? $tag['limit'] : ''; // L3724
$orderby = !empty($tag['orderby']) ? $tag['orderby'] : 'logintime'; // L3728
$ordermode = !empty($tag['ordermode']) ? $tag['ordermode'] : (!empty($tag['orderway']) ? $tag['orderway'] : 'desc'); // L3729
$js = !empty($tag['js']) ? $tag['js'] : ''; // L3730
```

### `memberinfos`

- 名称/别名：`memberinfos`, `usersinfo`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:128`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagMemberinfos.php`。
- 注册属性：`mid`、`users_id`、`empty`、`id`、`addfields`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$users_id = !empty($tag['mid']) ? $tag['mid'] : ''; // L3784
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L3786
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L3787
$addfields = isset($tag['addfields']) ? $tag['addfields'] : ''; // L3789
```

### `diyurl`

- 名称/别名：`diyurl`
- 范围：`global_enterprise`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:130`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagDiyurl.php`。
- 注册属性：`type`、`link`、`vars`、`suffix`、`domain`、`seo_pseudo`、`seo_pseudo_format`、`seo_inlet`
- 编译器另有读取：`class`；注册属性列表不是完整白名单。
- 官方来源：https://www.eyoucms.com/doc/label/all/8391.html；上游更新时间：2023-10-23 17:15:01。
- 官方来源：https://www.eyoucms.com/doc/label/list/11823.html；上游更新时间：2023-10-23 17:35:45。

属性默认值及优先级（源码事实）：

```php
$type = isset($tag['type']) ? $tag['type'] : ''; // L3836
$link = isset($tag['link']) ? $tag['link'] : ''; // L3838
$vars = isset($tag['vars']) ? $tag['vars'] : ''; // L3839
$suffix = isset($tag['suffix']) ? $tag['suffix'] : ''; // L3840
$domain = isset($tag['domain']) ? $tag['domain'] : ''; // L3841
$seo_pseudo = isset($tag['seo_pseudo']) ? $tag['seo_pseudo'] : ''; // L3842
$seo_pseudo_format = isset($tag['seo_pseudo_format']) ? $tag['seo_pseudo_format'] : ''; // L3843
$seo_inlet = isset($tag['seo_inlet']) ? $tag['seo_inlet'] : ''; // L3844
$class = isset($tag['class']) ? $tag['class'] : 'ey_active'; // L3845
```

### `likearticle`

- 名称/别名：`likearticle`, `relevarticle`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:132`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagLikearticle.php`。
- 注册属性：`modelid`、`channelid`、`limit`、`row`、`loop`、`titlelen`、`bodylen`、`infolen`、`mytypeid`、`typeid`、`byabs`、`empty`、`mod`、`name`、`id`、`key`、`thumb`
- 官方来源：https://www.eyoucms.com/doc/label/arc/8785.html；上游更新时间：2023-10-25 16:01:12。

属性默认值及优先级（源码事实）：

```php
$modelid = isset($tag['modelid']) ? intval($tag['modelid']) : (isset($tag['channelid']) ? intval($tag['channelid']) : ''); // L3874
$mytypeid = !empty($tag['mytypeid']) ? $tag['mytypeid'] : ''; // L3877
$typeid = !empty($tag['typeid']) ? $tag['typeid'] : ''; // L3881
$name = !empty($tag['name']) ? $tag['name'] : ''; // L3885
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L3886
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L3887
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L3888
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L3890
$byabs = !empty($tag['byabs']) ? $tag['byabs'] : 0; // L3891
$titlelen = !empty($tag['titlelen']) && is_numeric($tag['titlelen']) ? intval($tag['titlelen']) : 100; // L3892
$bodylen = !empty($tag['bodylen']) && is_numeric($tag['bodylen']) ? intval($tag['bodylen']) : 160; // L3893
$bodylen = !empty($tag['infolen']) && is_numeric($tag['infolen']) ? intval($tag['infolen']) : 160; // L3895
$thumb = !empty($tag['thumb']) ? $tag['thumb'] : 'on'; // L3897
$row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 12; // L3899
$limit = !empty($tag['limit']) ? $tag['limit'] : ''; // L3900
```

### `videoplay`

- 名称/别名：`videoplay`
- 范围：`content`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:134`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagVideoplay.php`。
- 注册属性：`aid`、`empty`、`id`、`autoplay`
- 官方来源：https://www.eyoucms.com/doc/label/arc/9993.html；上游更新时间：2023-10-25 16:01:23。

属性默认值及优先级（源码事实）：

```php
$aid = !empty($tag['aid']) ? $tag['aid'] : ''; // L2802
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L2804
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L2805
$autoplay = !empty($tag['autoplay']) ? $tag['autoplay'] : 'off'; // L2807
```

官方基础示例（具体版本差异仍需测试）：

```html
{eyou:videoplay aid='文档ID' autoplay='on' id='video'}
    <video {$video.id} width="100%" controls='true' preload="auto">
        <source src="{$video.file_url}" type="video/mp4">
    </video>
    {$video.hidden}
{/eyou:videoplay}
```

### `videolist`

- 名称/别名：`videolist`
- 范围：`content`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:136`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagVideolist.php`。
- 注册属性：`aid`、`empty`、`id`、`mod`、`key`、`autoplay`、`player`
- 官方来源：https://www.eyoucms.com/doc/label/arc/9994.html；上游更新时间：2023-11-01 17:15:33。

属性默认值及优先级（源码事实）：

```php
$aid = !empty($tag['aid']) ? $tag['aid'] : ''; // L2850
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L2852
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L2853
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L2854
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L2855
$autoplay = !empty($tag['autoplay']) ? $tag['autoplay'] : ''; // L2857
$player = isset($tag['player']) ? $tag['player'] : 'default'; // L2858
```

官方基础示例（具体版本差异仍需测试）：

```html
{eyou:videolist aid='文档ID' autoplay='on' id='video'}
    <a href="javascript:void(0);" {$video.onclick}>{$video.file_title} - {$video.file_time}</a><br/>
    {$video.hidden}
{/eyou:videolist}
```

### `hotwords`

- 名称/别名：`hotwords`, `hotkeywords`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:138`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagHotwords.php`。
- 注册属性：`subday`、`num`、`id`、`key`、`mod`、`maxlength`、`empty`、`orderby`、`ordermode`、`orderway`、`screen`
- 编译器另有读取：`orderWay`；注册属性列表不是完整白名单。
- 官方来源：https://www.eyoucms.com/doc/label/all/10195.html；上游更新时间：2024-05-21 11:05:24。

属性默认值及优先级（源码事实）：

```php
$num = !empty($tag['num']) && is_numeric($tag['num']) ? $tag['num'] : '6'; // L3962
$subday = !empty($tag['subday']) && is_numeric($tag['subday']) ? $tag['subday'] : '365'; // L3963
$maxlength = !empty($tag['maxlength']) && is_numeric($tag['maxlength']) ? $tag['maxlength'] : '20'; // L3964
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L3965
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L3966
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L3967
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L3969
$orderby = isset($tag['orderby']) ? $tag['orderby'] : ''; // L3970
$ordermode = !empty($tag['orderway']) ? $tag['orderway'] : $ordermode; // L3978
$screen = isset($tag['screen']) ? $tag['screen'] : ''; // L3981
```

### `weapptaglib`

- 名称/别名：`weapptaglib`
- 范围：`extension_contract`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:140`。
- 注册属性：`name`、`id`、`offset`、`length`、`key`、`mod`、`limit`、`row`、`loop`
- 编译器另有读取：`empty`；注册属性列表不是完整白名单。
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$name = !empty($tag['name']) ? ":weapptaglib".$tag['name'] : $tag['name']; // L4027
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L4028
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L4029
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L4031
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L4032
$offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0; // L4033
$length = !empty($tag['length']) && is_numeric($tag['length']) ? intval($tag['length']) : 'null'; // L4034
$length = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 'null'; // L4037
```

### `asklist`

- 名称/别名：`asklist`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:142`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagAsklist.php`。
- 注册属性：`id`、`key`、`mod`、`titlelen`、`limit`、`row`、`loop`、`orderby`、`ordermode`、`orderway`
- 编译器另有读取：`empty`；注册属性列表不是完整白名单。
- 官方来源：https://www.eyoucms.com/doc/label/all/11336.html；上游更新时间：2023-10-23 17:08:28。

属性默认值及优先级（源码事实）：

```php
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L4315
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L4316
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L4318
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L4319
$titlelen = !empty($tag['titlelen']) && is_numeric($tag['titlelen']) ? intval($tag['titlelen']) : 100; // L4320
$orderby = isset($tag['orderby']) ? $tag['orderby'] : ''; // L4321
$ordermode = !empty($tag['ordermode']) ? $tag['ordermode'] : (!empty($tag['orderway']) ? $tag['orderway'] : 'desc'); // L4322
$limit = !empty($tag['limit']) ? trim($tag['limit']) : 20; // L4323
$limit = !empty($tag['row']) ? intval($tag['row']) : $limit; // L4326
```

官方基础示例（具体版本差异仍需测试）：

```html
{eyou:asklist id="field" loop='20'}
    <li>
        <div class="date">
            <span class="dateline" title="天">{$field.add_time|MyDate='d',###}</span>
            <em title="年-月">{$field.add_time|MyDate='Y-m',###}</em>
        </div>
        <div class="txt">
            <a href="{$field.ask_url}" title="{$field.ask_title}">
                <!-- 是否显示推荐字样 -->
                {eyou:eq name="$field.is_recom" value="1"}
                     <span style="color: red;">[推荐]</span>
                {/eyou:eq}
                 <!-- 问答标题 -->
                {$field.ask_title|html_msubstr=###, 0, 30, true}
            </a>
            <!-- 问答内容 -->
            {$field.content|html_msubstr=###, 0, 35, true}
        </div>
        <div class="txt">
            <span>查看人数：{$field.click}</span>
             | <span>回复人数：{$field.replies}</span>
             | <span style="color: red;" title="所属分类"><a href="{$field.type_url}">{$field.type_name}</a></span>
             | <span title="完整发布时间显示">{$field.add_time|MyDate='Y-m-d H:i:s',###}</span>
        </div>
    </li>
{/eyou:asklist}
```

### `specnodelist`

- 名称/别名：`specnodelist`
- 范围：`content`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:144`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagSpecnodelist.php`。
- 注册属性：`id`、`key`、`mod`、`empty`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L4088
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L4089
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L4090
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L4091
```

### `specnode`

- 名称/别名：`specnode`, `specialnode`
- 范围：`content`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:146`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagSpecnode.php`。
- 注册属性：`aid`、`code`、`title`、`isauto`、`aidlist`、`keyword`、`typeid`、`row`、`loop`、`limit`、`bodylen`、`infolen`、`titlelen`、`name`、`empty`、`mod`、`id`、`key`、`thumb`
- 官方来源：https://www.eyoucms.com/doc/label/arc/10999.html；上游更新时间：2024-06-24 17:23:03。

属性默认值及优先级（源码事实）：

```php
$aid = !empty($tag['aid']) ? $tag['aid'] : '0'; // L4124
$title = !empty($tag['title']) ? $tag['title'] : ''; // L4127
$code = !empty($tag['code']) ? $tag['code'] : ''; // L4130
$typeid = !empty($tag['typeid']) ? $tag['typeid'] : ''; // L4133
$keyword = !empty($tag['keyword']) ? $tag['keyword'] : ''; // L4136
$aidlist = !empty($tag['aidlist']) ? $tag['aidlist'] : ''; // L4139
$isauto = !empty($tag['isauto']) && is_numeric($tag['isauto']) ? $tag['isauto'] : '0'; // L4140
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L4141
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L4142
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L4143
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L4145
$thumb = !empty($tag['thumb']) ? $tag['thumb'] : 'on'; // L4146
$titlelen = !empty($tag['titlelen']) && is_numeric($tag['titlelen']) ? intval($tag['titlelen']) : 100; // L4147
$bodylen = !empty($tag['bodylen']) && is_numeric($tag['bodylen']) ? intval($tag['bodylen']) : 160; // L4148
$bodylen = !empty($tag['infolen']) && is_numeric($tag['infolen']) ? intval($tag['infolen']) : 160; // L4150
$limit = !empty($tag['limit']) ? trim($tag['limit']) : 10; // L4153
$limit = !empty($tag['row']) ? intval($tag['row']) : $limit; // L4159
```

官方基础示例（具体版本差异仍需测试）：

```html
{eyou:specialnode code='节点标识' id='field'}
<a href='{$field.arcurl}'>{$field.title}</a>
{/eyou:specialnode}
```

### `pagespecnode`

- 名称/别名：`pagespecnode`, `pagespecialnode`
- 范围：`content`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:147`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagPagespecnode.php`。
- 注册属性：`listitem`、`listsize`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$listitem = !empty($tag['listitem']) ? $tag['listitem'] : ''; // L4202
$listsize = !empty($tag['listsize']) ? intval($tag['listsize']) : ''; // L4203
```

### `collect`

- 名称/别名：`collect`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`unverified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:149`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagCollect.php`。
- 注册属性：`aid`、`collect`、`cancel`、`id`、`class`
- 官方来源：https://www.eyoucms.com/doc/label/arc/11113.html；上游更新时间：2023-10-24 09:24:41。

属性默认值及优先级（源码事实）：

```php
$aid_tmp = isset($tag['aid']) ? $tag['aid'] : 0; // L4226
$collect = isset($tag['collect']) ? $tag['collect'] : '已收藏'; // L4229
$cancel = isset($tag['cancel']) ? $tag['cancel'] : '加入收藏'; // L4232
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L4235
$class = isset($tag['class']) ? $tag['class'] : 'off'; // L4236
```

### `notice`

- 名称/别名：`notice`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`unverified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:151`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagNotice.php`。
- 注册属性：`id`
- 官方来源：https://www.eyoucms.com/doc/label/all/11112.html；上游更新时间：2023-10-23 17:10:26。

属性默认值及优先级（源码事实）：

```php
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L4277
```

### `comment`

- 名称/别名：`comment`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`unverified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:154`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagComment.php`。
- 注册属性：`id`、` aid`
- 编译器另有读取：`aid`；注册属性列表不是完整白名单。
- 官方来源：https://www.eyoucms.com/doc/label/arc/11191.html；上游更新时间：2025-03-21 16:39:43。

属性默认值及优先级（源码事实）：

```php
$aid = !empty($tag['aid']) ? $tag['aid'] : 0; // L4370
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L4372
```

官方基础示例（具体版本差异仍需测试）：

```html
<div class="content overflow-x">
    <div class="layout">
    <!-- 评价展示区域：请参考评价标签手册，下载商品评价模板文件拷贝到模板目录 mobile/system 里,具体教程链接  -->
    {eyou:comment aid='$eyou.field.aid' id='c_field'}
        <div class="evalute" {$c_field.CommentID}></div>
        {$c_field.hidden}
    {/eyou:comment}
    <!-- END -->
    </div>
</div>
```

### `commentlist`

- 名称/别名：`commentlist`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`unverified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:156`。
- 注册属性：`name`、` id`、` offset`、` length`、` key`、` mod`、` limit`、` row`、`loop`
- 编译器另有读取：`empty`、`id`、`key`、`length`、`limit`、`mod`、`offset`、`row`；注册属性列表不是完整白名单。
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L4412
$empty = isset($tag['empty']) ? $tag['empty'] : 'false'; // L4413
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L4415
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L4416
$offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0; // L4417
$length = !empty($tag['length']) && is_numeric($tag['length']) ? intval($tag['length']) : 'null'; // L4418
$length = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 'null'; // L4421
```

### `citysite`

- 名称/别名：`citysite`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:158`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagCitysite.php`。
- 注册属性：`siteid`、`nositeid`、`pid`、`type`、`row`、`loop`、`currentstyle`、`currentclass`、`id`、`name`、`key`、`empty`、`mod`、`titlelen`、`offset`、`limit`
- 官方来源：https://www.eyoucms.com/doc/label/all/11842.html；上游更新时间：2025-10-14 11:07:37。

属性默认值及优先级（源码事实）：

```php
$siteid = !empty($tag['siteid']) ? $tag['siteid'] : ''; // L4475
$nositeid = !empty($tag['nositeid']) ? $tag['nositeid'] : ''; // L4478
$name = !empty($tag['name']) ? $tag['name'] : ''; // L4481
$type = !empty($tag['type']) ? $tag['type'] : 'son'; // L4482
$currentclass = !empty($tag['currentclass']) ? $tag['currentclass'] : ''; // L4483
$currentclass = !empty($tag['currentstyle']) ? $tag['currentstyle'] : ''; // L4485
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L4487
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L4488
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L4489
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L4491
$titlelen = !empty($tag['titlelen']) && is_numeric($tag['titlelen']) ? intval($tag['titlelen']) : 100; // L4492
$offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0; // L4493
$row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 100; // L4495
```

### `articlepay`

- 名称/别名：`articlepay`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`unverified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:160`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagArticlepay.php`。
- 注册属性：`id`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L4555
```

### `navigation`

- 名称/别名：`navigation`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:162`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagNavigation.php`。
- 注册属性：`position_id`、`row`、`loop`、`id`、`name`、`key`、`empty`、`mod`、`titlelen`、`orderby`、`ordermode`、`orderway`、`alltxt`、`currentstyle`、`currentclass`
- 编译器另有读取：`nav_id`；注册属性列表不是完整白名单。
- 官方来源：https://www.eyoucms.com/doc/label/all/11849.html；上游更新时间：2025-03-21 16:32:01。

属性默认值及优先级（源码事实）：

```php
$name = isset($tag['name']) ? $tag['name'] : ''; // L4603
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L4604
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L4605
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L4606
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L4608
$row = !empty($tag['row']) ? intval($tag['row']) : 0; // L4610
$orderby = isset($tag['orderby']) ? $tag['orderby'] : 'sort_order'; // L4611
$ordermode = !empty($tag['ordermode']) ? $tag['ordermode'] : (!empty($tag['orderway']) ? $tag['orderway'] : 'asc'); // L4612
$titlelen = !empty($tag['titlelen']) && is_numeric($tag['titlelen']) ? intval($tag['titlelen']) : 100; // L4613
$position_id = !empty($tag['position_id']) ? $tag['position_id'] : ''; // L4614
$nav_id = !empty($tag['nav_id']) ? $tag['nav_id'] : ''; // L4616
$currentclass = !empty($tag['currentclass']) ? $tag['currentclass'] : ''; // L4618
$currentclass = !empty($tag['currentstyle']) ? $tag['currentstyle'] : ''; // L4620
```

### `downloadpay`

- 名称/别名：`downloadpay`
- 范围：`disabled_member_commerce`；闭合：成对；验证：`unverified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:164`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagDownloadpay.php`。
- 注册属性：`id`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L4574
```

### `faq`

- 名称/别名：`faq`
- 范围：`global_enterprise`；闭合：成对；验证：`partially_verified`。
- 编译源码：`core/library/think/template/taglib/Eyou.php:166`。
- 数据/渲染源码：`core/library/think/template/taglib/eyou/TagFaq.php`。
- 注册属性：`group_id`、`row`、`loop`、`orderby`、`where`、`id`、`empty`、`key`、`mod`、`currentstyle`、`currentclass`
- 官方目录未提供独立主说明页；按源码登记，不能据此视为不支持。

属性默认值及优先级（源码事实）：

```php
$group_id = !empty($tag['group_id']) ? $tag['group_id'] : '0'; // L4736
$id = isset($tag['id']) ? $tag['id'] : 'field'; // L4738
$orderby = !empty($tag['orderby']) ? $tag['orderby'] : ''; // L4739
$row = !empty($tag['row']) ? $tag['row'] : 10; // L4741
$where = !empty($tag['where']) ? $tag['where'] : ''; // L4742
$key = !empty($tag['key']) ? $tag['key'] : 'key'; // L4743
$key = !empty($tag['key']) ? $tag['key'] : 'i'; // L4744
$empty = isset($tag['empty']) ? $tag['empty'] : ''; // L4745
$mod = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2'; // L4747
$currentclass = !empty($tag['currentclass']) ? $tag['currentclass'] : ''; // L4748
$currentclass = !empty($tag['currentstyle']) ? $tag['currentstyle'] : ''; // L4750
```

### `include`

- 名称/别名：`include`
- 范围：`template_syntax`；闭合：单标签；验证：`partially_verified`。
- 编译源码：`core/library/think/Template.php:None`。
- 注册属性：`file`
- 官方来源：https://www.eyoucms.com/doc/label/all/495.html；上游更新时间：2023-10-23 17:23:09。

## 官方文档全索引

### artlist 文章列表

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/491.html；更新时间：2025-08-14 16:02:04。
- 示例涉及标签：`artlist`, `eq`, `tag`
- 示例观察到的属性：`addfields`, `aid`, `arcrank`, `empty`, `flag`, `getall`, `id`, `idlist`, `idrange`, `infolen`, `keyword`, `limit`, `loop`, `mod`, `modelid`, `name`, `noflag`, `notypeid`, `orderby`, `ordermode`, `sort`, `tagid`, `thumb`, `titlelen`, `typeid`, `value`
- 输出变量/字段：`$field.arcurl`, `$field.level_name`, `$field.level_value`, `$field.title`, `$field1.link`, `$field1.tag`, `$field1.title`, `$field2.arcurl`, `$field2.title`

### models 栏目列表

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/492.html；更新时间：2023-10-23 17:27:14。
- 示例涉及标签：`eq`, `models`, `notempty`
- 示例观察到的属性：`currentclass`, `empty`, `id`, `limit`, `loop`, `mod`, `name`, `offset`, `titlelen`, `type`, `typeid`, `value`
- 输出变量/字段：`$field.currentclass`, `$field.extends`, `$field.title`, `$field.typename`, `$field.typeurl`, `$field1.currentclass`, `$field1.title`, `$field1.typename`, `$field1.typeurl`, `$field2.typename`, `$field2.typeurl`, `$field3.typename`, `$field3.typeurl`

### modelsartlist 频道循环

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/493.html；更新时间：2023-10-23 17:25:52。
- 示例涉及标签：`artlist`, `field`, `models`, `modelsartlist`, `type`
- 示例观察到的属性：`currentclass`, `empty`, `id`, `infolen`, `limit`, `loop`, `mod`, `name`, `titlelen`, `type`, `typeid`
- 输出变量/字段：`$field.arcurl`, `$field.currentclass`, `$field.litpic`, `$field.title`, `$field.typename`, `$field.typeurl`

### type 指定栏目

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/494.html；更新时间：2023-10-23 17:23:32。
- 示例涉及标签：`type`
- 示例观察到的属性：`addfields`, `empty`, `id`, `type`, `typeid`
- 输出变量/字段：`$field.content`, `$field.typename`, `$field.typeurl`, `$field1.typename`

### include 引用模板

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/495.html；更新时间：2023-10-23 17:23:09。
- 示例涉及标签：`include`
- 示例观察到的属性：`file`
- 输出变量/字段：未直接出现。

### arcview 单条文档

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/498.html；更新时间：2023-10-23 17:22:38。
- 示例涉及标签：`arcview`
- 示例观察到的属性：`addfields`, `aid`, `id`, `joinaid`
- 输出变量/字段：`$field.arcurl`, `$field.content`, `$field.title`, `$field1.title`, `$view.test1`, `$view.test2`

### position 面包屑

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/500.html；更新时间：2023-10-23 17:22:20。
- 示例涉及标签：`position`
- 示例观察到的属性：`style`
- 输出变量/字段：未直接出现。

### assign 定义变量

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/503.html；更新时间：2023-10-23 17:21:54。
- 示例涉及标签：`assign`, `channel`, `type`
- 示例观察到的属性：`name`, `type`, `typeid`, `value`
- 输出变量/字段：`$field.typename`, `$field.typeurl`

### empty 为空判断

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/504.html；更新时间：2025-07-07 10:05:13。
- 示例涉及标签：`else`, `empty`
- 示例观察到的属性：`name`
- 输出变量/字段：`$eyou.field.seo_title`, `$eyou.field.title`

### notempty 不为空判断

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/505.html；更新时间：2025-07-07 10:05:40。
- 示例涉及标签：`else`, `notempty`
- 示例观察到的属性：`name`
- 输出变量/字段：`$eyou.field.seo_title`, `$eyou.field.title`

### foreach 循环输出

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/506.html；更新时间：2023-10-23 17:32:50。
- 示例涉及标签：`channel`, `foreach`, `guestbookform`
- 示例观察到的属性：`item`, `name`, `type`, `typeid`
- 输出变量/字段：`$field.action`, `$field.attr_5`, `$field.attr_6`, `$field.attr_7`, `$field.hidden`, `$field.itemname_5`, `$field.itemname_6`, `$field.title`, `$field1.title`, `$field1.typename`, `$field1.typeurl`, `$field1.value`

### volist 数据循环

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/507.html；更新时间：2023-10-23 17:30:05。
- 示例涉及标签：`channel`, `guestbookform`, `volist`
- 示例观察到的属性：`id`, `name`, `type`, `typeid`
- 输出变量/字段：`$field.action`, `$field.attr_5`, `$field.attr_6`, `$field.attr_7`, `$field.hidden`, `$field.itemname_5`, `$field.itemname_6`, `$field.title`, `$field1.title`, `$field1.typename`, `$field1.typeurl`, `$field1.value`

### if 条件判断

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/508.html；更新时间：2023-10-23 17:30:30。
- 示例涉及标签：`else`, `elseif`, `if`
- 示例观察到的属性：`condition`
- 输出变量/字段：未直接出现。

### switch 条件判断

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/509.html；更新时间：2023-10-23 17:32:20。
- 示例涉及标签：`case`, `default`, `switch`
- 示例观察到的属性：`condition`, `name`, `value`
- 输出变量/字段：未直接出现。

### compare 变量比较

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/510.html；更新时间：2023-10-23 17:31:29。
- 示例涉及标签：`egt`, `elt`, `eq`, `gt`, `heq`, `lt`, `neq`, `nheq`
- 示例观察到的属性：`name`, `value`
- 输出变量/字段：`$eyou.field.typename`

### adv 广告列表

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/511.html；更新时间：2023-10-23 17:33:02。
- 示例涉及标签：`adv`, `eq`, `list`
- 示例观察到的属性：`currentclass`, `empty`, `id`, `loop`, `mod`, `name`, `orderby`, `pagesize`, `pid`, `value`, `where`
- 输出变量/字段：`$field.arcurl`, `$field.currentclass`, `$field.links`, `$field.litpic`, `$field.target`, `$field.title`, `$field1.title`, `$field2.intro`, `$field2.links`, `$field2.litpic`, `$field2.title`

### global 全局变量

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/513.html；更新时间：2023-10-23 17:29:18。
- 示例涉及标签：`global`
- 示例观察到的属性：`name`
- 输出变量/字段：`$eyou.global.web_description`, `$eyou.global.web_title`

### field 字段值

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/519.html；更新时间：2023-10-23 17:28:50。
- 示例涉及标签：`arclist`, `channelartlist`, `field`
- 示例观察到的属性：`limit`, `name`, `row`, `titlelen`, `type`, `typeid`
- 输出变量/字段：`$field.arcurl`, `$field.title`

### tags 标签调用

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/520.html；更新时间：2022-08-04 17:31:56。
- 示例涉及标签：`tags`
- 示例观察到的属性：`aid`, `empty`, `getall`, `id`, `loop`, `mod`, `name`, `sort`, `type`, `typeid`
- 输出变量/字段：`$field.link`, `$field.tag`, `$field.title`, `$field.total`, `$field1.title`, `$field2.link`, `$field2.tag`

### searchform 搜索标签

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/521.html；更新时间：2024-08-14 16:08:22。
- 示例涉及标签：`searchform`
- 示例观察到的属性：`modelid`, `type`, `typeid`
- 输出变量/字段：`$field.action`, `$field.hidden`

### 常用函数

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/546.html；更新时间：2026-01-05 14:58:35。
- 示例涉及标签：`arclist`, `type`
- 示例观察到的属性：`limit`, `type`, `typeid`
- 输出变量/字段：`$eyou.field`, `$eyou.field.typeid`, `$eyou.field.update_time`, `$field.content`, `$field.id`, `$field.title`, `$field.typename`, `$field.update_time`

### links 友情链接

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/578.html；更新时间：2023-10-23 17:20:48。
- 示例涉及标签：`links`
- 示例观察到的属性：`empty`, `groupid`, `id`, `loop`, `mod`, `titlelen`, `type`
- 输出变量/字段：`$field.logo`, `$field.nofollow`, `$field.target`, `$field.title`, `$field.url`, `$field1.title`, `$field2.title`, `$field2.url`

### SQL 数据查询

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/579.html；更新时间：2023-10-23 17:20:27。
- 示例涉及标签：`sql`
- 示例观察到的属性：`cachetime`, `empty`, `id`, `mod`, `sql`
- 输出变量/字段：`$field.title`, `$field.typename`, `$field1.title`, `$field2.typename`

### weapp 应用插件

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/580.html；更新时间：2023-10-23 17:19:45。
- 示例涉及标签：`weapp`
- 示例观察到的属性：`type`
- 输出变量/字段：未直接出现。

### range 范围判断

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/581.html；更新时间：2023-10-23 17:19:28。
- 示例涉及标签：`else`, `range`
- 示例观察到的属性：`name`, `type`, `value`
- 输出变量/字段：未直接出现。

### for 数据循环

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/582.html；更新时间：2023-10-23 17:19:12。
- 示例涉及标签：`for`
- 示例观察到的属性：`comparison`, `end`, `name`, `start`, `step`
- 输出变量/字段：未直接出现。

### ad 单条广告

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/836.html；更新时间：2023-10-23 17:18:30。
- 示例涉及标签：`ad`
- 示例观察到的属性：`aid`, `id`
- 输出变量/字段：`$field.links`, `$field.litpic`, `$field.target`, `$field.title`, `$field1.title`

### attribute 栏目属性

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/6394.html；更新时间：2023-10-23 17:30:16。
- 示例涉及标签：`attribute`
- 示例观察到的属性：`aid`, `empty`, `id`, `mod`, `type`
- 输出变量/字段：`$attr.name`, `$attr.value`, `$field.title`, `$field1.title`

### user 会员信息

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/7171.html；更新时间：2025-02-12 12:57:25。
- 示例涉及标签：`diyurl`, `user`
- 示例观察到的属性：`id`, `link`, `type`
- 输出变量/字段：`$field.cartid`, `$field.head_pic`, `$field.hidden`, `$field.htmlid`, `$field.id`, `$field.last_ip`, `$field.loginurl`, `$field.reg_time`, `$field.regurl`, `$field.url`, `$field.username`, `$field1.url`, `$users.cart_num`, `$users.email`, `$users.head_pic`, `$users.last_ip`, `$users.level_name`, `$users.level_value`, `$users.mobile`, `$users.nickname`, `$users.scores`, `$users.username`, `$users.users_money`

### diyurl 内链调用

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/8391.html；更新时间：2023-10-23 17:15:01。
- 示例涉及标签：`diyurl`
- 示例观察到的属性：`type`
- 输出变量/字段：未直接出现。

### php标签

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/8905.html；更新时间：2025-03-21 16:49:55。
- 示例涉及标签：`php`
- 示例观察到的属性：无。
- 输出变量/字段：未直接出现。

### hotkeywords 热门搜索

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/10195.html；更新时间：2024-05-21 11:05:24。
- 示例涉及标签：`hotkeywords`
- 示例观察到的属性：`empty`, `id`, `maxlength`, `mod`, `num`, `orderby`, `screen`, `subday`
- 输出变量/字段：`$field.url`, `$field.word`, `$field1.word`

### notice 站内信

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/11112.html；更新时间：2023-10-23 17:10:26。
- 示例涉及标签：`notice`
- 示例观察到的属性：`id`
- 输出变量/字段：`$field.hidden`, `$field.id`, `$field.url`, `$field1.url`

### asklist 问答列表

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/11336.html；更新时间：2023-10-23 17:08:28。
- 示例涉及标签：`asklist`, `eq`
- 示例观察到的属性：`empty`, `id`, `limit`, `loop`, `mod`, `name`, `orderby`, `ordermode`, `row`, `titlelen`, `value`
- 输出变量/字段：`$field.add_time`, `$field.ask_title`, `$field.ask_url`, `$field.click`, `$field.content`, `$field.money`, `$field.replies`, `$field.title`, `$field.type_name`, `$field.type_url`, `$field.users.head_pic`, `$field.users.level_name`, `$field.users.nickname`, `$field.users.username`, `$field1.title`

### citysite 城市站点

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/11842.html；更新时间：2025-10-14 11:07:37。
- 示例涉及标签：`channel`, `citysite`, `eq`, `global`, `list`, `notempty`
- 示例观察到的属性：`currentclass`, `empty`, `id`, `limit`, `loop`, `mod`, `name`, `notypeid`, `offset`, `siteid`, `titlelen`, `type`, `value`
- 输出变量/字段：`$eyou.site.name`, `$eyou.site.seo_description`, `$eyou.site.seo_keywords`, `$eyou.site.seo_title`, `$field.arcurl`, `$field.area_name`, `$field.city_name`, `$field.currentclass`, `$field.name`, `$field.province_name`, `$field.siteurl`, `$field.title`, `$field1.currentclass`, `$field1.name`, `$field1.siteurl`, `$field2.name`, `$field2.siteurl`, `$field3.name`, `$field3.siteurl`

### navigation 导航菜单

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/11849.html；更新时间：2025-03-21 16:32:01。
- 示例涉及标签：`navigation`, `notempty`
- 示例观察到的属性：`currentclass`, `empty`, `id`, `name`, `nav_id`, `position_id`
- 输出变量/字段：`$field.currentclass`, `$field.nav_name`, `$field.nav_url`, `$field.nofollow`, `$field.target`, `$field.title`, `$field1.currentclass`, `$field1.nav_name`, `$field1.nav_url`, `$field1.nofollow`, `$field1.target`, `$field1.title`, `$field2.currentclass`, `$field2.nav_name`, `$field2.nav_url`, `$field2.nofollow`, `$field2.target`, `$field3.currentclass`, `$field3.nav_name`, `$field3.nav_url`, `$field3.nofollow`, `$field3.target`

### static 静态资源文件引入

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/11919.html；更新时间：2025-07-07 17:15:45。
- 示例涉及标签：`static`
- 示例观察到的属性：`file`, `href`
- 输出变量/字段：未直接出现。

### 会员信息(memberlist)

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/29780.html；更新时间：2024-12-20 16:42:05。
- 示例涉及标签：`memberlist`
- 示例观察到的属性：`id`, `orderby`, `row`
- 输出变量/字段：`$field.email`, `$field.head_pic`, `$field.hidden`, `$field.level`, `$field.level_maturity_days`, `$field.mobile`, `$field.nickname`, `$field.para_3`, `$field.para_4`, `$field.para_5`, `$field.para_6`, `$field.scores`, `$field.txtid`, `$field.username`, `$field.users_id`, `$field1.users_id`

### 会员注销标签使用方法

- 分类：全局；来源：https://www.eyoucms.com/doc/label/all/30629.html；更新时间：2023-11-15 15:56:17。
- 示例涉及标签：`volist`
- 示例观察到的属性：`id`, `name`
- 输出变量/字段：`$field.func`, `$field.hidden`, `$field.text`

### list 文档列表

- 分类：列表；来源：https://www.eyoucms.com/doc/label/list/496.html；更新时间：2023-10-23 18:12:51。
- 示例涉及标签：`eq`, `list`
- 示例观察到的属性：`addfields`, `arcrank`, `empty`, `flag`, `id`, `idlist`, `idrange`, `infolen`, `limit`, `loop`, `mod`, `modelid`, `name`, `noflag`, `notypeid`, `orderby`, `ordermode`, `thumb`, `titlelen`, `typeid`, `value`
- 输出变量/字段：`$eyou.field.update_time`, `$field.arcurl`, `$field.level_name`, `$field.level_value`, `$field.title`, `$field1.title`, `$field2.arcurl`, `$field2.title`

### pagelist 列表分页

- 分类：列表；来源：https://www.eyoucms.com/doc/label/list/497.html；更新时间：2023-10-23 18:04:30。
- 示例涉及标签：`list`, `pagelist`
- 示例观察到的属性：`infolen`, `listitem`, `listsize`, `pagesize`, `titlelen`
- 输出变量/字段：`$field.arcurl`, `$field.title`

### artpagelist 瀑布流

- 分类：列表；来源：https://www.eyoucms.com/doc/label/list/6279.html；更新时间：2023-12-27 10:22:02。
- 示例涉及标签：`artlist`, `artpagelist`
- 示例观察到的属性：`id`, `loading`, `pagesize`, `row`, `tagid`, `tips`, `typeid`
- 输出变量/字段：`$field.arcurl`, `$field.onclick`, `$field.title`, `$field1.title`

### screening 文档筛选

- 分类：列表；来源：https://www.eyoucms.com/doc/label/list/7881.html；更新时间：2023-10-23 17:50:40。
- 示例涉及标签：`screening`, `volist`
- 示例观察到的属性：`addfields`, `alltxt`, `currentclass`, `currentstyle`, `empty`, `id`, `name`, `typeid`
- 输出变量/字段：`$field.hidden`, `$field.resetUrl`, `$field.title`, `$field1.title`, `$three.currentstyle`, `$three.name`, `$three.onClick`, `$two.currentstyle`, `$two.name`, `$two.onClick`, `$val.currentclass`, `$val.currentstyle`, `$val.name`, `$val.onClick`, `$vo.title`

### diyurl 列表排序

- 分类：列表；来源：https://www.eyoucms.com/doc/label/list/11823.html；更新时间：2023-10-23 17:35:45。
- 示例涉及标签：`diyurl`
- 示例观察到的属性：`class`, `type`
- 输出变量/字段：未直接出现。

### formreply 自由表单回复列表

- 分类：列表；来源：https://www.eyoucms.com/doc/label/list/31220.html；更新时间：2026-03-23 17:00:23。
- 示例涉及标签：`formreply`
- 示例观察到的属性：`empty`, `id`, `ordermode`, `pagesize`, `typeid`
- 输出变量/字段：`$field.add_time`, `$field.attr`, `$field.head_pic`, `$field.hidden`, `$field.nickname`, `$field.onclick`, `$field1.head_pic`

### arcclick 点击数

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/499.html；更新时间：2023-10-24 11:05:17。
- 示例涉及标签：`arcclick`
- 示例观察到的属性：无。
- 输出变量/字段：未直接出现。

### guestbookform 栏目留言

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/502.html；更新时间：2026-04-24 14:25:09。
- 示例涉及标签：`guestbookform`, `volist`
- 示例观察到的属性：`empty`, `id`, `name`, `type`, `typeid`
- 输出变量/字段：`$field.action`, `$field.attr_1`, `$field.attr_16`, `$field.attr_2`, `$field.attr_3`, `$field.attr_5`, `$field.attr_6`, `$field.attr_7`, `$field.formhidden`, `$field.hidden`, `$field.itemname_1`, `$field.itemname_2`, `$field.itemname_3`, `$field.itemname_5`, `$field.itemname_6`, `$field.submit`, `$field.title`, `$field1.title`, `$vo.value`

### beafter 上下篇

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/518.html；更新时间：2023-10-25 16:00:52。
- 示例涉及标签：`beafter`, `else`
- 示例观察到的属性：`get`, `id`, `titlelen`
- 输出变量/字段：`$field.arcurl`, `$field.title`, `$field1.arcurl`, `$field1.title`

### downcount 下载次数

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/8392.html；更新时间：2023-10-24 10:57:15。
- 示例涉及标签：`downcount`
- 示例观察到的属性：无。
- 输出变量/字段：未直接出现。

### relevarticle 相关文档

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/8785.html；更新时间：2023-10-25 16:01:12。
- 示例涉及标签：`relevarticle`
- 示例观察到的属性：`empty`, `id`, `infolen`, `limit`, `mod`, `modelid`, `thumb`, `titlelen`, `typeid`
- 输出变量/字段：`$field.arcurl`, `$field.title`, `$field1.title`

### 扩展标签

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/8812.html；更新时间：2023-10-24 10:55:01。
- 示例涉及标签：`volist`
- 示例观察到的属性：`id`, `name`
- 输出变量/字段：`$field.extract_code`, `$field.file_name`, `$field.file_size`, `$field.file_url`, `$field.image_url`, `$field.intro`, `$field.server_name`, `$field.title`

### videoplay 视频播放

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/9993.html；更新时间：2023-10-25 16:01:23。
- 示例涉及标签：`memberinfos`, `videolist`, `videoplay`
- 示例观察到的属性：`aid`, `autoplay`, `empty`, `id`, `mid`
- 输出变量/字段：`$eyou.field.courseware`, `$field.title`, `$field1.title`, `$users.head_pic`, `$users.hidden`, `$users.nickname`, `$video.file_time`, `$video.file_title`, `$video.file_url`, `$video.hidden`, `$video.id`, `$video.onclick`

### videolist 视频列表

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/9994.html；更新时间：2023-11-01 17:15:33。
- 示例涉及标签：`memberinfos`, `videolist`, `videoplay`
- 示例观察到的属性：`aid`, `autoplay`, `empty`, `id`, `mid`, `mod`
- 输出变量/字段：`$eyou.field.courseware`, `$field.title`, `$field1.title`, `$users.head_pic`, `$users.hidden`, `$users.nickname`, `$video.file_time`, `$video.file_title`, `$video.file_url`, `$video.hidden`, `$video.id`, `$video.onclick`

### memberinfos 文档关联的会员信息

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/10050.html；更新时间：2024-12-20 16:42:07。
- 示例涉及标签：`memberinfos`
- 示例观察到的属性：`addfields`, `empty`, `id`, `mid`
- 输出变量/字段：`$field.nickname`, `$users.head_pic`, `$users.hidden`, `$users.level_name`, `$users.nickname`, `$users.para_这里请查阅后台会员属性的ID`

### attribute 商品参数

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/10108.html；更新时间：2025-03-21 16:46:06。
- 示例涉及标签：`arclist`, `attribute`
- 示例观察到的属性：`aid`, `attrid`, `empty`, `id`, `mod`, `row`, `type`
- 输出变量/字段：`$attr.name`, `$attr.value`, `$field.title`, `$field1.title`

### specialnode 专题文档

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/10999.html；更新时间：2024-06-24 17:23:03。
- 示例涉及标签：`notempty`, `specialnode`, `specnode`, `specnodelist`
- 示例观察到的属性：`aid`, `code`, `empty`, `id`, `infolen`, `isauto`, `loop`, `mod`, `name`, `thumb`, `titlelen`, `typeid`
- 输出变量/字段：`$field.add_time`, `$field.arcurl`, `$field.click`, `$field.litpic`, `$field.seo_description`, `$field.title`, `$field.typename`, `$field.typeurl`, `$field1.title`, `$list.title`

### collect 文档收藏

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/11113.html；更新时间：2023-10-24 09:24:41。
- 示例涉及标签：`collect`
- 示例观察到的属性：`aid`, `cancel`, `class`, `collect`, `id`, `src`
- 输出变量/字段：`$collect.cancel`, `$collect.hidden`, `$collect.numId`, `$collect.onclick`, `$collect1.numId`

### collectnum 收藏数

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/11186.html；更新时间：2023-10-24 09:22:31。
- 示例涉及标签：`collectnum`
- 示例观察到的属性：无。
- 输出变量/字段：未直接出现。

### comment 商品评价

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/11191.html；更新时间：2025-03-21 16:39:43。
- 示例涉及标签：`comment`
- 示例观察到的属性：`aid`, `id`
- 输出变量/字段：`$c_field.CommentID`, `$c_field.hidden`

### articlepay 文章付费

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/11810.html；更新时间：2025-04-22 11:29:16。
- 示例涉及标签：`articlepay`, `else`, `elseif`, `eq`, `freebuynum`, `if`
- 示例观察到的属性：`aid`, `condition`, `id`, `name`, `value`
- 输出变量/字段：`$c_field.hidden`, `$eyou.field.arc_level_id`, `$eyou.field.content`, `$eyou.field.users_price`, `$eyou.field.users_score`, `$field.content`, `$field.contentId`, `$field.displayId`, `$field.hidden`, `$field.onBuyVipClick`, `$field.onclick`, `$field.vipDisplayId`, `$usersConfig.score_name`

### downloadpay 下载付费

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/28561.html；更新时间：2024-01-31 14:31:04。
- 示例涉及标签：`downloadpay`, `else`, `elseif`, `eq`, `freebuynum`, `if`, `volist`
- 示例观察到的属性：`condition`, `id`, `name`, `value`
- 输出变量/字段：`$eyou.field.arc_level_id`, `$eyou.field.users_price`, `$res.buyId`, `$res.downloadId`, `$res.downloadTipsId`, `$res.hidden`, `$res.onclick`, `$vo.downurl`, `$vo.extract_code`, `$vo.server_name`

### form 自由表单

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/29138.html；更新时间：2026-04-07 15:17:59。
- 示例涉及标签：`form`, `volist`
- 示例观察到的属性：`empty`, `formid`, `id`, `name`, `type`
- 输出变量/字段：`$attr.attr_html`, `$attr.attr_name`, `$field.action`, `$field.attr_1`, `$field.attr_2`, `$field.attr_3`, `$field.attr_45`, `$field.attr_5`, `$field.attr_6`, `$field.attr_7`, `$field.formhidden`, `$field.hidden`, `$field.itemname_1`, `$field.itemname_2`, `$field.itemname_3`, `$field.itemname_5`, `$field.itemname_6`, `$field.submit`, `$field.title`, `$field1.title`, `$vo.value`

### sppurchase 商品购买

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/31362.html；更新时间：2024-05-07 16:51:15。
- 示例涉及标签：`sppurchase`, `volist`
- 示例观察到的属性：`currentstyle`, `id`, `name`
- 输出变量/字段：`$field.BuyNow`, `$field.IncreaseQuantity`, `$field.ReduceQuantity`, `$field.ShopAddCart`, `$field.UpdateQuantity`, `$field.crossed_price`, `$field.hidden`, `$field.old_price`, `$field.spec_price`, `$field.stock_count`, `$field.stock_show`, `$field.title`, `$field.totol_price`, `$field.users_price`, `$field1.title`, `$field2.spec_name`, `$field3.SpecClass`, `$field3.SpecData`, `$field3.spec_image`, `$field3.spec_value`

### 商品其它标签栏

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/31557.html；更新时间：2025-04-15 13:38:58。
- 示例涉及标签：`volist`
- 示例观察到的属性：`id`, `name`
- 输出变量/字段：`$field.crossed_price`, `$field.label_intro`, `$field.label_pic`, `$field.label_title`

### couponslist 优惠券标签

- 分类：内容；来源：https://www.eyoucms.com/doc/label/arc/32091.html；更新时间：2026-08-11 10:15:00。
- 示例涉及标签：`couponslist`, `else`, `eq`, `if`
- 示例观察到的属性：`aid`, `condition`, `empty`, `id`, `key`, `limit`, `loop`, `mod`, `name`, `row`, `value`
- 输出变量/字段：`$cft.center_url`, `$cft.receive_url`, `$chd.center_url`, `$coupon.center_url`, `$coupon.conditions_use`, `$coupon.conditions_use_text`, `$coupon.coupon_discount`, `$coupon.coupon_form_name`, `$coupon.coupon_id`, `$coupon.coupon_intro`, `$coupon.coupon_name`, `$coupon.coupon_price`, `$coupon.coupon_stock`, `$coupon.coupon_type_name`, `$coupon.coupon_value_text`, `$coupon.geted`, `$coupon.receive_url`, `$coupon.valid_text`, `$field.coupon_name`

### ui 开关标签

- 分类：可视化；来源：https://www.eyoucms.com/doc/label/ui/571.html；更新时间：2023-10-24 11:58:04。
- 示例涉及标签：`ui`
- 示例观察到的属性：`open`
- 输出变量/字段：未直接出现。

### uitext 纯文本

- 分类：可视化；来源：https://www.eyoucms.com/doc/label/ui/572.html；更新时间：2023-10-24 11:58:13。
- 示例涉及标签：`else`, `ui`, `uitext`
- 示例观察到的属性：`e-id`, `e-page`, `id`, `open`
- 输出变量/字段：`$field.value`, `$field1.value`

### uihtml 富文本

- 分类：可视化；来源：https://www.eyoucms.com/doc/label/ui/573.html；更新时间：2023-10-24 11:58:31。
- 示例涉及标签：`else`, `ui`, `uihtml`
- 示例观察到的属性：`e-id`, `e-page`, `id`, `open`
- 输出变量/字段：`$field.value`, `$field1.value`

### uiupload 图片上传

- 分类：可视化；来源：https://www.eyoucms.com/doc/label/ui/574.html；更新时间：2023-10-24 11:58:40。
- 示例涉及标签：`else`, `ui`, `uiupload`
- 示例观察到的属性：`e-id`, `e-page`, `id`, `open`
- 输出变量/字段：`$field.value`, `$field1.value`

### uitype 栏目调用

- 分类：可视化；来源：https://www.eyoucms.com/doc/label/ui/575.html；更新时间：2023-10-24 11:59:55。
- 示例涉及标签：`type`, `ui`, `uitype`
- 示例观察到的属性：`e-id`, `e-page`, `empty`, `id`, `open`, `type`, `typeid`
- 输出变量/字段：`$field.typename`, `$field.typeurl`, `$field.value`, `$field1.value`

### uiarclist 文档列表

- 分类：可视化；来源：https://www.eyoucms.com/doc/label/ui/576.html；更新时间：2023-10-24 12:01:34。
- 示例涉及标签：`arclist`, `type`, `ui`, `uiarclist`
- 示例观察到的属性：`e-id`, `e-page`, `empty`, `id`, `limit`, `open`, `titlelen`, `type`, `typeid`
- 输出变量/字段：`$field.arcurl`, `$field.title`, `$field.typename`, `$field.typeurl`, `$field.value`, `$field1.value`

### uichannel 栏目菜单

- 分类：可视化；来源：https://www.eyoucms.com/doc/label/ui/577.html；更新时间：2023-10-24 12:01:23。
- 示例涉及标签：`channel`, `type`, `ui`, `uichannel`
- 示例观察到的属性：`e-id`, `e-page`, `empty`, `id`, `open`, `row`, `type`, `typeid`
- 输出变量/字段：`$field.typename`, `$field.typeurl`, `$field.value`, `$field1.value`

### uicode 源代码

- 分类：可视化；来源：https://www.eyoucms.com/doc/label/ui/11518.html；更新时间：2023-10-24 11:08:31。
- 示例涉及标签：`else`, `ui`, `uicode`
- 示例观察到的属性：`e-id`, `e-page`, `id`, `open`
- 输出变量/字段：`$field.value`, `$field1.value`

### uibackground 背景图片

- 分类：可视化；来源：https://www.eyoucms.com/doc/label/ui/11540.html；更新时间：2023-10-24 11:08:15。
- 示例涉及标签：`ui`, `uibackground`
- 示例观察到的属性：`e-id`, `e-img`, `e-page`, `open`
- 输出变量/字段：未直接出现。

### uimap 地图定位

- 分类：可视化；来源：https://www.eyoucms.com/doc/label/ui/11541.html；更新时间：2023-10-24 11:06:08。
- 示例涉及标签：`ui`, `uimap`
- 示例观察到的属性：`e-id`, `e-page`, `height`, `id`, `open`, `width`
- 输出变量/字段：`$field.value`, `$field1.value`

### 功能开启方法

- 分类：可视化；来源：https://www.eyoucms.com/doc/label/ui/31154.html；更新时间：2024-01-04 09:26:20。
- 示例涉及标签：本页不是独立标签用法。
- 示例观察到的属性：无。
- 输出变量/字段：未直接出现。

### language 语言列表

- 分类：多语言；来源：https://www.eyoucms.com/doc/label/language/6058.html；更新时间：2023-10-24 12:05:05。
- 示例涉及标签：`language`
- 示例观察到的属性：`empty`, `id`, `limit`, `mod`, `titlelen`, `type`
- 输出变量/字段：`$field.logo`, `$field.title`, `$field.url`, `$field1.title`

### lang 语言包变量

- 分类：多语言；来源：https://www.eyoucms.com/doc/label/language/6059.html；更新时间：2023-10-25 16:01:02。
- 示例涉及标签：`lang`
- 示例观察到的属性：`name`
- 输出变量/字段：未直接出现。

### lang_title 当前语言

- 分类：多语言；来源：https://www.eyoucms.com/doc/label/language/11886.html；更新时间：2024-01-22 17:30:46。
- 示例涉及标签：本页不是独立标签用法。
- 示例观察到的属性：无。
- 输出变量/字段：`$eyou.global.lang_logo`, `$eyou.global.lang_title`, `$eyou.global.lang_url`

## 后续验收指引

- 有限可执行清单：[acceptance-gaps.md](acceptance-gaps.md)；机器计划：[acceptance-plan.json](acceptance-plan.json)。按来源分支和代表值分区验收，不枚举所有属性的笛卡尔积。
- 属性输入台账：[tag-attribute-coverage.json](tag-attribute-coverage.json)。属性出现在成功输入中，只证明该输入被执行，不能作为完整语义兼容率。
- 原包逐文件页面、片段及原空mobile回退：[template-acceptance.md](template-acceptance.md)；实际运行记录：[verification.md](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/verification.md)。
- 后台UI编辑接口原版契约：[uiset-contract.md](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/uiset-contract.md)；外部服务、原包资源缺失和明确行为差异在有限清单中分开记录。
