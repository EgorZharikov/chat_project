<?php

use App\models\AjaxModel;

$input = json_decode(file_get_contents('php://input'), true);
$model = new AjaxModel;
if (isset($input['user'])){
    $login = ['login' => $input['user']];
    $data = $model->get_user($login);
    echo json_encode($data);
}

if (isset($input['create_room'])) {
    $params = ['privacy' => $input['privacy']];
    $id = $model->create_private_room($params);
    $params = ['id_room' => $id, 'id_user' => $input['user_id']];
    $model->add_room_member($params);
    $params = ['id_room' => $id, 'id_user' => $input['members']];
    $model->add_room_member($params);
    echo json_encode($id);
}

if (isset($input['get_private_rooms'])) {
    $params = ['id_user' => intval($input['user_id'])];
    $data = $model->get_private_rooms($params);
    echo json_encode($data);
}

if (isset($input['id_room'])) {
    $params = ['id_user' => intval($input['id_user']), 'id_room' => intval($input['id_room'])];
    $room_data = $model->get_room_data($params);
    echo json_encode($room_data);
}

if (isset($input['delete_msg'])) {
    $params = ['id' => intval($input['id'])];
    $model->delete_msg($params);
    echo json_encode(true);

}

if (isset($input['check_login'])) {
    $params = ['login' => $input['login']];
    $result = $model->check_login_availability($params);
    if(!$result) {
        echo json_encode(true);
    } else {
        echo json_encode(false);
    }
}

if (isset($input['get_rooms'])) {
    $params = ['id_user' => intval($input['id_user'])];
    $chat_rooms = $model->get_chat_rooms($params);
    $unread_msg = $model->get_unreaded_msg($params);
    $result = ['chat_rooms' => $chat_rooms,'unread_msg' => $unread_msg];
    echo json_encode($result);
}

if (isset($input['get_unread_msg'])) {
    $params = ['id_user' => intval($input['id_user'])];
    $result = $model->get_unreaded_msg($params);
    echo json_encode($result);
}

if(isset($input['edit_msg'])) {
    $params = ['message' => $input['message'], 'id' => intval($input['id']), 'id_user' => intval($input['sender_id'])];
    $result = $model->edit_message($params);
    if ($result) {
        echo json_encode(true);
    } else {
        echo json_encode(false);
    }
}