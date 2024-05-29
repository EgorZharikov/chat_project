const toastLiveExample = document.getElementById('liveToast')
const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample)
const alert = document.querySelector('.toast-body')
const logo = document.querySelector('#inputFile')
const title = document.querySelector('#room-title')
const members = document.querySelector('#add-group');
const message = document.querySelector('#message');
const id = document.querySelector('#profile-id');
const msg_id = document.querySelector('#msg-id');
const message_input = document.querySelector('#message');
const message_edit_input = document.querySelector('#edit-message');
const userName = document.querySelector('.profile-title');
const roomList = document.querySelectorAll('#chat-rooms a');
const chatRoom = document.querySelector('#chat-room');
const chatBox = document.querySelector('.chatbox');
const chatIcon = document.querySelector('#chat-icon');
const profileId = document.querySelector('#profile-id');
const chatHeader = document.querySelector('#chat-header');
const msgBody = document.querySelector('.msg-body ul');
const msgDelete = document.querySelector('#msg-delete');
const msgForward = document.querySelector('#msg-forward');
const sendBox = document.querySelector('#send-box');
const editBox = document.querySelector('#edit-box');
const chatRooms = document.querySelector('#chat-rooms');
const editClose = document.querySelector('#close');
const editBtn = document.querySelector('#edit');
const modalBox = document.querySelector('#modalbox');
sendBox.style.display = 'none';
editBox.style.display = 'none';

//отрисовка чатов пользователя



// вывод ошибок при создании группового чата
document.querySelector('#group-form').onsubmit = function () {

  if (!title.value || members.childNodes.length <= 1 || !logo.value) {
    let error = ''
    error += !title.value ? '#Вы не указали название чата!<br>' : ''
    error += (members.childNodes.length <= 1) ? '#Вы не выбрали участников чата!<br>' : ''
    error += !logo.value ? '#Вы не выбрали логотип чата!<br>' : ''
    alert.innerHTML = error
    toastBootstrap.show()
    return false
  }
}

//autoScrool
var autoScroll = true;
const chatWindow = document.querySelector('#chatBox');
chatWindow.addEventListener('scroll', function () {
  var scrollTop = this.scrollTop;
  var scrollHeight = this.scrollHeight;
  var height = this.clientHeight;

  if (autoScroll) {
    if (scrollTop < scrollHeight - height) {
      autoScroll = false;
    }
  } else {
    if (scrollTop + height >= scrollHeight) {
      autoScroll = true;
    }
  }
});

chatIcon.addEventListener('click', function () {
  modalBox.classList.remove('showbox')
})

function scrollToBottom(elem) {
  elem.scrollTop = elem.scrollHeight;
}
//функция удаления сообщения
function delete_message(id) {
  let msg = {
    delete_msg: true,
    id: id
  }

  fetch('/home/ajax', {
    method: 'POST',
    // Тело запроса в JSON-формате
    body: JSON.stringify(msg),
    headers: {
      // Добавляем необходимые заголовки
      'Content-type': 'application/json; charset=UTF-8',
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data) {
        let del_msg = {
          sender_id: profileId.innerHTML,
          id_message: id,
          room: chatRoom.innerHTML,
          type: 'delete_msg'
        }
        websocket.send(JSON.stringify(del_msg))
      }
    })

}

function edit_message(id_message, message) {
  let msg = {
    edit_msg: true,
    sender_id: profileId.innerHTML,
    id: id_message,
    message: message
  }

  fetch('/home/ajax', {
    method: 'POST',
    // Тело запроса в JSON-формате
    body: JSON.stringify(msg),
    headers: {
      // Добавляем необходимые заголовки
      'Content-type': 'application/json; charset=UTF-8',
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data) {
        let edit_msg = {
          sender_id: profileId.innerHTML,
          id_message: id_message,
          room: chatRoom.innerHTML,
          message: message,
          type: 'edit_msg'
        }
        websocket.send(JSON.stringify(edit_msg))
      }
    })

}

