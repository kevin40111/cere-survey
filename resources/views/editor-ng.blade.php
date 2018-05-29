
<div ng-controller="editorController" layout="row" style="height:100%">
    <survey-book ng-if="book && !book.lock" book="book">
        <md-button class="md-icon-button md-primary" href="loginCondition"><md-tooltip md-direction="bottom">登入設定</md-tooltip><md-icon>people</md-icon></md-button>
        <md-button class="md-icon-button md-primary" href="exportSheet" target="_blank"><md-tooltip md-direction="bottom">下載填答值</md-tooltip><md-icon>file_download</md-icon></md-button>
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
    .deleting.ng-leave {
        transition: 0.5s linear all;
        opacity: 1;
    }
    .deleting.ng-leave.ng-leave-active {
        opacity: 0;
    }
    .up.leave {
        top: 0px;
        opacity: 0.5;
    }
    .up.leave.active {
        transition: 0.2s linear all;
        position: relative;
        top: -20px;
        opacity: 0;
    }
    .up.enter {
        position: relative;
        top: 20px;
        opacity: 0.5;
    }
    .up.enter.active {
        transition: 0.2s linear all;
        top: 0px;
        opacity: 1;
    }
    .down.leave {
        top: 0;
        opacity: 0.5;
    }
    .down.leave.active {
        transition: 0.2s linear all;
        position: relative;
        top: 20px;
        opacity: 0;
    }
    .down.enter {
        position: relative;
        top: -20px;
        opacity: 0.5;
    }
    .down.enter.active {
        transition: 0.2s linear all;
        top: 0px;
        opacity: 1;
    }
    .noneAbove {
        color: red;
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

    $scope.getBook();

    $scope.openBooks = function() {
        $mdSidenav('survey-book').toggle();
    };

});
</script>
