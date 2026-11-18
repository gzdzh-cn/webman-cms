<?php
namespace app\cms;

/** Fixed-provider geocoding; test transports can supply loopback responses. */
final class MapGeocoder
{
    public function __construct(private ?\Closure $transport = null) {}

    public function lookup(string $address, CmsContext $context): array
    {
        $address = trim($address);
        if ($address === '' || mb_strlen($address) > 300) return ['code' => 0, 'msg' => '请输入有效地址'];
        $ak = base64_decode((string)$context->getConfig('global.baidu_map_ak', ''), true) ?: '';
        if ($ak === '') return ['code' => 0, 'msg' => '请先配置地图密钥'];
        $url = 'https://api.map.baidu.com/geocoder/v2/?' . http_build_query(['address' => $address, 'city' => '', 'output' => 'json', 'ak' => $ak]);
        if ($this->transport) $raw = ($this->transport)($url);
        else {
            $curl = curl_init($url);
            curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_FOLLOWLOCATION => false, CURLOPT_PROTOCOLS => CURLPROTO_HTTPS]);
            $raw = curl_exec($curl);
            if (curl_getinfo($curl, CURLINFO_RESPONSE_CODE) !== 200) $raw = false;
            curl_close($curl);
        }
        $data = is_string($raw) ? json_decode($raw, true) : null;
        $location = $data['result']['location'] ?? null;
        if ((int)($data['status'] ?? -1) !== 0 || !is_array($location) || !is_numeric($location['lng'] ?? null) || !is_numeric($location['lat'] ?? null) || abs((float)$location['lng']) > 180 || abs((float)$location['lat']) > 90) return ['code' => 0, 'msg' => '请求失败'];
        return ['code' => 1, 'msg' => '请求成功', 'data' => ['lng' => $location['lng'], 'lat' => $location['lat'], 'map' => $location['lng'] . ',' . $location['lat']]];
    }
}
