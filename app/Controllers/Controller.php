<?php

namespace App\Controllers;

use App\config;
use App\Extensions\AppExtensions;
use App\Extensions\RoutingExtensions;
use Database\DB;
use DateTime;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use PDO;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Controller
{

    private Environment $twig;

    public function __construct()
    {
        $this->twig = new Environment(new FilesystemLoader('..\templates'));
        $this->twig->addExtension(new AppExtensions());
        $this->twig->addExtension(new RoutingExtensions($GLOBALS["__routes__"]));

        AuthController::check_login();
    }

    public function isAccessValid(int $reqAccess) : bool
    {
        return isset($_SESSION["loggedIn"]) && isset($_SESSION["access"]) && $_SESSION["loggedIn"] && $_SESSION["access"] >= $reqAccess;
    }

    protected function view(String $filename, array $params = []) : void
    {
        try {
            $params["url"] = config::SSL ? 'https://' . config::APP_URL : 'http://' . config::APP_URL;
            $params["config"] = [
                "APP_NAME" => config::APP_NAME,
                "APP_URL" => config::APP_URL
            ];

            if (isset($_SESSION["auth"])) {
                $params["user"] = $_SESSION["auth"];
            }

            die ($this->twig->render($filename, $params));
        } catch (Exception $exception) {
            echo $exception->getMessage();
            http_response_code(404);
        }
    }

    #[NoReturn]
    public function Debug(...$vars): void {
        echo "<div style='color: #FFFFFF; background-color:#1e1e1e; padding: 10px'>";

        foreach ($vars as $var) {
            echo "<pre>";
            var_dump($var);
            echo "</pre>";
        }

        echo "</div>";
        die();
    }

    #[NoReturn]
    protected function abort(int $http_code) : void
    {
        http_response_code($http_code);
        exit();
    }

    protected function isAjax() : bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    public function getDateTime() : \DateTime
    {
        return new DateTime();
    }

    protected function settingRate(string $name) : float|null
    {
        $rates = DB::Connection()->First("SELECT value FROM settings_rates WHERE name = :Name", [":Name", $name, PDO::PARAM_INT]);
        return $rates != null ? $rates["value"] : null;
    }

    protected function validator($targets) : array
    {
        $errorMsg = [];

        foreach ($targets as $name => $requirements)
        {
            $isNull = false;

            foreach ($requirements as $requirement)
            {
                if (array_key_exists($name, $errorMsg)) continue;

                if ($requirement == "null")
                {
                    $isNull = true;
                    continue;
                }

                if ($requirement == "required" && !isset($_POST[$name]) || ($requirement == "required" && isset($_POST[$name]) && strlen($_POST[$name]) < 1))
                {
                    $errorMsg[$name][] = $name . " is required!";
                }
                else if (isset($_POST[$name]))
                {
                    if (!$isNull && $requirement == "string" && !is_string($_POST[$name]))
                    {
                        $errorMsg[$name][] = "The " . $name . " must be a string.";
                    }
                    else if (!$isNull && $requirement == "int" && !is_numeric($_POST[$name]))
                    {
                        $errorMsg[$name][] = "The " . $name . " must be a integer.";
                    }
                    else if (!$isNull && $requirement == "decimal" && !$this->is_double_string($_POST[$name]))
                    {
                        $errorMsg[$name][] = "The " . $name . " must be a decimal.";
                    }
                    else if (str_contains($requirement, "min:"))
                    {
                        $min = explode(':', $requirement)[1];

                        if (is_int($min) && strlen($_POST[$name]) < $min)
                        {
                            $errorMsg[$name][] = "The " . $name . " must be at least " . $min . " characters.";
                        }
                    }
                    else if (str_contains($requirement, "max:"))
                    {
                        $max = explode(':', $requirement)[1];

                        if (is_int($max) && strlen($_POST[$name]) > $max)
                        {
                            $errorMsg[$name][] = "The " . $name . " must not be greater than " . $max . " characters.";
                        }
                    }
                }
            }
        }

        return $errorMsg;
    }

    public function is_double_string($value) {
        return is_numeric($value) && strpos($value, '.') !== false;
    }

    protected function isChecked($target) : int
    {
        return isset($_POST[$target]) && $_POST[$target] == "on" ? 1 : 0;
    }

    #[NoReturn]
    protected function response_json($response = [], int $http_code = 200) : void
    {
        http_response_code($http_code);
        die(json_encode($response));
    }

}