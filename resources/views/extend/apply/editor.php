<div style="text-align:center;" ng-include="'stepsTemplate'"></div>
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
    <survey-book ng-if="book && !book.lock" book="book"></survey-book>
    <node-browser ng-if="book.lock" book="book.id"></node-browser>
</div>

<script src="/js/angular-file-upload.min.js"></script>
<script src="/packages/cere/survey/js/ng/ngEditor.js"></script>
<script src="/packages/cere/survey/js/ng/ngBrowser.js"></script>
<script src="/packages/cere/survey/js/quill.min.js"></script>
<script src="/packages/cere/survey/js/ng-quill.min.js"></script>

<link rel="stylesheet" href="/packages/cere/survey/js/quill.snow.min.css">
<link rel="stylesheet" href="/packages/cere/survey/js/quill.bubble.min.css">

<style>
    .fade.ng-leave {
        transition:1s linear all;
        opacity:1;
    }
    .fade.ng-leave.ng-leave-active {
        opacity:0;
    }
</style>

<script>
app.requires.push('angularFileUpload');
app.requires.push('ngEditor');
app.requires.push('ngBrowser');
app.controller('editorController', function($http, $scope, $sce, $interval, $filter, $mdSidenav) {

    $scope.getBook = function() {
        $scope.$parent.main.loading = true;
        $http({method: 'POST', url: 'getBook', data:{}})
        .success(function(data, status, headers, config) {
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
