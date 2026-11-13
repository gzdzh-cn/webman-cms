<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\CmsContext;
use app\cms\ThemeCatalog;
use app\cms\UrlGenerator;
use app\cms\compat\Runtime;

/** Read-only page metadata for themes that explicitly share one responsive template. */
final class PagePresentationService
{
    public function present(CmsContext $context): array
    {
        $theme = ThemeCatalog::configured($context->global['web_tpl_theme'] ?? null);
        $sections = ThemeCatalog::metadata($theme)['sections'] ?? [];
        $chain = $context->tid > 0 ? Runtime::model('Arctype')->getAllPid($context->tid) : [];
        $chain = is_array($chain) ? array_values($chain) : [];
        $top = (int)($chain[0]['id'] ?? $context->tid);
        $section = array_search($top, is_array($sections) ? $sections : [], true) ?: '';
        $search = strcasecmp($context->controller, 'Search') === 0;
        $error = $context->controller === 'Error';
        $detail = !$error && !$search && strcasecmp($context->controller, 'View') === 0;
        $home = !$search && !$error && $context->tid === 0 && $context->aid === 0;
        $kind = $error ? 'error' : ($search ? 'search' : ($home ? 'home' : ($detail ? ($section === 'products' ? 'product-detail' : ($section === 'cases' ? 'case-detail' : 'article')) : ($section === 'products' ? 'products' : ($section === 'cases' ? 'cases' : ($section === 'news' ? 'news' : 'single'))))));
        $origin = $this->trustedOrigin($context);
        $homeUrl = (string)($context->global['lang_url'] ?? '');
        if ($homeUrl === '') $homeUrl = rtrim((string)($context->global['web_cmspath'] ?? ''), '/') . '/';
        $searchUrl = UrlGenerator::build('home/Search/index');
        $route = $context->field['pageurl'] ?? ($detail ? ($context->field['arcurl'] ?? '') : ($context->field['typeurl'] ?? $homeUrl));
        if ($search) $route = UrlGenerator::build('home/Search/' . ($context->action === 'index' ? 'index' : 'lists'));
        $params = [];
        foreach (['lang','site','page'] as $key) {
            $v = $context->input('param.' . $key);
            if (is_scalar($v) && (string)$v !== '' && ($key !== 'page' || ((string)$v === (string)(int)$v && (int)$v > 1))) $params[$key] = (string)$v;
        }
        if ($search && isset($context->field['keywords'])) $params['keywords'] = (string)$context->field['keywords'];
        $canonical = $this->url((string)$route, $origin, $params);
        $name = $this->text($context->global['web_name'] ?? '');
        $title = $this->text($context->field[$detail ? 'title' : 'typename'] ?? $name);
        if ($search) $title = '站内搜索';
        if ($error) $title = '页面未找到';
        $seoTitle = $this->text($context->field['seo_title'] ?? '') ?: $this->text($context->global['web_title'] ?? $name);
        if ($search || $error) $seoTitle = $title . '_' . $name;
        $crumbs = [['name'=>'首页','url'=>$homeUrl]];
        foreach ($chain as $category) $crumbs[] = ['name'=>$this->text($category['typename'] ?? ''),'url'=>(string)($category['typeurl'] ?? '')];
        if ($detail) $crumbs[] = ['name'=>$title,'url'=>$canonical];
        if ($search || $error) $crumbs[] = ['name'=>$title,'url'=>$search ? $searchUrl : ''];
        $published = $this->date($context->field['add_time'] ?? null);
        $modified = $this->date($context->field['update_time'] ?? null);
        $organization = ['@type'=>'Organization','@id'=>$this->url($homeUrl,$origin).'#organization','name'=>$name,'url'=>$this->url($homeUrl,$origin)];
        $logo = (string)($context->global['web_logo'] ?? '');
        if ($logo !== '') $organization['logo'] = $this->url($logo,$origin);
        if (!empty($context->global['web_attr_5'])) $organization['telephone'] = $this->text($context->global['web_attr_5']);
        if (!empty($context->global['web_attr_8'])) $organization['address'] = $this->text($context->global['web_attr_8']);
        $graph = [$organization];
        if (!$home && !$error) {
            $items=[];
            foreach ($crumbs as $i=>$crumb) {
                $item=['@type'=>'ListItem','position'=>$i+1,'name'=>$crumb['name']];
                if ($crumb['url'] !== '') $item['item']=$this->url($crumb['url'],$origin);
                $items[]=$item;
            }
            $graph[]=['@type'=>'BreadcrumbList','itemListElement'=>$items];
        }
        if ($detail && $section === 'news') {
            $article=['@type'=>'Article','headline'=>$title,'mainEntityOfPage'=>['@type'=>'WebPage','@id'=>$canonical],'publisher'=>['@id'=>$organization['@id']]];
            if ($published) $article['datePublished']=$published;
            if ($modified) $article['dateModified']=$modified;
            if (!empty($context->field['author'])) $article['author']=['@type'=>'Organization','name'=>$this->text($context->field['author'])];
            if (!empty($context->field['litpic'])) $article['image']=[$this->url((string)$context->field['litpic'],$origin)];
            $graph[]=$article;
        }
        $aboutIntro = '';
        if ($home && !empty($sections['about'])) {
            $about = \app\cms\compat\Db::name('single_content')->where('typeid', (int)$sections['about'])->value('content');
            $aboutIntro = trim(strip_tags($this->text($about)));
            // The legacy body starts with SMC's history; the homepage introduces the actual company.
            $start = mb_strpos($aboutIntro, $name . '是一家');
            if ($name !== '' && $start !== false) $aboutIntro = mb_substr($aboutIntro, $start);
            $aboutIntro = mb_substr($aboutIntro, 0, 210);
        }
        $eyebrow = match ($kind) {
            'products'=>'PRODUCT CENTER', 'product-detail'=>'PRODUCT DETAIL',
            'cases','case-detail'=>'CUSTOMER CASES', 'news','article'=>'NEWS & INSIGHTS',
            'search'=>'SITE SEARCH', 'error'=>'PAGE NOT FOUND', default=>'ABOUT DERUISEN',
        };
        $intro = match ($kind) {
            'products'=>'从产品型号与分类出发，找到适合的气动元件。',
            'product-detail'=>'型号与产品资料，帮助您进行选型咨询。',
            'cases','case-detail'=>'探索工业自动化设备的应用与案例。',
            'news','article'=>'公司动态、产品知识与行业资讯。',
            'search'=>'搜索产品型号、产品名称与站内资讯。',
            'error'=>'您可以返回首页，或继续浏览产品与资讯。',
            default=>'了解德瑞森机电，获取产品与服务信息。',
        };
        if ($kind === 'products' && $context->tid === (int)($sections['products'] ?? 0)) $title = '产品总览';
        $flags=JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE|JSON_THROW_ON_ERROR;
        return ['kind'=>$kind,'section'=>$section,'title'=>$title,'seo_title'=>$seoTitle,
            'description'=>$this->text($context->field['seo_description'] ?? '') ?: $this->text($context->global['web_description'] ?? ''),
            'keywords'=>$this->text($context->field['seo_keywords'] ?? '') ?: $this->text($context->global['web_keywords'] ?? ''),
            'home_url'=>$homeUrl,'search_url'=>$searchUrl,'canonical'=>$error?'':$canonical,
            'robots'=>$search||$error?'noindex,follow':'index,follow','breadcrumbs'=>$crumbs,
            'json_ld'=>$error?'':json_encode(['@context'=>'https://schema.org','@graph'=>$graph],$flags),
            'date_published'=>$published?substr($published,0,10):'','date_modified'=>$modified?substr($modified,0,10):'',
            'published_iso'=>$published,'modified_iso'=>$modified,
            'phone_uri'=>'tel:'.preg_replace('/[^0-9+]/','',(string)($context->global['web_attr_6']??'')),
            'telephone_uri'=>'tel:'.preg_replace('/[^0-9+]/','',(string)($context->global['web_attr_5']??'')),
            'about_intro'=>$aboutIntro, 'eyebrow'=>$eyebrow, 'intro'=>$intro,
            'list_title'=>$context->tid === (int)($sections['products'] ?? 0) ? '全部产品' : $title,
            'content_html'=>$this->content((string)($context->field['content'] ?? '')),
            'record_number'=>strip_tags($this->text($context->global['web_recordnum'] ?? '')),
        ];
    }

