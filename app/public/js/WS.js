const wsUri = `ws://localhost:9000/server.php?id=${id.innerHTML}`;

websocket = new WebSocket(wsUri)

websocket.onopen = function () {
    console.log('Соединение установлено');
    connected()
}

websocket.onmessage = function (ev) {
    const response = JSON.parse(ev.data)
    let date = new Date(response.time * 1000).toLocaleString();
    const res_type = response.type
    const user_message = response.message ? response.message : false
    const user_name = response.sender_name ? response.sender_name : false
    const id_room = response.room ? response.room : false
    const id_message = response.id_message ? response.id_message : false
    const id_user = response.sender_id ? response.sender_id : false

    switch (res_type) {
        case 'usermsg':
            let li = document.createElement('li');
            let check_room_exist = document.querySelector(`.room-${id_room}`)
            if (!check_room_exist) {
                render_chatrooms()
            }
            if (Number(id_user) === Number(id.innerHTML)) {
                li.className = "repaly";
                li.innerHTML = `<span id="msgID" hidden>${id_message}</span>
                                <span id="userID" hidden>${id_user}</span>
                                <div class="dropup-center dropstart">
                                <p id="msg-${id_message}-value" data-bs-toggle="dropdown">${user_message}</p>
                                <ul class="dropdown-menu">
                                <li><a class="dropdown-item" id="msg-delete" href="#">Удалить сообщение</a></li>
                                <li><a class="dropdown-item" id="msg-forward" href="#">Переслать сообщение</a></li>
                                <li><a class="dropdown-item" id="msg-edit" href="#">Редактировать сообщение</a></li>
                                </ul>
                                <div class="d-flex justify-content-end">
                                <img class="msg-status me-2 pb-1" id="msg-${id_message}" src="/img/sys/unreaded.png" alt="msg status">
                                <span class="time">${date}</span>
                                </div>
                                </div>`
                msgBody.appendChild(li);

                li.querySelector('#msg-delete').addEventListener('click', function (e) {
                    e.preventDefault();
                    delete_message(id_message);
                    msgBody.removeChild(li);

                })

                li.querySelector('#msg-forward').addEventListener('click', function (e) {
                    e.preventDefault();
                    message_input.value = li.querySelector('p').innerHTML;
                })

                li.querySelector('#msg-edit').addEventListener('click', function (e) {
                    e.preventDefault();
                    let msg_id = document.querySelector('#msg-id') 
                    sendBox.style.display = 'none'
                    editBox.style.removeProperty('display');
                    message_edit_input.value = li.querySelector('p').innerHTML;
                    msg_id.innerHTML = id_message
                })

                if (autoScroll) {
                    scrollToBottom(chatWindow);
                    message.focus();
                }
            } else if (Number(chatRoom.innerHTML) === Number(id_room)) {
                if (Number(id_user) != Number(id.innerHTML)) {
                    read_message(id_message, id_user);
                }
                li.className = "sender";
                li.innerHTML = `<span id="msgID" hidden>${id_message}</span>
                                <span id="userID" hidden>${id_user}</span>
                                <span class="sender-name">${user_name}</span>
                                <div class="dropup-center dropend">
                                <p id="msg-${id_message}-value" data-bs-toggle="dropdown">${user_message}</p>
                                <ul class="dropdown-menu">
                                <li><a class="dropdown-item" id="msg-forward" href="#">Переслать сообщение</a></li>
                                </ul>
                                <span class="time">${date}</span>`
                msgBody.appendChild(li);

                li.querySelector('#msg-forward').addEventListener('click', function (e) {
                    e.preventDefault();
                    message_input.value = li.querySelector('p').innerHTML;
                })

                if (autoScroll) {
                    scrollToBottom(chatWindow);
                    message.focus();
                }
            } else if (Number(chatRoom.innerHTML) !== Number(id_room)) {
                let room = document.querySelector(`.room-${id_room}`);
                let notification = room.querySelector('#notification');
                let div = document.createElement('div');
                if (!notification.querySelector('#new-msg')) {
                    div.id = 'new-msg';
                    div.innerHTML = '<img class="new-msg" src="/img/sys/new_msg.png" alt="new msg">';
                    notification.appendChild(div);
                }

                audio_notification()
            }

            break
        case 'system':
            // console.log(user_message)
            // messageBox.append('div style="color:#bbbbbb">' + user_message + '</div>')
            break
        case 'msg_status':
            let msg_id = response.msg_id
            let msg_status = response.status
            let msg = document.querySelector(`#msg-${msg_id}`)
            if (msg) {
                msg.src = "/img/sys/readed.png"
            }
            break
        case 'user_status':
            const user_id = response.user_id
            const user_status = response.status
            if (Number(user_id) != Number(id.innerHTML)) {
                roomStatus = document.querySelectorAll(`#room-${user_id}-status`);
                roomStatus.forEach(function (elem) {
                    elem.className = user_status;
                })
            }

            if (document.querySelector(`#user_${user_id}_status`)) {
                document.querySelector(`#user_${user_id}_status`).className = user_status;
            }
            break
        case 'delete_msg':
            if (Number(chatRoom.innerHTML) === Number(id_room) &&
                Number(id_user) != Number(id.innerHTML)) {
                let del_msg = document.querySelector(`#msg-${id_message}-value`)
                if (del_msg) {
                    del_msg.innerHTML = 'Сообщение удалено пользователем!'
                }
            }
            break
        case 'edit_msg':

            if (Number(chatRoom.innerHTML) === Number(id_room) &&
                Number(id_user) != Number(id.innerHTML)) {
                let message = response.message
                let edit_msg = document.querySelector(`#msg-${id_message}-value`)
                if (edit_msg) {
                    edit_msg.innerHTML = message
                }
            }
            break
    }
}

websocket.onerror = function (ev) {
    console.log(ev.data);
    disconnected()
}

websocket.onclose = function () {
    console.log('соеденение закрыто');
    disconnected()
}

document.querySelector('#send').addEventListener('click', function (e) {
    e.preventDefault();
    if (message_input.value.length >= 1) {
        send_message()
    }
})

message_input.addEventListener('keypress', function (e) {

    if (e.code === 'Enter') {
        e.preventDefault();
        if (message_input.value.length >= 1) send_message();
    }
})



function send_message() {
    let result_message = {
        message: message_input.value,
        sender_id: profileId.innerHTML,
        sender_name: userName.innerHTML,
        room: chatRoom.innerHTML,
        type: 'user_msg',
        status: 'unreaded'
    }
    websocket.send(JSON.stringify(result_message))
    message_input.value = '';
}

function read_message(messageID, senderID) {
    let msg = {
        sender_id: senderID,
        room: chatRoom.innerHTML,
        msg_id: messageID,
        type: 'msg_status'
    }
    websocket.send(JSON.stringify(msg))
}

function disconnected() {
    let div = document.createElement('div');
    chatHeader.innerHTML = '';
    div.id = "connection";
    div.innerHTML = `<img src="/img/sys/disconnection.png">
                    <h3 id="connection-error">Подключение к серверу не установленно!<h3>`
    chatHeader.appendChild(div);
}

function connected() {
    let div = document.createElement('div');
    div.id = "connection";
    chatHeader.innerHTML = '';
    div.innerHTML = `<img src="/img/sys/connection.png">
                    <h3 id="connection-success">Соединение с сервером установленно!<h3>`
    chatHeader.appendChild(div);
}
