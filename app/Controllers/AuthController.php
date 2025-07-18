<?php

namespace App\Controllers;

use App\config;
use Database\DB;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use PDO;
use PDOException;

class AuthController extends Controller
{

    public function login_page() : void
    {
        if (isset($_SESSION["loggedIn"]))
        {
            header("Location: /");
            exit();
        }

        $this->view('login.html.twig');
    }

    public function register_page() : void
    {
        $this->view('register.html.twig');
    }

    public function account_page() : void
    {
        $user = DB::Connection()->First("SELECT Name, DiscordID, DiscordAvatar, Level, Email, DateCreated, LastArea FROM users WHERe Name = :Name", [":Name", $_SESSION["name"], PDO::PARAM_STR]);

        $user["LastArea"] = explode("|", $user["LastArea"])[0];

        $this->view("account.html.twig", [
            "account" => $user,
            "isDiscordConnected" => $user["DiscordID"] != null
        ]);
    }

    public static function check_login() : void
    {
        if (!isset($_SESSION["name"], $_SESSION["access"], $_SESSION["loggedIn"]) && !isset($_COOKIE["remember_me"])) return;

        if (isset($_COOKIE['remember_me']))
        {
            $token = $_COOKIE['remember_me'];
            $user = DB::Connection()->First("SELECT id, Name, Access FROM users WHERE Rember_Token = :Token", [":Token", $token, PDO::PARAM_STR]);

            if ($user != null)
            {
                $_SESSION["loggedIn"] = true;
                $_SESSION["id"] = $user["id"];
                $_SESSION["name"] = $user["Name"];
                $_SESSION["access"] = $user["Access"];
            }
            else
            {
                session_unset();
                session_destroy();
            }
        }
    }

    public function isCaptchaSuccess() : bool
    {
        if (!isset($_POST["g-recaptcha-response"])) return false;

        $userIP = $_SERVER['REMOTE_ADDR'];

        $verifyURL = "https://www.google.com/recaptcha/api/siteverify";
        $data = [
            'secret' => config::GOOGLE_RECAPTCHA_SECRET_KEY,
            'response' => $_POST["g-recaptcha-response"],
            'remoteip' => $userIP
        ];

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded",
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($verifyURL, false, $context);
        $verify = json_decode($result);

        return $verify->success;
    }

    public function login() : void
    {
        header('Content-Type: application/json');

        if (!isset($_POST["username"], $_POST["password"]))
        {
            header("Location: https://www.pornhub.com/");
            return;
        }

        $username = htmlspecialchars($_POST["username"]);
        $pass = htmlspecialchars($_POST["password"]);
        $rememberMe = isset($_POST['remember_me']);

        $validator = $this->validator([
            "username" => ["required", 'string', "min:4", "max:20"],
            "password" => ["required", "string", "max:32"]
        ]);

        if (count($validator) > 0) $this->response_json(["msg" => $validator], 403);
        if (empty($username) || empty($pass)) $this->response_json(["msg" => "Please fill in the input field."], 403);
        if (!$this->isCaptchaSuccess()) $this->response_json(["msg" => "CAPTCHA failed."], 403);

        $user = DB::Connection()->First("SELECT id, Hash, Access FROM users WHERE Name = :Name", [":Name", $username, PDO::PARAM_STR]);

        if ($user == null || !password_verify($pass, $user["Hash"])) {
            $this->response_json([
                'msg' => 'The username or password you entered is incorrect. Please double-check and try again.',
            ], 403);
        }

        if ($rememberMe) {
            DB::Connection()->Instance()->beginTransaction();

            try
            {
                $token = bin2hex(random_bytes(16));

                DB::Connection()->Prepare("UPDATE users SET Rember_Token = :Token WHERE id = :id", [
                   [":Token", $token, PDO::PARAM_STR],
                   [":id", $user["id"], PDO::PARAM_INT]
                ])->execute();

                setcookie('remember_me', $token, time() + (30 * 24 * 60 * 60), "/"); // Set cookie for 30 days

                DB::Connection()->Instance()->commit();

                $_SESSION["loggedIn"] = true;
                $_SESSION["id"] = $user["id"];
                $_SESSION["name"] = $username;
                $_SESSION["access"] = $user["Access"];

                $this->response_json(["msg" => "You have successfully logged in."]);
            }
            catch (Exception)
            {
                if (DB::Connection()->Instance()->inTransaction())
                    DB::Connection()->Instance()->rollBack();
            }
        }
    }

