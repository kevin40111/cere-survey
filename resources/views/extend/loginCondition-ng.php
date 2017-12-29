<md-content ng-cloak layout="column" ng-controller="application" layout-align="start center">
    <div style="width:960px">
        <md-card style="width: 100%" ng-if="book.lock">
            <login-information></login-information>
        </md-card>
        <md-card style="width: 100%" ng-if="!book.lock">
            <md-card-header md-colors="{background: 'indigo'}">
                <md-card-header-text>
                    <span class="md-title">問卷登入條件設定</span>
                </md-card-header-text>
            </md-card-header>
            <md-content>
                <md-list flex>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>母體名單設定</h4></md-subheader>
                    <md-list-item>
                        <p>使用無母體名單</p><md-checkbox class="md-secondary" ng-model="book.no_population"></md-checkbox>
                    </md-list-item>

                    <div ng-if="book.no_population">
                        <md-list-item >
                            <no-rows no-rows="noRows"></no-rows>
                        </md-list-item>
                    </div>

                    <div ng-if="!book.no_population">
                        <md-list-item>
                            <md-select  placeholder="請選擇" ng-model="book.rowsFile_id" style="width: 920px">
                                <md-option ng-value="table.id" ng-repeat="table in rowsTables">{{table.title}}</md-option>
                            </md-select>
                        </md-list-item>
                    </div>
                    <md-divider></md-divider>
                        <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>主題本登入條件設定</h4></md-subheader>
                        <md-list-item>
                            <md-select placeholder="請選擇" ng-model="book.loginRow_id" style="width: 920px">
                                <md-option ng-value="column.id" ng-repeat="column in columns">{{column.title}}</md-option>
                            </md-select>
                        </md-list-item>
                </md-list>
            </md-content>
        </md-card>
        <md-button class="md-raised md-primary md-display-2" ng-click="setLoginCondition(true)" style="width: 100%;height: 50px;font-size: 18px" ng-if="!book.lock">送出</md-button>
        <md-button class="md-raised md-primary md-display-2" ng-click="setLoginCondition(false)" style="width: 100%;height: 50px;font-size: 18px" ng-if="book.lock">重新設定</md-button>
    </div>
</md-content>
<script>
    app.controller('application', function ($scope, $http, $filter){
        $scope.getBook = function() {
            $http({method: 'POST', url: 'getBook', data:{}})
            .success(function(data, status, headers, config) {
                $scope.book = data.book;
                console.log($scope.book);
            })
            .error(function(e){
                console.log(e);
            });
        }
        $scope.getBook();

        $scope.getRowsTable = function() {
            $http({method: 'POST', url: 'getRowsTable', data:{}})
            .success(function(data, status, headers, config) {
                $scope.rowsTables = data.rowsTables;
                $scope.columnRules = data.rowRules;
            })
            .error(function(e){
                console.log(e);
            });
        }
        $scope.getRowsTable();

        $scope.$watch('book.rowsFile_id',function(){
            $http({method: 'POST', url: 'getColumns', data:{book: $scope.book}})
            .success(function(data, status, headers, config) {
                $scope.columns = data.columns;
            })
            .error(function(e){
                console.log(e);
            });
        });

        $scope.setLoginCondition = function(lock) {
            $scope.disabled = true;
            $http({method: 'POST', url: 'setLoginCondition', data:{book: $scope.book, lock:lock}})
            .success(function(data, status, headers, config) {
                $scope.getBook();
            })
            .error(function(e){
                console.log(e);
            });
        }
    })
    .directive('noRows', function() {
        return {
            restrict: 'E',
            scope: "=",
            template:
            `
            <div layout="row" style="padding: 16px">
                <md-input-container>
                    <label>無母體名單請設定欄位代號</label>
                    <input ng-model="noRows.name" required>
                </md-input-container>
                <md-input-container>
                    <label>無母體名單請設定欄位名稱</label>
                    <input ng-model="noRows.title" required>
                </md-input-container>
                <md-input-container style="width: 200px">
                    <label>無母體名單請設定欄位類型</label>
                    <md-select ng-model="noRows.rule" required>
                        <md-option ng-repeat="(key,value) in columnRules" ng-value="key">
                        {{value.title}}
                        </md-option>
                    </md-select>
                </md-input-container>
            </div>
            <span flex></span>
            <md-button class="md-raised md-warn" ng-click="setNoPopulationColumn()" ng-disabled="!noRows.name || !noRows.title || !noRows.rule">建立母體欄位</md-button>
            `,
            controller: function($scope, $http) {
                $scope.setNoPopulationColumn = function() {
                    $http({method: 'POST', url: 'setNoPopulationColumn', data:{column: $scope.noRows}})
                    .success(function(data, status, headers, config) {
                        angular.extend($scope.columns, [data]);
                    }).error(function(e){
                        console.log(e);
                    });
                }

            }
        };
    })
    .directive('loginInformation', function() {
        return {
            restrict: 'E',
            scope: "=",
            template:
            `
            <md-list flex>
                <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>母體名單</h4></md-subheader>
                <md-list-item>
                    <p>{{book.rowsFile.title}}</p>
                </md-list-item>
                <md-divider ></md-divider>
                <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>主題本登入條件</h4></md-subheader>
                <md-list-item>
                    <p>{{book.loginColumn.title}}</p>
                </md-list-item>
            </md-list>
            `
        };
    });
</script>

