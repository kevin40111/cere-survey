<md-content ng-cloak layout="column" ng-controller="surveylogin" layout-align="start center">
<form action="checkInRows" class="ui large form" method="post">
    <div class="ui middle aligned center aligned grid">
      <div class="column">
        <h2 class="ui teal image header">
            <div class="content">
                主題本登入
            </div>
        </h2>
        <div class="ui stacked segment">
            <div class="field">
                <div class="ui left icon input">
                    <i class="write icon"></i>
                    <input type="text" name="id" ng-model="input_value" placeholder="請輸入所申請的登入資料">
                </div>
            </div>
            <input type="submit" class="ui fluid large teal submit button" value="登入">
        </div>
      </div>
    </div>
</form>
</md-content>
<script src="/packages/cere/survey/js/ng/ngSurvey.js"></script>
<script >
    app.controller('surveylogin', function ($scope, $http){});
</script>
