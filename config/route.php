<?php
use Webman\Route;
use app\controller\CmsController;

// Only explicit front routes and the CMS fallback are public; implementation classes are private.
Route::disableDefaultRoute();
Route::get('/install', [\app\cms\install\InstallerController::class, 'index']);
Route::get('/install/', [\app\cms\install\InstallerController::class, 'index']);
Route::get('/install/status', [\app\cms\install\InstallerController::class, 'status']);
Route::get('/install/handoff/status', [\app\cms\install\InstallerController::class, 'handoffStatus']);
Route::post('/install/handoff/retry', [\app\cms\install\InstallerController::class, 'handoffRetry']);
Route::post('/install/handoff/login', [\app\cms\install\InstallerController::class, 'handoffLogin']);
Route::post('/install', [\app\cms\install\InstallerController::class, 'install']);
// Legacy EyouCMS installer URL, served by the same guarded application controller.
Route::get('/install/index.php', [\app\cms\install\InstallerController::class, 'index']);
Route::post('/install/index.php', [\app\cms\install\InstallerController::class, 'install']);


// Backend is an application module; no physical plugin discovery is required.
require base_path('app/cms/admin/config/route.php');

// Exact registry lookup inside each static namespace; never route admin APIs here.
foreach (['admin-assets', 'component', 'admin', 'plugins', 'static', 'errpage', 'app/admin/component', 'app/admin/admin', 'app/admin/plugins', 'app/admin/static', 'app/admin/errpage'] as $assetPrefix) {
    Route::add(['GET', 'HEAD'], '/' . $assetPrefix . '/{file:.+}', [CmsController::class, 'asset']);
}

$base = trim((string)\app\cms\config\SiteSettings::get('base_path'), '/');
if ($base !== '') {
    // Register the deployment prefix before Webman's conventional controller
    // lookup can interpret /cms/ as CmsController::index and discard dispatch.
    foreach (['/' . $base, '/' . $base . '/', '/' . $base . '/index.php'] as $entry) {
        Route::any($entry, [CmsController::class, 'dispatch']);
    }
    Route::any('/' . $base . '/{path:.+}', [CmsController::class, 'resolve']);
}

Route::any('/', [CmsController::class, 'dispatch']);
Route::any('/index.php', [CmsController::class, 'dispatch']);
Route::get('/lists', [CmsController::class, 'lists']);
Route::get('/view/{id}', [CmsController::class, 'view']);
Route::any('/search', [CmsController::class, 'search']);
Route::get('/tags', [CmsController::class, 'tags']);
Route::get('/tags/{id:\d+}.html', [CmsController::class, 'tags']);
foreach (['template', 'uploads', 'upload', 'public'] as $prefix) {
    Route::get('/' . $prefix . '/{file:.+}', [CmsController::class, 'asset']);
}
foreach (['upload', 'uploads'] as $prefix) {
    Route::get('/app/admin/' . $prefix . '/avatar/{file:.+}', [CmsController::class, 'asset']);
}
\app\cms\admin\support\AdminRoutes::registerFallback();
Route::any('/index.php/{path:.+}', [CmsController::class, 'resolve']);
// Webman fallback callbacks skip global middleware; retain the same request
// context and outgoing language/city cookies as explicitly registered routes.
Route::fallback(static fn(support\Request $request) => (new CmsController())->resolve($request))
    ->middleware([app\middleware\CmsContextMiddleware::class]);
