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
                    <md-card-actions layout="row">
                        <md-button class="md-raised md-primary" ng-click="setConsent()" style="width: 100%;height: 50px;font-size: 18px">儲存</md-button>
                    </md-card-actions>
                </md-card>
            </md-tab>
            <md-tab label="可申請欄位(母體名單)">
                <md-card>
                    <md-card-title>
                        <md-card-title-text>
                            <span class="md-title">可申請的欄位 (請勾選)</span>
                            <md-switch ng-model="isShow.columns" aria-label="只顯示已勾選" ng-false-value="" class="md-warn">只顯示已勾選</md-switch>
                        </md-card-title-text>
                    </md-card-title>
                    <md-card-content>
                        <md-list style="height: 300px;overflow: auto">
                            <md-list-item ng-if="applicable.column.length == 0">
                                <div class="ui negative message" flex>
                                    <div class="header">請先完成登入設定</div>
                                </div>
                            </md-list-item>
                            <md-list-item ng-repeat="column in columns | filter: {selected: isShow.columns}">
                                <md-checkbox ng-model="column.selected" ng-change="columnsForm.columnsLimit.$validate();columnsForm.$setSubmitted()"></md-checkbox>
                                <p>{{column.title}}</p>
                            </md-list-item>
                        </md-list>
                    </md-card-content>
                    <md-divider ></md-divider>
                    <md-card-content ng-form="columnsForm">
                        <md-input-container style="min-width: 200px">
                            <label>可申請數量</label>
                            <md-select ng-model="columnsLimit" name="columnsLimit" amount-limit="columns">
                                <md-option ng-value="0">0</md-option>
                                <md-option ng-repeat="column in columns" ng-value="$index+1">{{$index+1}}</md-option>
                            </md-select>
                            <div class="errors" ng-messages="columnsForm.columnsLimit.$error">
                                <div ng-message="limit">可申請數量不能大於欄位總數</div>
                            </div>
                        </md-input-container>
                    </md-card-content>
                    <md-card-actions layout="row">
                        <md-button flex class="md-raised md-primary" ng-click="setApplicableOptions()" style="height: 50px;font-size: 18px" ng-disabled="disabled || !columnsForm.$valid">儲存</md-button>
                    </md-card-actions>
                </md-card>
            </md-tab>
            <md-tab label="可申請欄位(母體問卷)">
                <md-card>
                    <md-card-title>
                        <md-card-title-text>
                            <span class="md-title">可申請的題目 (請勾選)</span>
                            <md-switch ng-model="isShow.questions" aria-label="只顯示已勾選" ng-false-value="" class="md-warn">只顯示已勾選</md-switch>
                        </md-card-title-text>
                    </md-card-title>
                    <md-card-content>
                        <md-tabs md-dynamic-height md-border-bottom>
                            <md-tab label="第{{$index+1}}頁" ng-repeat="page in pages">
                                <md-list style="height: 300px;overflow: auto">
                                    <md-list-item ng-repeat="question in page.questions | filter: {selected: isShow.questions}">
                                        <md-checkbox ng-model="question.selected" aria-label="{{question.title}}" ng-change="questionsForm.fieldsLimit.$validate();questionsForm.$setSubmitted()"></md-checkbox>
                                        <p>{{question.title}}</p>
                                    </md-list-item>
                                </md-list>
                            </md-tab>
                        </md-tabs>
                    </md-card-content>
                    <md-divider ></md-divider>
                    <md-card-content ng-form="questionsForm">
                        <md-input-container style="min-width: 200px">
                            <label>可申請數量</label>
                            <md-select ng-model="fieldsLimit" name="fieldsLimit" fields-limit="questions">
                                <md-option ng-value="0">0</md-option>
                                <md-option ng-repeat="question in questions" ng-value="$index+1">{{$index+1}}</md-option>
                            </md-select>
                            <div class="errors" ng-messages="questionsForm.fieldsLimit.$error">
                                <div ng-message="limit">可申請數量不能大於欄位總數</div>
                            </div>
                        </md-input-container>
                    </md-card-content>
                    <md-card-actions layout="row">
                        <md-button flex class="md-raised md-primary" ng-click="setApplicableOptions()" style="height: 50px;font-size: 18px" ng-disabled="disabled || !questionsForm.$valid">儲存</md-button>
                    </md-card-actions>
                </md-card>
            </md-tab>
        </md-tabs>
    </div>