    private function text(mixed $value): string
    {
        return trim(html_entity_decode(is_scalar($value) ? (string)$value : '',ENT_QUOTES|ENT_HTML5,'UTF-8'));
    }

    private function date(mixed $value): string
    {
        return is_numeric($value) && (int)$value > 0 ? date(DATE_ATOM,(int)$value) : '';
    }

    private function trustedOrigin(CmsContext $context): string
    {
        $value=$context->memo['cms.configured_basehost'] ?? $context->global['web_basehost'] ?? '';
        foreach ([$value,\app\cms\config\SiteSettings::get('base_url')] as $candidate) {
            if (!is_string($candidate) || preg_match('/[\x00-\x20\x7f\\\\]/',$candidate)) continue;
            $parts=parse_url($candidate);
            if (!$parts || !in_array(strtolower($parts['scheme']??''),['http','https'],true) || empty($parts['host']) || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) continue;
            return strtolower($parts['scheme']).'://'.strtolower($parts['host']).(isset($parts['port'])?':'.$parts['port']:'');
        }
        throw new \RuntimeException('请配置有效的站点域名');
    }

    /** Presentation only: preserve the stored body while reserving image space. */
    private function content(string $html): string
    {
        if ($html === '') return '';
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $dom->loadHTML('<?xml encoding="UTF-8"><div id="responsive-body">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
            foreach ($dom->getElementsByTagName('img') as $image) {
                if (!$image->hasAttribute('alt')) $image->setAttribute('alt', '');
                $image->setAttribute('loading', 'lazy');
                $image->setAttribute('decoding', 'async');
                $source = parse_url($image->getAttribute('src'), PHP_URL_PATH);
                if (!is_string($source) || !str_starts_with($source, '/uploads/') || str_contains($source, '..')) continue;
                $file = \app\cms\Paths::uploads(substr($source, strlen('/uploads/')));
                if (!is_file($file)) continue;
                $size = @getimagesize($file);
                if ($size) { $image->setAttribute('width', (string)$size[0]); $image->setAttribute('height', (string)$size[1]); }
            }
            foreach (iterator_to_array($dom->getElementsByTagName('h1')) as $heading) {
                $replacement = $dom->createElement('h2');
                while ($heading->firstChild) $replacement->appendChild($heading->firstChild);
                $heading->parentNode->replaceChild($replacement, $heading);
            }
            $body = $dom->getElementById('responsive-body');
            if (!$body) return $html;
            $result=''; foreach ($body->childNodes as $node) $result .= $dom->saveHTML($node);
            return $result;
        } finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
    }

    /** Route-derived paths plus a small query allowlist; never trust the request Host. */
    public function url(string $route, string $origin, array $parameters=[]): string
    {
        $route=html_entity_decode($route,ENT_QUOTES|ENT_HTML5,'UTF-8');
        $parts=parse_url($route);$parts=is_array($parts)?$parts:[];
        $path=$parts['path']??'/';
        if ($path==='' || $path[0]!=='/') $path='/'.$path;
        $path=preg_replace('/[\x00-\x20\x7f]/','',$path);
        parse_str($parts['query']??'',$query);
        $query=array_intersect_key(array_replace($query,$parameters),array_flip(['m','c','a','tid','aid','lang','site','page','keywords','channelid','typeid']));
        foreach($query as$key=>$value)if(!is_scalar($value)||($key==='page'&&(!ctype_digit((string)$value)||(int)$value<=1)))unset($query[$key]);
        ksort($query);
        return rtrim($origin,'/').$path.($query?'?'.http_build_query($query,'','&',PHP_QUERY_RFC3986):'');
    }
}
