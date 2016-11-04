<?php

include '__header.php';
?>

    <style>
        .dbg-container {
            position: relative;
        }

        .dbg-shadow {
            position: absolute;
            width: 100%;
            height: 600px;
            left: 0;
            top: 0;
            background: white;
            z-index: 99999;
        }
    </style>
    <div class="dbg-container">
        <iframe src="<?= $iframe_url ?>" width="100%" height="600px" scrolling="no"
                style="border:1px solid darkgrey;"></iframe>
        <div class="dbg-shadow">
            <p>
                Статья проверяется Литерой.
            </p>
            <div class="progress">
                <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0">
                </div>
            </div>
            <div id="log">

            </div>
        </div>
    </div>
    <script>
        $(function(){
            var $shadow = $('.dbg-shadow');
            var $log = $('#log');
            var count = 0;
            var $progress = $shadow.find('.progress-bar');
            function checkProgress(){
                count ++;
                $.getJSON('_cms_status.php?token=<?=urlencode($token)?>', function(resp){
                    if (resp.checking) {
                        var p = $progress.attr('aria-valuenow');
                        p = (parseInt(p) + 10) % 100;
                        $progress.attr('aria-valuenow', p);
                        $progress.css('width', p + '%');
                        scheduleCheckProgress();
                    } else if (resp.error) {
                        $log.prepend('<p>Ошибка: ' + resp.error + '</p>');
                    } else if (resp.success) {
                        $shadow.hide();
                    }
                });
            }
            function scheduleCheckProgress(){
                setTimeout(checkProgress, 1000)
            }
            scheduleCheckProgress();
        });
    </script>
<? include '__footer.php'; ?>