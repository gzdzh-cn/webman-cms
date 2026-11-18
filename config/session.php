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

use Webman\Session\FileSessionHandler;
use Webman\Session\RedisSessionHandler;
use Webman\Session\RedisClusterSessionHandler;

return [

    'type' => 'file', // or redis or redis_cluster

    'handler' => \app\cms\security\SessionStorageHandler::class,

    'config' => ['file'=>['save_path'=>\app\cms\Paths::runtime('sessions')]],

    'session_name' => \app\cms\config\DeploymentConfig::get('session.name'),
    
    'auto_update_timestamp' => false,

    'lifetime' => \app\cms\config\DeploymentConfig::get('session.lifetime'),

    'cookie_lifetime' => \app\cms\config\DeploymentConfig::get('session.cookie_lifetime'),

    'cookie_path' => \app\cms\config\DeploymentConfig::get('session.cookie_path'),

    'domain' => \app\cms\config\DeploymentConfig::get('session.domain'),
    
    'http_only' => \app\cms\config\DeploymentConfig::get('session.http_only'),

    'secure' => \app\cms\config\DeploymentConfig::get('session.secure'),
    
    'same_site' => \app\cms\config\DeploymentConfig::get('session.same_site'),

    'gc_probability' => [1, 1000],

];
