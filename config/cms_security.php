<?php
/** Store only the external private key path here; never the key itself. */
return [
    'key_file' => (string)\app\cms\config\Environment::value('CMS_SECURITY_KEY_FILE', '/Volumes/disk/site/php/dzhcms/cms-private/dzhcms002.key'),
];
