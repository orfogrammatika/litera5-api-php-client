<?php

include '__header.php';

if ($_GET['action'] == 'FAILED') {
?>
<div class="alert alert-error">
    <p>Во время проверки произошла ошибка. Пожалуйста, обратитесь к вашему системному администратору.</p>
    <div class="text-right">
        <a href="index.php" class="btn btn-lg btn-default">Вернуться</a>
    </div>
</div>
<?php
} else if ($_GET['action'] == 'SAVED') {
    $token = $_GET['token'];
    $results = json_decode(file_get_contents($token))
?>
    <style>
        .container {
            margin-top: 32px;
        }

        #editor {
            height: 300px;
            margin-top: 16px;
            margin-bottom: 16px;
            overflow-y: auto;
            overflow-x: hidden;
        }
    </style>

    <div class="well">
        <form class="form" action="" method="post">
            <input type="hidden" name="mode" value="regular">
            <div class="form-group">
                <label for="title">Заголовок</label>
                <input type="text" class="form-control" id="title" name="title" placeholder="Напишите заголовок документа" value="<?=$results->title?>">
            </div>
            <div class="form-group">
                <label for="description">Описание</label>
            <textarea class="form-control" id="description" name="description" placeholder="Напишите описание документа"
                      rows="3"><?=$results->description?></textarea>
            </div>
            <div class="form-group">
                <label for="custom-sample">Дополнительное поле (custom/sample)</label>
            <textarea class="form-control" id="custom-sample" name="custom-sample" placeholder="Укажите дополнительное поле"
                      rows="3"><?=$results->custom->sample?></textarea>
            </div>
        </form>
    </div>
    <h4>Текст статьи</h4>
    <div class="well" spellcheck="false">
        <?=$results->html?>
    </div>
    <h4>Результаты последней проверки:</h4>
    <pre>
    <?=json_encode($results->stats)?>
</pre>

    <div class="text-right">
        <a href="index.php" class="btn btn-lg btn-default">Вернуться</a>
    </div>
<?php
}
?>

<? include '__footer.php'; ?>
