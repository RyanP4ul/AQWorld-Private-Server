<?php

namespace App\Controllers\Panel;

use Database\DB;
use PDO;

class ShopController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "shops",
            reqAccess: 40,
            title: "Shops",
            viewTemplate: "panel/shops.html.twig",
            validationRules: [
                "id" => ["required", "int", "min:1"],
                "Name" => ["required", "string", "min:4", "max:20"],
                "House" => ["string", "min:1", "max:2"],
                "Upgrade" => ["string", "min:1", "max:2"],
                "Staff" => ["string", "min:1", "max:2"],
                "Limited" => ["string", "min:1", "max:2"],
                "Field" => ["string"],
            ],
            viewParams: [
                "now" => $this->getDateTime()->format('Y-m-d H:i:s')
            ]
        );
    }

    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if ($_GET["type"] != "update" && DB::Connection()->Exists("shops", "Name", $_POST["Name"]))
        {
            $this->response_json(["msg" => ["Name" => [$_POST["Name"] . " Name has already been taken."]]], 403);
        }

        foreach (["House", "Upgrade", "Staff", "Limited"] as $field) {
            foreach ($data as &$datum) {
                if ($datum[0] === ":$field") {
                    $datum[1] = $this->isChecked($field);
                    $datum[2] = PDO::PARAM_INT;
                }
            }
        }

        return $data;
    }

}