<?php


namespace Ling\Light_Kit_Store\Controller\Front;


use Ling\Light\Http\HttpJsonResponse;
use Ling\Light\Http\HttpRequestInterface;
use Ling\Light\Http\HttpResponseInterface;


/**
 * The StoreRatingListController class.
 */
class StoreRatingListController extends StoreProductPageController
{


    /**
     * Renders the home page, and returns the appropriate http response.
     *
     * @param HttpRequestInterface $request
     * @return HttpResponseInterface
     */
    public function render(HttpRequestInterface $request): HttpResponseInterface
    {

        $itemId = $request->getGetValue("id") ?? null;
        if (null === $itemId) {
            return $this->getRedirectResponse("404");
        }

        $item = $this->getItem($itemId, [
            'useReviews' => false,
        ]);


        if (null === $item) {
            return $this->getRedirectResponse("404_product");
        }


        return $this->renderPage("Ling.Light_Kit_Store/ratings", [
            "widgetVariables" => [
                "body.kitstore_ratings" => [
                    "item" => $item,
                    "orderBy" => "_default",
                    "orderByLabel" => "Most recent",
                    "orderByPublicMap" => [
                        "_default" => "Most recent",
                        "ratings" => "Highest rating",
                    ],
                    "ratingFilter" => "all",
                    "ratingFilterLabel" => "All stars",
                    "ratingFilterMap" => [
                        'all' => 'All stars',
                        '5' => '5 star only',
                        '4' => '4 star only',
                        '3' => '3 star only',
                        '2' => '2 star only',
                        '1' => '1 star only',
                    ],
                ],
            ],
            "dynamicVariables" => [
                "product_label" => "the product label",
            ]
        ]);
    }


    /**
     * Renders the rating items.
     *
     * This is an @page(alcp service).
     *
     *
     * The input parameters are passed via POST:
     *
     * - item_id: int, the id of the item to extract the reviews from.
     * - page: int=1, the number of the page to display (in case there are a lot of reviews)
     * - search: string=''. An expression to filter the results. We search in the reviews titles and comments. If empty, no filter is applied.
     * - sort: string=_default, the sort used. Can be one of: _default (most recent to oldest), ratings (highest ratings desc)
     * - rating_filter: string=all, can be either the special string "all" for all ratings, or a number from 1 to 5 to filter by the rating.
     *
     *
     *
     * In case of success, the returned array contains the following properties:
     *
     * - html: string, the html representing the customer items
     * - nbRatings: int, the number of ratings returned by the (user driven) query
     * - nbReviews: int, the number of reviews returned by the (user driven) query
     *
     *
     * @param HttpRequestInterface $request
     * @return HttpJsonResponse
     */
    public function renderRatings(HttpRequestInterface $request): HttpJsonResponse
    {

        $itemId = $request->getPostValue("item_id");


        $search = $request->getPostValue("search") ?? "";
        $sort = $request->getPostValue("sort") ?? "_default";
        $page = $request->getPostValue("page") ?? 1;
        $ratingFilter = $request->getPostValue("rating_filter") ?? "all";


        $error = null;

        try {


            $f = $this->getKitStoreService()->getFactory();
            $uriApi = $f->getUserRatesItemApi();
            $items = $uriApi->getUserRatesItemsListByItemId($itemId, [
                'search' => $search,
                'orderBy' => $sort,
                'page' => $page,
                'pageLength' => 8,
            ]);

            az($items);


        } catch (\Exception $e) {
            $error = "An exception occurred. See the logs for more information. Sorry for the inconvenience.";
            $this->logError($e);
        }

        $html = 0;
        $nbRatings = 0;
        $nbReviews = 0;


        if (null !== $error) {
            return HttpJsonResponse::create([
                "type" => "error",
                "error" => $error,
            ]);
        }


        return HttpJsonResponse::create([
            "type" => "success",
            "html" => $html,
            "nbRatings" => "success",
            "nbReviews" => "success",
        ]);


    }
}

