<md-content ng-cloak layout="column" ng-controller="condition" layout-align="start center">
    <div layout="column" style="width:960px">
        <md-card flex>
            <md-card-header md-colors="{background: 'indigo'}">
                <md-card-header-text>
                    <span class="md-title">問卷登入條件設定</span>
                </md-card-header-text>
            </md-card-header>
            <md-content>
                <md-list flex>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>設定填答名單<md-button ng-click="option.createField = true">建立填答名單</md-button></h4></md-subheader>
                    <md-list-item ng-if="option.createField">
                        <field flex option="option" auth="auth"></field>
                    </md-list-item>
                    <md-list-item ng-if="!option.createField">
                        <md-select placeholder="請選擇填答名單" ng-model="auth.fieldFile_id" ng-change="getAuthColumns()" style="width: 920px">
                            <md-option ng-value="file.id" ng-repeat="file in option.files">{{file.title}}</md-option>
                        </md-select>
                    </md-list-item>
                    <md-divider></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>登入輸入欄位</h4></md-subheader>
                    <md-list-item ng-repeat="authField in option.fields">
                        <p>{{authField.title}}</p>
                        <md-checkbox class="md-secondary" ng-model="authField.isInput" ng-true-value="true" ng-false-value="false" aria-label="{{authField.title}}"></md-checkbox>
                    </md-list-item>
                    <md-divider></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>檢查是有資料才能登入</h4></md-subheader>
                    <md-list-item ng-repeat="authField in option.fields | filter:{isInput: true}">
                        <p>{{authField.title}}</p>
                        <md-checkbox class="md-secondary" ng-model="authField.isValid" ng-true-value="true" ng-false-value="false" aria-label="{{authField.title}}"></md-checkbox>
                    </md-list-item>
                </md-list>
            </md-content>
        </md-card>
        <md-button flex class="md-raised md-primary" ng-click="setLoginCondition(true)" ng-disabled="loading" style="height: 50px;font-size: 18px">儲存</md-button>
    </div>
</md-content>
<script>
    app.controller('condition', function ($scope, $http, $filter) {

        $scope.auth = {};
        $scope.option = {};
        $scope.loading = true;

        $scope.getAuthFieldFiles = function() {
            $http({method: 'POST', url: 'getAuthOptions', data:{}})
            .success(function(data, status, headers, config) {
                $scope.option.files = data.fieldFiles;
                $scope.option.rules = data.rules;
                $scope.auth.fieldFile_id = data.fieldFile_id;
            })
            .error(function(e){
                console.log(e);
            });
        }
        $scope.getAuthFieldFiles();

        $scope.$watch('auth.fieldFile_id', function() {
            $scope.loading = true;
            if ($scope.auth.fieldFile_id) {
                $http({method: 'POST', url: 'getAuthFields', data:{fieldFile_id: $scope.auth.fieldFile_id}})
                .success(function(data, status, headers, config) {
                    $scope.option.fields = data.authFields;
                    $scope.loading = false;
                })
                .error(function(e){
                    console.log(e);
                });
            }
        });

        $scope.setLoginCondition = function() {
            $scope.auth.fields = $filter('filter')($scope.option.fields, {isInput: true});
            $scope.loading = true;
            $http({method: 'POST', url: 'setLoginCondition', data:{auth: $scope.auth}})
            .success(function(data, status, headers, config) {
                $scope.loading = false;
            })
            .error(function(e){
                console.log(e);
            });
        }
    })
    .directive('field', function() {
        return {
            restrict: 'E',
            scope: {
                option: '=',
                auth: '='
            },
            template:
            `
            <md-content layout="column" layout-padding>
            <div layout="row">
                <md-input-container flex>
                    <label>表單名稱</label>
                    <input ng-model="fileTitle" required>
                </md-input-container>
            </div>
            <div layout="row">
                <md-input-container>
                    <label>欄位名稱</label>
                    <input ng-model="field.title" required>
                </md-input-container>
                <md-input-container>
                    <label>欄位代號</label>
                    <input ng-model="field.name" required>
                </md-input-container>
                <md-input-container style="width: 200px">
                    <label>欄位類型</label>
                    <md-select ng-model="field.rules" required>
                        <md-option ng-repeat="(key, rule) in option.rules" ng-value="key">{{rule.title}}</md-option>
                    </md-select>
                </md-input-container>
            </div>
            <div layout="row">
                <md-button class="md-raised md-primary" style="height: 50px;font-size: 18px" ng-click="createAuthField()" ng-disabled="!field.name || !field.title || !field.rules">確定</md-button>
                <md-button class="md-raised" style="height: 50px;font-size: 18px" ng-click="option.createField = false">取消</md-button>
            </div>
            </md-content>
            `,
            controller: function($scope, $http) {
                $scope.createAuthField = function() {
                    $http({method: 'POST', url: 'createAuthField', data:{field: $scope.field, fileTitle: $scope.fileTitle}})
                    .success(function(data, status, headers, config) {
                        $scope.option.files.push(data.file);
                        $scope.option.createField = false;
                        $scope.auth.fieldFile_id = data.file.id;
                    }).error(function(e){
                        console.log(e);
                    });
                }
            }
        };
    });
</script>