    public function register() : void
    {
        header('Content-Type: application/json');

        if (!isset($_POST["username"], $_POST["password"], $_POST["retype_password"], $_POST["email"])) return;

        $username = strtolower(htmlspecialchars($_POST["username"]));
        $password = htmlspecialchars($_POST["password"]);
        $retypePassword = htmlspecialchars($_POST["retype_password"]);
        $email = $_POST["email"];

        $validator = $this->validator([
            "username" => ["required", 'string', "min:4", "max:20"],
            "password" => ["required", "string", "min:5", "max:32"],
            "retype_password" => ["required", "string", "min:5", "max:32"],
            "email" => ["required", "string"],
        ]);

        if (count($validator) > 0) $this->response_json(["msg" => $validator], 403);
        if (empty($username) || empty($password) || empty($retypePassword) || empty($email)) $this->response_json(["msg" => "Please fill in the input field."], 403);
        if (!preg_match('/^[A-Za-z0-9_]+$/', $username)) $this->response_json(["msg" => "Username can only have letters, numbers, and underscores."], 403);
        if (!$this->isCaptchaSuccess()) $this->response_json(["msg" => "CAPTCHA failed."], 403);

        $user = DB::Connection()->First("SELECT COUNT(*) as row_count FROM users WHERE Name = :Name", [":Name", $username, PDO::PARAM_STR]);

        if ($user["row_count"] > 0) $this->response_json(["msg" => "$username is already taken"], 403);
        if ($password != $retypePassword) $this->response_json(["msg" => "Password and confirmation must match."], 403);

        DB::Connection()->Instance()->beginTransaction();
        try {
            $userId = DB::Connection()->InsertGetId("INSERT INTO users (Name, Hash, Email, DateCreated) VALUE (:Name, :Hash, :Email, now())", [
                [":Name", $username, PDO::PARAM_STR],
                [":Hash", password_hash($password, PASSWORD_BCRYPT), PDO::PARAM_STR],
                [":Email", $email, PDO::PARAM_STR],
            ]);

            if ($userId > 0) {
                DB::Connection()->Prepare("INSERT INTO users_items (UserID, ItemID) VALUE (:UserID, :ItemID)", [
                    [":UserID", $userId, PDO::PARAM_INT],
                    [":ItemID", 1, PDO::PARAM_INT],
                ]);

                DB::Connection()->Prepare("INSERT INTO users_items (UserID, ItemID) VALUE (:UserID, :ItemID)", [
                    [":UserID", $userId, PDO::PARAM_INT],
                    [":ItemID", 2, PDO::PARAM_INT],
                ]);
            }

            DB::Connection()->Instance()->commit();
        } catch (PDOException) {
            if (DB::Connection()->Instance()->inTransaction())
                DB::Connection()->Instance()->rollBack();

            $this->response_json(["msg" => "Transaction failed!"], 403);
        }
    }

    public function change_password() : void
    {
        if (!$this->isAjax() || !isset($_POST["current_password"], $_POST["new_password"], $_POST["confirm_new_password"]))
        {
            $this->response_json(["msg" => "Error!"], 403);
        }

        $validator = $this->validator([
            "current_password" => ["required", 'string', "min:5", "max:32"],
            "new_password" => ["required", "string", "min:5", "max:32"],
            "confirm_new_password" => ["required", "string", "min:5", "max:32"],
        ]);

        if (count($validator) > 0) $this->response_json(["msg" => $validator], 403);

        $currentPassword = $_POST["current_password"];
        $newPassword = $_POST["new_password"];
        $confirmPassword = $_POST["confirm_new_password"];

        $user = DB::Connection()->First("SELECT id, Hash FROM users WHERE Name = :Name", [":Name", $_SESSION["name"], PDO::PARAM_STR]);

        if ($user == null) $this->response_json(["msg" => "User not found."], 403);
        if (!password_verify($currentPassword, $user["Hash"])) $this->response_json(["msg" => "Incorrect password!"], 403);
        if ($currentPassword == $newPassword) $this->response_json(["msg" => "You're using the same password as your current one."], 403);
        if ($newPassword != $confirmPassword) $this->response_json(["msg" => "Password and confirmation must match."], 403);
        if (!$this->isCaptchaSuccess()) $this->response_json(["msg" => "CAPTCHA failed."], 403);

        DB::Connection()->Instance()->beginTransaction();

        try {
            DB::Connection()->Prepare("UPDATE users SET Hash = :NewHash WHERE id = :Id AND Name = :Name", [
                [":NewHash", password_hash($newPassword, PASSWORD_BCRYPT), PDO::PARAM_STR],
                [":Id", $user["id"], PDO::PARAM_INT],
                [":Name", $_SESSION["name"], PDO::PARAM_INT],
            ]);

            DB::Connection()->Instance()->commit();

            $this->response_json(["msg" => "Successfully change password!"]);
        } catch (PDOException) {
            if (DB::Connection()->Instance()->inTransaction())
                DB::Connection()->Instance()->rollBack();

            $this->response_json(["msg" => "Transaction Failed! => "]);
        }
    }