editBtn.addEventListener('click', function () {
  if (message_edit_input.value.length >= 1) {
    let id_msg = msg_id.innerHTML
    let message = message_edit_input.value
    edit_message(id_msg, message);
    let msg = document.querySelector(`#msg-${id_msg}-value`)
    if (msg) {
      msg.innerHTML = message_edit_input.value
    }
    message_edit_input.value = ''
    msg_id.innerHTML = ''
    editBox.style.display = 'none'
    sendBox.style.removeProperty('display');
  }
})

editClose.addEventListener('click', function () {
  message_edit_input.value = ''
  msg_id.innerHTML = ''
  editBox.style.display = 'none'
  sendBox.style.removeProperty('display');
})


// загрузка сообщений и участников чата
function get_room_data(elem) {
  elem.addEventListener('click', function (e) {
    e.preventDefault();
    modalBox.classList.add('showbox')
    sendBox.style.removeProperty('display');
    let notification = elem.querySelector('#new-msg');
    if (notification) {
      notification.remove();
    }
    roomID = elem.querySelector('#room-id');
    chatRoom.innerHTML = roomID.innerHTML;
    let child = chatHeader.lastElementChild;
    let childB = msgBody.lastElementChild;
    while (child) {
      chatHeader.removeChild(child);
      child = chatHeader.lastElementChild;
    }
    while (childB) {
      msgBody.removeChild(childB);
      childB = msgBody.lastElementChild;
    }

    let roomData = {
      id_user: profileId.innerHTML,
      id_room: roomID.innerHTML
    }

    fetch('/home/ajax', {
      method: 'POST',
      // Тело запроса в JSON-формате
      body: JSON.stringify(roomData),
      headers: {
        // Добавляем необходимые заголовки
        'Content-type': 'application/json; charset=UTF-8',
      },
    })
      .then((response) => response.json())
      .then((data) => {
        for (let value of data['room_members']) {
          let div = document.createElement("div");
          div.className = 'col';
          div.innerHTML = `<div class="col">
                            <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                            <img class="avatar" src="/img/${value.avatar}?chache=${Date.now()}" alt="user img">
                            <span class="${value.status}" id="user_${value.id}_status"></span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                            <h3 class="room-member">${value.login}</h3>
                            </div>
                            </div>
                            </div>`
          chatHeader.appendChild(div);
        }
        for (let value of data['messages']) {
          let date = new Date(value.time * 1000).toLocaleString();
          let li = document.createElement('li');
          if (Number(value.id_user) === Number(profileId.innerHTML)) {
            li.className = "repaly";
            li.innerHTML = `<span id="msgID-${value.id}" hidden></span>
                                <span id="userID" hidden>${value.id_user}</span>
                                <div class="dropup-center dropup">
                                <p id="msg-${value.id}-value" data-bs-toggle="dropdown">${value.message}</p>
                                <ul class="dropdown-menu">
                                <li><a class="dropdown-item" id="msg-delete" href="#">Удалить сообщение</a></li>
                                <li><a class="dropdown-item" id="msg-forward" href="#">Переслать сообщение</a></li>
                                <li><a class="dropdown-item" id="msg-edit" href="#">Редактировать сообщение</a></li>
                                </ul>
                                <div class="d-flex justify-content-end">
                                <img class="msg-status me-2 pb-1" src="/img/sys/${value.status}.png" alt="msg status">
                                <span class="time">${date}</span>
                                </div>
                                </div>`
            msgBody.appendChild(li);
            li.querySelector('#msg-delete').addEventListener('click', function (e) {
              e.preventDefault();
              delete_message(value.id);
              msgBody.removeChild(li);
            })

            li.querySelector('#msg-forward').addEventListener('click', function (e) {
              e.preventDefault();
              message_input.value = li.querySelector('p').innerHTML;
            })

            li.querySelector('#msg-edit').addEventListener('click', function (e) {
              e.preventDefault();
              sendBox.style.display = 'none'
              editBox.style.removeProperty('display');
              message_edit_input.value = li.querySelector('p').innerHTML;
              msg_id.innerHTML = value.id
            })
            scrollToBottom(chatWindow);
          } else {
            li.className = "sender";
            li.innerHTML = `<span id="msgID-${value.id}" hidden>${value.id}</span>
                            <span id="userID" hidden>${value.id_user}</span>
                            <span class="sender-name">${value.login}</span>
                            <div class="dropup-center dropend">
                            <p id="msg-${value.id}-value" data-bs-toggle="dropdown">${value.message}</p>
                            <ul class="dropdown-menu">
                            <li><a class="dropdown-item" id="msg-forward" href="#">Переслать сообщение</a></li>
                            </ul>
                            <span class="time">${date}</span>`
            msgBody.appendChild(li);
            if (value.status === 'unreaded') {
              read_message(value.id, value.id_user);
            }
            li.querySelector('#msg-forward').addEventListener('click', function (e) {
              e.preventDefault();
              message_input.value = li.querySelector('p').innerHTML;
            })
            scrollToBottom(chatWindow);
          }

        }
        message.focus();
      })
  })
}

