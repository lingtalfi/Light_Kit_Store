<?php


namespace Ling\Light_Kit_Store\Controller;


use Ling\Bat\ConvertTool;
use Ling\Bat\DateTool;
use Ling\Bat\HashTool;
use Ling\Bat\HttpTool;
use Ling\Bat\ValidationTool;
use Ling\Light\Controller\LightController;
use Ling\Light\Http\HttpJsonResponse;
use Ling\Light\Http\HttpRequestInterface;
use Ling\Light\Http\HttpResponse;
use Ling\Light\Http\HttpResponseInterface;
use Ling\Light_Kit_Store\Helper\LightKitStoreRememberMeHelper;
use Ling\Light_Kit_Store\Helper\LightKitStoreUserHelper;
use Ling\Light_Kit_Store\Service\LightKitStoreService;
use Ling\Light_Mailer\Service\LightMailerService;
use Ling\Light_MailStats\Service\LightMailStatsService;
use Ling\Light_UserManager\Service\LightUserManagerService;
use Ling\SimplePdoWrapper\Util\Where;


/**
 * The StoreApiController class.
 *
 * All methods of this class are alcp ends for clients.
 *
 *
 */
class StoreApiController extends LightController
{


    /**
     * Executes the action given in the GET parameters and returns a response.
     *
     * The "action" parameter should be present in GET.
     *
     * This is designed as a hub/proxy for all the other methods of this class.
     *
     * It's basically the only method that we expose publicly.
     *
     *
     *
     * @param HttpRequestInterface $request
     * @return HttpResponseInterface
     */
    public function execute(HttpRequestInterface $request): HttpResponseInterface
    {
        $action = $request->getGetValue("action", false) ?? "undefined";

        switch ($action) {
            case "registerWebsite":
                return $this->registerWebsite($request);
            case "signUp":
                return $this->signUp($request);
            case "signIn":
                return $this->signIn($request);
            case "resetPassword":
                return $this->sendResetPasswordEmail($request);
            case "disconnect":
                return $this->disconnect($request);
            default:
                return new HttpResponse("Unknown action: $action.", 404);

        }
    }


    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * Disconnects the user, and returns a successful @page(alcp response).
     *
     * @param HttpRequestInterface $request
     * @return HttpJsonResponse
     * @throws \Exception
     */
    private function disconnect(HttpRequestInterface $request): HttpJsonResponse
    {
        /**
         * @var $_um LightUserManagerService
         */
        $_um = $this->getContainer()->get("user_manager");
        $user = $_um->getOpenUser();


        LightKitStoreRememberMeHelper::destroyTokenByValidUser($this->getContainer(), $user);

        $user->disconnect();


        return HttpJsonResponse::create([
            "type" => "success",
        ]);

    }


    /**
     * Registers a website to the store database, and returns an @page(alcp response).
     * work in progress...
     *
     *
     * @param HttpRequestInterface $request
     * @return HttpJsonResponse
     */
    private function registerWebsite(HttpRequestInterface $request): HttpJsonResponse
    {

        /**
         * todo: here... open new window (kit_store), and continue implementing "install item process", see config/open/Ling.Light_Kit_Editor to resume ideas...
         * todo: here... open new window (kit_store), and continue implementing "install item process", see config/open/Ling.Light_Kit_Editor to resume ideas...
         * todo: here... open new window (kit_store), and continue implementing "install item process", see config/open/Ling.Light_Kit_Editor to resume ideas...
         * todo: here... open new window (kit_store), and continue implementing "install item process", see config/open/Ling.Light_Kit_Editor to resume ideas...
         */
        return HttpJsonResponse::create([
            "type" => "success",
            "content" => "Boris à la plage",
        ]);
    }


