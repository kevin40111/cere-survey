<md-content ng-cloak layout="column" ng-controller="application" layout-align="start center">
    <div style="width:960px">
        <md-card style="width: 100%">
            <md-card-header md-colors="{background: 'indigo'}">
                <md-card-header-text>
                    <span class="md-title">加掛題申請表</span>
                </md-card-header-text>
            </md-card-header>
            <md-content>
                <md-list flex ng-if="!edited">
                    <form action="register/save" method="post" ng-submit="save($event)">
                        <md-subheader class="md-no-sticky"><h4>變向選擇</h4></md-subheader>
                        <md-list-item ng-repeat="column in columns">
                            <p>{{column.survey_applicable_option.title}}</p>
                            <md-checkbox class="md-secondary" ng-model="column.selected" ng-true-value="true" ng-false-value="false" aria-label="{}"></md-checkbox>
                        </md-list-item>
                        <md-divider ></md-divider>
                        <md-subheader class="md-no-sticky"><h4>使用主題本題目</h4></md-subheader>
                        <md-list-item ng-repeat="question in questions">
                            <p>{{question.survey_applicable_option.title}}</p>
                            <md-checkbox class="md-secondary" ng-model="question.selected" ng-true-value="true" ng-false-value="false" aria-label="{}"></md-checkbox>
                        </md-list-item>
                    </form>
                </md-list>
                <md-list flex ng-if="edited">
                    <md-subheader class="md-no-sticky"><h4>變向選擇</h4></md-subheader>
                    <md-list-item ng-repeat="column in columns">
                        <p>{{column.survey_applicable_option.title}}</p>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky"><h4>使用主題本題目</h4></md-subheader>
                    <md-list-item ng-repeat="question in questions">
                        <p>{{question.survey_applicable_option.title}}</p>
                    </md-list-item>
                </md-list>
            </md-content>
        </md-card>
        <md-button class="md-raised md-primary md-display-2" ng-click="setAppliedOptions()" ng-if="!edited" style="width: 100%;height: 50px;font-size: 18px">送出</md-button>
        <md-button class="md-raised md-primary md-display-2" ng-click="resetApplication()" ng-if="edited" style="width: 100%;height: 50px;font-size: 18px">重新申請</md-button>
    </div>
</md-content>
<script>
    app.controller('application', function ($scope, $http, $filter){
        $scope.columns = [];
        $scope.questions = [];
        $scope.edited = [];
        $scope.getAppliedOptions = function() {
            $http({method: 'POST', url: 'getAppliedOptions', data:{}})
            .success(function(data, status, headers, config) {
                $scope.setVar(data.columns, data.questions, data.edited);
            })
            .error(function(e){
                console.log(e);
            });
        }

        function getSeleted() {
            var columns = $filter('filter')($scope.columns, {selected: true}).map(function(column) {
                return column.id;
            });
            var questions = $filter('filter')($scope.questions, {selected: true}).map(function(question) {
                return question.id;
            });
            return columns.concat(questions);
        }

        $scope.setAppliedOptions = function() {
            var selected = getSeleted();
            $http({method: 'POST', url: 'setAppliedOptions', data:{selected: selected, book_id: $scope.columns[0].book_id}})
            .success(function(data, status, headers, config) {
                $scope.setVar(data.columns, data.questions, data.edited);
            })
            .error(function(e){
                console.log(e);
            });
        }

        $scope.resetApplication = function() {
            $http({method: 'POST', url: 'resetApplication', data:{}})
            .success(function(data, status, headers, config) {
                $scope.setVar(data.columns, data.questions, data.edited);
            })
            .error(function(e){
                console.log(e);
            });
        }

        $scope.setVar = function(columns, questions, edited) {
            $scope.columns = columns;
            $scope.questions = questions;
            $scope.edited = edited
        }

        $scope.getAppliedOptions();
    });
</script>
