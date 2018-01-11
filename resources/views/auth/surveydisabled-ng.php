<div layout="column" layout-align="center center" style="height:100%">
    <div md-whiteframe="1" style="width: 500px" layout-padding md-colors="{backgroundColor: 'grey-A100'}">
        <md-toolbar>
            <div class="md-toolbar-tools">
                <h2><?php echo $book->title?> 目前停止調查</h2>
            </div>
        </md-toolbar>
        <div layout="column" layout-align="center center" style="height:100px">
            調查時間：<?php echo $book->auth['start_at'] . '~' . $book->auth['close_at'];?>
        </div>
        <md-divider></md-divider>
        <div style="text-align: center">
            Copyright © 國立台灣師範大學 教育研究與評鑑中心
        </div>
    </div>
</div>
