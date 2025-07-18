<?php

namespace App\Controllers;

use App\config;
use Database\DB;
use DateTime;
use Exception;
use PDO;
use PDOException;
use stdClass;

class GameController extends Controller
{

    private function hasProxyHeaders() : bool
    {
        $headers = apache_request_headers();
        return isset($headers['X-Forwarded-For']) || isset($headers['Via']) || isset($headers['Forwarded']);
    }

    public function game() : void
    {
        $this->view("game.html.twig");
    }

    public function game_swf() : void
    {
         if ((config::SWF_PROTECT || $_REQUEST["deviceType"] == "Air" || $_REQUEST["deviceType"] == "Windows") || (!isset($_SERVER["HTTP_REFERER"], $_REQUEST["path"], $_REQUEST["deviceType"]) || $_SERVER["HTTP_REFERER"] != "http://127.0.0.1/game" || !file_exists("../" . $_REQUEST["path"]))) $this->abort(404);

        $filePath = "../" . $_REQUEST["path"];

        header('Content-Type: application/x-shockwave-flash');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
    }

    public function game_version() : void
    {
        header('Content-Type: application/json');

        $arrayString = [];

        foreach (DB::Connection()->Get("SELECT * FROm settings_login WHERE location = 'loader'") as $result) $arrayString[$result["name"]] = $result["value"];

        $this->response_json($arrayString);
    }

    public function game_clientvars() : void {
        header('Content-Type: application/json');

        $arrayString = [];

        foreach (DB::Connection()->Get("SELECT * FROM settings_login WHERE location = 'game'") as $result) $arrayString[$result["name"]] = $result["value"];

        $this->response_json($arrayString);
    }

    public function game_login() : void
    {
        if (!isset($_POST["user"], $_POST["pass"])) $this->abort(404);

        header('Content-Type: application/json');

        $name = $_POST["user"];
        $pass = $_POST["pass"];

        try {
            $user = DB::Connection()->First("SELECT * FROM users WHERE name = :Name", [":Name", $name, PDO::PARAM_STR]);

            if ($user == null || !password_verify($pass, $user["Hash"]))
            {
                $this->response_json([
                    'bSuccess' => 0,
                    'sMsg' => 'The username you typed was not found. Please check your spelling and try again.',
                ]);
            }

            $std = new stdClass();
            $std->bSuccess = 1;
            $std->login = [
                'userId' => $user["id"],
                'bSuccess' => 1,
                'sMsg' => 'success',
                'sToken' => $user["Hash"],
                'strEmail' => $user["Email"],
                'unm' => $user["Name"]
            ];

            $std->servers = [];

            $servers = DB::Connection()->Get("SELECT * FROM servers");

            foreach ($servers as $server)
            {
                $std->servers[] = [
                    "sName" => $server["Name"],
                    "sIP" => $server["IP"],
                    "iCount" => $server["Count"],
                    "iLevel" => $server["Level"],
                    "iMax" => $server["Max"],
                    "bOnline" => $server["Online"],
                    "iChat" => $server["Chat"],
                    "bUpg" => $server["Upgrade"],
                    "sLang" => "xx",
                    "iPort" => $server["Port"]
                ];
            }

            $this->response_json($std);
        } catch (Exception $e) {
            $this->response_json(["bSuccess" => 0, "sMsg" => "Error! => " . $e->getMessage() . " => " . $e->getTraceAsString()]);
        }
    }

