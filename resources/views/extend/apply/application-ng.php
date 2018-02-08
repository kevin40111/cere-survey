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
<div ng-include="'master'"></div>
<div style="width:960px">
    <md-card style="width: 100%">
        <md-card-header md-colors="{background: 'indigo'}">
            <md-card-header-text>
                <span class="md-title">加掛題申請</span>
            </md-card-header-text>
        </md-card-header>
        <md-content>
            <md-list flex>
                <md-subheader class="md-no-sticky"  md-colors="{color: 'indigo-800'}"><h4>可申請的母體名單數量: <span  md-colors="{color: 'grey'}">{{columnsLimit}}</span></h4></md-subheader>
                <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>申請母體名單欄位(請勾選)：</h4></md-subheader>
                <md-list-item ng-repeat="column in columns">
                    <p>{{column.title}}</p>
                    <md-checkbox ng-click="toggle(column, $event)" ng-checked="column.selected" aria-label="{{column.title}}" ng-disabled="disabled"></md-checkbox>
                </md-list-item>
                <md-divider ></md-divider>
                <md-subheader class="md-no-sticky"  md-colors="{color: 'indigo-800'}"><h4>可加入母體問卷之題目欄位的數量： <span  md-colors="{color: 'grey'}">{{fieldsLimit}}</span></h4></md-subheader>
                <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>釋出的母體問卷之題目欄位 (請勾選)</h4></md-subheader>
                <md-list-item>
                    <button class="ui blue button" flex="30" ng-click="showQuestion($event)" ng-disabled="disabled">新增題目</button>
                </md-list-item>

                <md-divider ></md-divider>
                <md-subheader class="md-no-sticky">
                    <button class="ui small blue button" ng-click="selectAllPage(pages[page])" ng-disabled="disabled">全選此頁</button>
                    <button class="ui small blue button" ng-click="delete(pages[page])" ng-disabled="disabled">刪除</button>
                    <md-input-container>
                        <md-select placeholder="請選擇" ng-model="page">
                            <md-option ng-repeat="page in release(pages)" ng-value="page">{{$index+1}}</md-option>
                        </md-select>
                    </md-input-container>
                    <span md-colors="{color: 'red'}">共新增{{getSelected().length}}個欄位(含母體)</span>
                </md-subheader>
                <div style="height:300px; overflow:scroll;">
                <md-list>
                    <md-list-item ng-repeat="question in pages[page]| filter:{selected:true}">
                        {{question.title}}
                        <md-checkbox class="md-secondary" ng-model="question.deleted" aria-label="{{question.title}}" ng-disabled="disabled"></md-checkbox>
                    </md-list-item>
                </md-list>
                </div>
            </md-list>
        </md-content>
    </md-card>

    <div layout="row">
        <md-button class="md-raised md-primary" ng-click="setAppliedOptions()" style="width: 50%;height: 50px;font-size: 18px" ng-disabled="disabled">送出審核</md-button>
        <md-button class="md-raised md-primary"ng-if="edited" style="width: 50%;height: 50px;font-size: 18px" href="open">上一步</md-button>
    </div>
