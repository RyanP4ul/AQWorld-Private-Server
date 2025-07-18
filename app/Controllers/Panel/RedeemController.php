<?php

namespace App\Controllers\Panel;

use Database\DB;
use DateTime;
use PDO;

class RedeemController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "redeems",
            reqAccess: 40,
            title: "Redeem",
            viewTemplate: "panel/redeem.html.twig",
            validationRules: [
                "id" => ["required", "int", "min:1"],
                "Code" => ["required", "string", "min:4", "max:20"],
                "Gold" => ["required", "int"],
                "Coins" => ["required", "int"],
                "Exp" => ["required", "int"],
                "ClassPoints" => ["required", "int"],
                "ItemID" => ["required", "int"],
                "Quantity" => ["required", "int"],
                "QuantityLeft" => ["required", "int"],
                "Limited" => ["required", "int"],
                "Expires" => ["required", "int"],
                "DateExpiry" => ["required", "string"],
            ],
            viewParams: [
                "now" => $this->getDateTime()->format('Y-m-d H:i:s')
            ]
        );
    }

    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if ($_GET["type"] != "update" && DB::Connection()->Exists("redeems", "Code", $_POST["Code"]))
        {
            $this->response_json(["msg" => ["Code" => [$_POST["Code"] . " Code has already been taken."]]], 403);
        }

        if (DB::Connection()->Count("SELECT COUNT(*) FROM items WHERE id = :Id", [":Id", $_POST["ItemID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["ItemID" => ["ItemID does not exists!"]]], 403);
        }

        return $data;
    }

}