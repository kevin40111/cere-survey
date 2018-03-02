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
                    <span class="md-title">可申請的母體名單欄位：(請勾選，可申請數量：{{mainListLimit.amount}})</span>
                </md-card-title-text>
            </md-card-title>

            <md-card-content>
                <md-list flex>
                    <md-list-item ng-repeat="field in mainListLimit.fields">
                        <p>{{field.title}}</p>
                        <md-checkbox class="md-secondary" ng-model="field.selected" ng-change="toggle(field, $event)" aria-label="{{field.title}}"></md-checkbox>
                    </md-list-item>
                </md-list>
                <md-divider></md-divider>
            </md-card-content>

            <md-card-title>
                <md-card-title-text>
                    <span class="md-title">已選擇的母體問卷題目欄位：(可申請數量：{{mainBookLimit.amount}})</span>
                </md-card-title-text>
            </md-card-title>

            <md-card-content>
                <md-list>
                    <md-list-item md-colors="{backgroundColor: 'primary'}" ng-click="showQuestion($event)">
                        <p style="text-align: center">新增題目欄位</p>
                    </md-list-item>
                    <md-subheader class="md-no-sticky md-primary" ng-repeat-start="page in mainBookLimit.pages" ng-if="(page.fields | filter:{selected:true}).length > 0">母體問卷第{{$index+1}}頁</md-subheader>
                    <md-list-item ng-repeat-end ng-repeat="field in page.fields | filter: {selected: true}">
                        <p>{{field.title}}</p>
                        <md-icon class="md-secondary" ng-click="delete(field)" aria-label="刪除">delete</md-icon>
                    </md-list-item>
                </md-list>
            </md-card-content>
            <md-card-actions layout="row">
                <md-button class="md-raised md-primary" flex style="height: 50px;font-size: 18px" ng-click="changeStep('preStep')">上一步</md-button>
                <md-button class="md-raised md-primary" flex style="height: 50px;font-size: 18px" ng-click="showConfirm($event)">送出審核</md-button>
            </md-card-actions>
    </md-card>
</div>
</md-content>
<script>
app.controller('application', function ($scope, $http, $filter, $location, $element, $mdDialog){
    $scope.changeStep = function(method) {
        $http({method: 'POST', url: method, data:{}})
        .success(function(data, status, headers, config) {
            location.reload();
        })
        .error(function(e){
            console.log(e);
        });
    }

    $scope.delete = function(field){
        field.selected = false;
        $scope.setAppliedOptions();
    }

    $scope.toggle = function(field, ev){
        if (field.selected) {
            if ($filter('filter')($scope.mainListLimit.fields, {selected: true}).length > $scope.mainListLimit.amount){
                field.selected = false;
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
            $scope.mainListLimit = data.mainListLimit;
            $scope.mainBookLimit = data.mainBookLimit;
        })
        .error(function(e){
            console.log(e);
        });
    }

    $scope.getSelected = function getSelected() {
        var fields = $filter('filter')($scope.mainListLimit.fields, {selected: true}).map(function(field) {
            return field.id;
        });
        angular.forEach($scope.mainBookLimit.pages, function(page){
            fields = $filter('filter')(page.fields, {selected: true}).map(function(field){
                return field.id;
            }).concat(fields);
        })
        return fields;
    }

    $scope.setAppliedOptions = function() {
        $http({method: 'POST', url: 'setAppliedOptions', data:{selected: $scope.getSelected()}})
        .success(function(data, status, headers, config) {
        })
        .error(function(e){
            console.log(e);
        });
    }

    $scope.getAppliedOptions();

    $scope.showConfirm = function(ev){
        $mdDialog.show({
            controller: function(scope){
                scope.changeStep = $scope.changeStep;
                scope.close = function(){
                    $mdDialog.hide();
                }
            },
            template: `
                <md-dialog arial-label="confirm check">
                    <md-dialog-content>
                        <div class="md-dialog-content">
                            <h3>送出後將無法再做任何變更，<br>請問確定要送出嗎?</h3>
                        </div>
                    </md-dialog-content>
                    <md-dialog-actions>
                        <md-button class="md-primary md-raised" ng-click="changeStep('nextStep')">確認</md-button>
                        <md-button class="md-accent md-raised" ng-click="close()">取消</md-button>
                    </md-dialog-actions>
                </md-dialog>
            `,
            parent: angular.element(document.body),
            targetEvent: ev,
            clickOutsideToClose: false
        });
    }

    $scope.showQuestion = function(ev){
        var application = $scope;
        $mdDialog.show({
            controller: function($scope, $mdDialog){
                $scope.mainBookLimit = application.mainBookLimit;
                $scope.limitMessage = application.limitMessage;
                $scope.setAppliedOptions = application.setAppliedOptions;

                $scope.getAmount = function(){
                    return $scope.mainBookLimit.pages.reduce(function(amount, page) {
                        return amount + $filter('filter')(page.fields, function(field) {
                            return field.selected || field.picked;
                        }).length;
                    }, 0);
                };

                $scope.checkLimit = function(field, ev) {
                    if ($scope.getAmount() > application.mainBookLimit.amount){
                        $scope.limitMessage(ev);
                        field.picked = false;
                    }
                }

                $scope.save = function() {
                    angular.forEach($scope.mainBookLimit.pages, function(page){
                        $filter('filter')(page.fields, {picked: true}).forEach(function(field){
                            field.selected = true;
                            field.picked = false;
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
                        <md-tab label="第{{$index+1}}頁" ng-repeat="page in mainBookLimit.pages">
                            <div>
                                <md-list>
                                    <md-list-item ng-repeat="field in page.fields">
                                        <p>{{field.title}}</p>
                                        <md-checkbox class="md-secondary" ng-model="field.picked" ng-checked="field.selected" ng-disabled="field.selected" ng-change="checkLimit(field)" aria-label="{{field.title}}"></md-checkbox>
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