//поиск пользователей
document.querySelector('#search').addEventListener('keydown', function () {
  let searchInput = document.querySelector('#search');
  let addUsers = document.querySelector('#add-users');
  let searchList = document.querySelector('#search-list');
  let profileId = document.querySelector('#profile-id');
  let child = searchList.lastElementChild;
    while (child) {
    searchList.removeChild(child);
    child = searchList.lastElementChild;
  }
  let searchUser = {
    user: searchInput.value + '%'
  }
  if (searchInput.value.length > 2) {

    fetch('/home/ajax', {
      method: 'POST',
      // Тело запроса в JSON-формате
      body: JSON.stringify(searchUser),
      headers: {
        // Добавляем необходимые заголовки
        'Content-type': 'application/json; charset=UTF-8',
      },
    })
      .then((response) => response.json())
      .then((data) => {
        for (let value of data) {
          let div = document.createElement("div");
          div.className = 'user-' + value.id;
          div.innerHTML = `<a href="#" class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <img class="avatar" src="/img/${value.avatar}?chache=${Date.now()}" alt="user img">
                                    <span class="${value.status}" id="status"></span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 id="login">${value.login}</h3>
                                </div>
                                <div id="user-id" hidden>${value.id}</div>
                            </a>`
          searchList.appendChild(div);
          let userId = div.querySelector('#user-id');
          div.addEventListener('click', function () {
            let addUsers = document.querySelector('#add-users');
            let user = addUsers.querySelector(`.${div.className}`);
            if (!user &&
              Number(profileId.innerHTML) != Number(userId.innerHTML) &&
              addUsers.childElementCount === 0) {
              addUsers.appendChild(div);
            }
          })
        }
      })
  }
});

// создание приватного чата
document.querySelector('#create-chat').addEventListener('click', function (e) {
  let addUsers = document.querySelector('#add-users');
  let title = addUsers.querySelector('#login');
  let profileId = document.querySelector('#profile-id');
  let userId = addUsers.querySelector('#user-id');
  let url = new URL(addUsers.querySelector('.avatar').src);
  let userAvatar = url.pathname.substr(5);
  let status = addUsers.querySelector('#status').className;
  e.preventDefault();
  if (addUsers.childElementCount === 1) {
    let div = document.createElement("div");
    let chatRoom = {
      create_room: true,
      user_id: profileId.innerHTML,
      title: title.innerHTML,
      members: userId.innerHTML,
      privacy: 1
    }

    fetch('/home/ajax', {
      method: 'POST',
      // Тело запроса в JSON-формате
      body: JSON.stringify(chatRoom),
      headers: {
        // Добавляем необходимые заголовки
        'Content-type': 'application/json; charset=UTF-8',
      },
    })
      .then((response) => response.json())
      .then((data) => {
        render_chatrooms();
      })

  }
})

// очистка выбранных пользователей
let addUser = document.querySelector(".add");
addUser.addEventListener('click', function () {
  let userList = document.querySelector('#add-users');
  let searchList = document.querySelector('#search-list');
  let child = searchList.lastElementChild;
  let childL = userList.lastElementChild;
  while (child) {
    searchList.removeChild(child);
    child = searchList.lastElementChild;
  }
  while (childL) {
    userList.removeChild(childL);
    childL = userList.lastElementChild;
  }
})

