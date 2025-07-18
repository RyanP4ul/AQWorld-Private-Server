<?php

declare(strict_types = 1);

use App\config;
use App\Route\Middleware\AuthMiddleware;
use App\Route\Middleware\IsFlashRequestMiddleware;
use App\Route\Router;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

require __DIR__ . '/../vendor/autoload.php';

if (config::PHP_CONFIG)
{
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('max_execution_time', 30);
    ini_set("max_input_time", 60);
    ini_set("post_max_size", "8M");
    ini_set("upload_max_filesize", "8M");
    ini_set("max_input_vars", 1000);
    ini_set("expose_php", "Off");
    ini_set("allow_url_fopen", "Off");
    ini_set("allow_url_include", "Off");
    ini_set("disable_functions", "exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source");
    ini_set("session.gc_maxlifetime", 1440);
    ini_set("session.cookie_lifetime", 0);
    ini_set("session.use_strict_mode", 1);
    ini_set("memory_limit", "128M");
}

error_reporting(config::DEBUG ? E_ALL : 0);

session_start();

$GLOBALS["__routes__"] = [
    "home" => ["GET", "/home", ['App\Controllers\HomeController', 'index']],

    "login_page" => ["GET", "/login", ['App\Controllers\AuthController', 'login_page']],
    "register_page" => ["GET", "/register", ['App\Controllers\AuthController', 'register_page']],

    "launcher" => ["GET", "/launcher", ['App\Controllers\LauncherController', 'index']],

    "store" => ["GET", "/store", ['App\Controllers\StoreController', 'index']],
    "store.checkout" => ["GET", "/store/checkout", ['App\Controllers\StoreController', 'checkout']],

    "game" => ["GET", "/game", ['App\Controllers\GameController', 'game']],
//    "game.swf" => ["GET", "/game/swf", ['App\Controllers\GameController', 'game_swf']],
    "game.version" => ["GET", "/api/game/version", ['App\Controllers\GameController', 'game_version']],
    "game.login" => ["POST", "/api/game/login", ['App\Controllers\GameController', 'game_login']],
    "game.clientvars" => ["GET", "/api/game/clientvars", ['App\Controllers\GameController', 'game_clientvars']],
    "game.banks" => ["POST", "/api/char/bank", ['App\Controllers\GameController', 'game_clientvars']],
    "game.house" => ["POST", "/api/char/HouseSaveRoom", ['App\Controllers\GameController', 'houseSaveRoom']],

    "auth.account" => ["GET", "/account", ['App\Controllers\AuthController', 'account_page']],
    "auth.profile" => ["GET", "/account/profile", ['App\Controllers\HomeController', 'index']],

    "auth.login" => ["POST", "/auth/login", ['App\Controllers\AuthController', 'login']],
    "auth.register" => ["POST", "/auth/register", ['App\Controllers\AuthController', 'register']],
    "auth.change.password" => ["POST", "/auth/change/password", ['App\Controllers\AuthController', 'change_password']],
    "auth.logout" => ["GET", "/auth/logout", ['App\Controllers\AuthController', 'logout']],

    "auth.discord.callback" => ["GET", "/discord/callback", ['App\Controllers\AuthController', 'discord_callback']],
    "auth.discord.disconnect" => ["GET", "/discord/disconnect", ['App\Controllers\AuthController', 'discord_disconnect']],

    // PANEL
    "panel.dashboard" => ["GET", "/panel", ['App\Controllers\Panel\DashboardController', 'index']],


    // PANEL -> AURAS
    "panel.auras" => ["GET", "/panel/auras", ['App\Controllers\Panel\AuraController', 'index']],
    "panel.auras.save" => ["POST", "/panel/auras/save", ['App\Controllers\Panel\AuraController', 'save']],
    "panel.auras.edit" => ["GET", "/panel/auras/edit", ['App\Controllers\Panel\AuraController', 'edit']],
    "panel.auras.delete" => ["GET", "/panel/auras/delete", ['App\Controllers\Panel\AuraController', 'delete']],


    // PANEL -> AURAS EFFECTS
    "panel.effects" => ["GET", "/panel/effects", ['App\Controllers\Panel\AuraEffectController', 'index']],
    "panel.effects.save" => ["POST", "/panel/effects/save", ['App\Controllers\Panel\AuraEffectController', 'save']],
    "panel.effects.edit" => ["GET", "/panel/effects/edit", ['App\Controllers\Panel\AuraEffectController', 'edit']],
    "panel.effects.delete" => ["GET", "/panel/effects/delete", ['App\Controllers\Panel\AuraEffectController', 'delete']],


    // PANEL -> CLASSES
    "panel.classes" => ["GET", "/panel/classes", ['App\Controllers\Panel\ClassesController', 'index']],
    "panel.classes.save" => ["POST", "/panel/classes/save", ['App\Controllers\Panel\ClassesController', 'save']],
    "panel.classes.edit" => ["GET", "/panel/classes/edit", ['App\Controllers\Panel\ClassesController', 'edit']],
    "panel.classes.delete" => ["GET", "/panel/classes/delete", ['App\Controllers\Panel\ClassesController', 'delete']],


    // PANEL -> ENHANCEMENT
    "panel.enhancement" => ["GET", "/panel/enhancement", ['App\Controllers\Panel\EnhancementController', 'index']],
    "panel.enhancement.save" => ["POST", "/panel/enhancement/save", ['App\Controllers\Panel\EnhancementController', 'save']],
    "panel.enhancement.edit" => ["GET", "/panel/enhancement/edit", ['App\Controllers\Panel\EnhancementController', 'edit']],
    "panel.enhancement.delete" => ["GET", "/panel/enhancement/delete", ['App\Controllers\Panel\EnhancementController', 'delete']],


    // PANEL -> ENHANCEMENT PATTERNS
    "panel.patterns" => ["GET", "/panel/patterns", ['App\Controllers\Panel\PatternController', 'index']],
    "panel.patterns.save" => ["POST", "/panel/patterns/save", ['App\Controllers\Panel\PatternController', 'save']],
    "panel.patterns.edit" => ["GET", "/panel/patterns/edit", ['App\Controllers\Panel\PatternController', 'edit']],
    "panel.patterns.delete" => ["GET", "/panel/patterns/delete", ['App\Controllers\Panel\PatternController', 'delete']],


    // PANEL -> FACTIONS
    "panel.factions" => ["GET", "/panel/factions", ['App\Controllers\Panel\FactionController', 'index']],
    "panel.factions.save" => ["POST", "/panel/factions/save", ['App\Controllers\Panel\FactionController', 'save']],
    "panel.factions.edit" => ["GET", "/panel/factions/edit", ['App\Controllers\Panel\FactionController', 'edit']],
    "panel.factions.delete" => ["GET", "/panel/factions/delete", ['App\Controllers\Panel\FactionController', 'delete']],


    // PANEL -> FACTIONS
    "panel.globaldrops" => ["GET", "/panel/globaldrops", ['App\Controllers\Panel\GlobalDropController', 'index']],
    "panel.globaldrops.save" => ["POST", "/panel/globaldrops/save", ['App\Controllers\Panel\GlobalDropController', 'save']],
    "panel.globaldrops.edit" => ["GET", "/panel/globaldrops/edit", ['App\Controllers\Panel\GlobalDropController', 'edit']],
    "panel.globaldrops.delete" => ["GET", "/panel/globaldrops/delete", ['App\Controllers\Panel\GlobalDropController', 'delete']],


    // PANEL -> ITEMS
    "panel.items" => ["GET", "/panel/items", ['App\Controllers\Panel\ItemController', 'index']],
    "panel.items.save" => ["POST", "/panel/items/save", ['App\Controllers\Panel\ItemController', 'save']],
    "panel.items.edit" => ["GET", "/panel/items/edit", ['App\Controllers\Panel\ItemController', 'edit']],
    "panel.items.delete" => ["GET", "/panel/items/delete", ['App\Controllers\Panel\ItemController', 'delete']],


    // PANEL -> ITEMS REQUIREMENTS
    "panel.items.requirements" => ["GET", "/panel/items/requirements", ['App\Controllers\Panel\ItemReqController', 'index']],
    "panel.items.requirements.save" => ["POST", "/panel/items/requirements/save", ['App\Controllers\Panel\ItemReqController', 'save']],
    "panel.items.requirements.edit" => ["GET", "/panel/items/requirements/edit", ['App\Controllers\Panel\ItemReqController', 'edit']],
    "panel.items.requirements.delete" => ["GET", "/panel/items/requirements/delete", ['App\Controllers\Panel\ItemReqController', 'delete']],


    // PANEL -> ITEMS SKILLS
    "panel.items.skills" => ["GET", "/panel/items/skills", ['App\Controllers\Panel\ItemSkillController', 'index']],
    "panel.items.skills.save" => ["POST", "/panel/items/skills/save", ['App\Controllers\Panel\ItemSkillController', 'save']],
    "panel.items.skills.edit" => ["GET", "/panel/items/skills/edit", ['App\Controllers\Panel\ItemSkillController', 'edit']],
    "panel.items.skills.delete" => ["GET", "/panel/items/skills/delete", ['App\Controllers\Panel\ItemSkillController', 'delete']],


    // PANEL -> MAPS
    "panel.maps" => ["GET", "/panel/maps", ['App\Controllers\Panel\MapsController', 'index']],
    "panel.maps.save" => ["POST", "/panel/maps/save", ['App\Controllers\Panel\MapsController', 'save']],
    "panel.maps.edit" => ["GET", "/panel/maps/edit", ['App\Controllers\Panel\MapsController', 'edit']],
    "panel.maps.delete" => ["GET", "/panel/maps/delete", ['App\Controllers\Panel\MapsController', 'delete']],


    // PANEL -> MAPS
    "panel.maps.items" => ["GET", "/panel/maps/items", ['App\Controllers\Panel\MapItemController', 'index']],
    "panel.maps.items.save" => ["POST", "/panel/maps/items/save", ['App\Controllers\Panel\MapItemController', 'save']],
    "panel.maps.items.edit" => ["GET", "/panel/maps/items/edit", ['App\Controllers\Panel\MapItemController', 'edit']],
    "panel.maps.items.delete" => ["GET", "/panel/maps/items/delete", ['App\Controllers\Panel\MapItemController', 'delete']],


    // PANEL -> MAPS
    "panel.maps.monsters" => ["GET", "/panel/maps/monsters", ['App\Controllers\Panel\MapMonsterController', 'index']],
    "panel.maps.monsters.save" => ["POST", "/panel/maps/monsters/save", ['App\Controllers\Panel\MapMonsterController', 'save']],
    "panel.maps.monsters.edit" => ["GET", "/panel/maps/monsters/edit", ['App\Controllers\Panel\MapMonsterController', 'edit']],
    "panel.maps.monsters.delete" => ["GET", "/panel/maps/monsters/delete", ['App\Controllers\Panel\MapMonsterController', 'delete']],


    // PANEL -> MONSTERS
    "panel.monsters" => ["GET", "/panel/monsters", ['App\Controllers\Panel\MonstersController', 'index']],
    "panel.monsters.save" => ["POST", "/panel/monsters/save", ['App\Controllers\Panel\MonstersController', 'save']],
    "panel.monsters.edit" => ["GET", "/panel/monsters/edit", ['App\Controllers\Panel\MonstersController', 'edit']],
    "panel.monsters.delete" => ["GET", "/panel/monsters/delete", ['App\Controllers\Panel\MonstersController', 'delete']],


    // PANEL -> MONSTERS BOSSES
    "panel.monsters.bosses" => ["GET", "/panel/monsters/bosses", ['App\Controllers\Panel\MonsterBossController', 'index']],
    "panel.monsters.bosses.save" => ["POST", "/panel/monsters/bosses/save", ['App\Controllers\Panel\MonsterBossController', 'save']],
    "panel.monsters.bosses.edit" => ["GET", "/panel/monsters/bosses/edit", ['App\Controllers\Panel\MonsterBossController', 'edit']],
    "panel.monsters.bosses.delete" => ["GET", "/panel/monsters/bosses/delete", ['App\Controllers\Panel\MonsterBossController', 'delete']],


    // PANEL -> MONSTERS DROPS
    "panel.monsters.drops" => ["GET", "/panel/monsters/drops", ['App\Controllers\Panel\MonsterDropController', 'index']],
    "panel.monsters.drops.save" => ["POST", "/panel/monsters/drops/save", ['App\Controllers\Panel\MonsterDropController', 'save']],
    "panel.monsters.drops.edit" => ["GET", "/panel/monsters/drops/edit", ['App\Controllers\Panel\MonsterDropController', 'edit']],
    "panel.monsters.drops.delete" => ["GET", "/panel/monsters/drops/delete", ['App\Controllers\Panel\MonsterDropController', 'delete']],


    // PANEL -> MONSTERS SKILLS
    "panel.monsters.skills" => ["GET", "/panel/monsters/skills", ['App\Controllers\Panel\MonsterSkillController', 'index']],
    "panel.monsters.skills.save" => ["POST", "/panel/monsters/skills/save", ['App\Controllers\Panel\MonsterSkillController', 'save']],
    "panel.monsters.skills.edit" => ["GET", "/panel/monsters/skills/edit", ['App\Controllers\Panel\MonsterSkillController', 'edit']],
    "panel.monsters.skills.delete" => ["GET", "/panel/monsters/skills/delete", ['App\Controllers\Panel\MonsterSkillController', 'delete']],


    // PANEL -> QUEST
    "panel.quests" => ["GET", "/panel/quests", ['App\Controllers\Panel\QuestController', 'index']],
    "panel.quests.save" => ["POST", "/panel/quests/save", ['App\Controllers\Panel\QuestController', 'save']],
    "panel.quests.edit" => ["GET", "/panel/quests/edit", ['App\Controllers\Panel\QuestController', 'edit']],
    "panel.quests.delete" => ["GET", "/panel/quests/delete", ['App\Controllers\Panel\QuestController', 'delete']],


    // PANEL -> QUEST REQUIRED ITEMS
    "panel.quests.reqitem" => ["GET", "/panel/quests/reqitem", ['App\Controllers\Panel\QuestReqItemController', 'index']],
    "panel.quests.reqitem.save" => ["POST", "/panel/quests/reqitem/save", ['App\Controllers\Panel\QuestReqItemController', 'save']],
    "panel.quests.reqitem.edit" => ["GET", "/panel/quests/reqitem/edit", ['App\Controllers\Panel\QuestReqItemController', 'edit']],
    "panel.quests.reqitem.delete" => ["GET", "/panel/quests/reqitem/delete", ['App\Controllers\Panel\QuestReqItemController', 'delete']],



    // PANEL -> QUEST REQUIREMENTS
    "panel.quests.requirements" => ["GET", "/panel/quests/requirements", ['App\Controllers\Panel\QuestRequirementController', 'index']],
    "panel.quests.requirements.save" => ["POST", "/panel/quests/requirements/save", ['App\Controllers\Panel\QuestRequirementController', 'save']],
    "panel.quests.requirements.edit" => ["GET", "/panel/quests/requirements/edit", ['App\Controllers\Panel\QuestRequirementController', 'edit']],
    "panel.quests.requirements.delete" => ["GET", "/panel/quests/requirements/delete", ['App\Controllers\Panel\QuestRequirementController', 'delete']],



    // PANEL -> QUEST REWARDS
    "panel.quests.rewards" => ["GET", "/panel/quests/rewards", ['App\Controllers\Panel\QuestRewardController', 'index']],
    "panel.quests.rewards.save" => ["POST", "/panel/quests/rewards/save", ['App\Controllers\Panel\QuestRewardController', 'save']],
    "panel.quests.rewards.edit" => ["GET", "/panel/quests/rewards/edit", ['App\Controllers\Panel\QuestRewardController', 'edit']],
    "panel.quests.rewards.delete" => ["GET", "/panel/quests/rewards/delete", ['App\Controllers\Panel\QuestRewardController', 'delete']],


    // PANEL -> QUEST REWARDS
    "panel.redeem" => ["GET", "/panel/redeem", ['App\Controllers\Panel\RedeemController', 'index']],
    "panel.redeem.save" => ["POST", "/panel/redeem/save", ['App\Controllers\Panel\RedeemController', 'save']],
    "panel.redeem.edit" => ["GET", "/panel/redeem/edit", ['App\Controllers\Panel\RedeemController', 'edit']],
    "panel.redeem.delete" => ["GET", "/panel/redeem/delete", ['App\Controllers\Panel\RedeemController', 'delete']],


    // PANEL -> SHOPS
    "panel.shops" => ["GET", "/panel/shops", ['App\Controllers\Panel\ShopController', 'index']],
    "panel.shops.save" => ["POST", "/panel/shops/save", ['App\Controllers\Panel\ShopController', 'save']],
    "panel.shops.edit" => ["GET", "/panel/shops/edit", ['App\Controllers\Panel\ShopController', 'edit']],
    "panel.shops.delete" => ["GET", "/panel/shops/delete", ['App\Controllers\Panel\ShopController', 'delete']],

    // PANEL -> SHOPS ITEMS
    "panel.shops.items" => ["GET", "/panel/shops/items", ['App\Controllers\Panel\ShopItemController', 'index']],
    "panel.shops.items.save" => ["POST", "/panel/shops/items/save", ['App\Controllers\Panel\ShopItemController', 'save']],
    "panel.shops.items.edit" => ["GET", "/panel/shops/items/edit", ['App\Controllers\Panel\ShopItemController', 'edit']],
    "panel.shops.items.delete" => ["GET", "/panel/shops/items/delete", ['App\Controllers\Panel\ShopItemController', 'delete']],


    // PANEL -> SHOPS ITEMS
    "panel.shops.seasonal" => ["GET", "/panel/shops/seasonal", ['App\Controllers\Panel\ShopSeasonalController', 'index']],
    "panel.shops.seasonal.save" => ["POST", "/panel/shops/seasonal/save", ['App\Controllers\Panel\ShopSeasonalController', 'save']],
    "panel.shops.seasonal.edit" => ["GET", "/panel/shops/seasonal/edit", ['App\Controllers\Panel\ShopSeasonalController', 'edit']],
    "panel.shops.seasonal.delete" => ["GET", "/panel/shops/seasonal/delete", ['App\Controllers\Panel\ShopSeasonalController', 'delete']],



    // PANEL -> SKILLS
    "panel.skills" => ["GET", "/panel/skills", ['App\Controllers\Panel\SkillController', 'index']],
    "panel.skills.save" => ["POST", "/panel/skills/save", ['App\Controllers\Panel\SkillController', 'save']],
    "panel.skills.edit" => ["GET", "/panel/skills/edit", ['App\Controllers\Panel\SkillController', 'edit']],
    "panel.skills.delete" => ["GET", "/panel/skills/delete", ['App\Controllers\Panel\SkillController', 'delete']],


    // PANEL -> SKILLS ASSIGN
    "panel.skills.assign" => ["GET", "/panel/skills/assign", ['App\Controllers\Panel\SkillAssignController', 'index']],
    "panel.skills.assign.save" => ["POST", "/panel/skills/assign/save", ['App\Controllers\Panel\SkillAssignController', 'save']],
    "panel.skills.assign.edit" => ["GET", "/panel/skills/assign/edit", ['App\Controllers\Panel\SkillAssignController', 'edit']],
    "panel.skills.assign.delete" => ["GET", "/panel/skills/assign/delete", ['App\Controllers\Panel\SkillAssignController', 'delete']],


    // PANEL -> SKILLS AURAS
    "panel.skills.auras" => ["GET", "/panel/skills/auras", ['App\Controllers\Panel\SkillAuraController', 'index']],
    "panel.skills.auras.save" => ["POST", "/panel/skills/auras/save", ['App\Controllers\Panel\SkillAuraController', 'save']],
    "panel.skills.auras.edit" => ["GET", "/panel/skills/auras/edit", ['App\Controllers\Panel\SkillAuraController', 'edit']],
    "panel.skills.auras.delete" => ["GET", "/panel/skills/auras/delete", ['App\Controllers\Panel\SkillAuraController', 'delete']],


    // PANEL -> TITLES
    "panel.titles" => ["GET", "/panel/titles", ['App\Controllers\Panel\TitleController', 'index']],
    "panel.titles.save" => ["POST", "/panel/titles/save", ['App\Controllers\Panel\TitleController', 'save']],
    "panel.titles.edit" => ["GET", "/panel/titles/edit", ['App\Controllers\Panel\TitleController', 'edit']],
    "panel.titles.delete" => ["GET", "/panel/titles/delete", ['App\Controllers\Panel\TitleController', 'delete']],

    "panel.upload" => ["GET", "/panel/upload", ['App\Controllers\Panel\UploadController', 'index']],

    // PANEL -> WHEELS
    "panel.wheels" => ["GET", "/panel/wheels", ['App\Controllers\Panel\WheelController', 'index']],
    "panel.wheels.save" => ["POST", "/panel/wheels/save", ['App\Controllers\Panel\WheelController', 'save']],
    "panel.wheels.edit" => ["GET", "/panel/wheels/edit", ['App\Controllers\Panel\WheelController', 'edit']],
    "panel.wheels.delete" => ["GET", "/panel/wheels/delete", ['App\Controllers\Panel\WheelController', 'delete']],


    // PANEL -> WHEELS
    "panel.web.posts" => ["GET", "/panel/web/posts", ['App\Controllers\Panel\WebPostsController', 'index']],
    "panel.web.posts.save" => ["POST", "/panel/web/posts/save", ['App\Controllers\Panel\WebPostsController', 'save']],
    "panel.web.posts.edit" => ["GET", "/panel/web/posts/edit", ['App\Controllers\Panel\WebPostsController', 'edit']],
    "panel.web.posts.delete" => ["GET", "/panel/web/posts/delete", ['App\Controllers\Panel\WebPostsController', 'delete']],

    // PANEL -> TEST
    "panel.areas.test" => ["GET", "/auto_increment", ['App\Controllers\MainController', 'get_next_id']],


    // PANEL -> TEST UPLOAD
    "panel.upload.test" => ["POST", "/upload", ['App\Controllers\Panel\UploadController', 'Upload']],

    // PANEL -> CHARACTER
    "panel.users" => ["GET", "/panel/users", ['App\Controllers\Panel\UserController', 'index']],
    "panel.user.character" => ["GET", "/panel/user", ['App\Controllers\Panel\UserController', 'view_character']],
    "panel.user.character.info" => ["GET", "/panel/character/info", ['App\Controllers\Panel\UserController', 'get_character_info']],
    "panel.user.character.additem" => ["GET", "/panel/character/additem", ['App\Controllers\Panel\UserController', 'character_add_item']],
];

$dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) {
    foreach ($GLOBALS["__routes__"] as $route) $routeCollector->addRoute($route[0], $route[1], $route[2]);
});

$router = new Router($dispatcher);
$router->group('auth', ['/account']);
$router->addGroupMiddleware('auth', new AuthMiddleware());

//$router->group('game', ['/game/swf', '/game/api/version', '/game/api/login', '/game/api/character/password', '/game/api/character/hairs', '/game/api/character/confirm', '/game/api/character/create', '/game/api/character/skills', '/game/api/character/delete', '/game/api/character/HouseSaveRoom']);
//$router->addGroupMiddleware('game', new IsFlashRequestMiddleware());

$router->dispatch();
