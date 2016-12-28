<md-content ng-cloak layout="column" ng-controller="application" layout-align="start center">
    <div style="width:960px">
        <md-card style="width: 100%">
            <md-card-header md-colors="{background: 'indigo'}">
                <md-card-header-text>
                    <span class="md-title">加掛題申請表</span>
                </md-card-header-text>
            </md-card-header>
            <md-content>
                <md-list flex>
                    <md-subheader class="md-no-sticky"><h4>變向選擇</h4></md-subheader>
                    <md-list-item ng-repeat="column in applicableOption.columns">
                        <p>{{column.survey_applicable_option.title}}</p>
                        <md-checkbox class="md-secondary" ng-model="column.selected" ng-true-value="true" ng-false-value="" aria-label="{}"></md-checkbox>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky"><h4>使用主題本題目</h4></md-subheader>
                    <md-list-item ng-repeat="question in applicableOption.questions">
                        <p>{{question.survey_applicable_option.title}}</p>
                        <md-checkbox class="md-secondary" ng-model="question.selected" ng-true-value="true" ng-false-value="" aria-label="{}"></md-checkbox>
                    </md-list-item>
                </md-list>
            </md-content>
        </md-card>
        <md-button class="md-raised md-primary md-display-2" ng-click="setAppliedOptions()" style="width: 100%;height: 50px;font-size: 18px">送出</md-button>
    </div>
</md-content>
<script>
    app.controller('application', function ($scope, $http, $filter){
        $scope.applicableOption = [];
        $scope.getAppliedOptions = function() {
            $http({method: 'POST', url: 'getAppliedOptions', data:{}})
            .success(function(data, status, headers, config) {
                $scope.applicableOption = data.applicableOption;
            })
            .error(function(e){
                console.log(e);
            });
        }

        function getSeleted() {
            var columns = $filter('filter')($scope.applicableOption.columns, {selected: true}).map(function(column) {
                return column.id;
            });
            var questions = $filter('filter')($scope.applicableOption.questions, {selected: true}).map(function(question) {
                return question.id;
            });
            return $.merge( columns, questions );
        }

        $scope.setAppliedOptions = function() {
            var selected = getSeleted();
            $http({method: 'POST', url: 'setAppliedOptions', data:{selected: selected}})
            .error(function(e){
                console.log(e);
            });
        }

        $scope.getAppliedOptions();
    });
</script>
