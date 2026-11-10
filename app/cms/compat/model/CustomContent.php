<?php
namespace app\cms\compat\model;

use app\cms\compat\Db;
use app\cms\compat\Model;
use app\cms\compat\Runtime;

/** The generated Eyou content-model contract for database-registered channels. */
class CustomContent extends Model
{
    public function __construct(private array $channel)
    {
        $table = (string)($channel['table'] ?? '');
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/D', $table)) {
            throw new \RuntimeException('Invalid registered content-model table');
        }
        parent::__construct($table);
    }

    public function getInfo($aid, $field = '', $isshowbody = true)
    {
        if ($field !== '') {
            $field = implode(',', array_map(static function ($column) {
                $column = trim($column);
                return preg_match('/^([a-z]+)\./i', $column) ? $column : 'a.' . $column;
            }, explode(',', $field)));
        }
        $query = Db::name('archives')->alias('a');
        $numericId = is_numeric($aid) && strval(intval($aid)) === strval($aid);
        $query->where($numericId ? 'a.aid' : 'a.htmlfilename', $numericId ? intval($aid) : $aid);
        if ($isshowbody) {
            $query->join('__' . strtoupper($this->channel['table']) . '_CONTENT__ b', 'b.aid = a.aid', 'LEFT');
        }
        $result = $query->field($field ?: ($isshowbody ? 'b.*, a.*' : 'a.*'))->find();
        if (!empty($result)) {
            $result['tags'] = Runtime::model('Taglist')->getListByAid($result['aid'] ?? $aid, $result['typeid'] ?? 0);
        }
        return $result;
    }
}
