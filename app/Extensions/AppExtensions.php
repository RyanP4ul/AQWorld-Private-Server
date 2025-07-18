<?php

namespace App\Extensions;

use App\config;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtensions extends AbstractExtension
{

    public function getFunctions() : array
    {
        return [
            new TwigFunction('auth', function ($property) {

                if (isset($_SESSION["name"]) && $property == "name") return $_SESSION["name"];
                if (isset($_SESSION["access"]) && $property == "access") return $_SESSION["access"];
                if (isset($_SESSION["loggedIn"]) && $property == "loggedIn") return $_SESSION["loggedIn"];

                return null;
            }),

            new TwigFunction('config', function($property) {
                if ($property == "APP_NAME")
                {
                    return config::APP_NAME;
                }
                elseif ($property == "APP_URL")
                {
                    return config::APP_URL;
                }
                elseif ($property == "GOGGLE_RECAPTCHA_SITE_KEY")
                {
                    return config::GOOGLE_RECAPTCHA_SITE_KEY;
                }
                elseif ($property == "DISCORD_CLIENT_ID")
                {
                    return config::DISCORD_CLIENT_ID;
                }
                elseif ($property == "DISCORD_SCOPE")
                {
                    return config::DISCORD_SCOPE;
                }
                return null;
            }),
        ];
    }

}