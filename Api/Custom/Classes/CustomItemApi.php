<?php


namespace Ling\Light_Kit_Store\Api\Custom\Classes;

use Ling\BabyYaml\BabyYamlUtil;
use Ling\Bat\FileSystemTool;
use Ling\Bat\ImageTool;
use Ling\Bat\PsvTool;
use Ling\Light_Kit_Store\Api\Custom\Interfaces\CustomItemApiInterface;
use Ling\Light_Kit_Store\Api\Generated\Classes\ItemApi;
use Ling\Light_Kit_Store\Exception\LightKitStoreException;
use Ling\SqlFiddler\SqlFiddlerUtil;


/**
 * The CustomItemApi class.
 */
class CustomItemApi extends ItemApi implements CustomItemApiInterface
{


    /**
     * Builds the CustomItemApi instance.
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @implementation
     */
    public function getProductListItems(array $options = []): array
    {

        $authorName = $options['author'] ?? null;
        $status = $options['status'] ?? "1";
        $status = (int)$status; // this is a dev option, but still...


        $search = $options['search'] ?? "";
        $orderBy = $options['orderBy'] ?? "_default";
        $page = $options['page'] ?? 1;
        $pageLength = $options['pageLength'] ?? 50;
        $itemTypes = $options['itemTypes'] ?? "*";
        if (
            '*' === $itemTypes
        ) {
            $itemTypes = [
                1,
                2,
                3,
            ];
        }
        $sItemTypes = PsvTool::implode(",", $itemTypes, 's');


        $u = new SqlFiddlerUtil();
        $orderByMap = [
            "_default" => [
                'i.front_importance desc, i.id asc',
                'Featured',
            ],
            "price_increasing" => [
                'i.price_in_euro asc',
                'Price: Low to High',
            ],
            "price_decreasing" => [
                'i.price_in_euro desc',
                'Price: High to Low',
            ],
            "avg_rating" => [
                't2.avg_rating desc',
                'Avg. Customer Review',
            ],
            "newest" => [
                'i.post_datetime desc',
                "Newest",
            ],
        ];
        $u
            ->setSearchExpression('(
          i.label like :search or 
          i.reference like :search 
          )', 'search')
            ->setOrderByMap($orderByMap);


        $markers = [];
        $sSearch = $u->getSearchExpression($search, $markers);


        $orderByInfo = $u->getOrderByInfo($orderBy);
        $sOrderBy = $orderByInfo['query'];
        $orderByPublicMap = $orderByInfo['publicMap'];
        $orderByReal = $orderByInfo['real'];


        $sAuthor = '1';
        if (null !== $authorName) {
            $sAuthor = 'a.author_name=:author_name';
            $markers[':author_name'] = $authorName;
        }


        $q = "
select 

        i.id, i.label, i.reference, i.price_in_euro, i.screenshots,
        a.label as author_label,
        a.author_name,
       
       group_concat(concat(t.rating, ':', t.nb_ratings) order by t.rating separator ', ') as ratings,
       
       t2.avg_rating, t2.nb_ratings

        -- endselect

from lks_item i
    
    
    
         inner join (
    select item_id,
           rating,
           count(*) as nb_ratings
    from lks_user_rates_item
    group by rating, item_id
) as t on i.id = t.item_id


         inner join (
    select item_id,
           avg(rating) as avg_rating,
           count(*) as nb_ratings
    from lks_user_rates_item
    group by item_id
) as t2 on i.id = t2.item_id

    inner join lks_author a on i.author_id = a.id

where 
      i.status = '$status'
      and i.item_type IN ($sItemTypes)
      and $sSearch
      and $sAuthor

group by i.id
order by $sOrderBy
limit 0, 1 -- endlimit



        ";



        $info = $u->fetchAllCountInfo($this->pdoWrapper, $q, $markers, $page, $pageLength, true);
        $info['orderByPublicMap'] = $orderByPublicMap;
        $info['orderByReal'] = $orderByReal;
        return $info;
    }


    /**
     * @implementation
     * @inheritDoc
     */
    public function getProductInfoById(int $itemId, array $options = []): array
    {

        $imageSizes = $options['imageSizes'] ?? false;
        $appDir = $this->container->getApplicationDir();


        $q = "
select 

        i.id, i.label, i.reference, i.description,  i.price_in_euro, i.screenshots,
        a.label as author_label,
        a.author_name,
       group_concat(concat(t.rating, ':', t.nb_ratings) order by t.rating separator ', ') as ratings,       
       t2.avg_rating, t2.nb_ratings


from lks_item i
    
    
    
         inner join (
    select item_id,
           rating,
           count(*) as nb_ratings
    from lks_user_rates_item
    group by rating, item_id
) as t on i.id = t.item_id


         inner join (
    select item_id,
           avg(rating) as avg_rating,
           count(*) as nb_ratings
    from lks_user_rates_item
    group by item_id
) as t2 on i.id = t2.item_id

    inner join lks_author a on i.author_id = a.id

where 
      i.id = $itemId




        ";

//        az($q, $markers);

        $res = $this->pdoWrapper->fetch($q);
        if (false !== $res) {


            //--------------------------------------------
            // screenshots
            //--------------------------------------------
            $screenshots = BabyYamlUtil::readBabyYamlString($res['screenshots']);
            $items = [];
            foreach ($screenshots as $screenshot) {

                $item = [];

                $type = "photo";
                $subType = null;
                if (true === str_starts_with($screenshot, 'yt:')) {
                    $type = "video/youtube";

                }


                $ext = strtolower(FileSystemTool::getFileExtension($screenshot));
                if ('mp4' === $ext) {
                    $type = "video/mp4";
                }


                $item["type"] = $type;

                if ('photo' === $type) {
                    $item["url"] = $screenshot;
                    $item['thumb'] = str_replace('/medium/', "/thumb/", $screenshot);
                    $item['large'] = str_replace('/medium/', "/large/", $screenshot);

                    if (true === $imageSizes) {
                        $f = $appDir . "/www" . $item['large'];
                        $width = 0;
                        $height = 0;
                        if (true === file_exists($f)) {
                            list($width, $height) = ImageTool::getDimensions($f);
                        }

                        $item['largeWidth'] = $width;
                        $item['largeHeight'] = $height;
                    }

                } elseif ('video/mp4' === $type) {
                    $item["url"] = $screenshot;
                    $thumb = str_replace('/video/', "/thumb/", $screenshot);
                    $thumb = substr($thumb, 0, -4) . ".jpg";
                    $poster = str_replace('/thumb/', "/poster/", $thumb);

                    $item['thumb'] = $thumb;
                    $item['poster'] = $poster;
                } elseif ('video/youtube' === $type) {
                    $p = explode(":", $screenshot, 2);
                    $videoId = trim(array_pop($p));
                    $item['videoId'] = $videoId;
                } else {
                    throw new LightKitStoreException("Unknown visual element type: $type.");
                }


                $items[] = $item;
            }
            $res['screenshots'] = $items;

        }
        return $res;
    }


}