    /**
     * Signs up a new user.
     * This method can be called via ajax from a client website.
     *
     * See the @page(Light_Kit_Store conception notes) for more details.
     *
     * The response is an basic @page(alcp response).
     *
     *
     * Note that we use the google recaptcha v3 system.
     *
     *
     * The expected parameters for the request are:
     *
     * - email
     * - password
     * - password_confirm
     * - g-recaptcha-response
     * - project_name
     *
     *
     *
     *
     * @param HttpRequestInterface $request
     * @return HttpJsonResponse
     */
    private function signUp(HttpRequestInterface $request): HttpJsonResponse
    {


        $email = $request->getPostValue("email", false) ?? "";
        $password = $request->getPostValue("password", false) ?? "";
        $passwordConfirm = $request->getPostValue("password_confirm", false) ?? "";
        $recaptcha = $request->getPostValue("g-recaptcha-response", false) ?? "";
        $projectName = $request->getPostValue("project_name", false) ?? "";


        $error = null;

        if ('' !== $projectName) {
            if ('' !== $recaptcha) {


                /**
                 * @var $_ks LightKitStoreService
                 */
                $_ks = $this->getContainer()->get("kit_store");
                $secret = $_ks->getRecaptchaKey($projectName, false);


                $s = HttpTool::post("https://www.google.com/recaptcha/api/siteverify", [
                    'secret' => $secret,
                    'response' => $recaptcha,
                ]);

                $arr = [];
                if (false !== $s) {
                    $arr = (array)json_decode($s);
                }


                if ($arr) {

                    $success = $arr['success'] ?? false;
                    if (true === $success) {


                        if ('' !== $email) {
                            if (true === ValidationTool::isEmail($email)) {
                                if ("" !== $password) {
                                    if ($password === $passwordConfirm) {


                                        /**
                                         * @var $kit_store LightKitStoreService
                                         */
                                        $kit_store = $this->getContainer()->get("kit_store");
                                        $userApi = $kit_store->getFactory()->getUserApi();
                                        $res = $userApi->getUser(Where::inst()->key("email")->equals($email));
                                        if (null === $res) {
                                            // all good.

                                            $password = HashTool::passwordEncrypt($password);


                                            $userId = $userApi->insertUser([
                                                "email" => $email,
                                                "password" => $password,
                                                "company" => "",
                                                "first_name" => "",
                                                "last_name" => "",
                                                "address" => "",
                                                "zip_postal_code" => "",
                                                "city" => "",
                                                "state_province_region" => "",
                                                "country" => "",
                                                "phone" => "",
                                            ]);


                                        } else {
                                            $error = "An user already exists with the email $email.";
                                        }
                                    } else {
                                        $error = "Passwords don't match.";
                                    }
                                } else {
                                    $error = "The password cannot be empty.";
                                }
                            } else {
                                $error = "Invalid email: $email.";
                            }
                        } else {
                            $error = "The email cannot be empty.";
                        }
                    } else {
                        $error = "The recaptcha test failed.";
                    }
                } else {
                    $error = "The recaptcha's server response was empty.";
                }
            } else {
                $error = "The recaptcha cannot be empty.";
            }
        } else {
            $error = "The project name cannot be empty.";
        }


        if (null !== $error) {
            $response = [
                "type" => "error",
                "error" => $error,

            ];
        } else {
            $response = [
                "type" => "success",
            ];
        }


        return HttpJsonResponse::create($response);
    }


