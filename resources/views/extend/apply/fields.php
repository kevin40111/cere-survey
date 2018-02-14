<style>
.md-dialog-backdrop:nth-of-type(even) {
    z-index: 60;
}

.md-dialog-backdrop:nth-of-type(odd) {
    z-index: 79;
}

.md-dialog-container:nth-of-type(even) {
    z-index: 80;
}

.md-dialog-container:nth-of-type(odd) {
    z-index: 82;
}
</style>
<md-content ng-cloak layout="column" ng-controller="application" layout-align="start center">
<div ng-include="'stepsTemplate'"></div>
<div style="width:960px">
    <md-card style="width: 100%">
        <md-card-title>
                <md-card-title-text>
                    <span class="md-title">可申請的母體名單欄位：(請勾選，可申請數量：{{columnsLimit}})</span>
                </md-card-title-text>
            </md-card-title>

            <md-card-content>
                <md-list flex>
                    <md-list-item ng-repeat="column in columns">
                        <p>{{column.title}}</p>
                        <md-checkbox class="md-secondary" ng-model="column.selected" ng-change="toggle(column, $event)" aria-label="{{column.title}}"></md-checkbox>
                    </md-list-item>
                </md-list>
                <md-divider></md-divider>
            </md-card-content>

            <md-card-title>
                <md-card-title-text>
                    <span class="md-title">已選擇的母體問卷題目欄位：(可申請數量：{{fieldsLimit}})</span>

                </md-card-title-text>
            </md-card-title>

            <md-card-content>
                <md-list>
                    <md-list-item md-colors="{backgroundColor: 'primary'}" ng-click="showQuestion($event)">
                        <p style="text-align: center">新增題目欄位</p>
                    </md-list-item>
                    <md-subheader class="md-no-sticky md-primary" ng-repeat-start="page in pages" ng-if="(page.questions | filter:{selected:true}).length > 0">母體問卷第{{$index+1}}頁</md-subheader>
                    <md-list-item ng-repeat-end ng-repeat="question in page.questions | filter: {selected: true}">
                        <p>{{question.title}}</p>
                        <md-icon class="md-secondary" ng-click="delete(question)" aria-label="刪除">delete</md-icon>
                    </md-list-item>
                </md-list>
            </md-card-content>
            <md-card-actions layout="row">
                <md-button class="md-raised md-primary" flex style="height: 50px;font-size: 18px" ng-click="changeStep('preStep')">上一步</md-button>
                <md-button class="md-raised md-primary" flex style="height: 50px;font-size: 18px" ng-click="changeStep('nextStep')">送出審核</md-button>
            </md-card-actions>
    </md-card>
</div>
</md-content>
<script>
app.controller('application', function ($scope, $http, $filter, $location, $element, $mdDialog){
    $scope.columns = [];
    $scope.edited = [];
    $scope.extBook = {};
    $scope.extColumn = {};

    $scope.changeStep = function(method) {
        $http({method: 'POST', url: method, data:{}})
        .success(function(data, status, headers, config) {
            location.reload();
        })
        .error(function(e){
            console.log(e);
        });
    }

    $scope.delete = function(question){
        question.selected = false;
        $scope.setAppliedOptions();
    }

    $scope.toggle = function(column, ev){
        if (column.selected){
            if ($filter('filter')($scope.columns, {selected: true}).length > $scope.columnsLimit){
                column.selected = false;
                $scope.limitMessage(ev);
            }
        }
        $scope.setAppliedOptions();
    }

    $scope.limitMessage = function(ev){
        $mdDialog.show(
            $mdDialog.alert()
            .parent(angular.element(document.body))
            .clickOutsideToClose(true)
            .title('超過可申請的數量!')
            .ariaLabel('超過可申請的數量')
            .ok('確定')
            .targetEvent(ev)
            .multiple(true)
        );
    }

    $scope.getAppliedOptions = function() {
        $http({method: 'POST', url: 'getAppliedOptions', data:{}})
        .success(function(data, status, headers, config) {
            $scope.columns = data.fields.mainList;
            $scope.pages = data.fields.mainBookPages;

            $scope.columnsLimit = data.limit.mainBook;
            $scope.fieldsLimit = data.limit.mainList;
        })
        .error(function(e){
            console.log(e);
        });
    }

    $scope.getSelected = function getSelected() {
        var fields = $filter('filter')($scope.columns, {selected: true}).map(function(field) {
            return field.id;
        });
        angular.forEach($scope.pages, function(page){
            fields = $filter('filter')(page.questions, {selected: true}).map(function(question){
                return question.id;
            }).concat(fields);
        })
        return fields;
    }

    $scope.setAppliedOptions = function() {
        $http({method: 'POST', url: 'setAppliedOptions', data:{selected: $scope.getSelected()}})
        .success(function(data, status, headers, config) {
            angular.extend($scope, data);
        })
        .error(function(e){
            console.log(e);
        });
    }

    $scope.getAppliedOptions();

    $scope.showQuestion = function(ev){
        var application = $scope;
        $mdDialog.show({
            controller: function($scope, $mdDialog){
                $scope.pages = application.pages;
                $scope.limitMessage = application.limitMessage;
                $scope.setAppliedOptions = application.setAppliedOptions;

                $scope.getAmount = function(){
                    return $scope.pages.reduce(function(amount, page) {
                        return amount + $filter('filter')(page.questions, function(question) {
                            return question.selected || question.picked;
                        }).length;
                    }, 0);
                };

                $scope.checkLimit = function(question, ev) {
                    if ($scope.getAmount() > application.fieldsLimit){
                        $scope.limitMessage(ev);
                        question.picked = false;
                    }
                }

                $scope.save = function() {
                    angular.forEach($scope.pages, function(page){
                        $filter('filter')(page.questions, {picked: true}).forEach(function(question){
                            question.selected = true;
                            question.picked = false;
                        });
                    })
                    $mdDialog.hide();
                    $scope.setAppliedOptions();
                }

            },
            template: `
            <md-dialog aria-label="新增欄位" style="width:1000px;">
                <md-toolbar>
                    <div class="md-toolbar-tools">
                        <p>新增題目欄位</p>
                    </div>
                </md-toolbar>

                <md-subheader class="md-primary">目前已新增{{getAmount()}}個欄位</md-subheader>

                <md-dialog-content>
                    <div class="md-dialog-content" style="height: 600px;overflow: scroll">
                    <md-tabs md-dynamic-height md-border-bottom>
                        <md-tab label="第{{$index+1}}頁" ng-repeat="page in pages">
                            <div>
                                <md-list>
                                    <md-list-item ng-repeat="question in page.questions">
                                        <p>{{question.title}}</p>
                                        <md-checkbox class="md-secondary" ng-model="question.picked" ng-change="checkLimit(question)" aria-label="{{question.title}}"></md-checkbox>
                                    </md-list-item>
                                </md-list>
                            </div>
                        </md-tab>
                    </md-tabs>
                    </div>
                </md-dialog-content>
                <md-dialog-actions layout="row">
                    <md-button ng-click="save()">新增</md-button>
                </md-dialog-actions>
            </md-dialog>
            `,
            parent: angular.element(document.body),
            targetEvent: ev,
            clickOutsideToClose: true,
            fullscreen: true,
        })
    }
});

</script>
