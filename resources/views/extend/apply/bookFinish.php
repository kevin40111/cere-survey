<md-content ng-cloak layout="column" ng-controller="book" layout-align="start center">
    <div ng-include="'master'"></div>
    <div style="width:960px">
        <md-card style="width: 100%">
            <md-card-header md-colors="{background: 'indigo'}">
                <md-card-header-text>
                    <span class="md-title">完成的加掛問卷 <md-button style="background-color:#4DB6AC;font-weight:bold"></md-button></span>
                </md-card-header-text>
            </md-card-header>
            <md-content>
                <md-list flex>

                </md-list>
            </md-content>
        </md-card>
    </div>
</md-content>
<script>
    app.controller('book', function ($scope, $http){
        $scope.getBookFinishQuestions = function() {
            $http({method: 'POST', url: 'getBookFinishQuestions', data:{}})
            .success(function(data, status, headers, config) {


            })
            .error(function(e){
                console.log(e);
            });
        }

        $scope.getBookFinishQuestions();
    });
</script>