<md-content ng-cloak layout="column" ng-controller="application" layout-align="start center">
    <div style="width:960px">
        <md-tabs md-dynamic-height md-border-bottom md-selected="selectIndex">
            <md-tab label="加掛同意書及注意事項">
                <md-card>
                    <md-card-title>
                        <md-card-title-text>
                            <span class="md-title">同意書</span>
                        </md-card-title-text>
                    </md-card-title>
                    <md-card-content>
                        <div style="margin:10px">
                            <ng-quill-editor placeholder="同意書" ng-model="consent.precaution"></ng-quill-editor>
                        </div>
                    </md-card-content>
                    <md-divider ></md-divider>
                    <md-card-title>
                        <md-card-title-text>
                            <span class="md-title">注意事項</span>
                        </md-card-title-text>
                    </md-card-title>
                    <md-card-content>
                        <div style="margin:10px">
                            <ng-quill-editor placeholder="注意事項" ng-model="consent.content"></ng-quill-editor>
                        </div>
                    </md-card-content>
                    <md-divider ></md-divider>
                    <md-card-title>
                        <md-card-title-text>
                            <span class="md-title">可申請時間</span>
                        </md-card-title-text>
                    </md-card-title>
                    <md-card-content>
                        <md-input-container style="margin-bottom: 0">
                            <label>開始時間</label>
                            <input mdc-datetime-picker date="true" time="true" type="text" ng-model="due.start" cancel-text="取消" ok-text="確定" today-text="今天">
                        </md-input-container>
                        <md-button class="md-icon-button" aria-label="清除" ng-click="due.start = null">
                            <md-icon>autorenew</md-icon>
                        </md-button>
                    </md-card-content>
                    <md-card-content>
                        <md-input-container style="margin-bottom: 0">
                            <label>結束時間</label>
                            <input mdc-datetime-picker date="true" time="true" type="text" ng-model="due.close" cancel-text="取消" ok-text="確定" today-text="今天">
                        </md-input-container>
                        <md-button class="md-icon-button" aria-label="清除" ng-click="due.close = null">
                            <md-icon>autorenew</md-icon>
                        </md-button>
                        <span md-colors="{color: 'red'}" ng-if="error.datetime">開始時間不能大於結束時間</span>
                    </md-card-content>
                    <md-card-actions layout="column">
                        <md-button class="md-raised md-primary" ng-click="setConsent()" style="width: 100%;height: 50px;font-size: 18px">儲存</md-button>
                    </md-card-actions>
                </md-card>
            </md-tab>
            <md-tab label="可申請欄位(母體名單)">
                <md-card>
                    <md-card-title>
                        <md-card-title-text>
                            <span class="md-title">可申請的欄位 (請勾選)</span>
                            <md-switch ng-model="isShow.mainListFields" aria-label="只顯示已勾選" ng-false-value="" class="md-warn">只顯示已勾選</md-switch>
                        </md-card-title-text>
                    </md-card-title>
                    <md-card-content>
                        <md-list style="height: 300px;overflow: auto">
                            <md-list-item ng-if="applicable.column.length == 0">
                                <div class="ui negative message" flex>
                                    <div class="header">請先完成登入設定</div>
                                </div>
                            </md-list-item>
                            <md-list-item ng-repeat="field in mainListLimit.fields | filter: {selected: isShow.mainListFields}">
                                <md-checkbox ng-model="field.selected" ng-change="mainListForm.mainListLimit.$validate();mainListForm.$setSubmitted()"></md-checkbox>
                                <p>{{field.title}}</p>
                            </md-list-item>
                        </md-list>
                    </md-card-content>
                    <md-divider ></md-divider>
                    <md-card-content ng-form="mainListForm">
                        <md-input-container style="min-width: 200px">
                            <label>可申請數量</label>
                            <md-select ng-model="mainListLimit.amount" name="mainListLimit" amount-limit="mainListLimit.fields">
                                <md-option ng-value="0">0</md-option>
                                <md-option ng-repeat="field in mainListLimit.fields" ng-value="$index+1">{{$index+1}}</md-option>
                            </md-select>
                            <div class="errors" ng-messages="mainListForm.mainListLimit.$error">
                                <div ng-message="limit">可申請數量不能大於欄位總數</div>
                            </div>
                        </md-input-container>
                    </md-card-content>
                    <md-card-actions layout="column">
                        <md-button class="md-raised md-primary" ng-click="setApplicableOptions('main_list_limit', mainListLimit)" style="height: 50px;font-size: 18px" ng-disabled="disabled || !mainListForm.$valid">儲存</md-button>
                    </md-card-actions>
                </md-card>
            </md-tab>
            <md-tab label="可申請欄位(母體問卷)">
                <md-card>
                    <md-card-title>
                        <md-card-title-text>
                            <span class="md-title">可申請的題目 (請勾選)</span>
                            <md-switch ng-model="isShow.mainBookFields" aria-label="只顯示已勾選" ng-false-value="" class="md-warn">只顯示已勾選</md-switch>
                        </md-card-title-text>
                    </md-card-title>
                    <md-card-content>
                        <md-tabs md-dynamic-height md-border-bottom>
                            <md-tab label="第{{$index+1}}頁" ng-repeat="mainBookPage in mainBookLimit.pages">
                                <md-list style="height: 300px;overflow: auto">
                                    <md-list-item ng-repeat="field in mainBookPage.fields | filter: {selected: isShow.mainBookFields}">
                                        <md-checkbox ng-model="field.selected" aria-label="{{field.title}}" ng-change="questionsForm.mainBookLimit.$validate();questionsForm.$setSubmitted()"></md-checkbox>
                                        <p>{{field.title}}</p>
                                    </md-list-item>
                                </md-list>
                            </md-tab>
                        </md-tabs>
                    </md-card-content>
                    <md-divider ></md-divider>
                    <md-card-content ng-form="questionsForm">
                        <md-input-container style="min-width: 200px">
                            <label>可申請數量</label>
                            <md-select ng-model="mainBookLimit.amount" name="mainBookLimit" amount-limit="mainBookLimit.fields">
                                <md-option ng-value="0">0</md-option>
                                <md-option ng-repeat="field in mainBookLimit.fields" ng-value="$index+1">{{$index+1}}</md-option>
                            </md-select>
                            <div class="errors" ng-messages="questionsForm.mainBookLimit.$error">
                                <div ng-message="limit">可申請數量不能大於欄位總數</div>
                            </div>
                        </md-input-container>
                    </md-card-content>
                    <md-card-actions layout="column">
                        <md-button class="md-raised md-primary" ng-click="setApplicableOptions('main_book_limit', mainBookLimit)" style="height: 50px;font-size: 18px" ng-disabled="disabled || !questionsForm.$valid">儲存</md-button>
                    </md-card-actions>
                </md-card>
            </md-tab>
        </md-tabs>
    </div>
