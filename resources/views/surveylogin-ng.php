<div ng-cloak ng-controller="surveylogin" layout="row" layout-align="center center" style="height:100%">

    <md-content md-whiteframe="1" style="width: 500px" layout-padding>
    <md-toolbar>
        <div class="md-toolbar-tools">
            <h2><?php echo $book->title?></h2>
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
    </md-content>


</div>
<script src="/packages/cere/survey/js/ng/ngSurvey.js"></script>
<script >
    app.controller('surveylogin', function ($scope, $http) {});
</script>
