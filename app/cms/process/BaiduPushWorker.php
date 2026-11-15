<?php
declare(strict_types=1);
namespace app\cms\process;

class BaiduPushWorker extends BackgroundWorker
{
    public function __construct() { parent::__construct('push'); }
}
