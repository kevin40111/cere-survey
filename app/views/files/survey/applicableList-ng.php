<md-content ng-cloak layout="column" ng-controller="application" layout-align="start center">
    <div style="width:960px">
        <md-card style="width: 100%">
            <md-card-header md-colors="{background: 'indigo'}">
                <md-card-header-text>
                    <span class="md-title">可申請加掛項目列表</span>
                </md-card-header-text>
            </md-card-header>
            <md-content>
                <md-list flex ng-if="!edited">
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>母體名單設定</h4></md-subheader>
                    <md-list-item>
                        <md-select placeholder="請選擇" ng-model="table.id" ng-change="setParentList(table.id)" style="width: 920px" >
                            <md-option ng-value="table.id"  ng-repeat="table in tableList">{{table.title}}</md-option>
                        </md-select>
                    </md-list-item>
                    <md-list-item ng-if="empty.table">
                        <div class="ui negative message" flex>
                            <div class="header">請選擇母體名單</div>
                        </div>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>主題本進入加掛題本條件設定</h4></md-subheader>
                    <md-list-item>
                        <md-select placeholder="請選擇" ng-model="column" ng-change="conditionSelected(column)" style="width: 920px">
                            <md-option ng-value="column" ng-repeat="column in columns">{{column.title}}</md-option>
                        </md-select>
                    </md-list-item>
                    <md-list-item ng-if="empty.conditionColumn">
                        <div class="ui negative message" flex>
                            <div class="header">請選擇欄位</div>
                        </div>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>變項選擇</h4></md-subheader>
                    <md-list-item ng-repeat="column in columns">
                        <p>{{column.title}}</p>
                        <md-checkbox class="md-secondary" ng-model="column.selected" ng-true-value="true" ng-false-value="" aria-label="{}"></md-checkbox>
                    </md-list-item>
                    <md-list-item ng-if="empty.column">
                        <div class="ui negative message" flex>
                            <div class="header">請選擇變項</div>
                        </div>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>使用主題本題目</h4></md-subheader>
                    <md-list-item ng-repeat="question in questions">
                        <p>{{question.title}}</p>
                        <md-checkbox class="md-secondary" ng-model="question.selected" ng-true-value="true" ng-false-value="" aria-label="{}"></md-checkbox>
                    </md-list-item>
                    <md-list-item ng-if="empty.questions">
                        <div class="ui negative message" flex>
                            <div class="header">請選擇主題本題目</div>
                        </div>
                    </md-list-item>
                </md-list>
                <md-list flex ng-if="edited">
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>母體名單設定</h4></md-subheader>
                    <md-list-item>
                        <p>{{tables.selected.title}}</p>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>主題本進入加掛題本條件欄位設定</h4></md-subheader>
                    <md-list-item>
                        <p>{{conditionColumn.title}}</p>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>變項選擇</h4></md-subheader>
                    <md-list-item ng-repeat="column in columns">
                        <p>{{column.title}}</p>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>使用主題本題目</h4></md-subheader>
                    <md-list-item ng-repeat="question in questions">
                        <p>{{question.title}}</p>
                    </md-list-item>
                </md-list>
            </md-content>
        </md-card>
        <md-button class="md-raised md-primary md-display-2" ng-click="setApplicableOptions()" style="width: 100%;height: 50px;font-size: 18px" ng-if="!edited" ng-disabled="disabled">送出</md-button>
        <md-button class="md-raised md-primary md-display-2" ng-click="resetApplicableOptions()" style="width: 100%;height: 50px;font-size: 18px" ng-if="edited" ng-disabled="disabled">重新設定</md-button>
    </div>
</md-content>
<script>
    app.controller('application', function ($scope, $http, $filter){
        $scope.columns = [];
        $scope.questions = [];
        $scope.edited = false;
        $scope.conditionColumn = [];
        $scope.tablesSelected = null;
        $scope.tables = {'list': [], 'selected': []};
        $scope.tableList = [];
        $scope.disabled = false;
        $scope.empty = {'conditionColumn': false, 'questions': false, 'table': false, 'column': false};

        $scope.getApplicableOptions = function() {
            $http({method: 'POST', url: 'getApplicableOptions', data:{rowsFileId: $scope.tablesSelected}})
            .success(function(data, status, headers, config) {
                angular.extend($scope, data);
                if ($scope.tableList.length <= 0) {
                    $scope.tableList = $scope.tables.list;
                }
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
            $scope.empty.table = $scope.tablesSelected == null ? true : false;
            $scope.empty.column = columns.length <= 0 ? true : false;
            $scope.empty.questions = questions.length <= 0 ? true : false;
            $scope.empty.conditionColumn = $scope.conditionColumn.length <= 0 ? true : false;
            var sent = !$scope.empty.table && !$scope.empty.column && !$scope.empty.questions && !$scope.empty.conditionColumn ? true : false;

            return {'columns': columns, 'questions': questions, 'conditionColumn': $scope.conditionColumn, 'tablesSelected': $scope.tablesSelected, 'sent': sent};
        }

        $scope.setParentList = function(mother_list_id){
            $scope.tablesSelected = mother_list_id;
            $scope.getApplicableOptions();
        }

        $scope.setApplicableOptions = function() {
            var selected = getSelected();
            if (selected.sent) {
                $scope.disabled = true;
                $http({method: 'POST', url: 'setApplicableOptions', data:{selected: selected}})
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

        $scope.resetApplicableOptions = function() {
            $scope.disabled = true;
            $http({method: 'POST', url: 'resetApplicableOptions', data:{}})
            .success(function(data, status, headers, config) {
                angular.extend($scope, data);
                if ($scope.tableList.length <= 0) {
                    $scope.tableList = $scope.tables.list;
                }
                $scope.disabled = false;
            })
            .error(function(e){
                console.log(e);
            });
        }

        $scope.conditionSelected = function(column) {
            $scope.conditionColumn = column;
        }

         $scope.getApplicableOptions();
    });
</script>
