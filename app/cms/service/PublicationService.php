<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use support\Request;

/** Read-only before-images and post-commit publication for enterprise mutations. */
class PublicationService
{
    public function __construct(private ?CmsRepository $repository = null, private ?StaticGenerationService $generation = null)
    {
        $this->repository ??= new CmsRepository();
        $this->generation ??= new StaticGenerationService($this->repository);
    }

    public function capture(Request $request): array
    {
        $module = class_basename((string)$request->controller); $action = (string)$request->action;
        $kind = null; $ids = [];
        if ($module === 'ArchiveController' && in_array($action, ['move', 'delete', 'updateStatus', 'update'], true)) {
            $kind = 'archives';
            $ids = $this->ids($action === 'move' ? $request->post('aids', $request->post('aid', [])) : $request->post('aid', []), $action === 'move');
        } elseif ($module === 'ArctypeController' && in_array($action, ['insert', 'update', 'delete'], true)) {
            $kind = 'arctype'; $ids = $this->ids($request->post('id', $request->get('id', [])));
        } elseif ($module === 'RecycleController' && in_array($action, ['restore', 'purge'], true) && in_array($request->post('type', 'archives'), ['archives', 'arctype'], true)) {
            $kind = $request->post('type', 'archives'); $ids = $this->ids($request->post('ids', []));
        }
        if ($kind === null) return [];
        $types = $this->categories();
        $scope = $kind === 'arctype' ? $this->categoryScope($ids, $types) : [];
        $selected = $kind === 'archives' ? $this->archivesByIds($ids) : [];
        if ($selected) $scope = $this->ancestors(array_column($selected, 'typeid'), $types);
        return ['module' => $module, 'action' => $action, 'kind' => $kind, 'ids' => $ids,
            'categories' => $types, 'scope' => $scope, 'selected' => $selected,
            'archives' => $this->archivesByTypes($kind === 'arctype' ? $scope : array_column($selected, 'typeid'))];
    }

    /** The caller invokes this only after a successful business response. */
    public function publish(Request $request, array $before, array $result): array
    {
        $types = $this->categories();
        $categoryMutation = $before['kind'] === 'arctype';
        $ids = $before['ids'];
        if ($categoryMutation && $before['action'] === 'insert') $ids = $this->ids($result['data']['id'] ?? []);
        $scope = $categoryMutation ? array_values(array_unique(array_merge($before['scope'], $this->categoryScope($ids, $types)))) : [];
        $selected = !$categoryMutation ? $this->archivesByIds($ids) : [];
        if (!$categoryMutation) $scope = array_values(array_unique(array_merge($before['scope'], $this->ancestors(array_column($selected, 'typeid'), $types))));
        $archives = $this->archivesByTypes($categoryMutation ? $scope : array_merge(array_column($before['selected'], 'typeid'), array_column($selected, 'typeid')));
        $changedTypes = [];
        if ($categoryMutation) foreach ($scope as $id) if (($before['categories'][$id] ?? null) !== ($types[$id] ?? null)) $changedTypes[] = $id;
        // A successful category update may retain the same second timestamp;
        // its selected tree still needs publication (e.g. custom field writes).
        if ($categoryMutation && in_array($before['action'], ['update', 'insert'], true)) $changedTypes = array_values(array_unique(array_merge($changedTypes, $ids)));
        $documents = !$categoryMutation ? $ids : [];
        if ($categoryMutation) {
            $documentTypes = array_unique(array_merge($this->descendants($changedTypes, $before['categories']), $this->descendants($changedTypes, $types)));
            foreach ($before['archives'] + $archives as $aid => $row) if (in_array((int)$row['typeid'], $documentTypes, true)) $documents[] = (int)$aid;
        }
        $documents = array_values(array_unique($documents));
        $neighbors = array_values(array_unique(array_merge($this->neighbors($documents, $before['archives']), $this->neighbors($documents, $archives))));
        $allDocuments = array_values(array_unique(array_merge($documents, $neighbors)));
        $categoryIds = $categoryMutation ? array_unique(array_merge($this->ancestors($changedTypes, $before['categories']), $this->ancestors($changedTypes, $types))) : $scope;
        $groups = [];
        foreach ($categoryIds as $id) foreach ([$before['categories'][$id] ?? null, $types[$id] ?? null] as $row) if ($row) $groups[$row['lang']]['category_ids'][(int)$id] = (int)$id;
        foreach ($allDocuments as $aid) foreach ([$before['archives'][$aid] ?? null, $archives[$aid] ?? null] as $row) if ($row) $groups[$row['lang']]['archive_ids'][(int)$aid] = (int)$aid;

        // Archive::contentSaved may already have rendered a page. Invalidate
        // again before the final pass: its first generation may have failed in
        // the same second as the edit, when timestamps cannot reject old HTML.
        $invalidated = []; $publications = []; $warnings = [];
        foreach ($groups as $lang => $group) {
            $invalidation = $this->generation->invalidatePublished(['lang' => (string)$lang, 'category_ids' => array_values($group['category_ids'] ?? []), 'archive_ids' => array_values($group['archive_ids'] ?? []), 'home' => true]);
            $invalidated[] = ['lang' => $lang] + $invalidation;
        }
        foreach ($groups as $lang => $group) {
            $existing = array_values(array_filter($group['category_ids'] ?? [], static fn($id) => isset($types[$id]) && $types[$id]['lang'] === $lang));
            // If the last column was purged, its language still owns a homepage.
            // An explicit language prevents falling back to the backend language.
            foreach ($existing ?: [(array_values($group['category_ids'] ?? [])[0] ?? 0)] as $id) {
                $publication = $this->generation->afterCategoryChange($request, (int)$id, (string)$lang);
                $publications[] = ['typeid' => (int)$id, 'lang' => $lang] + $publication;
                if (!empty($publication['warning'])) $warnings[] = $publication['warning'];
            }
        }
        foreach ($allDocuments as $aid) {
            $row = $archives[$aid] ?? null;
            if (!$this->publicArchive($row)) continue;
            $publication = $this->generation->afterContentSave($request, (int)$aid, true);
            $publications[] = ['aid' => (int)$aid] + $publication;
            if (!empty($publication['warning'])) $warnings[] = $publication['warning'];
        }
        return ['invalidated' => $invalidated, 'publications' => $publications, 'warnings' => array_values(array_unique($warnings))];
    }