    /**
     * Signs in a user.
     *
     * See the @page(Light_Kit_Store conception notes) for more details.
     *
     * The response is an basic @page(alcp response).
     *
     *
     *
     *
     *
     *
     * @param HttpRequestInterface $request
     * @return HttpJsonResponse
     */
    private function signIn(HttpRequestInterface $request): HttpJsonResponse
    {


        $email = $request->getPostValue("email", false) ?? "";
        $password = $request->getPostValue("password", false) ?? "";
        $rememberMe = ConvertTool::toBoolean($request->getPostValue("remember_me", false)) ?? false;


        $error = null;
        $token = null;


        if ('' !== $email) {
            if ("" !== $password) {
                /**
                 * @var $kit_store LightKitStoreService
                 */
                $kit_store = $this->getContainer()->get("kit_store");


                $userApi = $kit_store->getFactory()->getUserApi();
                $res = $userApi->getUser(Where::inst()
                    ->key("email")->equals($email)
                );


                if (null !== $res) {

                    if (true === HashTool::passwordVerify($password, $res['password'])) {
                        // all good.


                        $token = $kit_store->generateUserToken();
                        $now = date("Y-m-d H:i:s");

                        $userApi->updateUserById($res['id'], [
                            'token' => $token,
                            'token_first_connection_time' => $now,
                            'token_last_connection_time' => $now,
                        ]);


                        /**
                         * @var $_um LightUserManagerService
                         */
                        $_um = $this->getContainer()->get("user_manager");
                        $user = $_um->getOpenUser();
                        LightKitStoreUserHelper::setUserPropsFromRow($user, $res);
                        $user->connect();


                        if (true === $rememberMe) {
                            $rememberMeToken = LightKitStoreRememberMeHelper::generateRememberMeToken();
                            LightKitStoreRememberMeHelper::spreadTokenByValidUser($this->getContainer(), $user, $rememberMeToken);
                        }


                    } else {
                        $error = "No user found with the given credentials.";
                    }
                } else {
                    $error = "No user found with the given credentials.";
                }

            } else {
                $error = "The password cannot be empty.";
            }
        } else {
            $error = "The email cannot be empty.";
        }

        if (null !== $error) {
            $response = [
                "type" => "error",
                "error" => $error,

            ];
        } else {
            $response = [
                "type" => "success",
                "token" => $token,
            ];
        }


        return HttpJsonResponse::create($response);
    }


    /**
     * Sends an email to the user, which contains a link to reset his/her password.
     *
     * See the @page(Light_Kit_Store conception notes) for more details.
     *
     *
     * This is a @page(alcp service).
     *
     *
     * Expected request parameters:
     *
     * - email
     * - client_website (an identifier of the client website sending the request)
     *
     * Possible errors:
     *
     * - x field missing
     * - unknown email
     *
     *
     *
     *
     *
     * @param HttpRequestInterface $request
     * @return HttpJsonResponse
     */
    private function sendResetPasswordEmail(HttpRequestInterface $request): HttpJsonResponse
    {
        $email = $request->getPostValue("email", false);
        $error = null;


        if (null !== $email) {
            /**
             * @var $kit_store LightKitStoreService
             */
            $kit_store = $this->getContainer()->get("kit_store");

            $userApi = $kit_store->getFactory()->getUserApi();
            $res = $userApi->getUser(Where::inst()
                ->key("email")->equals($email)
            );

            if (null !== $res) {


                /**
                 * @var $_m LightMailerService
                 */
                $_m = $this->getContainer()->get("mailer");


                try {


                    /**
                     * @var $_ms LightMailStatsService
                     */
                    $_ms = $this->getContainer()->get("mail_stats");
                    $trackerApi = $_ms->getFactory()->getTrackerApi();
                    $trackerId = $trackerApi->insertTracker([
                        "group" => "kitstore",
                        "name" => "link-reset_password",
                        "url" => "",
                        "date_sent" => DateTool::getMysqlDatetime(),
                    ]);
                    azf($trackerId);


                    $nbSent = 1;
                    $nbSent = $_m->send("Ling.Light_Kit_Store/reset_password", $email, [
                        'vars' => [
                            "fullDate" => date("Y-m-d H:i:s"),
                            "link" => 0,
                        ],
                    ]);


                    if ($nbSent > 0) {

                    } else {
                        $error = "The mail couldn't be sent: $email.";
                    }
                } catch (\Exception $e) {
                    $error = "Error with mailer: " . $e->getMessage();
                }

            } else {
                $error = "Unknown email: $email.";
            }
        } else {
            $error = "The email is missing.";
        }

        if (null !== $error) {
            $response = [
                "type" => "error",
                "error" => $error,

            ];
        } else {
            $response = [
                "type" => "success",
            ];
        }


        return HttpJsonResponse::create($response);
    }


}

