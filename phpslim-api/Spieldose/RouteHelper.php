<?php

declare(strict_types=1);

namespace Spieldose;

class RouteHelper
{
    public static function getPagerFromParams(array $params = []): \aportela\DatabaseBrowserWrapper\Pager
    {
        $currentPageIndex = 1;
        $resultsPage = \Spieldose\Browse\Base::DEFAULT_RESULTS_PAGE;
        if (array_key_exists("pager", $params)) {
            if (array_key_exists("currentPageIndex", $params["pager"]) && is_numeric($params["pager"]["currentPageIndex"])) {
                $currentPageIndex = intval($params["pager"]["currentPageIndex"]);
            }

            if (array_key_exists("resultsPage", $params["pager"]) && is_numeric($params["pager"]["resultsPage"])) {
                $resultsPage = intval($params["pager"]["resultsPage"]);
            }
        }
        return (new \aportela\DatabaseBrowserWrapper\Pager(true, $currentPageIndex, $resultsPage));
    }

    public static function getSortFromParams(array $params = [], string $defaultSortField = '', ?\aportela\DatabaseBrowserWrapper\Order $defaultSortOrder = null, bool $caseInsensitive = false): \aportela\DatabaseBrowserWrapper\Sort
    {
        $sortItem = null;
        if (array_key_exists("sort", $params)) {
            $sortItem = new \aportela\DatabaseBrowserWrapper\SortItem(
                array_key_exists("field", $params["sort"]) && is_string($params["sort"]["field"]) ? $params["sort"]["field"] : $defaultSortField,
                array_key_exists("order", $params["sort"]) && is_string($params["sort"]["order"]) && in_array($params["sort"]["order"], ["ASC", "DESC"]) ? \aportela\DatabaseBrowserWrapper\Order::from($params["sort"]["order"]) : $defaultSortOrder,
                $caseInsensitive
            );
        } else {
            $sortItem = new \aportela\DatabaseBrowserWrapper\SortItem(
                $defaultSortField,
                $defaultSortOrder,
                $caseInsensitive
            );
        }

        return (new \aportela\DatabaseBrowserWrapper\Sort([$sortItem]));
    }

    public static function getFilterFromParams(array $params = []): \aportela\DatabaseBrowserWrapper\Filter
    {
        return (new \aportela\DatabaseBrowserWrapper\Filter(array_key_exists("filter", $params) && is_array($params["filter"]) ? $params["filter"] : []));
    }

    public static function skipCountTrueParamFound(array $params): bool
    {
        return (array_key_exists("skipCount", $params) && is_bool($params["skipCount"]) && $params["skipCount"] === true);
    }
}
