<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\News
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\News\Controller\SearchController;
use Modules\News\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^(?!:).+.*?' => [
        [
            'dest'       => '\Modules\News\Controller\SearchController:searchGeneral',
            'verb'       => RouteVerb::ANY,
            'active'     => true,
            'permission' => [
                'module' => SearchController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::NEWS,
            ],
        ],
    ],
    '^:tag .*?' => [
        [
            'dest'       => '\Modules\News\Controller\SearchController:searchTag',
            'verb'       => RouteVerb::ANY,
            'active'     => true,
            'permission' => [
                'module' => SearchController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::NEWS,
            ],
        ],
    ],
];
