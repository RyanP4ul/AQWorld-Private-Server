<?php

namespace Database;

use App\config;
use Exception;
use PDO;
use PDOException;
use PDOStatement;

class DB {

    private static PDO|null $_instance = null;

    public static function Connection(string $hostName = config::DB_USER) : DB
    {
        if (self::$_instance == null)
        {
            try {
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT         => true,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_TIMEOUT            => 5
                ];

                if (config::SSL)
                {
                    $options[] = [
                        PDO::MYSQL_ATTR_SSL_CA => 'ca-cert.pem',
                        PDO::MYSQL_ATTR_SSL_CERT => 'client-cert.pem',
                        PDO::MYSQL_ATTR_SSL_KEY => 'client-key.pem'
                    ];
                }

                self::$_instance = new PDO(sprintf("mysql:host=%s:%d;dbname=%s;charset=%s", config::DB_HOST, config::DB_PORT, config::DB_NAME, config::DB_CHARSET), $hostName, config::DB_PASS, $options);
            } catch (PDOException $exception) {
                die('Connection failed: ' . $exception->getMessage());
            }
        }

        return new static();
    }

    public function Instance() : PDO { return self::$_instance; }

    public function Prepare(string $sql, array $params = []) : PDOStatement
    {
        $stmt = self::$_instance->prepare($sql);

        if (count($params) > 0)
        {
            if (is_string($params[0]))
            {
                if (count($params) == 4)
                {
                    $stmt->bindParam($params[0], $params[1], $params[2], $params[3]);
                }
                else
                {
                    $stmt->bindParam($params[0], $params[1], $params[2]);
                }
            }
            else if (is_array($params[0]))
            {
                foreach ($params as $param)
                {
                    if (count($param) == 4)
                    {
                        $stmt->bindParam($param[0], $param[1], $param[2], $param[3]);
                    }
                    else
                    {
                        $stmt->bindParam($param[0], $param[1], $param[2]);
                    }
                }
            }
            else
            {
                throw new PDOException("Unsupported data type");
            }
        }

        $stmt->execute();

        return $stmt;
    }

    public function IsTableExist(string $tableName) : bool
    {
        try
        {
            $escapedTableName = self::$_instance->quote($tableName);
            return self::$_instance->query("SHOW TABLES LIKE $escapedTableName")->rowCount() > 0;
        }
        catch (PDOException $e)
        {
            throw new PDOException($e->getMessage());
        }
    }

    public function InsertGetId(string $sql, array $params = []) : int
    {
        $this->Prepare($sql, $params);
        return self::$_instance->lastInsertId();
    }

    public function First(string $sql, array $params = [], int $mode = PDO::FETCH_ASSOC) : mixed
    {
        $stmt = $this->Prepare($sql, $params);
        return $stmt->fetch($mode);
    }

    public function Get(string $sql, array $params = [], int $mode = PDO::FETCH_ASSOC) : array|object|false
    {
        $stmt = $this->Prepare($sql, $params);
        return $stmt->fetchAll($mode);
    }

    public function Count(string $sql, array $params = []) : int
    {
        $stmt = $this->Prepare($sql, $params);
        return $stmt->fetchColumn();
    }

    public function Exists(string $table, int|string $col, int|string $val) : bool
    {
        $paramType = is_int($col) ? PDO::PARAM_INT : PDO::PARAM_STR;
        return DB::Connection()->Count("SELECT COUNT(*) FROM $table WHERE $col = :Col", [":Col", $val, $paramType]) > 0;
    }

}