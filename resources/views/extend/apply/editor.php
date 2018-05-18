<div layout="column" layout-align="start center">
    <div ng-include="'stepsTemplate'"></div>
</div>
<div ng-controller="editorController" layout="row" style="height:100%">
    <survey-book ng-if="book && !book.lock" book="book">
        <div layout="row">
            <h1 ng-repeat="message in messages" md-colors="{color: 'red'}">{{message.description}}</h1>
            <md-button class="md-raised md-primary" ng-click="changeStep('nextStep')" style="font-size: 18px">完成編輯</md-button>
        </div>
    </survey-book>
</div>

<script src="/js/angular-file-upload.min.js"></script>
<script src="/packages/cere/survey/js/ng/ngEditor.js"></script>
<script src="/packages/cere/survey/js/ng/surveyRule.js"></script>
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

    $scope.changeStep = function(method) {
        $http({method: 'POST', url: method, data:{}})
        .success(function(data, status, headers, config) {
            $scope.messages = data.messages;
            if (data.messages.length === 0) {
                location.reload();
            }
        })
        .error(function(e){
            console.log(e);
        });
    }

    $scope.getBook();

});
</script>
