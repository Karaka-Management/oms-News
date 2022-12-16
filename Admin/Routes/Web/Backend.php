<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\News\Controller\BackendController;
use Modules\News\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/news/dashboard.*$' => [
        [
            'dest'       => '\Modules\News\Controller\BackendController:viewNewsDashboard',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::NEWS,
            ],
        ],
    ],
    '^.*/news/article.*$' => [
        [
            'dest'       => '\Modules\News\Controller\BackendController:viewNewsArticle',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::NEWS,
            ],
        ],
    ],
    '^.*/news/archive.*$' => [
        [
            'dest'       => '\Modules\News\Controller\BackendController:viewNewsArchive',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::NEWS,
            ],
        ],
    ],
    '^.*/news/draft/list.*$' => [
        [
            'dest'       => '\Modules\News\Controller\BackendController:viewNewsDraftList',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::MODIFY,
                'state'  => PermissionCategory::NEWS,
            ],
        ],
    ],
    '^.*/news/create.*$' => [
        [
            'dest'       => '\Modules\News\Controller\BackendController:viewNewsCreate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::NEWS,
            ],
        ],
    ],
    '^.*/news/edit.*$' => [
        [
            'dest'       => '\Modules\News\Controller\BackendController:viewNewsEdit',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::MODIFY,
                'state'  => PermissionCategory::NEWS,
            ],
        ],
    ],
    '^.*/news/analysis.*$' => [
        [
            'dest'       => '\Modules\News\Controller\BackendController:viewNewsAnalysis',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::MODIFY,
                'state'  => PermissionCategory::ANALYSIS,
            ],
        ],
    ],
];