    public function bank() : void {
        $headers = apache_request_headers();

        if (!isset($headers["Ccid"], $headers["Token"])) $this->abort(404);

        header('Content-Type: application/json');

        $user = DB::Connection()->First("SELECT * FROM users WHERE id = :Id", [":Id", $headers["Ccid"], PDO::PARAM_INT]);

        if ($user == null || $user["Hash"] != $headers["Token"]) $this->response_json("Invalid Account");

        $userItemBanks = DB::Connection()->Get("SELECT a.id CharItemID, a.UserID, a.ItemID, a.EnhID, a.Equipped, a.Quantity, a.Bank, a.DatePurchased, b.Name, b.Description, b.`Range`, b.DPS, b.Rarity, b.Cost, b.Level, b.Temporary, b.Stack, b.Upgrade, b.Coins, b.Staff, b.File, b.Link, b.Element, b.Type, b.Icon, b.Equipment FROM users_items AS a INNER JOIN items AS b ON a.ItemID = b.id WHERE UserID = :Id AND Bank = 1", [":Id", $user["id"], PDO::PARAM_INT]);

        if ($userItemBanks == null) $this->response_json("Unable to retrieve!");

        $items = [];

        foreach ($userItemBanks as $userItemBank) {
            $item = [
                "CharItemID" => $userItemBank["CharItemID"],
                "CharID" => $userItemBank["UserID"],
                "iQty" => $userItemBank["Quantity"],
                "bEquip" => $userItemBank["Equipped"] == 1,
                "bBank" => $userItemBank["Bank"] == 1,
                "ItemID" => $userItemBank["ItemID"],
                "sName" => $userItemBank["Name"],
                "sDesc" => $userItemBank["Description"],
                "iRng" => $userItemBank["Range"],
                "iDPS" => $userItemBank["DPS"],
                "iRty" => $userItemBank["Rarity"],
                "iCost" => $userItemBank["Cost"],
                "iLvl" => $userItemBank["Level"],
                "bTemp" => $userItemBank["Temporary"] == 1,
                "iStk" => $userItemBank["Stack"],
                "bUpg" => $userItemBank["Upgrade"] == 1,
                "bCoins" => $userItemBank["Coins"] == 1,
                "bStaff" => $userItemBank["Staff"] == 1,
                "sFile" => $userItemBank["File"],
                "sLink" => $userItemBank["Link"],
                "sElmt" => $userItemBank["Element"],
                "sType" => $userItemBank["Type"],
                "sIcon" => $userItemBank["Icon"],
                "sES" => $userItemBank["Equipment"],
                "EnhID" => $userItemBank["EnhID"],
                "iHrs" => 59100,
                "dPurchase" => str_replace(" ", "T", $userItemBank["DatePurchased"])
            ];

            $enh = DB::Connection()->First("SELECT * FROM enhancements WHERE id = :Id", [":Id", $userItemBank["EnhID"], PDO::PARAM_STR]);

            if ($enh != null) {
                $item["EnhRty"] = $enh["Rarity"];
                $item["EnhDPS"] = $enh["DPS"];
                $item["EnhRng"] = $userItemBank["Range"];
                $item["EnhLvl"] = $enh["Level"];
                $item["EnhPatternID"] = $enh["PatternID"];
            } else {
                $item["EnhRty"] = null;
                $item["EnhDPS"] = null;
                $item["EnhRng"] = null;
                $item["EnhLvl"] = null;
                $item["EnhPatternID"] = null;
            }

            $items[] = $item;
        }

        $this->response_json($items);
    }

    public function houseSaveRoom() : void {
        $headers = apache_request_headers();

        if (!isset($headers["Ccid"], $headers["Token"], $_POST["frame"])) $this->response_json("0", 400);

        header('Content-Type: application/json');

        $user = DB::Connection()->First("SELECT * FROM users WHERE id = :Id", [":Id", $headers["Ccid"], PDO::PARAM_INT]);

        if ($user == null || $user["Hash"] != $headers["Token"]) $this->response_json("1", 403);

        DB::Connection()->Instance()->beginTransaction();

        try {
            if ($_POST["frame"] == "*") {
                DB::Connection()->Prepare("DELETE FROM users_houses WHERE UserID = :UserID", [":UserID", $headers["Ccid"], PDO::PARAM_INT]);
                DB::Connection()->Instance()->commit();
                die("cleared");
            }

            if (!isset($_POST["layout"])) $this->response_json("2", 400);

            $layout = json_decode($_POST["layout"], true);

            if (!isset($layout['xi']) || !is_array($layout['xi'])) $this->response_json("3", 400);

            foreach ($layout['xi'] as $xi) {
                $isExist = DB::Connection()->Count("SELECT COUNT(*) FROM users_houses WHERE UserID = :UserID AND ItemID = :ItemID", [
                        [":UserID", $headers["Ccid"], PDO::PARAM_INT],
                        [":ItemID", $xi["ID"], PDO::PARAM_INT],
                    ]) > 0;

                if ($isExist) {
                    DB::Connection()->Prepare("UPDATE users_houses SET X = :X, Y = :Y WHERE UserID = :UserID AND ItemID = :ItemID", [
                        [":X", $xi["x"], PDO::PARAM_INT],
                        [":Y", $xi["y"], PDO::PARAM_INT],
                        [":UserID", $headers["Ccid"], PDO::PARAM_INT],
                        [":ItemID", $xi["ID"], PDO::PARAM_INT],
                    ]);
                } else {
                    DB::Connection()->Prepare("INSERT INTO users_houses (UserID, Frame, ItemID, X, Y) VALUES (:UserID, :Frame, :ItemID, :X, :Y)", [
                        [":UserID", $headers["Ccid"], PDO::PARAM_INT],
                        [":Frame", $_POST["frame"], PDO::PARAM_STR],
                        [":ItemID", $xi["ID"], PDO::PARAM_INT],
                        [":X", $xi["x"], PDO::PARAM_INT],
                        [":Y", $xi["y"], PDO::PARAM_INT]
                    ]);
                }
            }

            DB::Connection()->Instance()->commit();

            die("success");
        } catch (PDOException $PDOException) {
            if (DB::Connection()->Instance()->inTransaction()) DB::Connection()->Instance()->rollBack();
            $this->response_json(config::DEBUG ? ($PDOException->getMessage() . " </br> " . $PDOException->getTraceAsString()) : "4", 500);
        } catch (Exception $exception) {
            $this->response_json(config::DEBUG ? $exception->getMessage() : "5", 500);
        }
    }

}