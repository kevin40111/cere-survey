<md-content ng-cloak layout="column" ng-controller="application" layout-align="start center">
    <div ng-include="'stepsTemplate'"></div>
    <div style="width:960px">
        <md-card style="width: 100%">
            <md-card-header md-colors="{background: 'indigo'}">
                <md-card-header-text flex>
                    <span class="md-title">申請資料</span>
                </md-card-header-text>

            </md-card-header>
            <md-content>
                <md-list>
                    <md-subheader class="md-no-sticky" md-colors="{background: allStatus[status].background}">
                        <h4>{{allStatus[status].message}}</h4>
                        <md-menu ng-if="status == 2">
                            <md-button class="md-raised md-warn md-button md-ink-ripple" ng-click="$mdMenu.open()">
                                修改
                            </md-button>
                            <md-menu-content width="4" ng-mouseleave="$mdMenu.close()">
                                <md-menu-item ng-repeat="step in steps">
                                    <md-button ng-click="backToPreStep(step.method)">
                                        {{step.title}}
                                    </md-button>
                                </md-menu-item>
                            </md-menu-content>
                        </md-menu>
                    </md-subheader>
                </md-list>

                <md-list flex>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}">
                        <h4>可申請的母體名單數量:
                            <span md-colors="{color: 'grey'}">{{columnsLimit}}</span>
                        </h4>
                    </md-subheader>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}">
                        <h4>申請的母體名單欄位：</h4>
                    </md-subheader>
                    <md-list-item ng-repeat="column in columns">
                        <p>{{column.title}}</p>
                        <md-checkbox ng-click="toggle(column, $event)" ng-checked="column.selected" aria-label="{{column.title}}" ng-disabled="true"></md-checkbox>
                    </md-list-item>
                    <md-divider></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}">
                        <h4>可申請之題目欄位的數量：
                            <span md-colors="{color: 'grey'}">{{fieldsLimit}}</span>
                        </h4>
                    </md-subheader>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}">
                        <h4>申請的題目欄位：</h4>
                    </md-subheader>
                    <md-subheader class="md-no-sticky">
                        第
                        <md-input-container>
                            <md-select placeholder="請選擇" ng-model="page">
                                <md-option ng-repeat="page in release(pages)" ng-value="page">{{$index+1}}</md-option>
                            </md-select>
                        </md-input-container>
                        頁
                        </br>
                        <span md-colors="{color: 'red'}">共新增{{getSelected().length}}個欄位(含母體)</span>
                    </md-subheader>
                    <div style="height:300px; overflow:scroll;">
                        <md-list>
                            <md-list-item ng-repeat="question in pages[page]| filter:{selected:true}">
                                {{question.title}}
                                <md-checkbox class="md-secondary" ng-model="question.deleted" aria-label="{{question.title}}" ng-disabled="true"></md-checkbox>
                            </md-list-item>
                        </md-list>
                    </div>
                </md-list>
            </md-content>
        </md-card>
    </div>
</md-content>
<script>
    app.controller('application', function ($scope, $http, $filter, $location, $element, $mdDialog) {
        $scope.columns = [];
        $scope.allStatus = [
            {
                class: 'ui orange label',
                title: '審核中',
                message:'你的申請已送出，請靜待審核結果',
                background: 'cyan'
            },
            {
                class: 'ui green label',
                title: '審核通過',
                message: '恭喜你!審核已通過，問卷即將進入調查，請靜待通知',
                background: 'light-green'
            },
            {
                class: 'ui red label',
                title: '審核不通過',
                message: '很抱歉，你的審核未通過，請於期限內修改並重新送出審核',
                background: 'pink-A200'
            },
            {
                class: 'ui grey label',
                title: '取消',
                message: '你的申請已經取消，請於期限內送出加掛申請',
                background: 'grey'
            },
        ];

        $scope.steps = [
            {
                method:'backToEdit',
                title:'加掛問卷'
            },
            {
                method:'backToApply',
                title:'申請資料'
            }
        ]

        $scope.backToPreStep = function (method) {
            $http({
                    method: 'POST',
                    url: method,
                    data: {}
                })
                .success(function (data, status, headers, config) {
                     location.reload();
                })
                .error(function (e) {
                    console.log(e);
                });
        }

        $scope.getAppliedOptions = function () {
            $http({
                    method: 'POST',
                    url: 'getAppliedOptions',
                    data: {}
                })
                .success(function (data, status, headers, config) {
                    $scope.columns = data.fields.mainList;
                    $scope.pages = data.fields.mainBookPages;

                    $scope.columnsLimit = data.limit.mainBook;
                    $scope.fieldsLimit = data.limit.mainList;
                    $scope.status = data.status;
                })
                .error(function (e) {
                    console.log(e);
                });
        }

        $scope.release = function (pages) {
            var field = [];
            var release_length = 0;
            angular.forEach(pages, function (questions, key) {
                if (questions != 0) {
                    field.push(key);
                }
            })
            return field;
        }

        $scope.getSelected = function getSelected() {
            var fields = $filter('filter')($scope.columns, {
                selected: true
            }).map(function (field) {
                return field.id;
            });
            angular.forEach($scope.pages, function (questions) {
                fields = $filter('filter')(questions, {
                    selected: true
                }).map(function (question) {
                    return question.id;
                }).concat(fields);
            })
            return fields;
        }

        $scope.getAppliedOptions();
    });
</script>