// формирование списка пользоватлей для создания группового чата
let addGroup = document.querySelector('.add-group');
addGroup.addEventListener('click', function () {
  let profileId = document.querySelector('#profile-id');
  let groupForm = document.querySelector('#group-form');
  let userData = document.querySelector('.user-data');
  let friendList = document.querySelector('#friend-list');
  let add = document.querySelector('#add-group');
  let child = friendList.lastElementChild;
  let childL = add.lastElementChild;
  let childU = userData.lastElementChild;
  while (child) {
    friendList.removeChild(child);
    child = friendList.lastElementChild;
  }
  while (childL) {
    add.removeChild(childL);
    childL = add.lastElementChild;
  }
  while (childU) {
    userData.removeChild(childU);
    childU = userData.lastElementChild;
  }
  let idUser = {
    get_private_rooms: true,
    user_id: profileId.innerHTML
  }

  fetch('/home/ajax', {
    method: 'POST',
    body: JSON.stringify(idUser),
    headers: {
      'Content-type': 'application/json; charset=UTF-8',
    },
  })
    .then((response) => response.json())
    .then((data) => {
      for (let value of data) {
        let div = document.createElement("div");
        div.className = 'room-' + value.id_room;
        div.innerHTML = `<a href="#" class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <img class="avatar" src="/img/${value.avatar}?chache=${Date.now()}" alt="user img">
                                    <span class="${value.status}" id="status"></span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 id="login">${value.login}</h3>
                                </div>
                                <div id="room-id" hidden>${value.id_room}</div>
                            </a>`
        friendList.appendChild(div);
        div.addEventListener('click', function () {
          let addGroup = document.querySelector('#add-group');
          let user = addGroup.querySelector(`.${div.className}`);
          if (!user) {
            addGroup.appendChild(div);
            let input = document.createElement("input");
            input.type = 'hidden';
            input.name = 'users[]';
            input.value = value.id_user;
            userData.appendChild(input);
          }
        })
      }

    })
})


function render_chatrooms() {
  let id = document.querySelector('#profile-id');
  let getRooms = {
    get_rooms: true,
    id_user: id.innerHTML
  }

  fetch('/home/ajax', {
    method: 'POST',
    // Тело запроса в JSON-формате
    body: JSON.stringify(getRooms),
    headers: {
      // Добавляем необходимые заголовки
      'Content-type': 'application/json; charset=UTF-8',
    },
  })
    .then((response) => response.json())
    .then((data) => {
      let child = chatRooms.lastElementChild;
      while (child) {
        chatRooms.removeChild(child);
        child = chatRooms.lastElementChild;
      }

      for (let value of data['chat_rooms']) {
        let title = value.privacy ? value.login : value.title
        let logo = value.privacy ? value.avatar : value.logo
        let status = value.privacy ? value.status : 'offline'
        let div = document.createElement("div");
        div.className = `room-${value.id_room}`;
        div.innerHTML = `<a href="#" class="d-flex align-items-center">
                          <div class="flex-shrink-0">
                          <img class="avatar" src="/img/${logo}?cache=${Date.now()}" alt="user img">
                          <span class="${status}" id="room-${value.id_room}-status"></span>
                          </div>
                          <div class="flex-grow-1 ms-3">
                          <h3 id="title">${title}</h3>
                          </div>
                          <div class="flex-shrink-0" id="notification">
                          </div>
                          <div id="room-id" hidden>${value.id_room}</div>
                          </a>`
        chatRooms.appendChild(div);
        if (data['unread_msg'].includes(value.id_room)) {
          div.querySelector("#notification").innerHTML = `<div id="new-msg">
                                                          <img class="new-msg" src="/img/sys/new_msg.png" alt="new msg">
                                                          </div>`
        }
        get_room_data(div)
      }
    })

}

render_chatrooms()