<?php $errors = App\core\Session\Session::flash('errors');
$errors = $errors ?? '';
?>
<div class="container">
    <div class="row account-card align-items-center">
        <div class="col col-md-3 offset-md-4">
            <div class="account-logo">
                <h3 class="h3 mb-3 fw-normal">Код подтверждения отправлен на email</h3>
            </div>
            <form name="signup_form" method="post" action="activation">
                <div class="mb-3">
                    <label for="inputCode" class="form-label">Введите код подтверждения</label>
                    <input type="text" name="code" class="form-control mb-3" id="inputCode">
                <div class="d-grid gap-2">
                    <button type="submit" name="activation" id="activation" class="btn btn-primary">Проверить</button>
                </div>
                <?php if ($errors) : ?>
                    <div class="alert alert-danger mt-3" role="alert"> <?= $errors ?>
                    <?php endif ?>
                    </div>
            </form>
        </div>
    </div>
</div>