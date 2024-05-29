<?php
$session_data = (App\core\Auth\Auth::check_user());
App\core\Auth\Auth::check_auth();
if (isset($session_data)) {
    extract($session_data);
}
$private_rooms = (App\models\HomeModel::get_private_chat_rooms($id));
$group_rooms = (App\models\HomeModel::get_group_room($id));
$unreaded_msg = (App\models\HomeModel::get_unreaded_msg($id));
$user_settings = (App\models\HomeModel::get_user_settings($id));
$user_data = (App\models\HomeModel::get_user_data($id));
?>

<!-- Google Fonts -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatix</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/chatix.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <style>
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .new-msg {
            max-width: auto !important;
            height: 25px !important;
        }

        .msg-status {
            max-width: auto !important;
            height: 18px !important;
        }
    </style>
</head>

<body>
    <section class="message-area">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="chat-area">
                        <!-- chatlist -->
                        <div class="chatlist">
                            <div class="modal-dialog-scrollable" id="modal">
                                <div class="modal-content">
                                    <div class="chat-header d-flex">
                                        <a href="/account/profile" class="d-flex align-items-center" id="test">
                                            <div class="flex-shrink-0">
                                                <img class="img-fluid" src="/img/<?php if (isset($user_data[0]['avatar'])) echo $user_data[0]['avatar'] . '?cache=' . time() ?>" id="user-avatar" alt="user img">
                                                <span class="<?php if (isset($user_data[0]['status'])) echo $user_data[0]['status'] ?>"></span>
                                            </div>
                                            <div class="flex-grow-1 ms-3 me-2">
                                                <h3 class="profile-title"><?php if (isset($login)) echo $login ?></h3>
                                            </div>
                                        </a>
                                        <div class="d-flex align-items-center">
                                            <a class="add button d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#addUserModal"><img class="add-user" src="/img/sys/add-user.png" alt="add"></a>
                                            <a class="add-group button d-flex align-items-center ms-2" href="#" data-bs-toggle="modal" data-bs-target="#addGroupModal"><img class="add-group" src="/img/sys/add-friend.png" alt="add"></a>
                                        </div>
                                        <div id="profile-id" hidden><?php if (isset($id)) echo $id ?></div>
                                    </div>
                                    <div class="modal-body border">
                                        <!-- chat-list -->
                                        <div class="chat-list" id="chat-rooms">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- chatbox -->
                        <div class="chatbox" id="modalbox">
                            <div class="modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="msg-head d-flex">
                                        <div id="chat-room" hidden></div>
                                        <div class="col">
                                            <div class="" id="chat-icon">
                                                <span class="chat-icon d-flex"><img class="img-fluid" id="btn-back" src="/img/sys/arrowleft.svg" alt="image title"></span>
                                            </div>
                                            <div class="row row-cols-2 row-cols-lg-5 g-2 g-lg-1 p-2 d-flex" id="chat-header">

                                                <div id="connection">

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-body" id="chatBox">
                                        <div class="msg-body">
                                            <ul>

                                            </ul>
                                        </div>
                                    </div>
                                    <div class="send-box" id="send-box">
                                        <form>
                                            <input type="text" class="form-control" id="message" aria-label="message…" placeholder="Write message…">
                                            <button type="button" id="send"><i class="fa fa-paper-plane" aria-hidden="true"></i>
                                                Send</button>
                                        </form>
                                    </div>
                                    <div class="send-box" id="edit-box">
                                        <form>
                                            <input type="text" class="form-control" id="edit-message" aria-label="message…" placeholder="Edit message…">
                                            <div id="msg-id" hidden></div>
                                            <button type="button" id="edit"><i class="fa fa-pencil" aria-hidden="true"></i>
                                                Edit</button>
                                            <button type="button" id="close"><i class="fa fa-close" aria-hidden="true"></i>Close</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- chatbox -->
                </div>
            </div>
        </div>
        </div>
    </section>

    <!-- Модальное окно -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fs-5" id="addUserModalLabel">Поиск пользователей chatix:</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <div class="msg-search">
                        <input type="text" class="form-control" id="search" placeholder="Search">
                    </div>

                    <div class="chat-list align-items-center">
                        <div class="m-3 p-2" id="search-list">
                            <!-- Результат поиска -->
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <h5 class="modal-title fs-5">Выбранные пользователи:</h5>
                    <div class="chat-list">
                        <div class="m-2 p-2" id="add-users">

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="create-chat" data-bs-dismiss="modal">Создать чат</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно addGroup -->
    <div class="modal fade" id="addGroupModal" tabindex="-1" aria-labelledby="addGroupModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fs-5" id="addGrupModalLabel">Создание группового чата:</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <div class="msg-search">
                        <div class="room-img me-2">
                            <form enctype="multipart/form-data" action="home" id="group-form" method="POST">
                                <input type="file" class="form-control" name="files[]" accept=".jpg,.png" id="inputFile" aria-describedby="inputFileAddon" aria-label="Upload">
                                <label for="inputFile" id="imputFileLabel"><img class="add-room-img" src="/img/sys/add_logo.png" alt="room-img"></label>
                                <input type="hidden" name="users[]" id="room-members" value="<?php if (isset($id)) echo $id ?>">
                                <div class="user-data">

                                </div>
                        </div>
                        <input type="text" class="form-control" id="room-title" name="room-title" placeholder="Введите название чата...">
                    </div>

                    <div class="chat-list align-items-center">
                        <div class="m-3 p-2" id="friend-list">
                            <!-- Результат поиска -->
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <h5 class="modal-title fs-5">Выбранные пользователи:</h5>
                    <div class="chat-list">
                        <div class="m-2 p-2" id="add-group">

                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-secondary" id="create-chat" data-bs-dismiss="modal">Создать чат</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed top-0 end-0">
        <div class="toast align-items-center bg-danger text-white" style="--bs-bg-opacity: .7;" id="liveToast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Закрыть"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        function audio_notification() {
            let setting = <?php print($user_settings[0]['sound']) ?>

            if (setting) {
                let audio = new Audio('/audio/new_message.mp3');
                audio.play();
            }
        }
    </script>
    <script src="/js/script.js"></script>
    <script src="/js/WS.js"></script>
</body>

</html>