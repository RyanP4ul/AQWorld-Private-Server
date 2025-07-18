<?php

namespace App\Controllers\Panel;

use Database\DB;
use PDO;

class UserController extends PanelController
{

    private const WHITE_LIST_INFO = ["items", "achievements", "logs", "timeline"];

    public function index() : void {
        if ($this->isAjax()) {
            $characters = DB::Connection()->Get("SELECT id, Name, Level, Access FROM users");

            foreach ($characters as &$character) {
                if ($character["Access"] == 60) {
                    $character["Access"] = "<span class='badge bg-danger'>Administrator</span>";
                } else if ($character["Access"] = 40) {
                    $character["Access"] = "<span class='badge bg-warning'>Moderator</span>";
                } else {
                    $character["Access"] = "Player";
                }

                $character["Action"] = "<a href='/panel/user?id=" . $character["id"] . "' ><button id='btn-preview' class='btn btn-sm btn-default'><i class='fa fa-eye pr-2'></i> Preview</button></a>";
            }

            $this->response_json([
                "data" => $characters
            ]);
        }

        $this->view("panel/characters.html.twig", [
            "title" => "Characters"
        ]);
    }

    public function view_character() : void {
        if (!isset($_GET["id"])) $this->abort(404);

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $character = DB::Connection()->First("SELECT id, Name, Level, Access, Country, Gold, Coins FROM users WHERE id = :Id", [":Id", $id, PDO::PARAM_STR]);

        if ($character == null) $this->abort(404);

        $this->view("panel/character.html.twig", [
            "title" => $character["Name"] . "'s",
            "character" => $character
        ]);
    }

    public function get_character_info() : void {
        if (!isset($_GET["charId"], $_GET["type"])) $this->abort(404); // self::WHITE_LIST_INFO[$_GET["type"]]

        $userId = filter_input(INPUT_GET, 'charId', FILTER_SANITIZE_NUMBER_INT);
        $type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        header('Content-Type: application/json');

        switch ($type) {
            case "items":
                $userCharItems = DB::Connection()->Get("SELECT a.id AS CharIemID, a.Equipped, a.ItemID, a.Bank, b.id as ItemID, b.Name, b.Equipment, b.Rarity, b.Level, b.Upgrade, b.Icon FROM users_items AS a INNER JOIN items AS b ON a.ItemID = b.id WHERE a.UserID = :UserID", [":UserID", $userId, PDO::PARAM_INT]);

                if ($userCharItems == null) $this->abort(404);

                $items = [];
                $equipments = [];
                $bank = [];
                $houses = [];

                foreach ($userCharItems as &$userCharItem) {

                    $userCharItem["Name"] = "<a href='/panel/items?id=" . $userCharItem["ItemID"] . "'>" . $userCharItem["Name"] . "</a>";
                    $userCharItem["Rarity"] = "Unknown";

                    if ($userCharItem["Equipment"] == "ho" || $userCharItem["Equipment"] == "hi") {
                        $houses[] = $userCharItem;
                    } else if ($userCharItem["Bank"] == 0) {
                        $items[] = $userCharItem;
                    } else {
                        $bank[] = $userCharItem;
                    }
                }

                $this->response_json([
                    "data" => [
                        "items" => $items,
                        "equipments" => $equipments,
                        "bank" => $bank,
                        "houses" => $houses
                    ]
                ]);
//            case "achievements":
//                $achievements = DB::Connection()->Get("SELECT b.* FROM users_characters_achievements as a INNER JOIN achievements as b ON a.AchievementID = b.id WHERE a.CharID = :CharID", [":CharID", $charId, PDO::PARAM_INT]);
//                $this->response_json(["data" => $achievements]);
//            case "logs":
//                $logs = DB::Connection()->Get("SELECT * FROM users_characters_logs", [":CharID", $charId, PDO::PARAM_INT]);
//                $this->response_json(["data" => $logs]);
//            case "timeline":
//                break;
        }
    }

    public function character_add_item() : void {
        if (!isset($_GET["charId"], $_GET["itemId"], $_GET["qty"])) $this->abort(404);

        $id = filter_input(INPUT_GET, 'charId', FILTER_SANITIZE_NUMBER_INT);
        $itemId = filter_input(INPUT_GET, 'itemId', FILTER_SANITIZE_NUMBER_INT);
        $qty = filter_input(INPUT_GET, 'qty', FILTER_SANITIZE_NUMBER_INT);
    }

}