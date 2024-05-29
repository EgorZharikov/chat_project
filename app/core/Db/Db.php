<?php

namespace App\core\Db;

use PDO;

class Db
{

    protected $db;

    public function __construct()
    {

        // foreach (PDO::getAvailableDrivers() as $driver) {
        //     echo $driver . PHP_EOL;
        // }
        $this->db = new PDO("sqlite:" . DATABASE . 'chat.db');
    }

    public function query($sql, $params = [])
    {
        $this->db->exec('PRAGMA foreign_keys = ON;');
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
        }
        $stmt->execute();
        return $stmt;
    }

    public function row($sql, $params = [])
    {
        $result = $this->query($sql, $params);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function column($sql, $params = [])
    {
        $result = $this->query($sql, $params);
        return $result->fetchColumn();
    }

    public function insert($sql, $params = [])
    {
        $this->db->exec('PRAGMA foreign_keys = ON;');
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
        }
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function beginTransaction()
    {
        $this->db->beginTransaction();
    }

    public function commit()
    {
        $this->db->commit();
    }
}
