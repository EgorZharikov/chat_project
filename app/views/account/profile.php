<?php
$userdata = (App\core\Auth\Auth::check_user());
if (isset($userdata)) {
    extract($userdata);
}
$profile_data = (App\models\AccountModel::get_profile_data($id));
$errors = App\core\Session\Session::flash('errors');
$errors = $errors ?? false;
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/profile_style.css">
</head>

<body>
    <div class="container col-sm-3">
        <div class="row profile-card m-1">
            <div class="col border border-primary-subtle align-items-center p-0">
                <div class="">
                    <a href="/home"><img src="/img/sys/back_arrow.png"></a>
                    <h4 class="m-3">Мой профиль:</h4>
                </div>
                <div class="col">
                    <div class="header align-items-center mb-2">
                        <a class="select-btn" data-bs-toggle="modal" data-bs-target="#avatar-select"><img class="avatar d-flex" src="/img/<?php if (isset($profile_data[0]['avatar'])) echo $profile_data[0]['avatar'] ?>?time=<?php echo time() ?>" alt="user img"></a>
                            <h5 class="d-flex ms-3 me-3" id="user-name"><?php if (isset($profile_data[0]['login'])) echo $profile_data[0]['login'] ?></h5>
                        <a class="select-btn d-flex" data-bs-toggle="modal" data-bs-target="#edit-name"><img class="edit d-flex" src="/img/sys/edit_button.png" alt="user img"></a>
                        <div id="profile-id" hidden><?php if (isset($profile_data[0]['id'])) echo $profile_data[0]['id'] ?></div>
                    </div>
                </div>
                <h6>Настройки:</h6>
                <div class="row">
                    <div class="input-group-sm d-flex mb-2 mt-1" id="inputGroup">
                        <span class="input-group-text" id="basic-addon1">@email</span>
                        <input type="text" class="form-control" id="email" value="<?php if (isset($profile_data[0]['email'])) echo $profile_data[0]['email'] ?>" aria-label="Имя пользователя" aria-describedby="basic-addon1" disabled>
                    </div>
                    <form method="POST" action="profile">
                        <div>
                            <div class="form-check-reverse form-switch">
                                <label class="form-check-label m-0" for="notificationSound">Включить звук</label>
                                <input class="form-check-input m-1" type="checkbox" role="switch" name="sound_on" id="notificationSound" <?php echo $profile_data[0]['sound'] ? 'checked' : '' ?>>
                            </div>
                            <div class="form-check-reverse form-switch">
                                <label class="form-check-label m-0" for="emailHide">Скрыть email из поиска</label>
                                <input class="form-check-input m-1" type="checkbox" name="email_hide" role="switch" id="emailHide" <?php echo $profile_data[0]['email_privacy'] ? 'checked' : '' ?>>
                            </div>
                        </div>
                </div>
                <button type="submit" name="setting" class="btn btn-primary m-3">Применить</button>
                </form>
                <h6 class="mt-3">Последний вход:</h6>
                <span>IP: <?php if (isset($profile_data[0]['ip'])) echo long2ip($profile_data[0]['ip']) ?>
                    Дата: <?= date('m/d/Y H:i:s', $profile_data[0]['last_seen']) ?></span>
                <div class="mt-3">
                    <form method="post" action="signout">
                        <button type="submit" name="signout" class="btn btn-dark m-3">Выйти из аккаунта</button>
                    </form>
                </div>
                <!-- 
                <div class="border border-danger" id="delete">
                    <form method="post" action="signout">
                        <button type="submit" name="signout" class="btn btn-danger m-3">Удалить аккаунт</button>
                    </form>
                </div> -->
            </div>
            <?php if ($errors) : ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $errors ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="avatar-select" tabindex="-1" aria-labelledby="avatar-selectLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fs-5" id="avatar-selectLabel">Аватар:</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body align-items-center">
                    <div class="userAvatar">
                        <div class="avatar-img align-items-center">
                            <form enctype="multipart/form-data" class="align-items-center mb-0" action="profile" id="group-form" method="POST">
                                <input type="file" class="form-control" oninput="previewImage(this.files[0])" name=" files[]" accept=".jpg,.png" id="inputFile" aria-describedby="inputFileAddon" aria-label="Upload">
                                <label for="inputFile" id="imputFileLabel"><img class="add-room-img d-flex" src="/img/sys/plus.png" alt="room-img"></label>
                                <img class="d-flex mt-4" id="preview">
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-secondary" name="upload_avatar" id="uploadAvatar" data-bs-dismiss="modal">Загрузить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="edit-name" tabindex="-1" aria-labelledby="edit-nameLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fs-5" id="edit-nameLabel">Имя в сhatix:</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body align-items-center">
                    <div>
                        <div class="edit-name align-items-center">
                            <input type="text" id="login"> <button type="button" id="checkUserName" class="btn btn-primary m-3">Проверить</button>
                        </div>

                        <div class="log alert alert-success" id="success" role="alert">
                            # Отлично! Вы можете изменить имя пользователя
                        </div>
                        <div class="log alert alert-danger" id="danger" role="alert">
                            # Имя пользователя уже используется
                        </div>

                    </div>
                    <div class="modal-footer">
                        <form action="profile" method="POST">
                            <button type="submit" class="btn btn-secondary" id="edit_name" name="rename" data-bs-dismiss="modal">Применить</button>
                            <input type="text" id="login-form" name="login" hidden>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

        <script>
            function previewImage(file) {
                const reader = new FileReader();
                document.querySelector('#preview').style.display = 'flex';
                reader.onload = () => document.querySelector('#preview').src = reader.result;
                reader.readAsDataURL(file);
            }
        </script>

        <script>
            let login = document.querySelector('#login')
            let profileId = document.querySelector('#profile-id')
            let checkBtn = document.querySelector('#checkUserName')
            let success = document.querySelector('#success')
            let danger = document.querySelector('#danger')
            let loginForm = document.querySelector('#login-form')
            checkBtn.addEventListener('click', function() {
                success.style.display = 'none'
                danger.style.display = 'none'

                let check = {
                    check_login: true,
                    login: login.value
                }


                fetch('/home/ajax', {
                        method: 'POST',
                        body: JSON.stringify(check),
                        headers: {
                            'Content-type': 'application/json; charset=UTF-8',
                        },
                    })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data) {
                            success.style.display = 'block'
                            loginForm.value = login.value

                        } else {
                            danger.style.display = 'block'
                        }
                    })
            })
        </script>
</body>

</html>