<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

use Modules\News\Controller\BackendController;
use Modules\News\Models\PermissionState;
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
                'state'  => PermissionState::NEWS,
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
                'state'  => PermissionState::NEWS,
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
                'state'  => PermissionState::NEWS,
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
                'state'  => PermissionState::NEWS,
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
                'state'  => PermissionState::NEWS,
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
                'state'  => PermissionState::NEWS,
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
                'state'  => PermissionState::ANALYSIS,
            ],
        ],
    ],
];
