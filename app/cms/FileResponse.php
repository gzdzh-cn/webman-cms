<?php
namespace app\cms;

use support\Request;
use support\Response;

/** Byte ranges are required by copied video/audio players and resumable files. */
final class FileResponse
{
    public static function make(Request $request, string $file): Response
    {
        // Never stream a mutable runtime file after checking its digest: use
        // exactly the verified bytes, including derived public language JS.
        if (str_starts_with($file, Paths::runtime() . '/')) {
            $bytes = \app\cms\service\LanguageAssetService::readDerived($file);
            $etag = '"' . hash('sha256', $bytes) . '"';
            $headers = ['Content-Type'=>'application/javascript; charset=utf-8', 'Cache-Control'=>'no-cache', 'X-Content-Type-Options'=>'nosniff', 'ETag'=>$etag];
            if ($request->header('if-none-match') === $etag) return response('', 304, $headers);
            return response($request->method() === 'HEAD' ? '' : $bytes, 200, $headers);
        }
        $file = realpath($file) ?: $file;
        $size = (int)filesize($file);
        $stamp = (int)filemtime($file);
        $managedAsset = AssetManifest::isManagedFile($file) || AssetManifest::entry($request->path());
        $assetHash = $managedAsset ? hash_file('sha256', $file) : null;
        $etag = '"' . ($assetHash ?? sha1($file . ':' . $stamp . ':' . $size)) . '"';
        $headers = ['X-Content-Type-Options'=>'nosniff', 'ETag' => $etag, 'Last-Modified' => gmdate('D, d M Y H:i:s', $stamp) . ' GMT', 'Accept-Ranges' => 'bytes'];
        // Copied URLs stay stable while a verified compatibility derivative can
        // change. Revalidate these scripts instead of keeping a heuristic cache.
        $compatRoot = realpath(Paths::commonAssets('compat'));
        $actual = realpath($file);
        if ($compatRoot && $actual && str_starts_with($actual, $compatRoot . DIRECTORY_SEPARATOR) && pathinfo($actual, PATHINFO_EXTENSION) === 'js') {
            $headers['Cache-Control'] = 'no-cache';
        }
        if ($managedAsset) {
            $entry = AssetManifest::entry($request->path());
            $currentHash = $assetHash;
            $isVersioned = $entry && empty($entry['mutable']) && $request->get('v') === substr($currentHash, 0, 16);
            $headers['Cache-Control'] = $isVersioned ? 'public, max-age=31536000, immutable' : 'no-cache';
        }
        // Workerman's MIME registry does not include Windows cursor files.
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'cur') $headers['Content-Type'] = 'image/x-icon';
        if ($request->header('if-none-match') === $etag) return response('', 304, $headers);
        if ($request->method() === 'HEAD') {
            // Workerman streams every response with a file property, while its
            // ordinary serializer replaces Content-Length with the body size.
            // Reuse its file-header serializer without registering a body/file.
            return new class($file, $headers + ['Content-Length' => $size]) extends Response {
                public function __construct(private string $headFile, array $headers)
                {
                    parent::__construct(200, $headers);
                }

                public function __toString(): string
                {
                    return $this->createHeadForFile(['file' => $this->headFile]);
                }
            };
        }
        $range = (string)$request->header('range', '');
        $ifRange = (string)$request->header('if-range', '');
        if ($ifRange !== '' && $ifRange !== $etag && strtotime($ifRange) !== $stamp) $range = '';
        if (preg_match('/^bytes=(\d*)-(\d*)$/', $range, $match) && ($match[1] !== '' || $match[2] !== '')) {
            if ($match[1] === '') { $offset = max(0, $size - (int)$match[2]); $end = $size - 1; }
            else { $offset = (int)$match[1]; $end = $match[2] === '' ? $size - 1 : min($size - 1, (int)$match[2]); }
            if ($offset >= $size || $offset > $end || $end < 0) return response('', 416, $headers + ['Content-Range' => 'bytes */' . $size]);
            return (new Response())->withHeaders($headers)->withFile($file, $offset, $end - $offset + 1);
        }
        return (new Response())->withHeaders($headers)->withFile($file);
    }
}
