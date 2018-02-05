<md-content ng-cloak layout="column" ng-controller="application" layout-align="start center">
    <div ng-include="'master'"></div>
    <div style="width:960px">
        <md-card style="width: 100%">
            <md-card-header md-colors="{background: 'indigo'}">
                <md-card-header-text>
                    <span class="md-title">加掛題申請表 <md-button style="background-color:#4DB6AC;font-weight:bold"></md-button></span>
                </md-card-header-text>
            </md-card-header>
            <md-content>
                <md-list flex>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>資料申請</h4></md-subheader>
                    <md-list-item ng-repeat="field in application.optionFields">
                        <p>{{field.title}}</p>
                        <md-checkbox class="md-secondary" ng-model="field.selected" ng-true-value="true" ng-false-value="false" aria-label="{{field.title}}"></md-checkbox>
                    </md-list-item>
                </md-list>
            </md-content>
        </md-card>
        <md-button class="md-raised md-primary md-display-2" ng-click="setAppliedOptions()" ng-disabled="disabled" style="width: 100%;height: 50px;font-size: 18px">送出</md-button>
        <md-button class="md-raised md-primary md-display-2" href="open" ng-if="edited" style="width: 100%;height: 50px;font-size: 18px">前往編製加掛問卷</md-button>
    </div>
</md-content>
<script>
    app.controller('application', function ($scope, $http, $filter, $location, $element){
        $scope.columns = [];
        $scope.questions = [];
        $scope.edited = [];
        $scope.extBook = {};
        $scope.extColumn = {};
        $scope.disabled = true;
        $scope.allStatus = [
            {key: ' 0 ', title: '審核中'},
            {key: ' 1 ', title: '退件'},
            {key: ' 2 ', title: '審核通過'}
        ];
        $scope.application = {};

        $scope.getAppliedOptions = function() {
            $http({method: 'POST', url: 'getAppliedOptions', data:{}})
            .success(function(data, status, headers, config) {
                $scope.disabled = false;
                angular.extend($scope.application, data);
            })
            .error(function(e){
                console.log(e);
            });
        }

        function getSelected() {
            var optionFields = $filter('filter')($scope.application.optionFields, {selected: true}).map(function(optionField) {
                return optionField.id;
            });

            return {'optionFields': optionFields, 'organizations': $scope.application.organizations};
        }

        $scope.setAppliedOptions = function() {
            console.log(1);

            var selected = getSelected();
            if (true || selected.sent) {
                $scope.disabled = true;
                $http({method: 'POST', url: 'setAppliedOptions', data:{selected: selected}})
                .success(function(data, status, headers, config) {
                    angular.extend($scope, data);
                    $scope.disabled = false;
                    $scope.applicationStatus();
                })
                .error(function(e){
                    console.log(e);
                });
            }
        }

        $scope.getAppliedOptions();
    });
</script>