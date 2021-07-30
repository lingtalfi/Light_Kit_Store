<?php


namespace Ling\Light_Kit_Store\Api\Custom\Classes;

use Ling\Bat\PsvTool;
use Ling\Light_Kit_Store\Api\Custom\Interfaces\CustomUserRatesItemApiInterface;
use Ling\Light_Kit_Store\Api\Generated\Classes\UserRatesItemApi;
use Ling\SqlFiddler\SqlFiddlerUtil;


/**
 * The CustomUserRatesItemApi class.
 */
class CustomUserRatesItemApi extends UserRatesItemApi implements CustomUserRatesItemApiInterface
{


    /**
     * Builds the CustomUserRatesItemApi instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @implementation
     * @inheritDoc
     */
    public function getCustomUserRatesItemsByItemId(string $itemId, array $components = []): array
    {
        $markers = [
            ":item_id" => $itemId,
        ];
        $q = "
        select h.*,
               u.rating_name
        from `$this->table` h 
        inner join lks_user u on u.id=h.user_id
        where `item_id`=:item_id
        ";
        $options = $this->fetchRoutine($q, $markers, $components, [
            'whereKeyword' => 'and',
        ]);
        $fetchStyle = null;
        if (true === $options['singleColumn']) {
            $fetchStyle = \PDO::FETCH_COLUMN;
        }

        return $this->pdoWrapper->fetchAll($q, $markers, $fetchStyle);
    }


    /**
     * @implementation
     * @inheritDoc
     */
    public function getUserRatesItemsListByItemId(string $itemId, array $options = []): array
    {

        $search = $options['search'] ?? "";
        $orderBy = $options['orderBy'] ?? "_default";
        $page = $options['page'] ?? 1;
        $pageLength = $options['pageLength'] ?? 50;

        $u = new SqlFiddlerUtil();
        $orderByMap = [
            "_default" => [
                'h.datetime desc',
                'Most recent',
            ],
            "ratings" => [
                'h.rating desc',
                "Highest rating",
            ],
        ];
        $u
            ->setSearchExpression('(
          h.rating_title like :search or 
          h.rating_comment like :search or 
          a.label like :search 
          )', 'search')
            ->setOrderByMap($orderByMap);


        $markers = [];
        $sSearch = $u->getSearchExpression($search, $markers);


        $orderByInfo = $u->getOrderByInfo($orderBy);
        $sOrderBy = $orderByInfo['query'];
        $orderByPublicMap = $orderByInfo['publicMap'];
        $orderByReal = $orderByInfo['real'];


        $q = "
select 

        h.*, 
        i.label,
        a.label as author_label,
        a.author_name

        -- endselect

from lks_user_rates_item h
inner join lks_item i on i.id = h.item_id
inner join lks_author a on a.id = h.user_id

where 1
      and $sSearch

order by $sOrderBy
limit 0, 1 -- endlimit



        ";


        $info = $u->fetchAllCountInfo($this->pdoWrapper, $q, $markers, $page, $pageLength, true);
        $info['orderByPublicMap'] = $orderByPublicMap;
        $info['orderByReal'] = $orderByReal;
        return $info;
    }


}
