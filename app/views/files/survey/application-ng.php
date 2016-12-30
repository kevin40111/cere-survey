<md-content ng-cloak layout="column" ng-controller="application" layout-align="start center">
    <div style="width:960px">
        <md-card style="width: 100%">
            <md-card-header md-colors="{background: 'indigo'}">
                <md-card-header-text>
                    <span class="md-title">加掛題申請表</span>
                </md-card-header-text>
            </md-card-header>
            <md-content>
                <md-list flex ng-if="appliedOptions.length == 0">
                    <form action="register/save" method="post" ng-submit="save($event)">
                        <md-subheader class="md-no-sticky"><h4>變向選擇</h4></md-subheader>
                        <md-list-item ng-repeat="applicableColumn in applicableOption.applicableColumns">
                            <p>{{applicableColumn.survey_applicable_option.title}}</p>
                            <md-checkbox class="md-secondary" ng-model="applicableColumn.selected" ng-true-value="true" ng-false-value="false" aria-label="{}"></md-checkbox>
                        </md-list-item>
                        <md-divider ></md-divider>
                        <md-subheader class="md-no-sticky"><h4>使用主題本題目</h4></md-subheader>
                        <md-list-item ng-repeat="applicableQuestion in applicableOption.applicableQuestions">
                            <p>{{applicableQuestion.survey_applicable_option.title}}</p>
                            <md-checkbox class="md-secondary" ng-model="applicableQuestion.selected" ng-true-value="true" ng-false-value="false" aria-label="{}"></md-checkbox>
                        </md-list-item>
                    </form>
                </md-list>
                <md-list flex ng-if="appliedOptions.length != 0">
                    <md-subheader class="md-no-sticky"><h4>變向選擇</h4></md-subheader>
                    <md-list-item ng-repeat="applicableColumn in appliedOptions.applicableColumns">
                        <p>{{applicableColumn.survey_applicable_option.title}}</p>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky"><h4>使用主題本題目</h4></md-subheader>
                    <md-list-item ng-repeat="applicableQuestion in appliedOptions.applicableQuestions">
                        <p>{{applicableQuestion.survey_applicable_option.title}}</p>
                    </md-list-item>
                </md-list>
            </md-content>
        </md-card>
        <md-button class="md-raised md-primary md-display-2" ng-click="setAppliedOptions()" ng-if="appliedOptions.length == 0" style="width: 100%;height: 50px;font-size: 18px">送出</md-button>
        <md-button class="md-raised md-primary md-display-2" ng-click="resetApplication()" ng-if="appliedOptions.length != 0" style="width: 100%;height: 50px;font-size: 18px">重新申請</md-button>
    </div>
</md-content>
<script>
    app.controller('application', function ($scope, $http, $filter){
        $scope.applicableOption = [];
        $scope.getAppliedOptions = function() {
            $http({method: 'POST', url: 'getAppliedOptions', data:{}})
            .success(function(data, status, headers, config) {
                console.log(data);
                $scope.applicableOption = data.applicableOption;
                $scope.appliedOptions = data.appliedOptions;
            })
            .error(function(e){
                console.log(e);
            });
        }

        function getSeleted() {
            var applicableColumns = $filter('filter')($scope.applicableOption.applicableColumns, {selected: true}).map(function(column) {
                return column.id;
            });
            var applicableQuestions = $filter('filter')($scope.applicableOption.applicableQuestions, {selected: true}).map(function(question) {
                return question.id;
            });
            return applicableColumns.concat(applicableQuestions);
        }

        $scope.setAppliedOptions = function() {
            var selected = getSeleted();
            $http({method: 'POST', url: 'setAppliedOptions', data:{selected: selected, book_id: $scope.applicableOption.applicableColumns[0].book_id}})
            .success(function(data, status, headers, config) {
                console.log(data);
                $scope.appliedOptions = data.appliedOptions;
            })
            .error(function(e){
                console.log(e);
            });
        }

        $scope.resetApplication = function() {
            $http({method: 'POST', url: 'resetApplication', data:{book_id: $scope.applicableOption.applicableColumns[0].book_id}})
            .success(function(data, status, headers, config) {
                console.log(data);
                $scope.applicableOption = data.applicableOption;
                $scope.appliedOptions = data.appliedOptions;
            })
            .error(function(e){
                console.log(e);
            });
        }

        $scope.getAppliedOptions();
    });
</script>
