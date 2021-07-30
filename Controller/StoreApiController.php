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
use Ling\Light_Kit_Store\Helper\LightKitStoreOptionHelper;
use Ling\Light_Kit_Store\Helper\LightKitStorePasswordHelper;
use Ling\Light_Kit_Store\Helper\LightKitStoreRememberMeHelper;
use Ling\Light_Kit_Store\Helper\LightKitStoreUserHelper;
use Ling\Light_Kit_Store\Service\LightKitStoreService;
use Ling\Light_Logger\Service\LightLoggerService;
use Ling\Light_Mailer\Service\LightMailerService;
use Ling\Light_MailStats\Service\LightMailStatsService;
use Ling\Light_ProjectVars\Service\LightProjectVarsService;
use Ling\Light_ReverseRouter\Service\LightReverseRouterService;
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


        try {

            // alcp actions
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
                case "changePassword":
                    return $this->changePassword($request);
                case "updateBillingInfo":
                    return $this->updateBillingInfo($request);


            }
        } catch (\Exception $e) {


            /**
             * @var $_lo LightLoggerService
             */
            $_lo = $this->getContainer()->get("logger");
            $_lo->error($e);

            return HttpJsonResponse::create([
                "type" => "error",
                "error" => "Oops, an unexpected error occurred. We're working on it right now. Try again later, or contact us. Sorry for the inconvenience.",
            ]);
        }


        // other action types
        switch ($action) {
            default:
                return new HttpResponse("Unknown action: $action.", 404);
                break;
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
     * Updates the password of the connected user.
     *
     * This is an @page(alcp service).
     *
     *
     * Expected parameters are:
     *
     * - password
     * - password_confirm
     *
     * If those two are given and match, then the password of the connected user will be updated.
     *
     * If the user is not connected, this alcp service returns an erroneous response.
     *
     *
     * The password cannot be empty.
     *
     * The password is trimmed.
     *
     * In case of success, a "message" property contains the success message.
     *
     *
     * @param HttpRequestInterface $request
     * @return HttpJsonResponse
     * @throws \Exception
     */
    private function changePassword(HttpRequestInterface $request): HttpJsonResponse
    {

        $error = null;
        $successMsg = null;

        /**
         * @var $_um LightUserManagerService
         */
        $_um = $this->getContainer()->get("user_manager");
        $user = $_um->getOpenUser();

        if (true === $user->isValid()) {

            $password = $request->getPostValue("password", false) ?? "";
            $passwordConfirm = $request->getPostValue("password_confirm", false) ?? "";

            $password = trim($password);
            $passwordConfirm = trim($passwordConfirm);


            if ('' !== $password) {
                if ($passwordConfirm === $password) {


                    /**
                     * @var $_ks LightKitStoreService
                     */
                    $_ks = $this->getContainer()->get("kit_store");
                    $userApi = $_ks->getFactory()->getUserApi();
                    $userApi->updatePassword($password);


                    $successMsg = "Your password has been updated :)";


                } else {
                    $error = "The passwords don't match.";
                }
            } else {
                $error = "The password cannot be empty.";
            }
        } else {
            $error = "The user is not connected.";
        }


        //--------------------------------------------
        //
        //--------------------------------------------
        if (null !== $error) {
            $response = [
                "type" => "error",
                "error" => $error,

            ];
        } else {
            $response = [
                "type" => "success",
                "message" => $successMsg,
            ];
        }


        return HttpJsonResponse::create($response);

    }

    /**
     * Updates the billing info of the connected user.
     *
     * This is an @page(alcp service).
     *
     *
     * Expected parameters are:
     *
     * - company
     * - first_name
     * - last_name
     * - address
     * - zip_postal_code
     * - city
     * - state_province_region
     * - country
     * - phone
     *
     * All values are set to an empty string by default.
     *
     * If the user is not connected, this alcp service returns an erroneous response.
     *
     * In case of success, a "message" property contains the success message.
     *
     *
     *
     *
     * @param HttpRequestInterface $request
     * @return HttpJsonResponse
     * @throws \Exception
     */
    private function updateBillingInfo(HttpRequestInterface $request): HttpJsonResponse
    {
        $error = null;
        $successMsg = null;

        /**
         * @var $_um LightUserManagerService
         */
        $_um = $this->getContainer()->get("user_manager");
        $user = $_um->getOpenUser();

        if (true === $user->isValid()) {

            $company = $request->getPostValue("company", false) ?? "";
            $firstName = $request->getPostValue("first_name", false) ?? "";
            $lastName = $request->getPostValue("last_name", false) ?? "";
            $address = $request->getPostValue("address", false) ?? "";
            $zip = $request->getPostValue("zip_postal_code", false) ?? "";
            $city = $request->getPostValue("city", false) ?? "";
            $state = $request->getPostValue("state_province_region", false) ?? "";
            $country = $request->getPostValue("country", false) ?? "";
            $phone = $request->getPostValue("phone", false) ?? "";


            /**
             * @var $_ks LightKitStoreService
             */
            $_ks = $this->getContainer()->get("kit_store");
            $userApi = $_ks->getFactory()->getUserApi();


            $userApi->updateUserById($user->getProp("id"), [
                'company' => $company,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'address' => $address,
                'zip_postal_code' => $zip,
                'city' => $city,
                'state_province_region' => $state,
                'country' => $country,
                'phone' => $phone,
            ]);
            $successMsg = "Your billing information has been updated :)";

        } else {
            $error = "The user is not connected.";
        }


        //--------------------------------------------
        //
        //--------------------------------------------
        if (null !== $error) {
            $response = [
                "type" => "error",
                "error" => $error,

            ];
        } else {
            $response = [
                "type" => "success",
                "message" => $successMsg,
            ];
        }


        return HttpJsonResponse::create($response);

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


        $error = null;
        $successMsg = null;

        if ('' !== $recaptcha) {


            /**
             * @var $_pv LightProjectVarsService
             */
            $_pv = $this->getContainer()->get("project_vars");

            $projectName = $_pv->getVariable("project_name");


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

                                        $password = LightKitStorePasswordHelper::encrypt($password);


                                        //--------------------------------------------
                                        // SIGN UP MODES
                                        //--------------------------------------------
                                        $active = 0;
                                        $signupToken = "";
                                        $signupTokenTime = null;
                                        $signupMode = $_ks->getOption("signup_mode", LightKitStoreOptionHelper::SIGNUP_MODE);
                                        switch ($signupMode) {
                                            case "direct":
                                                $active = 1;
                                                $successMsg = "You've signed up successfully. You can now log in :)";
                                                break;
                                            case "mail":
                                                $signupToken = $_ks->generateUserToken();
                                                $signupTokenTime = date("Y-m-d H:i:s");
                                                $active = 2;
                                                break;
                                            case "moderator":
                                                $active = 3;
                                                $successMsg = "Your request has been registered. A moderator will confirm your account. You will be notified by email.";
                                                break;
                                            default:
                                                $error = "Unknown signup_mode: $signupMode."; // we could change the public message too, but that's ok for now...
                                                $this->logError(__METHOD__ . ": $error");
                                                break;
                                        }


                                        if (null === $error) {
                                            $userId = $userApi->insertUser([
                                                "email" => $email,
                                                "password" => $password,
                                                "signup_token" => $signupToken,
                                                "signup_token_time" => $signupTokenTime,
                                                "active" => $active,
                                            ]);

                                            if ('mail' === $signupMode) {


                                                /**
                                                 * @var $_m LightMailerService
                                                 */
                                                $_m = $this->getContainer()->get("mailer");


                                                /**
                                                 * @var $_pv LightProjectVarsService
                                                 */
                                                $_pv = $this->getContainer()->get("project_vars");


                                                /**
                                                 * @var $_rr LightReverseRouterService
                                                 */
                                                $_rr = $this->getContainer()->get("reverse_router");

                                                $targetUrl = $_rr->getUrl("lks_route-your_account_confirmed", [
                                                    "token" => $signupToken,
                                                ], true);


                                                /**
                                                 * @var $_ms LightMailStatsService
                                                 */
                                                $_ms = $this->getContainer()->get("mail_stats");
                                                $clickTrackerUrl = $_ms->createClickTracker("kitstore.confirm_user_subscription", $targetUrl);
                                                $openTrackerImg = $_ms->createOpenTracker("kitstore.confirm_user_subscription");


                                                $nbSent = $_m->send("Ling.Light_Kit_Store/confirm_user_subscription", $email, [
                                                    'vars' => [
                                                        "fullDate" => date("Y-m-d H:i:s"),
                                                        "link" => $clickTrackerUrl,
                                                        "website" => $_pv->getVariable("website"),
                                                        "tracker" => $openTrackerImg,
                                                    ],
                                                ]);


                                                $successMsg = "Almost there! Check your email to confirm your account.";
                                            }

                                        }


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


        if (null !== $error) {
            $response = [
                "type" => "error",
                "error" => $error,

            ];
        } else {
            $response = [
                "type" => "success",
                "message" => $successMsg,
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

                    if ('1' === $res['active']) {

                        if (true === LightKitStorePasswordHelper::passwordVerify($password, $res['password'])) {


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
                        $active = $res['active'];
                        switch ($active) {
                            case "2":
                                $error = "This user has not confirmed his/her registration sent by email.";
                                break;
                            case "3":
                                $error = "This user's registration is pending. A moderator shall confirm this account soon.";
                                break;
                            default:
                                $error = "This user is not active.";
                                break;
                        }
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

                $datetime = DateTool::getMysqlDatetime();

                /**
                 * @var $_m LightMailerService
                 */
                $_m = $this->getContainer()->get("mailer");


                try {


                    // set resetPassword token
                    $resetPasswordToken = HashTool::getRandomHash64();
                    $userApi->updateUserById($res["id"], [
                        "reset_password_token" => $resetPasswordToken,
                        "reset_password_token_time" => $datetime,
                    ]);


                    /**
                     * @var $_rr LightReverseRouterService
                     */
                    $_rr = $this->getContainer()->get("reverse_router");
                    $url = $_rr->getUrl("lks_route-your_new_password", [
                        "token" => $resetPasswordToken,
                    ], true);


                    /**
                     * @var $_ms LightMailStatsService
                     */
                    $_ms = $this->getContainer()->get("mail_stats");
                    $clickTrackerUrl = $_ms->createClickTracker("kitstore.reset_password", $url);
                    $openTrackerImg = $_ms->createOpenTracker("kitstore.reset_password");


                    $nbSent = $_m->send("Ling.Light_Kit_Store/reset_password", $email, [
                        'vars' => [
                            "fullDate" => $datetime,
                            "link" => $clickTrackerUrl,
                            "tracker" => $openTrackerImg,
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

