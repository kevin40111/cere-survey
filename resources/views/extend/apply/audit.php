<div ng-cloak layout="column" ng-controller="application" layout-align="start center">
    <div ng-include="'stepsTemplate'"></div>
    <div style="width:960px">
        <md-card>
            <md-card-header>
                <md-card-header-text>
                    <span class="md-title">申請結果</span>
                </md-card-header-text>
            </md-card-header>
            <div md-colors="{background: status.background}" style="padding: 50px">
                <h4>{{status.message}}</h4>
            </div>
            <md-card-actions layout="row" ng-if="status.back">
                <span flex></span>
                <md-button ng-repeat="step in steps" ng-click="backToPreStep(step.method)" md-colors="{color: status.background}">
                    修改{{step.title}}
                </md-button>
            </md-card-actions>
        </md-card>
        <md-card style="margin-top: 20px">
            <md-card-header>
                <md-card-header-text>
                    <span class="md-title">已申請資料，共申請{{getSelected().length}}個欄位</span>
                </md-card-header-text>
            </md-card-header>
            <md-card-content>
                <md-list flex>
                    <md-subheader class="md-no-sticky">母體名單欄位</md-subheader>
                    <md-list-item ng-repeat="column in columns | filter:{selected: true}">
                        <p>{{column.title}}</p>
                    </md-list-item>
                    <md-subheader class="md-no-sticky" ng-repeat-start="page in pages">母體問卷第{{$index+1}}頁</md-subheader>
                    <md-list-item ng-repeat-end ng-repeat="question in page.questions | filter:{selected: true}">
                        <p>{{question.title}}</p>
                    </md-list-item>
                </md-list>
            </md-card-content>
        </md-card>
    </div>
</div>
<script>
    app.controller('application', function ($scope, $http, $filter, $location, $element, $mdDialog) {
        $scope.columns = [];
        $scope.allStatus = [
            {
                class: 'ui orange label',
                title: '審核中',
                message:'你的申請已送出，請靜待審核結果',
                background: 'cyan',
                back: false
            },
            {
                class: 'ui green label',
                title: '審核通過',
                message: '恭喜你!審核已通過，問卷即將進入調查，請靜待通知',
                background: 'light-green',
                back: false
            },
            {
                class: 'ui red label',
                title: '審核不通過',
                message: '很抱歉，你的審核未通過，請於期限內修改並重新送出審核',
                background: 'pink-A200',
                back: true
            },
            {
                class: 'ui grey label',
                title: '取消',
                message: '你的申請已經取消，請於期限內送出加掛申請',
                background: 'grey',
                back: true
            }
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
        ];

        $scope.backToPreStep = function (method) {
            $http({method: 'POST', url: method, data: {}})
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
                $scope.pages = $filter('filter')(data.fields.mainBookPages, function(page) {
                    return $filter('filter')(page.questions, {selected: true}).length > 0;
                });

                $scope.columnsLimit = data.limit.mainBook;
                $scope.fieldsLimit = data.limit.mainList;
                $scope.status = $scope.allStatus[data.status];
            })
            .error(function (e) {
                console.log(e);
            });
        }

        $scope.getSelected = function getSelected() {
            var fields = $filter('filter')($scope.columns, {
                selected: true
            }).map(function (field) {
                return field.id;
            });
            angular.forEach($scope.pages, function (page) {
                fields = $filter('filter')(page.questions, {
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
