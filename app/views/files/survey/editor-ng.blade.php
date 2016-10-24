
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
                <div class="ui right labeled input">
                    <input type="number" ng-model="page" ng-model-options="{updateOn: 'default blur', debounce:{default: 2000, blur: 0}}" placeholder="輸入頁數" ng-change="jumpPage()">
                    <div class="ui basic label">總共 @{{ lastPage }} 頁 </div>
                </div>
                <md-button href="demo" target="_blank">預覽</md-button>
                <md-button href="skip" target="_blank">跳題設定</md-button>
                <md-button ng-click="openBooks()">設定題本</md-button>
                <md-button ng-click="prevPage()">前一頁</md-button>
                <md-button ng-click="nextPage()">下一頁</md-button>
                <md-button ng-disabled="true">刪除整頁</md-button>
                <span flex></span>
            </div>
        </md-toolbar>
        <md-divider></md-divider>
        <div layout="column" layout-align="start center" style="height:100%;overflow-y:scroll">
            <div style="width:960px">
                <question-page ng-if="book" book="book" page="page"></question-page>
            </div>
        </div>

    </md-content>

</div>

<script type="text/ng-template" id="bar">
    @include('files.survey.template_editor_bar')
</script>
<script type="text/ng-template" id="search">
    @include('files.survey.template_editor_search')
</script>
<script type="text/ng-template" id="checkboxs">
    @include('files.survey.template_editor_checkboxs')
</script>
<script type="text/ng-template" id="texts">
    @include('files.survey.template_editor_texts')
</script>
<script type="text/ng-template" id="list">
    @include('files.survey.template_editor_list')
</script>
<script type="text/ng-template" id="jump">
    @include('files.survey.template_editor_jump')
</script>

<script src="/js/angular-file-upload.min.js"></script>
<script src="/js/ng/ngEditor.js"></script>

<script>
app.requires.push('angularFileUpload');
app.requires.push('ngEditor');
app.controller('editorController', function($http, $scope, $sce, $interval, $filter, $mdSidenav) {

    $scope.questions = [];
    $scope.page = 1;
    $scope.order = 0 ;
    $scope.select =[];
    $scope.sbooks = [];

    $scope.getBook = function() {
        $scope.$parent.main.loading = true;
        $http({method: 'POST', url: 'getBook', data:{}})
        .success(function(data, status, headers, config) {
            console.log(data);
            $scope.book = data.book;
            $scope.page = 1;
            //$scope.book.pages[1] = $scope.book.pages[1] || [];
            //$scope.lastPage = Object.keys($scope.book.pages)[Object.keys($scope.book.pages).length-1];
            $scope.$parent.main.loading = false;
        }).error(function(e) {
            console.log(e);
        });
    };

    $scope.getBook();

    $scope.nextPage = function() {
        $scope.book.pages[++$scope.page] = $scope.book.pages[$scope.page] || [];
    };

    $scope.prevPage = function() {
        if ($scope.page > 1) {
            $scope.book.pages[--$scope.page] = $scope.book.pages[$scope.page] || [];
        }
    };

    $scope.setPage = function(question, page) {
        if (parseInt(page) != page) return false;
        question.saving = true;
        $http({method: 'POST', url: 'setPage', data:{question: question, page: page}})
        .success(function(data, status, headers, config) {
            console.log(data);
            $scope.book.pages[page] = $scope.book.pages[page] || [];
            angular.forEach(data.changeds, function(changed) {
                angular.forEach($scope.pages[question.page], function(pQuestion) {
                    if (changed.id == pQuestion.id) {
                        $scope.pages[pQuestion.page].splice($scope.pages[pQuestion.page].indexOf(pQuestion), 1);
                        $scope.pages[page].push(changed);
                    };
                });
            });
            console.log($scope.pages[page]);
        }).error(function(e) {
            console.log(e);
        });
    };

    $scope.openBooks = function() {
        $mdSidenav('survey-book').toggle();
    };

});
</script>
