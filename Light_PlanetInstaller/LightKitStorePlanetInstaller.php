<?php


namespace Ling\Light_Kit_Store\Light_PlanetInstaller;


use Ling\CliTools\Output\OutputInterface;
use Ling\Light_Database\Light_PlanetInstaller\LightDatabaseBasePlanetInstaller;
use Ling\Light_Kit_Editor\Service\LightKitEditorService;

/**
 * The LightKitStorePlanetInstaller class.
 */
class LightKitStorePlanetInstaller extends LightDatabaseBasePlanetInstaller
{


    /**
     * @overrides
     */
    public function init3(string $appDir, OutputInterface $output, array $options = []): void
    {
        parent::init3($appDir, $output, $options);


        $planetDotName = "Ling.Light_Kit_Store";


        //--------------------------------------------
        // kit editor
        //--------------------------------------------
        $output->write("$planetDotName: registering the kit_front_store website...");
        $sourceDir = "config/data/Ling.Light_Kit_Store/Ling.Light_Kit_Editor/front";
        /**
         * @var $_ke LightKitEditorService
         */
        $_ke = $this->container->get("kit_editor");
        $_ke->registerWebsite([
            "identifier" => "Ling.Light_Kit_Store.front",
            "provider" => "Ling.Light_Kit_Store",
            "engine" => "babyYaml",
            "rootDir" => '${app_dir}/config/data/Ling.Light_Kit_Store/Ling.Light_Kit_Editor/front',
            "label" => "Ling.Light_Kit_Store front",
        ]);
        $output->write("<success>ok</success>" . PHP_EOL);


    }
}