    #[NoReturn]
    public function logout() : void
    {
        if (strlen(session_id()) > 0)
        {
            session_unset();
            session_destroy();

            if (isset($_COOKIE["remember_me"]))
            {
                setcookie('remember_me', '', time() - 3600, '/');
            }
        }

        header("Location: /");
        exit();
    }

    public function exchangeDiscordToken() : string
    {
        if (!isset($_GET['code'])) $this->abort(404);

        $redirect_uri = config::APP_URL . "/discord/callback";
        $code = $_GET['code'];

        $token_request = curl_init();
        curl_setopt($token_request, CURLOPT_URL, "https://discord.com/api/oauth2/token");
        curl_setopt($token_request, CURLOPT_POST, true);
        curl_setopt($token_request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($token_request, CURLOPT_POSTFIELDS, http_build_query([
            'client_id'     => config::DISCORD_CLIENT_ID,
            'client_secret' => config::DISCORD_CLIENT_SECRET,
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $redirect_uri,
            'scope'         => 'identify'
        ]));

        $response = curl_exec($token_request);
        curl_close($token_request);
        $data = json_decode($response, true);

        if (!isset($data['access_token'])) {
            $this->abort(404);
        }

        return $data['access_token'];
    }

    public function getDiscordUser(string $token) : mixed
    {
        $user_request = curl_init();
        curl_setopt($user_request, CURLOPT_URL, "https://discord.com/api/users/@me");
        curl_setopt($user_request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($user_request, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token"
        ]);
        $user_response = curl_exec($user_request);
        curl_close($user_request);

        return json_decode($user_response, true);
    }

    public function discord_callback() : void
    {
        $token = $this->exchangeDiscordToken();
        $res = $this->getDiscordUser($token);

        if (isset($_SESSION["loggedIn"]))
        {
            $user = DB::Connection()->First("SELECT id, Name, Access FROM users WHERE id = :id AND Name = :Name", [
                [":id", $_SESSION["id"], PDO::PARAM_INT],
                [":Name", $_SESSION["name"], PDO::PARAM_INT]
            ]);

            if ($user == null)
            {
                header("Location: /account?error=Discord account not linked");
                exit();
            }

            if ($user["DiscordID"] != null)
            {
                header("Location: /account?error=Discord account not linked");
                exit();
            }

            DB::Connection()->Instance()->beginTransaction();

            try {
                DB::Connection()->Prepare("UPDATE users SET DiscordID = :DiscordID, DiscordAvatar = :DiscordAvatar WHERE id = :id AND Name = :Name",[
                    [":DiscordID", $res["id"], PDO::PARAM_STR],
                    [":DiscordAvatar", $res["avatar"], PDO::PARAM_STR],
                    [":id", $user["id"], PDO::PARAM_STR],
                    [":Name", $_SESSION["name"], PDO::PARAM_STR],
                ]);

                DB::Connection()->Instance()->commit();

                $_SESSION["loggedIn"] = true;
                $_SESSION["id"] = $user["id"];
                $_SESSION["name"] = $user["Name"];
                $_SESSION["access"] = $user["Access"];

                header("Location: /account");
            } catch (PDOException) {
                if (DB::Connection()->Instance()->inTransaction())
                    DB::Connection()->Instance()->rollBack();

                header("Location: /account?error=Unable to connect");
            }
        }
        else
        {
            $user = DB::Connection()->First("SELECT id, Name, Access FROM users WHERe DiscordID = :DiscordID", [":DiscordID", $res["id"], PDO::PARAM_STR]);

            if ($user == null)
            {
                header("Location: /login?error=Discord account not linked");
                exit();
            }

            $_SESSION["loggedIn"] = true;
            $_SESSION["id"] = $user["id"];
            $_SESSION["name"] = $user["Name"];
            $_SESSION["access"] = $user["Access"];

            header("Location: /");
        }
    }

    public function discord_disconnect() : void
    {
        if (!isset($_SESSION["loggedIn"]))
        {
            header("Location: /");
            exit();
        }

        DB::Connection()->Instance()->beginTransaction();
        try {
            DB::Connection()->Prepare("UPDATE users SET DiscordID = null, DiscordAvatar = null WHERE id = :Id", [":Id", $_SESSION["id"], PDO::PARAM_INT]);

            DB::Connection()->Instance()->commit();

            header("Location: /account");
        } catch (PDOException $PDOException) {
            if (DB::Connection()->Instance()->inTransaction())
                DB::Connection()->Instance()->rollBack();

            die($PDOException->getMessage());
        }
    }

}