<md-content layout="column" layout-align="start center" style="height:100%;">
    <div class="ui middle aligned center aligned grid">
      <div class="column">
        <div class="ui stacked segment">
            <h2 class="ui teal image header">
                <div class="content">
                    目前停止調查
                </div>
            </h2>
            <div style="height:300px; width:800px;">
                調查時間：<?php echo date_format(new DateTime($book->start_at), 'Y-m-d H:i') . '~' . date_format(new DateTime($book->close_at), 'Y-m-d H:i');?>
            </div>
            <div class="ui divider"></div>
            <div class="ui mini horizontal bulleted link list">
                Copyright © 國立台灣師範大學 教育研究與評鑑中心
            </div>
        </div>
      </div>
    </div>
</md-content>
