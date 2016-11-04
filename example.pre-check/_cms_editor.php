<?php

include '__header.php';
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
        <div class="form-group">
            <label for="title">Заголовок</label>
            <input type="text" class="form-control" id="title" name="title" placeholder="Напишите заголовок документа">
        </div>
        <div class="form-group">
            <label for="description">Описание</label>
            <textarea class="form-control" id="description" name="description" placeholder="Напишите описание документа"
                      rows="3"></textarea>
        </div>
        <div class="form-group">
            <label for="custom-sample">Дополнительное поле (custom/sample)</label>
            <textarea class="form-control" id="custom-sample" name="custom-sample" placeholder="Укажите дополнительное поле"
                      rows="3"></textarea>
        </div>
        <input type="hidden" name="html" value="">
    </form>
</div>
<div class="btn-toolbar" data-role="editor-toolbar" data-target="#editor">
    <div class="btn-group">
        <a class="btn btn-default dropdown-toggle" data-toggle="dropdown" title="" data-original-title="Font"><i
                class="fa fa-font"></i><b class="caret"></b></a>
        <ul class="dropdown-menu">
            <li><a data-edit="fontName Serif" style="font-family:'Serif'">Serif</a></li>
            <li><a data-edit="fontName Sans" style="font-family:'Sans'">Sans</a></li>
            <li><a data-edit="fontName Arial" style="font-family:'Arial'">Arial</a></li>
            <li><a data-edit="fontName Arial Black" style="font-family:'Arial Black'">Arial Black</a></li>
            <li><a data-edit="fontName Courier" style="font-family:'Courier'">Courier</a></li>
            <li><a data-edit="fontName Courier New" style="font-family:'Courier New'">Courier New</a></li>
            <li><a data-edit="fontName Comic Sans MS" style="font-family:'Comic Sans MS'">Comic Sans MS</a></li>
            <li><a data-edit="fontName Helvetica" style="font-family:'Helvetica'">Helvetica</a></li>
            <li><a data-edit="fontName Impact" style="font-family:'Impact'">Impact</a></li>
            <li><a data-edit="fontName Lucida Grande" style="font-family:'Lucida Grande'">Lucida Grande</a></li>
            <li><a data-edit="fontName Lucida Sans" style="font-family:'Lucida Sans'">Lucida Sans</a></li>
            <li><a data-edit="fontName Tahoma" style="font-family:'Tahoma'">Tahoma</a></li>
            <li><a data-edit="fontName Times" style="font-family:'Times'">Times</a></li>
            <li><a data-edit="fontName Times New Roman" style="font-family:'Times New Roman'">Times New Roman</a>
            </li>
            <li><a data-edit="fontName Verdana" style="font-family:'Verdana'">Verdana</a></li>
        </ul>
    </div>
    <div class="btn-group">
        <a class="btn btn-default dropdown-toggle" data-toggle="dropdown" title=""
           data-original-title="Font Size"><i
                class="fa fa-text-height"></i>&nbsp;<b class="caret"></b></a>
        <ul class="dropdown-menu">
            <li><a data-edit="fontSize 5"><font size="5">Huge</font></a></li>
            <li><a data-edit="fontSize 3"><font size="3">Normal</font></a></li>
            <li><a data-edit="fontSize 1"><font size="1">Small</font></a></li>
        </ul>
    </div>
    <div class="btn-group">
        <a class="btn btn-default" data-edit="bold" title="" data-original-title="Bold (Ctrl/Cmd+B)"><i
                class="fa fa-bold"></i></a>
        <a class="btn btn-default" data-edit="italic" title="" data-original-title="Italic (Ctrl/Cmd+I)"><i
                class="fa fa-italic"></i></a>
        <a class="btn btn-default" data-edit="strikethrough" title="" data-original-title="Strikethrough"><i
                class="fa fa-strikethrough"></i></a>
        <a class="btn btn-default" data-edit="underline" title="" data-original-title="Underline (Ctrl/Cmd+U)"><i
                class="fa fa-underline"></i></a>
    </div>
    <div class="btn-group">
        <a class="btn btn-default" data-edit="insertunorderedlist" title="" data-original-title="Bullet list"><i
                class="fa fa-list-ul"></i></a>
        <a class="btn btn-default" data-edit="insertorderedlist" title="" data-original-title="Number list"><i
                class="fa fa-list-ol"></i></a>
        <a class="btn btn-default" data-edit="outdent" title="" data-original-title="Reduce indent (Shift+Tab)"><i
                class="fa fa-outdent"></i></a>
        <a class="btn btn-default" data-edit="indent" title="" data-original-title="Indent (Tab)"><i
                class="fa fa-indent"></i></a>
    </div>
    <div class="btn-group">
        <a class="btn btn-default" data-edit="justifyleft" title="" data-original-title="Align Left (Ctrl/Cmd+L)"><i
                class="fa fa-align-left"></i></a>
        <a class="btn btn-default" data-edit="justifycenter" title="" data-original-title="Center (Ctrl/Cmd+E)"><i
                class="fa fa-align-center"></i></a>
        <a class="btn btn-default" data-edit="justifyright" title="" data-original-title="Align Right (Ctrl/Cmd+R)"><i
                class="fa fa-align-right"></i></a>
        <a class="btn btn-default" data-edit="justifyfull" title="" data-original-title="Justify (Ctrl/Cmd+J)"><i
                class="fa fa-align-justify"></i></a>
    </div>
    <div class="btn-group">
        <a class="btn btn-default" data-edit="undo" title="" data-original-title="Undo (Ctrl/Cmd+Z)"><i
                class="fa fa-undo"></i></a>
        <a class="btn btn-default" data-edit="redo" title="" data-original-title="Redo (Ctrl/Cmd+Y)"><i
                class="fa fa-repeat"></i></a>
    </div>
</div>
<div id="editor" contenteditable="true" class="form-control" spellcheck="false">

</div>
<div class="text-right">
    <button id="check" class="btn btn-lg btn-primary">Опубликовать</button>
</div>

<script>
    $(function () {
        var $editor = $('#editor');
        var $form = $('form.form');
        $editor.wysiwyg();
        $('button#check').on('click', function () {
            $form.find('input[name=html]').val($editor.cleanHtml());
            $form.submit();
        });
    });
</script>


<? include '__footer.php'; ?>
