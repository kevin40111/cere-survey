<div ng-controller="browser-layout">
    <md-content ng-cloak layout="column" layout-align="start center">
        <node-browser ng-if="book" book="book"></node-browser>
    </md-content>
</div>
<style type="text/css">
    .Dialog tr{
        text-align: center;
    }
    .td-two-logic{
        background-color: seagreen;
    }
</style>
<script src="/packages/cere/survey/js/ng/ngBrowser.js"></script>

<script>
app.requires.push('ngBrowser');
app.controller('browser-layout', function($http, $scope, $sce, $interval, $filter, $mdSidenav) {
    $scope.getBook = function() {
        $scope.$parent.main.loading = true;
        $http({method: 'POST', url: 'getBook', data:{}})
        .success(function(data, status, headers, config) {
            $scope.book = data.book.id;
            $scope.$parent.main.loading = false;
        }).error(function(e) {
            console.log(e);
        });
    };

    $scope.getBook();
});
</script>

