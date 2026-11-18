<?php
// Compatibility identity only; actual CMS Redis access uses request-pinned settings.
return ['default'=>\app\cms\config\DeploymentConfig::group('redis')];
