<md-content ng-cloak layout="column" ng-controller="book" layout-align="start center">
<div ng-include="'stepsTemplate'"></div>
<div style="width:960px">
    <md-card style="width: 100%">
        <md-card-header md-colors="{background: 'indigo'}">
            <md-card-header-text>
                <span class="md-title">完成的加掛問卷</span>
            </md-card-header-text>
        </md-card-header>
        <md-card-content>
            <survey-browser></survey-browser>
        </md-card-content>
        <md-card-actions layout="row">
            <md-button class="md-raised md-primary" ng-click="changeStep('preStep')" style="width: 50%;height: 50px;font-size: 18px">返回編輯問卷</md-button>
            <md-button class="md-raised md-primary" ng-click="changeStep('nextStep')" style="width: 50%;height: 50px;font-size: 18px">下一步</md-button>
        </md-card-actions>
    </md-card>
</div>
</md-content>
<script src="/packages/cere/survey/js/ng/ngBrowser.js"></script>
<script>
app.requires.push('ngBrowser');
app.controller('book', function ($scope, $http, $sce){
    $http({method: 'POST', url: 'getBrowserQuestions', data:{}})
    .then(function(response) {
        $scope.pages = response.data.pages;
    });

    $scope.trustAsHtml = function(string) {
        return $sce.trustAsHtml(string);
    };

    $scope.changeStep = function(method) {
        $http({method: 'POST', url: method, data:{}})
        .then(function(response) {
            location.reload();
        });
    }
});
</script>
