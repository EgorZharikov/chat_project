<?php

namespace ws\model;
use ws\db\Db;

class WSModel {
    public function add_msg_db($params) {
        $db = new Db();
        $sql = "INSERT INTO messages (id_user, id_room, message, time, status) VALUES (:id_user, :id_room, :message, :time, :status)";
        $id = $db->insert($sql, $params);
        return $id;
    }

    public function get_all_user_contacts($params) {
        $db = new Db();
        $sql = "SELECT DISTINCT id_user
                FROM room_members
                WHERE id_room IN (SELECT id_room
                FROM room_members
                WHERE 
                id_user = :id_user)";
        $result = $db->row($sql, $params);
        return $result;
    }

    public function get_room_members($params) {
        $db = new Db();
        $sql = "SELECT DISTINCT id_user FROM room_members WHERE id_room = :id_room";
        $result = $db->row($sql, $params);
        return $result;
    }

    public function set_status_online($params) {
        $db = new Db();
        $time = time();
        $sql = "UPDATE users SET status = 'online', last_seen = $time WHERE id = :id_user" ;
        $db->query($sql,$params);
    }

    public function set_status_offline($params)
    {
        $db = new Db();
        $time = time();
        $sql = "UPDATE users SET status = 'offline', last_seen = $time WHERE id = :id_user";
        $db->query($sql, $params);
    }

    public function set_msg_status_readed($params) {
        $db = new Db();
        $sql = "UPDATE messages SET status = 'readed' WHERE id = :id";
        $db->query($sql, $params);
    }

}
