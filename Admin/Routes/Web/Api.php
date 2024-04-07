<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\News\Controller\ApiController;
use Modules\News\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/news(\?.*$|$)' => [
        [
            'dest'       => '\Modules\News\Controller\ApiController:apiNewsCreate',
            'verb'       => RouteVerb::PUT,
            'csrf'       => true,
            'active'     => true,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::NEWS,
            ],
        ],
        [
            'dest'       => '\Modules\News\Controller\ApiController:apiNewsUpdate',
            'verb'       => RouteVerb::SET,
            'csrf'       => true,
            'active'     => true,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::MODIFY,
                'state'  => PermissionCategory::NEWS,
            ],
        ],
        [
            'dest'       => '\Modules\News\Controller\ApiController:apiNewsGet',
            'verb'       => RouteVerb::GET,
            'csrf'       => true,
            'active'     => true,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::NEWS,
            ],
        ],
        [
            'dest'       => '\Modules\News\Controller\ApiController:apiNewsDelete',
            'verb'       => RouteVerb::DELETE,
            'csrf'       => true,
            'active'     => true,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::DELETE,
                'state'  => PermissionCategory::NEWS,
            ],
        ],
    ],
];
