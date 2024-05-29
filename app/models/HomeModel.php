<?php

namespace App\models;

use App\core\Db\Db;
use App\core\Uploader\Uploader;
use App\core\Validator\Validator;
use App\core\Redirect\Redirect;

class HomeModel
{
        public static function get_private_chat_rooms($id)
        {
                $params = ['id_user' => intval($id)];
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

        public static function get_group_room($id)
        {
                $params = ['id_user' => intval($id)];
                $db = new Db;
                $sql = "SELECT g.id_room, g.title, g.logo
                        FROM group_rooms_settings AS g
                        JOIN chat_rooms AS c ON c.id = g.id_room
                        WHERE c.privacy = 0 AND g.id_room IN  
                        (SELECT id_room FROM room_members WHERE id_user = :id_user)";
                $result = $db->row($sql, $params);
                return $result;
        }

        public static function check_user_actions()
        {
                if (!empty($_FILES) && isset($_POST['users']) && $_POST['room-title']) {
                        $params = ['privacy' => 0];
                        $title = Validator::test_input($_POST['room-title']);
                        $id_room = self::create_group_room($params);
                        $file_name = 'room_' . $id_room;
                        $uploaded_img = Uploader::get_uploaded_img($file_name);
                        if (empty($uploaded_img['errors'])) {
                                $params = ['id_room' => $id_room, 'title' => $title, 'logo' => $uploaded_img['file_name']];
                                self::set_room_settings($params);
                                $users = $_POST['users'];
                                foreach ($users as $user) {
                                        $params = ['id_room' => $id_room, 'id_user' => $user];
                                        self::add_room_member($params);
                                }
                        } else {
                                self::delete_group_room($id_room);
                                $_SESSION['errors'] = $uploaded_img['errors'];
                        }

                        Redirect::redirect('/home');
                }
        }

        public static function create_group_room($params)
        {
                $db = new Db();
                $sql = "INSERT INTO chat_rooms (privacy) VALUES (:privacy)";
                $id = $db->insert($sql, $params);
                return $id;
        }

        public static function delete_group_room($id) {
                $db = new Db();
                $sql = "DELETE FROM chat_rooms WHERE id = $id";
                $db->query($sql);
        }

        public static function set_room_settings($params)
        {
                $db = new Db();
                $sql = "INSERT INTO group_rooms_settings (id_room, title, logo) VALUES (:id_room, :title, :logo)";
                $id = $db->insert($sql, $params);
                return $id;
        }

        public static function add_room_member($params)
        {
                $db = new Db();
                $sql = "INSERT INTO room_members (id_room, id_user) VALUES (:id_room, :id_user)";
                $id = $db->insert($sql, $params);
        }

        public static function get_unreaded_msg($id)
        {
                $db = new Db();
                $params = ['id_user' => intval($id)];
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

        public static function get_user_settings($id)
        {
                $db = new Db;
                $params = ['id_user' => intval($id)];
                $sql = "SELECT sound, email_privacy FROM user_settings WHERE id_user = :id_user";
                $result = $db->row($sql, $params);
                return $result;
        }

        public static function get_user_data($id)
        {
                $db = new Db;
                $params = ['id_user' => intval($id)];
                $sql = "SELECT login, email, status, avatar FROM users WHERE id = :id_user";
                $result = $db->row($sql, $params);
                return $result;
        }
}
