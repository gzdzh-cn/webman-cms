<?php
namespace app\cms;

use app\cms\template\TemplateRenderer;
use app\cms\template\TemplateMode;
use app\cms\template\TemplateDialect;
use support\Request;
use support\Response;
use support\think\Db;

final class AjaxActions
{
    public static function counters(Request $request, CmsContext $context, string $action): Response
    {
        $ajax = strtolower((string)$request->header('x-requested-with')) === 'xmlhttprequest' || (bool)$request->input('_ajax');
        if ($action === 'downcount') return response((string)(int)Db::name('archives')->where(['aid' => (int)$request->input('aid', 0), 'is_del' => 0, 'lang' => $context->lang])->value('downcount'));
        $multiple = $request->input('aids') !== null;
        $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', (string)$request->input($multiple ? 'aids' : 'aid', ''))), static fn($id) => $id > 0)));
        if (!$ids) return response('0');
        $query = fn() => Db::name('archives')->whereIn('aid', $ids)->where(['is_del' => 0, 'lang' => $context->lang]);
        if ($request->input('type') === 'view') $query()->inc('click')->update();
        if ($ajax && $multiple) return json(array_column($query()->field('aid,click')->select()->toArray(), null, 'aid'));
        $count = (int)$query()->value('click');
        return response($ajax ? (string)$count : "document.write('" . $count . "');\r\n", 200, ['Content-Type' => $ajax ? 'text/plain; charset=utf-8' : 'application/javascript; charset=utf-8']);
    }

    public static function region(Request $request): Response
    {
        return self::result(1, '请求成功', Db::name('region')->where('parent_id', (int)$request->input('pid', 0))->select()->toArray());
    }

    public static function download(Request $request, CmsContext $context): Response
    {
        $record = Db::name('archives')->alias('a')->join('download_content b', 'a.aid=b.aid', 'LEFT')->where(['a.aid' => (int)$request->input('aid', 0), 'a.lang' => $context->lang, 'a.is_del' => 0, 'a.status' => 1])->field('a.*,b.content')->find();
        if (!$record) return self::result(0, '缺少文档id');
        $free = empty($record['restric_type']);
        $data = ['canDownload' => $free ? 1 : 0, 'download_tips' => '', 'onclick' => $context->device === 'mobile' ? 'ey_download_v866225(' . (int)$record['aid'] . ');' : 'DownloadBuyNow1655866225(' . (int)$record['aid'] . ');'];
        if ($free) $data['art_content'] = htmlspecialchars_decode((string)($record['content'] ?? ''));
        return self::result(1, 'success', $data);
    }

    /** Original tag_formreply.js appends this fixed, theme-local fragment. */
    public static function formReplies(Request $request, CmsContext $context): Response
    {
        if (strtolower((string)$request->header('x-requested-with')) !== 'xmlhttprequest' && !$request->input('_ajax')) return response('页面不存在', 404);
        $values = [];
        foreach (['typeid' => 0, 'page' => 1, 'pagesize' => 20, 'totalpage' => 1, 'ordermode' => 'desc'] as $name => $default) {
            $value = $request->input($name, $default);
            if (!is_scalar($value)) return self::result(0, '参数有误');
            $values[$name] = $value;
        }
        $typeid = (int)$values['typeid'];
        $page = (int)$values['page'];
        $size = (int)$values['pagesize'];
        $total = (int)$values['totalpage'];
        $order = strtolower(trim((string)$values['ordermode']));
        // Keep visitor values out of generated PHP/SQL. Valid original calls
        // contain a positive page size and one of the two ordering directions.
        if ($typeid < 1 || $page < 1 || $size < 1 || $page > intdiv(PHP_INT_MAX, $size) || !in_array($order, ['asc', 'desc'], true)) return self::result(0, '参数有误');
        $tag = new \app\cms\compat\template\taglib\eyou\TagFormreply();
        if (!$tag->getFormreply($typeid, $page, $size, $order)) return self::result(1, '请求成功', null);
        $file = $context->templateRoot() . 'system/formreply_block001.htm';
        if (!is_file($file)) return self::result(0, '标签模板不存在', ['code' => -1, 'msg' => '模板追加文件 formreply_block001.htm 不存在！']);
        $fragment = file_get_contents($file);
        if (empty($fragment)) return self::result(0, '标签模板不存在', ['code' => -1, 'msg' => '模板追加文件 formreply_block001.htm 没有HTML代码！']);
        $mode = TemplateMode::current($context);
        // Include the original source through the normal per-file dialect check.
        $source = '{' . $mode . ':formreply typeid="' . $typeid . '" page="' . $page . '" pagesize="' . $size . '" ordermode="' . $order . '"}{' . $mode . ':include file="system/formreply_block001.htm" /}{/' . $mode . ':formreply}';
        $data = ['msg' => (new TemplateRenderer($context))->renderString($source)];
        // EyouCMS uses the caller's totalpage, even when it is stale.
        if ($total === $page) $data['lastpage'] = 1;
        return self::result(1, '请求成功', $data);
    }