</md-content>
<script src="/packages/cere/survey/js/quill.min.js"></script>
<script src="/packages/cere/survey/js/ng-quill.min.js"></script>

<link rel="stylesheet" href="/packages/cere/survey/js/quill.snow.min.css">
<link rel="stylesheet" href="/packages/cere/survey/js/quill.bubble.min.css">
<script>
    app.requires.push('ngQuill');
    app.requires.push('ngMessages');
    app.config(['ngQuillConfigProvider', function (ngQuillConfigProvider) {
        ngQuillConfigProvider.set(null, null, 'custom placeholder')
    }])
    app.controller('application', function ($scope, $http, $filter, $mdDialog){
        $scope.columns = [];
        $scope.questions = [];
        $scope.consent = {};
        $scope.selectIndex = 0;
        $scope.isShow = {columns: '', questions: ''};

        $scope.getConsent = function(){
            $http({method: 'POST', url: 'getConsent', data:{}})
            .success(function(data, status, headers, config) {
                angular.extend($scope.consent, data.consent);
            })
            .error(function(e){
                console.log(e);
            });
        }

        $scope.getConsent();

        $scope.setConsent = function(){
            $http({method: 'POST', url: 'setConsent', data:{consent:$scope.consent}})
            .success(function(data, status, headers, config) {

            })
            .error(function(e){
                console.log(e);
            });
        }

        $scope.getApplicableOptions = function() {
            $http({method: 'POST', url: 'getApplicableOptions', data:{}})
            .success(function(data, status, headers, config) {
                $scope.columns = data.fields.mainList;
                $scope.pages = data.fields.mainBookPages;

                $scope.columnsLimit = data.limit.mainBook;
                $scope.fieldsLimit = data.limit.mainList;

                $scope.questions = $scope.pages.reduce(function(carry, page){
                    return page.questions.concat(carry);
                }, []);
            })
            .error(function(e){
                console.log(e);
            });
        }
        $scope.getApplicableOptions();

        $scope.setApplicableOptions = function() {
            $scope.disabled = true;
            $http({method: 'POST', url: 'setApplicableOptions', data:{
                selecteds: {
                    'fieldsLimit': $scope.fieldsLimit,
                    'columnsLimit' : $scope.columnsLimit,
                    'fields': $scope.getFields()
                },
            }})
            .success(function(data, status, headers, config) {
                $scope.disabled = false;
            })
            .error(function(e){
                console.log(e);
            });
        }

        $scope.getFields = function() {
            var fields = $filter('filter')($scope.columns, {selected: true}).map(function(column) {
                return column.id;
            });

            angular.forEach($scope.pages, function(page){
                fields = $filter('filter')(page.questions, {selected: true}).map(function(question){
                    return question.id;
                }).concat(fields);
            })

            return fields;
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
                    var amount = $filter('filter')(scope.amountLimit, {selected: true}).length;
                    limit = modelValue || viewValue;
                    return amount >= limit;
                };
            }
        };
    });

    app.directive('fieldsLimit', function($filter) {
        return {
            restrict: 'A',
            require: 'ngModel',
            scope: {
                fieldsLimit: '='
            },
            link: function(scope, element, attr, ngModel) {
                ngModel.$validators.limit = function(modelValue, viewValue) {
                    var amount = $filter('filter')(scope.fieldsLimit, {selected: true}).length;
                    console.log(amount);
                    limit = modelValue || viewValue;
                    return amount >= limit;
                };
            }
        };
    });
</script>
