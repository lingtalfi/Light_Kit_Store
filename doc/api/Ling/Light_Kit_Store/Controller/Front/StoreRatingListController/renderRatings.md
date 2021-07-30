[Back to the Ling/Light_Kit_Store api](https://github.com/lingtalfi/Light_Kit_Store/blob/master/doc/api/Ling/Light_Kit_Store.md)<br>
[Back to the Ling\Light_Kit_Store\Controller\Front\StoreRatingListController class](https://github.com/lingtalfi/Light_Kit_Store/blob/master/doc/api/Ling/Light_Kit_Store/Controller/Front/StoreRatingListController.md)


StoreRatingListController::renderRatings
================



StoreRatingListController::renderRatings — Renders the rating items.




Description
================


public [StoreRatingListController::renderRatings](https://github.com/lingtalfi/Light_Kit_Store/blob/master/doc/api/Ling/Light_Kit_Store/Controller/Front/StoreRatingListController/renderRatings.md)(Ling\Light\Http\HttpRequestInterface $request) : [HttpJsonResponse](https://github.com/lingtalfi/Light/blob/master/doc/api/Ling/Light/Http/HttpJsonResponse.md)




Renders the rating items.

This is an [alcp service](https://github.com/lingtalfi/TheBar/blob/master/discussions/alcp-service.md).


The input parameters are passed via POST:

- item_id: int, the id of the item to extract the reviews from.
- page: int=1, the number of the page to display (in case there are a lot of reviews)
- search: string=''. An expression to filter the results. We search in the reviews titles and comments. If empty, no filter is applied.
- sort: string=_default, the sort used. Can be one of: _default (most recent to oldest), ratings (highest ratings desc)
- rating_filter: string=all, can be either the special string "all" for all ratings, or a number from 1 to 5 to filter by the rating.



In case of success, the returned array contains the following properties:

- html: string, the html representing the customer items
- nbRatings: int, the number of ratings returned by the (user driven) query
- nbReviews: int, the number of reviews returned by the (user driven) query




Parameters
================


- request

    


Return values
================

Returns [HttpJsonResponse](https://github.com/lingtalfi/Light/blob/master/doc/api/Ling/Light/Http/HttpJsonResponse.md).








Source Code
===========
See the source code for method [StoreRatingListController::renderRatings](https://github.com/lingtalfi/Light_Kit_Store/blob/master/Controller/Front/StoreRatingListController.php#L98-L153)


See Also
================

The [StoreRatingListController](https://github.com/lingtalfi/Light_Kit_Store/blob/master/doc/api/Ling/Light_Kit_Store/Controller/Front/StoreRatingListController.md) class.

Previous method: [render](https://github.com/lingtalfi/Light_Kit_Store/blob/master/doc/api/Ling/Light_Kit_Store/Controller/Front/StoreRatingListController/render.md)<br>