    public static function pagination(Request $request, CmsContext $context): Response
    {
        if (strtolower((string)$request->header('x-requested-with')) !== 'xmlhttprequest' && !$request->input('_ajax')) return response('页面不存在', 404);
        $page = (int)$request->input('page', 0);
        $tag = (string)$request->input('tagid', '');
        $hash = (string)$request->input('tagidmd5', '');
        if ($page < 1 || !preg_match('/^[A-Za-z0-9_-]+$/', $tag) || !preg_match('/^[A-Za-z0-9_]+$/', $hash)) return self::result(0, '参数有误');
        $data = ['code' => 1, 'msg' => '', 'lastpage' => 0];
        $record = Db::name('arcmulti')->where('tagid', $hash)->find();
        if ($record && $record['querysql']) {
            $savedSql = \app\cms\template\PaginationRecord::sql($hash, $record);
            if ($savedSql === null) return self::result(0, '分页数据已失效，请刷新页面');
            $attributes = unserialize(stripslashes($record['attstr']), ['allowed_classes' => false]);
            if (!is_array($attributes)) return self::result(0, '分页属性无效');
            $mode = TemplateMode::current($context);
            if (($attributes['__cms_template_mode'] ?? 'eyou') !== $mode
                || (isset($attributes['__cms_dialect_version']) && $attributes['__cms_dialect_version'] !== TemplateDialect::VERSION)) {
                return self::result(0, '分页模板模式或编译版本已变化，请刷新页面');
            }
            $size = (int)$request->input('pagesize', 0) ?: (int)$record['pagesize'];
            if ($size < 1) return self::result(0, '分页条数无效');
            $offset = (int)($attributes['row'] ?? 0) + (max(2, $page) - 2) * $size;
            $end = $offset + $size;
            $sql = preg_replace('#LIMIT(\s+)(\d+)(,\d+)?#i', '', $savedSql);
            $sql = preg_replace('#SELECT(\s+)(.*)(\s+)FROM#i', 'SELECT COUNT(*) AS totalNum FROM', $sql);
            $count = (int)(Db::query($sql)[0]['totalNum'] ?? 0);
            $file = $context->templateRoot() . 'system/arclist_' . $tag . '.htm';
            if (!is_file($file) || trim((string)file_get_contents($file)) === '') return self::result(0, '标签模板不存在', ['code' => -1, 'msg' => '模板追加文件 arclist_' . $tag . '.htm 不存在或没有HTML代码！', 'lastpage' => 0]);
            $source = '{' . $mode . ':arclist'; $variables = [];
            foreach ($attributes as $key => $value) {
                if (in_array($key, ['tagid', 'offset', 'row', '__cms_template_mode', '__cms_dialect_version'], true) || !preg_match('/^\w+$/', (string)$key) || !is_scalar($value)) continue;
                if ($key === 'keyword' && str_starts_with((string)$value, '$eyou')) {
                    // Keep visitor input as data; it must never become template source.
                    $variables['__cms_pagination_keywords'] = (string)$request->input('keywords', '');
                    $value = '$__cms_pagination_keywords';
                }
                // Database attributes were persisted after lexical normalization.
                if (in_array(strtolower((string)$key), ['condition', 'use', 'vars', 'call'], true) || preg_match('/^\s*(?:\$|:)/', (string)$value)) {
                    $value = TemplateDialect::externalizeInternalExpression((string)$value, $mode);
                }
                $value = (string)$value;
                $quote = str_contains($value, '"') ? "'" : '"';
                if (str_contains($value, $quote)) return self::result(0, '分页属性引号无法安全重建，请调整模板属性');
                $source .= ' ' . $key . '=' . $quote . $value . $quote;
            }
            $source .= ' tagid="' . $tag . '" limit="' . $offset . ',' . $end . '"}{' . $mode . ':include file="system/arclist_' . $tag . '.htm" /}{/' . $mode . ':arclist}';
            $data['msg'] = (new TemplateRenderer($context))->renderString($source, $variables);
            $data['lastpage'] = $count > 0 && $count <= $end ? 1 : 0;
        }
        return self::result(1, '请求成功', $data);
    }

    private static function result(int $code, string $message, ?array $data = []): Response { return json(['code' => $code, 'msg' => $message, 'data' => $data, 'url' => '', 'wait' => 3], JSON_UNESCAPED_UNICODE); }
}
