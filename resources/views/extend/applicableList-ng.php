<md-content ng-cloak layout="column" ng-controller="application" layout-align="start center">
    <div style="width:960px">
        <md-card style="width: 100%">
            <md-card-header md-colors="{background: 'indigo'}">
                <md-card-header-text>
                    <span class="md-title">設定加掛申請選項</span>
                </md-card-header-text>
            </md-card-header>
            <md-content>
                <md-list flex>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>主題本進入加掛題本判斷條件欄位</h4></md-subheader>
                    <md-list-item>
                        <md-select placeholder="請選擇" ng-model="applicable.extend.rule.conditionColumn_id" style="width: 920px">
                            <md-option ng-value="column.id" ng-repeat="column in applicable.options.columns">{{column.title}}</md-option>
                        </md-select>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>可申請母體欄位數量限制</h4></md-subheader>
                    <md-list-item>
                        <md-input-container>
                            <label>母體欄位數量限制</label>
                            <input type="number" ng-model="applicable.extend.rule.columnsLimit" />
                        </md-input-container>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>可申請母體名單變項</h4></md-subheader>
                    <md-list-item ng-repeat="column in applicable.options.columns">
                        <p>{{column.title}}</p>
                        <md-checkbox class="md-secondary" ng-model="column.selected" ng-true-value="true" ng-false-value="" aria-label="{{column.title}}"></md-checkbox>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>可申請題目數量限制</h4></md-subheader>
                    <md-list-item>
                        <md-input-container>
                            <label>題目數量限制</label>
                            <input type="number" ng-model="applicable.extend.rule.fieldsLimit" />
                        </md-input-container>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>可申請主題本題目</h4></md-subheader>
                    <md-list-item ng-repeat="question in applicable.options.questions">
                        <p>{{question.title}}</p>
                        <md-checkbox class="md-secondary" ng-model="question.selected" ng-true-value="true" ng-false-value="" aria-label="{{question.title}}"></md-checkbox>
                    </md-list-item>
                </md-list>
            </md-content>
        </md-card>
        <md-button class="md-raised md-primary md-display-2" ng-click="setApplicableOptions()" style="width: 100%;height: 50px;font-size: 18px" ng-disabled="disabled">儲存</md-button>
    </div>
</md-content>
<script>
    app.controller('application', function ($scope, $http, $filter){
        $scope.applicable = {};
        $scope.disabled = false;
        $scope.empty = false;

        $scope.getApplicableOptions = function() {
            $http({method: 'POST', url: 'getApplicableOptions', data:{}})
            .success(function(data, status, headers, config) {
                angular.extend($scope.applicable, data);
            })
            .error(function(e){
                console.log(e);
            });
        }

        function getFields() {
            var fields = {};
            $filter('filter')($scope.applicable.options.columns, {selected: true}).forEach(function(field) {
                fields[field.id] = {target: 'login'};
            });
            $filter('filter')($scope.applicable.options.questions, {selected: true}).forEach(function(field) {
                fields[field.id] = {target: 'main'};
            });

            return fields;
        }

        $scope.setApplicableOptions = function() {
            // var fields = getFields();
            var fields = [];

            if ($scope.applicable.extend.rule.conditionColumn_id) {
                $scope.disabled = true;
                $http({method: 'POST', url: 'setApplicableOptions', data:{
                    selected: {
                        'fieldsLimit': $scope.applicable.extend.rule.fieldsLimit,
                        'columnsLimit' : $scope.applicable.extend.rule.columnsLimit,
                        'fields': fields,
                        'conditionColumn_id': $scope.applicable.extend.rule.conditionColumn_id}
                    }
                })
                .success(function(data, status, headers, config) {
                    $scope.disabled = false;
                    $scope.empty = false;
                })
                .error(function(e){
                    console.log(e);
                });
            } else {
                $scope.empty = true;
            }
        }

        $scope.getApplicableOptions();
    });
</script>
