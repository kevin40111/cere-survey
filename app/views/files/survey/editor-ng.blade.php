
<div ng-controller="editorController" layout="row" style="height:100%">

    <md-sidenav class="md-sidenav-left" md-component-id="survey-book" layout="column">
        <md-content>
            <md-list>
                <md-subheader class="md-no-sticky">選擇題本</md-subheader>
                <md-list-item ng-repeat="sbook in sbooks">
                    <md-icon md-svg-icon="history"></md-icon>
                    <md-checkbox class="md-secondary" ng-model="sbook.checked"></md-checkbox>
                    <p>@{{sbook.title}}</p>
                </md-list-item>
            </md-list>
        </md-content>
    </md-sidenav>

    <md-content flex="100" layout="column">

        <md-toolbar md-colors="{background: 'grey-100'}">
            <div class="md-toolbar-tools">
                <span flex></span>
                <md-button href="demo" target="_blank">預覽</md-button>
                <md-button href="skip" target="_blank">跳題設定</md-button>
                <md-button ng-click="openBooks()">設定題本</md-button>
                <span flex></span>
            </div>
        </md-toolbar>
        <md-divider></md-divider>
        <div layout="column" layout-align="start center" style="height:100%;overflow-y:scroll">
            <div style="width:960px">
                <survey-book ng-if="book" book="book"></survey-book>
            </div>
        </div>

    </md-content>

</div>

<script src="/js/angular-file-upload.min.js"></script>
<script src="/js/ng/ngEditor.js"></script>

<script>
app.requires.push('angularFileUpload');
app.requires.push('ngEditor');
app.controller('editorController', function($http, $scope, $sce, $interval, $filter, $mdSidenav) {

    $scope.getBook = function() {
        $scope.$parent.main.loading = true;
        $http({method: 'POST', url: 'getBook', data:{}})
        .success(function(data, status, headers, config) {
            console.log(data);
            $scope.book = data.book;
            $scope.$parent.main.loading = false;
        }).error(function(e) {
            console.log(e);
        });
    };

    $scope.getBook();

    $scope.openBooks = function() {
        $mdSidenav('survey-book').toggle();
    };

});
</script>