</md-content>
<script src="/packages/cere/survey/js/quill.min.js"></script>
<script src="/packages/cere/survey/js/ng-quill.min.js"></script>
<script src="/packages/cere/survey/js/angular-material-datetimepicker.min.js"></script>
<script src="/packages/cere/survey/js/moment.min.js"></script>

<link rel="stylesheet" href="/packages/cere/survey/js/quill.snow.min.css">
<link rel="stylesheet" href="/packages/cere/survey/js/quill.bubble.min.css">
<link rel="stylesheet" href="/packages/cere/survey/js/material-datetimepicker.min.css" />
<script>
    app.requires.push('ngQuill');
    app.requires.push('ngMessages');
    app.requires.push('ngMaterialDatePicker');
    app.config(['ngQuillConfigProvider', function (ngQuillConfigProvider) {
        ngQuillConfigProvider.set(null, null, 'custom placeholder')
    }])
    app.controller('application', function ($scope, $http, $filter, $mdDialog){
        $scope.consent = {};
        $scope.selectIndex = 0;
        $scope.isShow = {mainListFields: '', mainBookFields: ''};
        $scope.due = {};
        $scope.$parent.main.loading = true;

        $scope.getConsent = function() {
            $http({method: 'POST', url: 'getConsent', data:{}})
            .success(function(data, status, headers, config) {
                angular.extend($scope.consent, data.consent);
                angular.extend($scope.due, data.due);
                $scope.getApplicableOptions();
            })
            .error(function(e){
                console.log(e);
            });
        }

        $scope.getConsent();

        $scope.setConsent = function(){
            $http({method: 'POST', url: 'setConsent', data:{
                consent:{
                    'content': $scope.consent,
                    'due': $scope.due
                }
            }})
            .success(function(data, status, headers, config) {

            })
            .error(function(e){
                console.log(e);
            });
        }

        $scope.getApplicableOptions = function() {
            $http({method: 'POST', url: 'getApplicableOptions', data:{}})
            .success(function(data, status, headers, config) {
                $scope.mainListLimit = data.mainListLimit;
                $scope.mainBookLimit = data.mainBookLimit;

                $scope.mainBookLimit.fields = $scope.mainBookLimit.pages.reduce(function(carry, page){
                    return page.fields.concat(carry);
                }, []);
                $scope.$parent.main.loading = false;
            })
            .error(function(e){
                console.log(e);
            });
        }

        $scope.setApplicableOptions = function(name, limit) {
            $scope.disabled = true;
            $http({method: 'POST', url: 'setApplicableOptions', data:{
                name: name,
                options: {
                    'amount': limit.amount,
                    'fields': $filter('filter')(limit.fields, {selected: true}).map(function(field) {
                        return field.id;
                    })
                },
            }})
            .success(function(data, status, headers, config) {
                $scope.disabled = false;
            })
            .error(function(e){
                console.log(e);
            });
        }
    });

    app.directive('amountLimit', function($filter) {
        return {
            restrict: 'A',
            require: 'ngModel',
            scope: {
                amountLimit: '='
            },
            link: function(scope, element, attr, ngModel) {
                ngModel.$validators.limit = function(modelValue, viewValue) {
                    if (! scope.amountLimit || scope.amountLimit.length === 0)
                        return true;
                    var amount = $filter('filter')(scope.amountLimit, {selected: true}).length;
                    limit = modelValue || viewValue;
                    return amount >= limit;
                };
            }
        };
    });
</script>
