<?php
namespace app\cms\compat;

/** Native-video metadata and markup, scoped to the current render/request. */
final class MediaPlayer
{
    private static function script(array $config, string $method): string
    {
        $context = Runtime::context();
        $include = '';
        if (empty($context->memo['media.bridge_included'])) {
            $context->memo['media.bridge_included'] = true;
            $src = \app\cms\AssetManifest::url(rtrim((string)Runtime::constant('ROOT_DIR'), '/') . '/public/static/cms/eyou-video.js?v=2');
            $include = '<script src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '"></script>';
        }
        $json = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
        $legacy = $method === 'registerList' ? 'var vars1612143009=' . $json . ';' : '';
        return $include . '<script>' . $legacy . 'window.EyouMedia.' . $method . '(' . $json . ');</script>';
    }

    public static function playback(int $aid, string $autoplay, array $file): array
    {
        $context = Runtime::context();
        $index = ($context->memo['media.player_count'][$aid] ?? 0) + 1;
        $context->memo['media.player_count'][$aid] = $index;
        $id = 'video_play_20200520_' . $aid . ($index > 1 ? '_' . $index : '');
        $hiddenId = empty($context->memo['media.first_hidden']) ? 'fid1616057948' : 'fid1616057948_' . $aid . '_' . $index;
        $context->memo['media.first_hidden'] = true;
        $context->memo['media.latest_player'][$aid] = $id;
        $context->memo['media.player_fid'][$id] = (int)($file['file_id'] ?? 0);
        $config = self::base($aid) + ['instanceKey' => $id, 'elementId' => $id, 'hiddenId' => $hiddenId,
            'fid' => (int)($file['file_id'] ?? 0), 'autoplay' => $autoplay];
        $file['id'] = " id='" . $id . "' ";
        $file['instance_key'] = $id;
        $file['hidden'] = '<input type="hidden" id="' . $hiddenId . '" value="' . $config['fid'] . '">'
            . self::script($config, 'registerPlayer');
        return $file;
    }

    /** The supplied rows already have upstream URL/time normalization. */
    public static function playlist(int $aid, string $autoplay, string $player, array $rows): array
    {
        $rows = array_values(array_filter($rows, static fn($row) => !empty($row['file_url']) || !empty($row['__eyou_media_has_source'])));
        if (!$rows) return [];
        $context = Runtime::context();
        $index = ($context->memo['media.list_count'] ?? 0) + 1;
        $context->memo['media.list_count'] = $index;
        $listKey = 'eyou_media_list_' . $aid . '_' . $index;
        $element = $context->memo['media.latest_player'][$aid] ?? 'video_play_20200520_' . $aid;
        $selected = $rows[0];
        $fid = $context->memo['media.player_fid'][$element] ?? 0;
        foreach ($rows as $row) {
            if ($fid > 0 && (int)$row['file_id'] === $fid) { $selected = $row;break; }
        }
        $config = self::base($aid) + ['listKey' => $listKey, 'instanceKey' => $element, 'elementId' => $element,
            'player' => $player, 'autoplay' => $autoplay, 'file_id_0' => (int)$selected['file_id'], 'uhash_0' => (string)($selected['uhash'] ?? ''),
            'episodes' => array_map(static fn($row) => ['file_id' => (int)$row['file_id'], 'uhash' => (string)($row['uhash'] ?? '')], $rows)];
        foreach ($rows as &$row) {
            unset($row['__eyou_media_has_source']);
            $args = array_map(static fn($value) => json_encode((string)$value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR),
                [$row['file_id'], $aid, $row['uhash'] ?? '', $player]);
            $call = 'return changeVideoUrl341922(' . implode(',', $args) . ',this,' . json_encode($listKey) . ')';
            $row['onclick'] = ' onclick="' . htmlspecialchars($call, ENT_QUOTES, 'UTF-8') . '" ';
            $row['hidden'] = '';
            $row['instance_key'] = $element;
        }
        unset($row);
        $rows[count($rows) - 1]['hidden'] = self::script($config, 'registerList');
        return $rows;
    }

    private static function base(int $aid): array
    {
        $context = Runtime::context();
        return ['aid' => $aid, 'root_dir' => (string)Runtime::constant('ROOT_DIR'), 'lang' => $context->lang,
            'device' => $context->device, 'membersEnabled' => false];
    }
}
