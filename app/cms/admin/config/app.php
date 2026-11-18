<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

return [
    'debug' => \app\cms\config\DeploymentConfig::get('app.debug'),
    'controller_suffix' => 'Controller',
    'controller_reuse' => false,
    'public_path' => \app\cms\Paths::publicRoot(),
    'plugin_market_host' => 'https://www.workerman.net',
    'version' => '0.6.33',
];
