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
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>主題本進入加掛題本{{extColumn.title}}條件設定</h4></md-subheader>
                    <md-list-item>
                        <md-input-container style="width: 100%">
                            <label>請選擇{{extColumn.title}}</label>
                            <md-select ng-model="organization" ng-change="setOrganization(organization)" data-md-container-class="selectdemoSelectHeader" multiple>
                                <md-option ng-value="organization" ng-repeat="organization in organizations.lists">{{organization.name}}</md-option>
                            </md-select>
                            <div class="ui negative message" ng-if="empty.organizations">
                                <div class="header">請選擇{{extColumn.title}}</div>
                            </div>
                        </md-input-container>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>變項選擇</h4></md-subheader>
                    <md-list-item ng-repeat="column in columns">
                        <p>{{column.survey_applicable_option.title}}</p>
                        <md-checkbox class="md-secondary" ng-model="column.selected" ng-true-value="true" ng-false-value="false" aria-label="{}"></md-checkbox>
                    </md-list-item>
                    <md-list-item ng-if="empty.columns">
                        <div class="ui negative message" flex>
                            <div class="header" >請選擇變項</div>
                        </div>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>使用主題本題目</h4></md-subheader>
                    <md-list-item ng-repeat="question in questions">
                        <p>{{question.survey_applicable_option.title}}</p>
                        <md-checkbox class="md-secondary" ng-model="question.selected" ng-true-value="true" ng-false-value="false" aria-label="{}"></md-checkbox>
                    </md-list-item>
                    <md-list-item ng-if="empty.questions">
                        <div class="ui negative message" flex>
                            <div class="header" >請選擇主題本題目</div>
                        </div>
                    </md-list-item>
                </md-list>
                <md-list flex ng-if="edited">
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>主題本進入加掛題本{{extColumn.title}}條件設定</h4></md-subheader>
                    <md-list-item ng-repeat="organization in organizations.selected">
                        <p>{{organization.name}}</p>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>變項選擇</h4></md-subheader>
                    <md-list-item ng-repeat="column in columns">
                        <p>{{column.survey_applicable_option.title}}</p>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>使用主題本題目</h4></md-subheader>
                    <md-list-item ng-repeat="question in questions">
                        <p>{{question.survey_applicable_option.title}}</p>
                    </md-list-item>
                </md-list>
            </md-content>
        </md-card>
        <md-button class="md-raised md-primary md-display-2" ng-click="setAppliedOptions()" ng-if="!edited" ng-disabled="disabled" style="width: 100%;height: 50px;font-size: 18px">送出</md-button>
        <md-button class="md-raised md-primary md-display-2" ng-click="toExtBook()" ng-if="edited" style="width: 100%;height: 50px;font-size: 18px">前往編製加掛問卷</md-button>
        <md-button class="md-raised md-primary md-display-2" ng-click="resetApplication()" ng-if="edited" ng-disabled="disabled" style="width: 100%;height: 50px;font-size: 18px">重新申請</md-button>
    </div>
</md-content>
<style>
    .selectdemoSelectHeader .demo-header-searchbox {
        border: none;
        outline: none;
        height: 100%;
        width: 100%;
        padding: 0; }
    .selectdemoSelectHeader .demo-select-header {
        box-shadow: 0 1px 0 0 rgba(0, 0, 0, 0.1), 0 0 0 0 rgba(0, 0, 0, 0.14), 0 0 0 0 rgba(0, 0, 0, 0.12);
        padding-left: 10.667px;
        height: 48px;
        cursor: pointer;
        position: relative;
        display: flex;
        align-items: center;
        width: auto; }
    .selectdemoSelectHeader md-content._md {
        max-height: 240px; }
</style>
<script>
    app.controller('application', function ($scope, $http, $filter, $location, $element){
        $scope.columns = [];
        $scope.questions = [];
        $scope.edited = [];
        $scope.book = [];
        $scope.extBook = {};
        $scope.extColumn = {};
        $scope.selected = {'organizations': []};
        $scope.organizations = {'lists': [], 'selected': []};
        $scope.disabled = false;
        $scope.empty = {'organizations': false, 'questions': false, 'columns': false};

        $scope.setOrganization = function(organization) {
            $scope.selected.organizations = organization;
        };

        $scope.getAppliedOptions = function() {
            $http({method: 'POST', url: 'getAppliedOptions', data:{}})
            .success(function(data, status, headers, config) {
                $scope.book = data.book;
                angular.extend($scope, data);
            })
            .error(function(e){
                console.log(e);
            });
        }

        function getSelected() {
            var columns = $filter('filter')($scope.columns, {selected: true}).map(function(column) {
                return column.id;
            });

            var questions = $filter('filter')($scope.questions, {selected: true}).map(function(question) {
                return question.id;
            });

            var rules = new Array({'conditions': []});
            for(key in $scope.selected.organizations) {
                var condition = {'type': $scope.extColumn.class, 'id': $scope.book.column_id, 'value': $scope.selected.organizations[key].id};
                if(key > 0) {
                    condition.logic = ' || ';
                }
                rules[0].conditions.push(condition);
            }
            $scope.empty.columns = columns.length <= 0 ? true : false;
            $scope.empty.questions = questions.length <= 0 ? true : false;
            $scope.empty.organizations = rules[0].conditions.length <= 0 ? true : false;
            var sent = !$scope.empty.columns && !$scope.empty.questions && !$scope.empty.organizations ? true : false;

            return {'columns': columns.concat(questions), 'rules': rules, 'sent': sent};
        }

        $scope.setAppliedOptions = function() {
            $scope.organizations.selected = null;
            var selected = getSelected();
            if (selected.sent) {
                $scope.disabled = true;
                $http({method: 'POST', url: 'setAppliedOptions', data:{selected: selected}})
                .success(function(data, status, headers, config) {
                    angular.extend($scope, data);
                    $scope.disabled = false;
                })
                .error(function(e){
                    console.log(e);
                });
            } else {
                return 0;
            }
        }

        $scope.resetApplication = function() {
            $scope.disabled = true;
            $http({method: 'POST', url: 'resetApplication', data:{}})
            .success(function(data, status, headers, config) {
                angular.extend($scope, data);
                $scope.selected.organizations = [];
                $scope.disabled = false;
            })
            .error(function(e){
                console.log(e);
            });
        }

        $scope.toExtBook = function(event) {
            open($scope.extBook.link, '_blank');
        };

        $scope.getAppliedOptions();
    });
</script>
