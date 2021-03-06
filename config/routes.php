<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\Frontend\HomeController@index');

/**
 * backend接口
 */
Router::addGroup('/backend/', function () {
    require_once BASE_PATH . '/config/routes/backend.php';
});

/**
 * frontend接口
 */
Router::addGroup('/frontend/', function () {
    require_once BASE_PATH . '/config/routes/frontend.php';
});