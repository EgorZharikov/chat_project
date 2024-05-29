<?php
namespace ws;
use ws\model\WSModel;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
$WSModel = new WSModel;

$host = HOST; //host
$port = PORT; //port
$null = NULL; //null var

//Create TCP/IP sream socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
//reuseable port
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

//bind socket to specified host
socket_bind($socket, 0, $port);

//listen to port
socket_listen($socket);

//create & add listning socket to the list
$clients = array($socket);

//start endless loop, so that our script doesn't stop
while (true) {
    //manage multipal connections
    $changed = $clients;
    //returns the socket resources in $changed array
    socket_select($changed, $null, $null, 0, 10);

    //check for new socket
    if (in_array($socket, $changed)) {
        $socket_new = socket_accept($socket); //accpet new socket
        $header = socket_read($socket_new, 1024); //read data sent by the socket
        $headersArr = explode("\n", $header);
        $params = explode(" ", $headersArr[0]);
        $parts = parse_url($params[1]);
        parse_str($parts['query'], $query);
        
        $clients[$query['id']] = $socket_new; //add socket to client array
        $params = ['id_user' => $query['id']];
        //update user status
        $WSModel->set_status_online($params);

        perform_handshaking($header, $socket_new, $host, $port); //perform websocket handshake

        socket_getpeername($socket_new, $ip); //get ip address of connected socket

        $response = mask(json_encode(array('type' => 'system', 'message' => 'Connection success'))); //prepare json data
        send_message($response, $query['id']);

        $response = mask(json_encode(array('type' => 'user_status', 'user_id' => $query['id'], 'status' => 'online')));
        $id_users = $WSModel->get_all_user_contacts($params);
        $contacts = array();
        foreach($id_users as $key => $value) {
            $contacts[$key] = $value['id_user'];
        }

        send_message($response, null, $contacts); //notify all friends about new connection

        //make room for new socket
        $found_socket = array_search($socket, $changed);
        unset($changed[$found_socket]);
    }

    //loop through all connected sockets
    foreach ($changed as $changed_socket => $value) {

        //check for any incomming data
        while (socket_recv($value, $buf, 1024, 0) >= 1) {
            $received_text = unmask($buf); //unmask data
            $msg_data = json_decode($received_text, true); //json decode

            if(isset($msg_data['type']) && $msg_data['type'] == 'msg_status') {
                $params = ['id' => $msg_data['msg_id']];
                $WSModel->set_msg_status_readed($params);
                $response_text = mask(json_encode(array('type' => 'msg_status', 'room' => $msg_data['room'], 'msg_id' => $msg_data['msg_id'], 'status' => 'readed')));
                send_message($response_text, $msg_data['sender_id'], null); //send data

            } else if (isset($msg_data['type']) && $msg_data['type'] == 'user_msg') {
            $sender_id = $msg_data['sender_id']; 
            $sender_name = $msg_data['sender_name'];//sender na
            $message = $msg_data['message']; //message text
            $room = $msg_data['room'];
            $time = time();
            $status = $msg_data['status'];

            // $contacts = [$changed_socket,intval($for_user)];
            $params = ['id_room' => $room];
            $id_users = $WSModel->get_room_members($params);
            $contacts = array();

            foreach ($id_users as $key => $value) {
                $contacts[$key] = $value['id_user'];
            }

            $params = ['id_user' => $sender_id, 'id_room' => $room, 'message' => $message, 'time' => $time, 'status' => $status];
            $id_message = $WSModel-> add_msg_db($params);
            //prepare data to be sent to client
            $response_text = mask(json_encode(array('type' => 'usermsg', 'sender_id' => $sender_id, 'sender_name' => $sender_name, 'message' => $message, 'room' => $room, 'id_message' => $id_message, 'time' => $time)));
            send_message($response_text, null, $contacts); //send data

        } else if(isset($msg_data['type']) && $msg_data['type'] == 'delete_msg') {
            $message_id = $msg_data['id_message'];
            $room = $msg_data['room'];
            $sender_id = $msg_data['sender_id'];
            $params = ['id_room' => $room];
            $id_users = $WSModel->get_room_members($params);
            $contacts = array();

            foreach ($id_users as $key => $value) {
                $contacts[$key] = $value['id_user'];
            }

            $response_text = mask(json_encode(array('type' => 'delete_msg', 'room' => $room, 'id_message' => $id_message,'sender_id' => $sender_id)));
            send_message($response_text, null, $contacts); //send data

        } else if (isset($msg_data['type']) && $msg_data['type'] == 'edit_msg') {
            $message_id = $msg_data['id_message'];
            $room = $msg_data['room'];
            $sender_id = $msg_data['sender_id'];
            $message = $msg_data['message'];
            $params = ['id_room' => $room];
            $id_users = $WSModel->get_room_members($params);
            $contacts = array();


            foreach ($id_users as $key => $value) {
                $contacts[$key] = $value['id_user'];
            }

            $response_text = mask(json_encode(array('type' => 'edit_msg', 'room' => $room, 'id_message' => $message_id,'sender_id' => $sender_id, 'message' => $message)));
            send_message($response_text, null, $contacts); //send data
        }
            break 2; //exist this loop
        }

        $buf = @socket_read($value, 1024, PHP_NORMAL_READ);
        if ($buf === false) { // check disconnected client
            // remove client for $clients array
            $found_socket = array_search($value, $clients);
            socket_getpeername($value, $ip);
            unset($clients[$found_socket]);
            $params = ['id_user' => $query['id']];
            //update user status
            $WSModel->set_status_offline($params);
            $id_users = $WSModel->get_all_user_contacts($params);
            $contacts = array();
            foreach ($id_users as $key => $value) {
                $contacts[$key] = $value['id_user'];
            }

            //notify all users about disconnected connection
            $response = mask(json_encode(array('type' => 'user_status', 'user_id' => $query['id'], 'status' => 'offline')));

            send_message($response,$id = null , $contacts);
        }
    }
}
// close the listening socket
socket_close($socket);

