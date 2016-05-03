<div class="form-group<?= (isset($error['captcha']) ? ' has-error' : '') ?>">
    <label>
    <!--<label for="captcha">-->
        <?php //$this->lng('verification_code') ?>
        <!--<br>-->
        <!--    Если Вы не видите рисунок с кодом, включите поддержку графики в настройках браузера и обновите страницу-->
        <img src="/captcha/" id="captcha" width="<?= $config['width'] ?>" height="<?= $config['height'] ?>" onclick="this.src='/captcha?'+Math.random()" class="img-rounded" alt="Captcha" style="cursor: pointer;" /> <span style="border-bottom: 1px dashed #f00; color: #f00; cursor: pointer;" onclick="document.getElementById('captcha').src='/captcha/?'+Math.random()">Обновить</span>
    </label>
    <input type="text" id="captcha" class="form-control" maxlength="<?= $config['lengthMax'] ?>"  name="<?= $config['name'] ?>" placeholder="Проверочный код" required autocomplete="off">
    <?php if (isset($error['captcha'])) : ?>
        <p><label class="label control-label"><?= $this->lng($error['captcha']) ?></label></p>
    <?php endif ?>
</div>