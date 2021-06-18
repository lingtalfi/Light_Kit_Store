<?php


namespace Ling\Light_Kit_Store\Service;


use Ling\Bat\HashTool;
use Ling\Light\ServiceContainer\LightServiceContainerInterface;
use Ling\Light_Database\Service\LightDatabaseService;
use Ling\Light_Kit_Store\Api\Custom\CustomLightKitStoreApiFactory;
use Ling\Light_Kit_Store\Exception\LightKitStoreException;
use Ling\Light_PasswordProtector\Service\LightPasswordProtector;


/**
 * The LightKitStoreService class.
 */
class LightKitStoreService
{

    /**
     * This property holds the container for this instance.
     * @var LightServiceContainerInterface
     */
    protected LightServiceContainerInterface $container;


    /**
     * This property holds the options for this instance.
     *
     * Available options are:
     *
     *
     *
     * See the @page(Light_Kit_Store conception notes) for more details.
     *
     *
     * @var array
     */
    protected array $options;


    /**
     * This property holds a passwordProtector for this instance.
     * See the Light_PasswordProtector planet for mor info: https://github.com/lingtalfi/Light_PasswordProtector.
     *
     * Note that we use our own instance, so that it's not altered by the user/maintainer configuration.
     *
     *
     * @var LightPasswordProtector|null $passwordProtector
     */
    protected $passwordProtector;

    /**
     * This property holds the factory for this instance.
     * @var CustomLightKitStoreApiFactory|null
     */
    protected ?CustomLightKitStoreApiFactory $factory;


    /**
     * Builds the LightKitStoreService instance.
     */
    public function __construct()
    {
        /**
         * todo: here add passwordProtector system, like (lka->addUser on the right); and finish signUp process
         * todo: here add passwordProtector system, like (lka->addUser on the right); and finish signUp process
         * todo: here add passwordProtector system, like (lka->addUser on the right); and finish signUp process
         * todo: here add passwordProtector system, like (lka->addUser on the right); and finish signUp process
         * todo: here add passwordProtector system, like (lka->addUser on the right); and finish signUp process
         * todo: here add passwordProtector system, like (lka->addUser on the right); and finish signUp process
         * todo: here add passwordProtector system, like (lka->addUser on the right); and finish signUp process
         */
        $this->options = [];
        $this->passwordProtector = null;
        $this->factory = null;
    }

    /**
     * Sets the container.
     *
     * @param LightServiceContainerInterface $container
     */
    public function setContainer(LightServiceContainerInterface $container)
    {
        $this->container = $container;
    }


    /**
     * Sets the options.
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }


    /**
     * Returns the recaptcha key corresponding to the given project, or an empty string if nothing matches.
     * If isSite is true, the site key is returned, otherwise the secret key is returned.
     *
     * https://www.google.com/recaptcha/about/
     *
     *
     * @param string $project
     * @param bool $isSite
     * @return string
     */
    public function getRecaptchaKey(string $project, bool $isSite = true): string
    {
        $cat = (true === $isSite) ? "site" : "secret";
        return $this->options["captchaKeys"][$project][$cat] ?? "";
    }


    /**
     * Generates a login token.
     *
     * See the @page(Light_Kit_Store conception notes) for more details.
     *
     * @return string
     */
    public function generateUserToken(): string
    {
        return HashTool::getRandomHash64();
    }


    /**
     * Registers a website from a directory.
     * work in progress...
     */
    public function registerWebsiteFromDirectory()
    {
        /**
         * todo: here... is the method name correct?
         * todo: here... is the method name correct?
         * todo: here... is the method name correct?
         */
    }


    /**
     * Returns the factory for this plugin's api.
     *
     * @return CustomLightKitStoreApiFactory
     */
    public function getFactory(): CustomLightKitStoreApiFactory
    {
        if (null === $this->factory) {
            $this->factory = new CustomLightKitStoreApiFactory();
            $this->factory->setContainer($this->container);
            /**
             * @var $db LightDatabaseService
             */
            $db = $this->container->get("database");
            $this->factory->setPdoWrapper($db);
        }
        return $this->factory;
    }


    /**
     * Returns a configured instance of LightPasswordProtector.
     * Note: the returned instance is always configured internally (i.e. its configuration does not depend on an external factor).
     *
     * @return LightPasswordProtector
     */
    public function getPasswordProtector(): LightPasswordProtector
    {
        if (null === $this->passwordProtector) {
            $this->passwordProtector = new LightPasswordProtector();
            $this->passwordProtector->setAlgorithmName("bcrypt");
            $this->passwordProtector->setAlgorithmOptions([]);
        }
        return $this->passwordProtector;
    }




    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * Throws an exception.
     * @param string $msg
     * @param int|null $code
     * @throws \Exception
     */
    private function error(string $msg, int $code = null)
    {
        throw new LightKitStoreException(static::class . ": " . $msg, $code);
    }


}