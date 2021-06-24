<?php


namespace Ling\Light_Kit_Store\Controller;


use Ling\Light\Controller\LightController;
use Ling\Light\Http\HttpResponse;
use Ling\Light\Http\HttpResponseInterface;
use Ling\Light_Kit\PageRenderer\LightKitPageRenderer;
use Ling\Light_Kit_Editor\Helper\LightKitEditorHelper;
use Ling\Light_Kit_Store\Helper\LightKitStoreHelper;
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
        $widgetVariables = $options['widgetVariables'] ?? [];


        /**
         * @var $_um LightUserManagerService
         */
        $_um = $this->getContainer()->get("user_manager");
        $user = $_um->getOpenUser();
        $widgetVariables["header.kitstore_header"]["user"] = $user;




        // --
        $options['widgetVariables'] = $widgetVariables;
        $kit = $this->getKitPageRendererInstance();
        return new HttpResponse($kit->renderPage($page, $options));
    }


    /**
     *
     * Returns the LightKitPageRenderer instance to use to render the pages.
     *
     * @return LightKitPageRenderer
     * @throws \Exception
     */
    private function getKitPageRendererInstance(): LightKitPageRenderer
    {
        /**
         * @var $va LightVarsService
         */
        $va = $this->getContainer()->get("vars");
        $theme = $va->getVar("kit_store_vars.front_theme", null, true);
        $root = LightKitStoreHelper::getLightKitEditorFrontRelativeRootPath();

        return LightKitEditorHelper::getBasicPageRenderer($this->getContainer(), [
            "type" => "babyYaml",
            "theme" => $theme,
            "root" => $root,
        ]);
    }


}

