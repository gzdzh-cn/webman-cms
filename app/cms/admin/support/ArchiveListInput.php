<?php
declare(strict_types=1);
namespace app\cms\admin\support;

use InvalidArgumentException;
use support\Request;

/** Project the archive list's category filter into IDs for both access checks. */
final class ArchiveListInput
{
    public static function forAccess(Request $request, array $input): array
    {
        if ($request->method() !== 'GET' || strtolower((string)$request->action) !== 'select'
            || !isset($input['typeid']) || !is_array($input['typeid'])) return $input;

        $filter = $input['typeid'];
        if (array_keys($filter) !== [0, 1] || $filter[0] !== 'in') throw new InvalidArgumentException('栏目筛选格式错误');
        $ids = is_string($filter[1]) ? explode(',', $filter[1]) : $filter[1];
        if (!is_array($ids) || !array_is_list($ids) || !$ids) throw new InvalidArgumentException('栏目筛选格式错误');
        foreach ($ids as $id) {
            if ((!is_string($id) && !is_int($id)) || !preg_match('/^\d{1,10}$/D', (string)$id)
                || (int)$id < 1 || (int)$id > 4294967295) throw new InvalidArgumentException('栏目筛选格式错误');
        }
        // Keep the request's query expression intact for the list controller.
        $input['typeid'] = array_values(array_unique(array_map('intval', $ids)));
        return $input;
    }
}
