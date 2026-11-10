<?php
/**
 * 易优CMS
 * ============================================================================
 * 版权所有 2016-2028 海南赞赞网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.eyoucms.com
 * ----------------------------------------------------------------------------
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 * Author: 小虎哥 <1105415366@qq.com>
 * Date: 2021-7-18
 */
namespace app\cms\compat\model;
use function app\cms\compat\config;
use function app\cms\compat\input;
use function app\cms\compat\request;
use function app\cms\compat\cache;
use function app\cms\compat\cookie;
use function app\cms\compat\session;
use function app\cms\compat\model;
use function app\cms\compat\M;
use function app\cms\compat\lang;
use app\cms\compat\Runtime;


use app\cms\compat\Db;
use app\cms\compat\Model;

/**
 * 外贸助手语言包变量
 */
class ForeignPack extends Model
{
    //初始化
    protected function initialize()
    {
        // 需要调用`Model`的`initialize`方法
        parent::initialize();
    }

    public function getPackValue($name = '', $lang = '')
    {
        $main = (new \app\cms\service\LanguagePolicy())->main();
        $lang = \app\cms\service\LanguageAssetService::selectedLanguage();
        $result = [];
        // Keep this independent legacy vocabulary; do not mix language_pack rows.
        foreach (Db::name('foreign_pack')->where(['id'=>['gt',0]])->order('id asc')->select() as $row) {
            $key = md5($row['name'].'_'.$row['lang']);
            $result[$key] = $row;
        }
        if ($name === '') return $result;
        $targetKey = md5($name.'_'.$lang);
        if (array_key_exists($targetKey, $result)) return (string)$result[$targetKey]['value'];
        return (string)($result[md5($name.'_'.$main)]['value'] ?? '');
    }

    /** Derived JS is rebuilt from database values into disposable runtime. */
    public function updateLangFile()
    {
        \app\cms\service\LanguageAssetService::generated();
    }

    public function appendForeignGlobalJs(&$params = '')
    {
        if (str_contains($params, '/lang/foreign_global.js')) return;
        \app\cms\service\LanguageAssetService::generated();
        $url = htmlspecialchars(\app\cms\AssetManifest::url('/public/static/common/js/lang/foreign_global.js'), ENT_QUOTES, 'UTF-8');
        $replacement = '<script language="javascript" type="text/javascript" src="' . $url . '"></script>';
        $params = preg_replace_callback('~<script\b[^>]*\bsrc=([\'"])[^\'"]*/(?:public/plugins|template/_shared/assets/plugins|' . preg_quote(ltrim(\app\cms\Paths::COMMON_ASSET_URL, '/'), '~') . '/plugins)/layer-v3\.1\.0/layer\.js[^\'"]*\1[^>]*>\s*</script>~i',
            static fn($match) => $replacement . PHP_EOL . $match[0], $params);
    }
}
