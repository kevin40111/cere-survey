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
                    <md-divider></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>填答時間</h4></md-subheader>
                    <md-list-item>
                        <div style="padding-left: 16px">
                            <md-input-container style="margin-bottom: 0">
                                <label>開始時間</label>
                                <input mdc-datetime-picker date="true" time="true" type="text" ng-model="auth.start_at" cancel-text="取消" ok-text="確定" today-text="今天">
                            </md-input-container>
                            <md-button class="md-icon-button" aria-label="清除" ng-click="auth.start_at = null"><md-icon>autorenew</md-icon></md-button>
                        </div>
                        <div style="padding-left: 16px">
                            <md-input-container style="margin-bottom: 0">
                                <label>結束時間</label>
                                <input mdc-datetime-picker date="true" time="true" type="text" ng-model="auth.close_at" cancel-text="取消" ok-text="確定" today-text="今天">
                            </md-input-container>
                            <md-button class="md-icon-button" aria-label="清除" ng-click="auth.close_at = null"><md-icon>autorenew</md-icon></md-button>
                        </div>
                        <span md-colors="{color: 'red'}" ng-if="error.datetime">開始時間不能大於結束時間</span>
                    </md-list-item>
                </md-list>
            </md-content>
        </md-card>
        <md-button class="md-raised md-primary" ng-click="setLoginCondition(true)" ng-disabled="loading" style="height: 50px;font-size: 18px">儲存</md-button>

    </div>
</md-content>
<script src="/packages/cere/survey/js/angular-material-datetimepicker.min.js"></script>
<script src="/packages/cere/survey/js/moment.min.js"></script>
<link rel="stylesheet" href="/packages/cere/survey/js/material-datetimepicker.min.css" />
<script>
    app.requires.push('ngMaterialDatePicker');
    app.controller('condition', function ($scope, $http, $filter, $mdToast) {

        $scope.auth = {};
        $scope.option = {};
        $scope.loading = true;
        $scope.error = {};

        $scope.getAuthFieldFiles = function() {
            $http({method: 'POST', url: 'getAuthOptions', data:{}})
            .success(function(data, status, headers, config) {
                $scope.option.files = data.fieldFiles;
                $scope.option.rules = data.rules;
                $scope.auth.fieldFile_id = data.fieldFile_id;
                $scope.auth.start_at = data.start_at;
                $scope.auth.close_at = data.close_at;
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
            if ($scope.auth.start_at && $scope.auth.close_at && (new Date($scope.auth.start_at) > new Date($scope.auth.close_at))) {
                return $scope.error.datetime = true;
            } else {
                $scope.error.datetime = false;
            }

            $scope.auth.fields = $filter('filter')($scope.option.fields, {isInput: true});
            $scope.loading = true;
            $http({method: 'POST', url: 'setLoginCondition', data:{auth: $scope.auth}})
            .success(function(data, status, headers, config) {
                $scope.loading = false;
                $mdToast.show(
                    $mdToast.simple()
                        .textContent('儲存成功')
                        .hideDelay(3000)
                );
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

