<?php
declare(strict_types=1);
namespace app\cms\process;

class StaticGenerationWorker extends BackgroundWorker
{
    public function __construct() { parent::__construct('generation'); }
}