function send_message($msg, $id=null, $contacts=null)
{
    global $clients;
    if ($contacts != null) {
        foreach ($contacts as $contact) {
            if (isset($clients[$contact])) {
                @socket_write($clients[$contact], $msg, strlen($msg));
            }
        }
    }
    foreach ($clients as $changed_socket => $value) {
        if(intval($id) === intval($changed_socket)) {
        @socket_write($value, $msg, strlen($msg));
        }
    }
    return true;
}


//Unmask incoming framed message
function unmask($text)
{
    $length = ord($text[1]) & 127;
    if ($length == 126) {
        $masks = substr($text, 4, 4);
        $data = substr($text, 8);
    } elseif ($length == 127) {
        $masks = substr($text, 10, 4);
        $data = substr($text, 14);
    } else {
        $masks = substr($text, 2, 4);
        $data = substr($text, 6);
    }
    $text = "";
    for ($i = 0; $i < strlen($data); ++$i) {
        $text .= $data[$i] ^ $masks[$i % 4];
    }
    return $text;
}

//Encode message for transfer to client.
function mask($text)
{
    $b1 = 0x80 | (0x1 & 0x0f);
    $length = strlen($text);

    if ($length <= 125)
        $header = pack('CC', $b1, $length);
    elseif ($length > 125 && $length < 65536)
        $header = pack('CCn', $b1, 126, $length);
    elseif ($length >= 65536)
        $header = pack('CCNN', $b1, 127, $length);
    return $header . $text;
}

//handshake new client.
function perform_handshaking($receved_header, $client_conn, $host, $port)
{
    $headers = array();
    $lines = preg_split("/\r\n/", $receved_header);
    foreach ($lines as $line) {
        $line = chop($line);
        if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
            $headers[$matches[1]] = $matches[2];
        }
    }

    $secKey = $headers['Sec-WebSocket-Key'];
    $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
    //hand shaking header
    $upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "WebSocket-Origin: $host\r\n" .
        "WebSocket-Location: ws://$host:$port/shout.php\r\n" .
        "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
    socket_write($client_conn, $upgrade, strlen($upgrade));
}
