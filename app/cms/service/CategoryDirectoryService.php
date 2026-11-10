<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/**
 * Original Arctype entry validation, get_dirname and function.php get_pinyin.
 * 易优CMS 版权所有 2016-2028 海南赞赞网络科技有限公司，并保留所有权利。
 * https://www.eyoucms.com — retain the upstream license for commercial use.
 */
class CategoryDirectoryService
{
    private const RESERVED = ['application','core','data','extend','install','public','plugins','uploads','template','vendor','weapp','tags','search','user','users','member','reg','centre','login','cart'];

    public function __construct(private CmsRepository $repository) {}

    public function resolve(string $title, string $posted, string $lang, int $id = 0): string
    {
        // The original controller sanitizes before deciding whether a directory
        // was explicitly supplied. Preserve that distinction on add and edit.
        $posted = preg_replace('/([^\w\-])/i', '', $posted);
        if (!empty($posted) && !$this->available($posted, $lang, $id)) throw new InvalidArgumentException('目录名称与系统内置或回收站里的栏目冲突，请更改');
        $name = !empty($posted) ? $posted : $this->pinyin($title);
        if ((string)(int)$name == $name) $name .= $this->suffix((bool)preg_match('/^[0-9]+$/D', $name));
        $name = preg_replace('/(\s)+/', '_', $name);
        if ($name === '') $name = $this->suffix(false);
        for ($attempt = 0; !$this->available($name, $lang, $id); ++$attempt) {
            if ($attempt >= 100) throw new InvalidArgumentException('无法生成唯一栏目目录，请手动填写目录名称');
            $name .= $this->suffix(false);
        }
        return $name;
    }

    private function available(string $name, string $lang, int $id): bool
    {
        $query = $this->repository->table('arctype')->where('lang', $lang);
        if ($id) $query->where('id', '<>', $id);
        $blocked = array_merge(self::RESERVED, $this->repository->table('language_mark')->column('mark'), $query->column('dirname'));
        foreach ($blocked as $value) if (strtolower($name) === strtolower((string)$value)) return false;
        return true;
    }

    protected function suffix(bool $numeric): string
    {
        // get_rand_str(3,0,0) is lowercase letters; (3,0,2) is digits 1–9.
        $alphabet = $numeric ? 'abcdefghijklmnopqrstuvwxyz' : '123456789'; $value = '';
        for ($i = 0; $i < 3; ++$i) $value .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        return $value;
    }

    /** The upstream GB2312 mapping is immutable process-wide data, not request state. */
    private function pinyin(string $text): string
    {
        static $mapping = null;
        $original = $text;
        $gb = @iconv('UTF-8', 'gb2312', $text);
        if ($gb !== false && @iconv('gb2312', 'UTF-8', $gb) === $text) $text = $gb;
        $text = trim($text); $length = strlen($text);
        if ($length < 2) return $text;
        if ($mapping === null) {
            $mapping = []; $path = \app\cms\Paths::legacyData('conf/pinyin.dat');
            $stream = fopen($path, 'rb');
            if (!$stream) throw new InvalidArgumentException('栏目拼音数据文件不存在');
            while (!feof($stream)) {
                $line = trim((string)fgets($stream));
                if (strlen($line) >= 3) $mapping[$line[0] . $line[1]] = substr($line, 3);
            }
            fclose($stream);
        }
        $result = '';
        for ($index = 0; $index < $length; ++$index) {
            if (ord($text[$index]) > 0x80) {
                $key = $text[$index] . ($text[++$index] ?? ''); $result .= $mapping[$key] ?? '-';
            } else $result .= preg_match('/[a-z0-9]/i', $text[$index]) ? $text[$index] : '-';
        }
        $result = strtolower($result);
        foreach (['深圳' => ['shenchou','shenzhen'], '内蒙古' => ['namenggu','neimenggu'], '重庆' => ['zhongqing','chongqing'], '西藏' => ['xicang','xizang']] as $place => [$from,$to]) {
            if (stristr($original, $place)) { $result = str_replace($from,$to,$result); break; }
        }
        return $result;
    }
}
