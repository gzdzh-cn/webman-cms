<?php
declare(strict_types=1);
namespace app\cms\service;

/** Internal cancellation, never a provider/transport failure. */
final class PushCancelled extends \RuntimeException {}
