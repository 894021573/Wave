<?php
/**
 *
 * @author: 洪涛
 * @date: 2017/5/21
 */
//header("Content-type: text/html; charset=utf-8")
?>

<style>
    #open_btn {
        position: fixed;
        display: block;
        margin: auto;
        right: 17px;
        z-index: 999;
        background-color: #cccccc;
        width: 80px;
        height: 40px;
        bottom:10px;
    }

    #debug {
        z-index: 998;
        position: fixed;
        width: 100%;
        height: 250px;
        background-color: #59baf6;
        bottom: 0;
        left: 0;
        display: none;
        color: #ffffff
    }

    #debug span {
        float: left;
        height: 100%;
        width: 10%;
        overflow-y: scroll;
    }

    .debug_h {
        padding-left: 15px;
    }

    .debug_detail {
        float: left;
        width: 90%;
        height: 100%;
        overflow-y: scroll
    }

    .debug_detail p {
        padding-left: 15px;
    }
</style>

<button id="open_btn">打开</button>

<div id="debug">
    <span>
<?php foreach ($this->_panels as $panel): ?>
    <h3 class="debug_h"><?php echo $panel ?></h3>
<?php endforeach; ?>
    </span>

    <?php $i = 0;
    foreach ($this->_debug_messages as $panel => $messages): ?>
        <?php
        if ($i == 0)
        {
            $display = 'display:block;';
        } else
        {
            $display = 'display:none;';
        }
        ?>
        <div class="debug_detail" style=";<?php echo $display ?>" id="<?php echo $panel ?>">
            <?php foreach ($messages as $message): ?>
                <p><?php echo $message ?></p>
            <?php endforeach; ?>
        </div>
        <?php
        $i++;
    endforeach;
    ?>
</div>

<script src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
<script>
    $(function () {
        $(".debug_h:first").css("background-color", "#ffa964");

        $(".debug_h").click(function () {
            $(this).css('background-color', '#ffa964');
            $(this).siblings().css("background-color", "");
            idName = "#" + $(this).html();
            $(idName).show();
            $(idName).siblings("div").hide();
        });

        var $openBtn = $("#open_btn");
        var $debug = $("#debug");

        $openBtn.click(function () {
            if (!$debug.is(":hidden")) {
                $("#debug").hide();
                $(this).css("bottom", "10px");
                $(this).html("打开");
            }
            else {
                $debug.show();
                $(this).css("bottom", "210px");
                $(this).html("关闭");
            }
        });

        <?php
        if(in_array('Error', $this->_panels) || in_array('Exception', $this->_panels))
        {
        ?>
        $openBtn.trigger("click");
        <?php
        }
        ?>
    })
</script>

