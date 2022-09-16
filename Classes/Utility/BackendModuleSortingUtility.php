<?php

declare(strict_types=1);

namespace T3\Dce\Utility;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class BackendModuleSortingUtility
{
    /**
     * @param $request
     * @return array
     */
    public static function getSortingAndOrdering($request): array
    {
        $args = $request->getArguments();

        $sort = $args['sort'] ?? 'sorting';
        $order = isset($args['order']) && $args['order'] ? QueryInterface::ORDER_DESCENDING : QueryInterface::ORDER_ASCENDING;

        return ['sort' => $sort, 'order' => $order];
    }
}
