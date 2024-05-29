<?php

namespace App\models;

use App\core\Db\Db;

class AjaxModel
{
    public function get_user($params)
    {
        $db = new Db;
        $sql = "SELECT u.id, u.email, u.login, u.status, u.avatar
                FROM users AS u
                JOIN user_settings AS us ON us.id_user = u.id
                WHERE login like :login OR email like :login AND us.email_privacy = 0;";
        $userdata = $db->row($sql, $params);
        return $userdata;
    }

    public function create_private_room($params)
    {
        $db = new Db();
        $sql = "INSERT INTO chat_rooms (privacy) VALUES (:privacy)";
        $id = $db->insert($sql, $params);
        return $id;
    }

    public function add_room_member($params)
    {
        $db = new Db();
        $sql = "INSERT INTO room_members (id_room, id_user) VALUES (:id_room, :id_user)";
        $id = $db->insert($sql, $params);
        return $id;
    }

    public function get_private_rooms($params) {
        $db = new Db;
        $sql = "SELECT u.login, u.status, u.avatar, c.id AS id_room, c.privacy, r.id_user
                FROM users AS u
                JOIN room_members AS r ON r.id_user = u.id 
                JOIN chat_rooms AS c ON c.id  = r.id_room
                WHERE  r.id_user  != :id_user AND c.privacy = 1 AND r.id_room  IN
                (SELECT id_room FROM room_members WHERE id_user = :id_user)";
        $result = $db->row($sql, $params);
        return $result;
    }

    public function get_room_data($params) {
        $db = new Db;
        $sql = "SELECT u.login, u.avatar, u.id, u.status
                FROM users as u
                JOIN room_members AS r ON r.id_user = u.id
                WHERE r.id_room = :id_room AND u.id != :id_user";

        $room_members = $db->row($sql, $params);

        $sql = "SELECT m.id, m.message, m.id_user, m.status, u.login, m.time
                FROM messages AS m
                JOIN users as u ON u.id = m.id_user 
                WHERE id_room  = :id_room
                ORDER BY m.time";
        $room_id = ['id_room' => $params['id_room']];
        $messages = $db->row($sql, $room_id);
        $result = ['room_members' => $room_members, 'messages' => $messages];
        return $result;

    }

    public function delete_msg($params) {
        $db = new Db;
        $sql = "DELETE FROM messages WHERE id = :id";
        $db->query($sql, $params);
    }

    public function check_login_availability($params) {
        $db = new Db;
        $sql = "SELECT id FROM users WHERE login = :login OR email = :login";
        $result =  $db->column($sql, $params);
        return $result;
    }

    public function get_chat_rooms($params) {
        $db = new Db;
        $sql = "SELECT u.login, u.status, u.avatar, c.id AS id_room, c.privacy, r.id_user, grs.title, grs.logo, MAX(m.time)
                FROM users AS u
                JOIN room_members AS r ON r.id_user = u.id
                JOIN chat_rooms AS c ON c.id  = r.id_room
                LEFT OUTER JOIN group_rooms_settings AS grs ON r.id_room = grs.id_room
                LEFT OUTER JOIN messages AS m ON c.id = m.id_room
                WHERE r.id_user != :id_user AND r.id_room IN
                (SELECT id_room FROM room_members WHERE id_user = :id_user)
                GROUP BY c.id
                ORDER BY m.time DESC";
        $result = $db->row($sql, $params);
        return $result;
    }

    public static function get_unreaded_msg($params)
    {
        $db = new Db();
        $sql = "SELECT DISTINCT id_room 
                FROM messages
                WHERE status = 'unreaded'AND id_user != :id_user AND id_room in (SELECT id_room FROM room_members WHERE id_user  = :id_user)";
        $result = $db->row($sql, $params);
        $arrOut = array();
        foreach ($result as $subArr) {
            $arrOut = array_merge($arrOut, array_values($subArr));
        }
        return $arrOut;
    }

    public function edit_message($params)
    {
        $db = new Db();
        $sql = "UPDATE messages SET  message = :message WHERE id = :id AND id_user = :id_user;";
        $db->query($sql, $params);
        return true;
    }

}

