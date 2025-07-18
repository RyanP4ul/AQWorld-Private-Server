<?php

namespace App\Controllers\Panel;

use App\config;
use Database\DB;
use PDO;
use PDOException;

class FactionController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "factions",
            reqAccess: 40,
            title: "Factions",
            viewTemplate: "panel/factions.html.twig",
            validationRules: [
                "id" => ["required", 'int', "min:1"],
                "Name" => ["required", "string", "max:32"]
            ],
            viewParams: []
        );
    }

    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if ($_GET["type"] != "update" && DB::Connection()->Exists("factions", "Name", $_POST["Name"]))
        {
            $this->response_json(["msg" => ["Name" => [$_POST["Name"] . " Name has already been taken."]]], 403);
        }

        return $data;
    }

}