    private function ids(mixed $value, bool $csv = false): array
    {
        if ($csv && is_string($value)) $value = explode(',', $value);
        $ids = [];
        foreach ((array)$value as $item) if ((is_string($item) || is_int($item)) && ctype_digit((string)$item) && (int)$item > 0 && (int)$item <= 2147483647) $ids[] = (int)$item;
        return array_values(array_unique($ids));
    }

    private function categories(): array { return array_column($this->repository->table('arctype')->select()->toArray(), null, 'id'); }
    private function archivesByIds(array $ids): array { return $ids ? array_column($this->repository->table('archives')->whereIn('aid', $ids)->select()->toArray(), null, 'aid') : []; }
    private function archivesByTypes(array $ids): array { return $ids ? array_column($this->repository->table('archives')->whereIn('typeid', array_values(array_unique(array_map('intval', $ids))))->select()->toArray(), null, 'aid') : []; }

    private function categoryScope(array $ids, array $types): array
    {
        $selected = $this->descendants($ids, $types);
        $bindings = $this->repository->table('language_attr')->where('attr_group', 'arctype')->select()->toArray();
        $names = [];
        foreach ($bindings as $row) if (in_array((int)$row['attr_value'], $selected, true)) $names[] = (string)$row['attr_name'];
        foreach ($bindings as $row) if (in_array((string)$row['attr_name'], $names, true)) $selected[] = (int)$row['attr_value'];
        return $this->ancestors($this->descendants(array_values(array_unique($selected)), $types), $types);
    }

    private function descendants(array $ids, array $types): array
    {
        $selected = array_values(array_unique(array_map('intval', $ids)));
        do {
            $count = count($selected);
            foreach ($types as $row) if (isset($types[$row['parent_id']]) && $types[$row['parent_id']]['lang'] === $row['lang'] && in_array((int)$row['parent_id'], $selected, true)) $selected[] = (int)$row['id'];
            $selected = array_values(array_unique($selected));
        } while (count($selected) > $count);
        return $selected;
    }

    private function ancestors(array $ids, array $types): array
    {
        $selected = [];
        foreach ($ids as $id) {
            $id = (int)$id; $lang = $types[$id]['lang'] ?? null; $seen = [];
            while ($id > 0 && isset($types[$id]) && $types[$id]['lang'] === $lang && !isset($seen[$id])) {
                $selected[] = $id; $seen[$id] = true; $id = (int)$types[$id]['parent_id'];
            }
        }
        return array_values(array_unique($selected));
    }

    private function publicArchive(?array $row): bool { return $row && empty($row['is_del']) && (int)$row['status'] === 1 && (int)$row['arcrank'] === 0 && empty($row['is_jump']); }

    private function neighbors(array $ids, array $archives): array
    {
        $neighbors = [];
        foreach ($ids as $id) {
            $selected = $archives[$id] ?? null; if (!$selected) continue;
            $previous = null; $next = null;
            foreach ($archives as $aid => $row) {
                if (!$this->publicArchive($row) || $row['lang'] !== $selected['lang'] || (int)$row['typeid'] !== (int)$selected['typeid']) continue;
                if ($aid < $id && ($previous === null || $aid > $previous)) $previous = (int)$aid;
                if ($aid > $id && ($next === null || $aid < $next)) $next = (int)$aid;
            }
            if ($previous !== null) $neighbors[] = $previous;
            if ($next !== null) $neighbors[] = $next;
        }
        return array_values(array_unique($neighbors));
    }
}
