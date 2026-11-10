# 两种前台模板模式

此文为安装模式与标签命名空间的当前规范，原版离线标签手册仍描述 EyouCMS 语法。两套语法共用原有标签实现；命名空间隔离不代表 T17 等历史兼容问题已经解决。

## 安装时选择

| 选择 | 模板标签 | 根变量 | 默认主题 |
| --- | --- | --- | --- |
| Webman（默认） | `{webman:*}` | `$webman` | `webman-default` |
| 兼容 EyouCMS | `{eyou:*}` | `$eyou` | `default` |

兼容模式还可选择导入原站 SQL；未导入时同样创建完整结构的空站。模式保存在可信后台 options 表的 `cms_template_mode`，原站 SQL 中的 config 不决定模式。历史站没有该配置时使用 EyouCMS。基本设置只读展示模式，本轮不提供在线切换。

## 等价调用

```html
<!-- Webman 模式 -->
{webman:global name='web_name' /}
{$webman.field.title}
{webman:arclist typeid='1' row='6'}
  <a href="{$field.arcurl}">{$field.title}</a>
{/webman:arclist}
{webman:include file='header.htm' /}
```

```html
<!-- EyouCMS 兼容模式 -->
{eyou:global name='web_name' /}
{$eyou.field.title}
{eyou:arclist typeid='1' row='6'}
  <a href="{$field.arcurl}">{$field.title}</a>
{/eyou:arclist}
{eyou:include file='header.htm' /}
```

Webman 也接受 `{ webman:global name='web_name' /}`，生成的示例采用不加空格的规范格式。`$field`、`$vo` 和 `web_attr_1` 等局部变量、字段名称保持。数组、过滤器、条件、循环和 PHP 块中的真实根变量遵循当前模式；普通正文、URL、JS 字符串及名称相似的普通变量不做全文替换。

当前模式的 literal 可以显示另一套语法示例：

```html
{webman:literal}
  {eyou:global name='web_name' /}
  {$eyou.field.title}
{/webman:literal}
```

Webman 模式直接执行 `eyou` 标签或根变量会编译报错，EyouCMS 模式反之。显式标签库、include、继承、布局、动态片段和缓存都遵循检查。错误提供原模板相对路径与行号；前台详情显示仍受原生报错配置控制。

## 主题与系统页面

原主题不在运行时转换。`webman-default` 为单独维护的企业起步主题，包含 PC/mobile 公共片段、首页、文章与产品列表/详情、单页、搜索和留言；后台模板列表显示 EyouCMS、Webman、混合或未识别。混合及与站点模式冲突的主题不能启用；未识别的纯静态主题允许启用，实际执行仍逐源检查。缺页仍允许启用，在访问该页时明确报错，不跨主题补页。

系统 Sitemap、AI 索引和空页的 Webman 版本在 `app/view/cms/system/webman`，原兼容版本保留在上级。后台页面自身的模板语法不变。后台复制标签和字段调用按站点模式输出。

## 缓存与重装

编译缓存包含模式、编译版本和源依赖；不使用跳过源检查的整页输出缓存。AJAX 分页元数据、生成任务及 Sitemap 队列记录模式与编译版本。历史没有模式的记录按 EyouCMS 解释，无法核实签名的旧产物不复用。

每次安装生成新的站点签名，续装沿用同一签名。静态发布签名包含站点签名、模板模式及编译版本，模板模式或站点不匹配的旧发布不能被当作当前页面返回；外来和人工修改文件仍按现有归属/hash规则保护。可视化缓存按模式与编译版本隔离，数据库仍是有效配置来源。

原版 `template/default` 及既有主题、上传和已生成 HTML 不因安装模式实现而改写。模板目录说明见 [架构说明](../architecture.md)，安装及精确冲突替换流程见 [安装说明](../installation.md)。
