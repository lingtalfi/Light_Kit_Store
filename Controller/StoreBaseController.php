<?php


namespace Ling\Light_Kit_Store\Controller;


use Ling\Light\Controller\LightController;
use Ling\Light\Http\HttpRedirectResponse;
use Ling\Light\Http\HttpResponseInterface;
use Ling\Light_Kit\PageRenderer\LightKitPageRenderer;
use Ling\Light_Kit_Editor\Service\LightKitEditorService;
use Ling\Light_Kit_Store\Service\LightKitStoreService;
use Ling\Light_ReverseRouter\Service\LightReverseRouterService;
use Ling\Light_UserManager\Service\LightUserManagerService;
use Ling\Light_Vars\Service\LightVarsService;


/**
 * The StoreBaseController class.
 *
 */
abstract class StoreBaseController extends LightController
{

    /**
     * Renders the given page using the @page(kit service).
     * Options are directly forwarded to @page(the LightKitPageRenderer->renderPage method).
     *
     * @param string $page
     * @param array $options
     * @return HttpResponseInterface
     * @throws \Exception
     *
     */
    public function renderPage(string $page, array $options = []): HttpResponseInterface
    {
        $this->setControllerGlobalVar("controller", $this);

        $websiteId = "Ling.Light_Kit_Store.front"; // should this be hardcoded?


        /**
         * @var $_ke LightKitEditorService
         */
        $_ke = $this->getContainer()->get("kit_editor");


        $widgetVariables = $options['widgetVariables'] ?? [];


        /**
         * @var $_um LightUserManagerService
         */
        $_um = $this->getContainer()->get("user_manager");
        $user = $_um->getOpenUser();
        $widgetVariables["header.kitstore_header"]["user"] = $user;


        // --
        $options['widgetVariables'] = $widgetVariables;


        return $_ke->renderPage($websiteId, $page, $options);

//        $kit = $this->getKitPageRendererInstance();
//        return new HttpResponse($kit->renderPage($page, $options));
    }


    /**
     * Proxy to the reverse router's getUrl method.
     * This method is designed to be used inside (kit) templates.
     *
     *
     * @param string $routeName
     * @param array $urlParams
     * @param bool $useAbsolute
     * @return string
     * @throws \Exception
     */
    public function getLink(string $routeName, array $urlParams = [], bool $useAbsolute = false): string
    {

        /**
         * @var $_rr LightReverseRouterService
         */
        $_rr = $this->getContainer()->get("reverse_router");
        return $_rr->getUrl($routeName, $urlParams, $useAbsolute);
    }





    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * Sets a variable globally, with the "controller" namespace.
     *
     * This is part of the global controller vars convention.
     * https://github.com/lingtalfi/TheBar/blob/master/discussions/global-controller-vars.md
     *
     *
     *
     * @param string $key
     * @param $value
     * @throws \Exception
     */
    protected function setControllerGlobalVar(string $key, $value)
    {
        /**
         * @var $_va LightVarsService
         */
        $_va = $this->getContainer()->get("vars");
        $_va->setVar("controller.$key", $value);
    }


    /**
     * Returns the kit store service instance.
     *
     * @return LightKitStoreService
     * @throws \Exception
     */
    protected function getKitStoreService(): LightKitStoreService
    {
        return $this->getContainer()->get("kit_store");
    }

    /**
     * Returns a redirect response based on the given type.
     * Available types are:
     * - 404
     * - 404_product
     *
     *
     *
     * @param string $type
     * @return HttpResponseInterface
     */
    protected function getRedirectResponse(string $type): HttpResponseInterface
    {
        /**
         * @var $_rr LightReverseRouterService
         */
        $_rr = $this->getContainer()->get("reverse_router");
        $url = $_rr->getUrl("lks_route-$type", [], true);
        return HttpRedirectResponse::create($url);
    }



    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     *
     * Returns the LightKitPageRenderer instance to use to render the pages.
     *
     * @return LightKitPageRenderer
     * @throws \Exception
     */
//    private function getKitPageRendererInstance(): LightKitPageRenderer
//    {
//        /**
//         * @var $va LightVarsService
//         */
//        $va = $this->getContainer()->get("vars");
//        $theme = $va->getVar("kit_store_vars.front_theme", null, true);
//        $root = LightKitStoreHelper::getLightKitEditorFrontRelativeRootPath();
//
//        return LightKitEditorHelper::getBasicPageRenderer($this->getContainer(), [
//            "type" => "babyYaml",
//            "theme" => $theme,
//            "root" => $root,
//        ]);
//    }


}

