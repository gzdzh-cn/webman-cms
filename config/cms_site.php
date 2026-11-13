<?php
return array_combine(['base_path','base_url','theme','backup_path'], array_map(
    [\app\cms\config\SiteSettings::class, 'get'], ['base_path','base_url','theme','backup_path']
));