</div>
</md-content>
<script>
app.controller('application', function ($scope, $http, $filter, $location, $element, $mdDialog){
    $scope.columns = [];
    $scope.edited = [];
    $scope.extBook = {};
    $scope.extColumn = {};
    $scope.allStatus = [
        {key: ' 0 ', title: '審核中'},
        {key: ' 1 ', title: '退件'},
        {key: ' 2 ', title: '審核通過'}
    ];

    $scope.delete = function(){
        angular.forEach($scope.pages, function(questions){
            $filter('filter')(questions, {deleted: true}).forEach(function(question){
                question.selected = false;
                question.deleted = false;
            });
        })
    }

    $scope.toggle = function(column, ev){
        var fields = $filter('filter')($scope.columns, {selected: true}).map(function(field) {
            return field.id;
        });
        if(column.selected == false){
            if(fields.length < $scope.columnsLimit){
                column.selected = true;
            }else{
                $scope.limitMessage();
            }
        }else{
            column.selected = false;
        }
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

    $scope.selectAllPage = function(value){
        angular.forEach(value, function(question){
            question.deleted = true;
        })
    }

    $scope.getAppliedOptions = function() {
        $http({method: 'POST', url: 'getAppliedOptions', data:{}})
        .success(function(data, status, headers, config) {
            $scope.disabled = false;
            $scope.columns = data.fields.mainList;
            $scope.pages = data.fields.mainBookPages;
            $scope.columnsLimit = data.limit.mainBook;
            $scope.fieldsLimit = data.limit.mainList;

        })
        .error(function(e){
            console.log(e);
        });
    }

    $scope.release = function(pages){
        var field = [];
        var release_length = 0;
        angular.forEach(pages, function(questions,key){
            if(questions != 0){
                field.push(key);
            }
        })
        return field;
    }

    $scope.getSelected = function getSelected() {
        var fields = $filter('filter')($scope.columns, {selected: true}).map(function(field) {
            return field.id;
        });
        angular.forEach($scope.pages, function(questions){
            fields = $filter('filter')(questions, {selected: true}).map(function(question){
                return question.id;
            }).concat(fields);
        })
        return fields;
    }

    $scope.setAppliedOptions = function() {
        $http({method: 'POST', url: 'setAppliedOptions', data:{selected: $scope.getSelected()}})
        .success(function(data, status, headers, config) {
            angular.extend($scope, data);
            $scope.disabled = true;
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
                $scope.fieldsLimit = application.fieldsLimit;
                $scope.limitMessage = application.limitMessage;
                $scope.release = application.release;

                console.log($scope.pages);

                $scope.selectAllPage = function(value, ev){
                    var release = [];
                    release = $filter('filter')(value, {selected: false}).length;

                    if($scope.getFields().length + release > $scope.fieldsLimit){
                        $scope.limitMessage(ev);
                    }else{
                        angular.forEach(value, function(question){
                            question.picked = true;
                        })
                    }
                }

                $scope.getFields = function(){
                    var fields = [];
                    angular.forEach($scope.pages, function(questions){
                        $filter('filter')(questions, {picked: true}).map(function(question){
                            fields.push(question.id);
                        });
                        $filter('filter')(questions, {selected: true}).map(function(question){
                            fields.push(question.id);
                        })
                    })
                    return fields;
                };

                $scope.toggle = function(question, ev){
                    if($scope.getFields().length > $scope.fieldsLimit){
                        $scope.limitMessage(ev);
                        question.picked = false;
                    }
                }

                $scope.save = function() {
                    angular.forEach($scope.pages, function(questions){
                        $filter('filter')(questions, {picked: true}).forEach(function(question){
                            question.selected = true;
                            question.picked = false;
                        });
                    })
                    console.log($scope.pages);
                    $mdDialog.hide();
                }

            },
            template: `
            <md-dialog aria-label="新增欄位" style="width:1000px;">
                <form>
                    <md-toolbar>
                        <div class="md-toolbar-tools">
                            <p flex md-truncate>目前已新增{{getFields().length}}個欄位</p>
                        </div>
                    </md-toolbar>

                    <md-dialog-content style=" height:600px; overflow:scroll">
                        <div class="md-dialog-content">
                            <div>
                                <md-button class="md-raised md-primary" ng-click="selectAllPage(pages[page], $event)">全選此頁</md-button>
                                <md-input-container>
                                    <md-select placeholder="請選擇" ng-model="page">
                                        <md-option ng-repeat="page in release(pages)" ng-value="page">{{$index+1}}</md-option>
                                    </md-select>
                                </md-input-container>
                                <md-list>
                                    <md-list-item ng-repeat="question in pages[page]">
                                        {{question.title}}
                                        <md-checkbox class="md-secondary" ng-hide="question.selected" aria-label="question.selected" ng-model="question.picked" ng-change="toggle(question, $event)"></md-checkbox>
                                    </md-list-item>
                                </md-list>
                            </div>
                        </div>
                    </md-dialog-content>
                    <md-dialog-actions layout="row">
                        <md-button ng-click="save()">新增</md-button>
                    </md-dialog-actions>
              </form>
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
