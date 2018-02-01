<style>
    .select {
        background-color: #d4d4d5;
    }
</style>
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
                        <md-select placeholder="請選擇" ng-model="conditionColumn_id" style="width: 920px">
                            <md-option ng-value="column.id" ng-repeat="column in columns">{{column.title}}</md-option>
                        </md-select>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>可申請母體欄位數量限制</h4></md-subheader>
                    <md-list-item>
                        <md-input-container>
                            <label>母體欄位數量限制</label>
                            <input type="number" ng-model="columnsLimit" />
                        </md-input-container>
                    </md-list-item>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>加掛者可申請的母體名單欄位 (請勾選)</h4></md-subheader>
                    <md-list-item ng-if="applicable.column.length == 0">
                        <div class="ui negative message" flex>
                            <div class="header">請先完成登入設定</div>
                        </div>
                    </md-list-item>
                    <md-list-item ng-repeat="column in columns">
                        <p>{{column.title}}</p>
                        <md-checkbox class="md-secondary" ng-checked="column.selected" ng-click="toggleColumn(column)"></md-checkbox>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>可申請題目數量限制</h4></md-subheader>
                    <md-list-item>
                        <md-input-container>
                            <label>題目數量限制</label>
                            <input type="number" ng-model="fieldsLimit" />
                        </md-input-container>
                    </md-list-item>
                    <md-divider ></md-divider>

                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>釋出的母體問卷之題目欄位 (請勾選)</h4></md-subheader>
                    <md-list-item>
                        <button class="ui blue button" flex="30" ng-click="showQuestion($event)">新增題目</button>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'red'}">共釋出{{selected.length}}個欄位(含母體)</md-subheader>
                        <div style="background-color:aliceblue; margin:20px;" ng-if="selected.length">
                            <button class="ui small blue button" ng-click="selectAllPage(questions[page])">全選</button>
                            <button class="ui small blue button" ng-click="delete(questions[page])">刪除</button>
                            <md-button ng-click="prePage(page)">上一頁</md-button>
                            <md-input-container>
                                <md-select placeholder="請選擇" ng-model="page">
                                    <md-option ng-repeat="(key,page) in questions" ng-value="key" >{{$index+1}}</md-option>
                                </md-select>
                            </md-input-container>
                            <md-button ng-click="nextPage(page)">下一頁</md-button>
                            <div style="height:300px; overflow:scroll;">
                                <div ng-repeat="question in questions[page] |filter:{selected:true}">
                                    <md-checkbox ng-click="toggle(question)" ng-checked="question.deleted" aria-label=" ">
                                    {{question.title}}
                                </div>
                            </div>
                        </div>
                </md-list>
            </md-content>
        </md-card>
        <md-button class="md-raised md-primary md-display-2" ng-click="setApplicableOptions()" style="width: 100%;height: 50px;font-size: 18px" ng-disabled="disabled">儲存</md-button>
    </div>
</md-content>
<script>

    app.controller('application', function ($scope, $http, $filter, $mdDialog){
        $scope.empty = false;
        $scope.selected = [];
        $scope.columns = [];
        $scope.questions = [];

        $scope.toggleColumn = function (item) {
            item.selected = !item.selected;
            var idx = $scope.selected.indexOf(item.id);
            if(idx>-1){
                $scope.selected.splice(idx, 1);
            }else {
                $scope.selected.push(item.id);
            }
        }

        $scope.toggle = function (item) {
            item.deleted = !item.deleted;
        }

        $scope.delete = function(){
            var index ;
            angular.forEach($scope.questions, function(value, key){
                $filter('filter')(value, {deleted:true}).forEach(function(question){
                    index = $scope.selected.indexOf(question.id);
                    $scope.selected.splice(index,1);
                    question.selected = false;
                    question.deleted = false;

                })
            })
            console.log($scope.selected);
        }

        $scope.selectAllPage = function(page){
            angular.forEach(page, function(question){
                question.delete = true;
            })
        }

        $scope.getApplicableOptions = function() {
            $http({method: 'POST', url: 'getApplicableOptions', data:{}})
            .success(function(data, status, headers, config) {
                $scope.selected = data.rule.fields;
                $scope.columns = data.options.columns;
                $scope.questions = data.options.questions;

                $scope.fieldsLimit = data.rule.fieldsLimit;
                $scope.columnsLimit = data.rule.columnsLimit;
                $scope.conditionColumn_id = data.rule.conditionColumn_id;
            })
            .error(function(e){
                console.log(e);
            });
        }
        $scope.getApplicableOptions();

        $scope.setApplicableOptions = function() {
            console.log($scope.conditionColumn_id);
            if ($scope.conditionColumn_id) {
                $scope.disabled = true;
                $http({method: 'POST', url: 'setApplicableOptions', data:{
                    selected: {
                        'fieldsLimit': $scope.fieldsLimit,
                        'columnsLimit' : $scope.columnsLimit,
                        'fields': $scope.selected,
                        'conditionColumn_id': $scope.conditionColumn_id}
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

        $scope.showQuestion = function(ev){
            var application = $scope;
            $mdDialog.show({
                controller: function($scope, $mdDialog){
                    $scope.questions = application.questions;
                    $scope.selected = application.selected;

                    console.log($scope.questions);
                    console.log($scope.selected);

                    $scope.selectAllPage = function(page){
                        angular.forEach(page, function(question){
                            question.picked = true;
                        })
                    }

                    $scope.toggle = function(item) {
                        item.picked = !item.picked;
                    }

                    $scope.save = function() {
                        angular.forEach($scope.questions, function(value, key){
                            $filter('filter')(value, {picked:true}).forEach(function(question){
                                question.selected = true;
                                question.picked = false;
                                $scope.selected.push(question.id);
                            })
                        })
                        console.log($scope.selected);
                        $mdDialog.hide();
                    }

                },
                template: `
                <md-dialog aria-label="Mango (Fruit)" style="width:1000px;">
                    <form>
                        <md-toolbar>
                            <div class="md-toolbar-tools">
                                <p flex md-truncate>目前已新增{{selected_length}}個欄位</p>
                            </div>
                        </md-toolbar>

                        <md-dialog-content style=" height:600px; overflow:scroll">
                            <div class="md-dialog-content">
                                <div>
                                    <md-button class="md-raised md-primary" ng-click="selectAllPage(questions[page])">全選此頁</md-button>
                                    <md-button ng-click="prePage(page)">上一頁</md-button>
                                    <md-input-container>
                                        <md-select placeholder="請選擇" ng-model="page">
                                            <md-option ng-repeat="(key,page) in questions" ng-value="key" >{{$index+1}}</md-option>
                                        </md-select>
                                    </md-input-container>
                                    <md-button ng-click="nextPage(page)">下一頁</md-button>
                                    <applicable-column page="questions[page]"></applicable-column>
                                </div>
                            </div>
                        </md-dialog-content>
                        <md-dialog-actions layout="row">
                            <md-button ng-click="save()">SAVE</md-button>
                        </md-dialog-actions>
                  </form>
                </md-dialog>
                `,
                parent: angular.element(document.body),
                targetEvent: ev,
                clickOutsideToClose:true,
                fullscreen: true
            })
        }

    });

    app.directive('applicableColumn', function(){
        return {
            restrict: 'E',
            replace: true,
            transclude: false,
            scope: {
                page: '=',
            },
            template: `
                <div ng-repeat="question in page" ng-class="{ select: question.selected}">
                    <md-checkbox ng-click="toggle(question)" ng-checked="question.picked" ng-disabled="question.selected" aria-label="question">
                    {{question.title}}
                </div>
            `
        }
    })

</script>
