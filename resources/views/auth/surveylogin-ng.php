<div layout="column" layout-align="center center" style="height:100%">
    <div md-whiteframe="1" style="width: 500px" layout-padding md-colors="{backgroundColor: 'grey-A100'}">
        <md-toolbar>
            <div class="md-toolbar-tools">
                <h2>登入 <?php echo $book->title?></h2>
            </div>
        </md-toolbar>
        <form action="login" method="post">
        <?php foreach($fields as $field) { ?>
            <md-input-container class="md-block">
                <label>請輸入<?php echo $field->title; ?></label>
                <input type="text" name="<?php echo $field->name; ?>">
            </md-input-container>
        <?php } ?>
        <div>
        <?=$errors->first("fail")?>
            <md-button type="submit">登入</md-button>
        </div>
        </form>
    </div>
</div>
