<?php

namespace App;

interface config {

    const DEBUG = true;
    const PHP_CONFIG = true;

    const SWF_PROTECT = false;
    const WHITE_LIST_TABLES = [
        "auras", "auras_effects", "classes", "enhancements", "enhancements_patterns", "factions", "global_drops",
        "items", "items_requirements", "items_skills", "maps", "maps_items", "maps_monsters", "monsters", "monsters_bosses",
        "monsters_drops", "monsters_skills", "quests", "quests_required_items", "quests_requirements", "quests_rewards",
        "redeems", "shops", "shops_items", "shops_seasonal", "skills", "skills_assign", "skills_auras", "stores", "titles",
        "stores", "wheels", "web_posts"
    ];

    const SSL = false;
    const APP_NAME = "SilverAQ";
    const APP_URL = "http://127.0.0.1";

    const DB_HOST                     = "localhost";
    const DB_USER                     = "root";
    const DB_PASS                     = "123";
    const DB_NAME                     = "aqworlds";
    const DB_PORT                     = 9519;
    const DB_CHARSET                  = "utf8mb4";

    const GOOGLE_RECAPTCHA_SITE_KEY   = "6Ldc-YUrAAAAAFUqjdY5H2XwTbNeRci4RwCmQgGA";
    const GOOGLE_RECAPTCHA_SECRET_KEY = "6Ldc-YUrAAAAAEBNtzYg6KWqm0sYnaZxHYReQDtQ";

    const DISCORD_CLIENT_ID           = "1393807737242255432";
    const DISCORD_CLIENT_SECRET       = "tsqTdxBuwPDBtoSbizPDs65_i95qrQvk";
    const DISCORD_SCOPE               = "identify";

}