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
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>加掛者可申請的母體名單欄位 (請勾選)</h4></md-subheader>
                    <md-list-item ng-if="applicable.column.length == 0">
                        <div class="ui negative message" flex>
                            <div class="header">請先完成登入設定</div>
                        </div>
                    </md-list-item>
                    <md-list-item ng-repeat="column in applicable.options.columns">
                        <p>{{column.title}}</p>
                        <md-checkbox class="md-secondary" ng-checked="exists(column, columnselected)" ng-click="toggle(column, columnselected, $event)" ng-true-value="true" ng-false-value="" aria-label="{{column.title}}"></md-checkbox>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>可申請題目數量限制</h4></md-subheader>
                    <md-list-item>
                        <md-input-container>
                            <label>題目數量限制</label>
                            <input type="number" ng-model="applicable.extend.rule.fieldsLimit" />
                        </md-input-container>
                    </md-list-item>
                    <md-divider ></md-divider>

                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>釋出的母體問卷之題目欄位 (請勾選)</h4></md-subheader>
                    <md-list-item>
                        <button class="ui blue button" flex= 30 ng-click="showQuestion($event)">新增題目</button>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'red'}">共釋出{{selected_length}}個欄位</md-subheader>
                    <div style="border:1px solid grey; margin:20px;">
                        <p>105年度高二調查問卷</p>
                        <div>
                            <md-select ng-model="pages">
                                <md-option ng-repeat="page in haveSelect" ng-value="page">{{$index+1}}</md-option>
                            </md-select>
                            <div ng-repeat="question_column in pages">
                                {{question_column.title}}
                                <md-checkbox ng-click="toggle(question_column)" ng-checked="question_column.selected" aria-label="question_column">
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
        $scope.applicable = {};
        $scope.disabled = false;
        $scope.empty = false;
        $scope.columnselected =[];

        $scope.getApplicableOptions = function() {
            $http({method: 'POST', url: 'getApplicableOptions', data:{}})
            .success(function(data, status, headers, config) {
                angular.extend($scope.applicable, data);
            })
            .error(function(e){
                console.log(e);
            });
        }

        function getFields() {
            var fields = {};
            $filter('filter')($scope.applicable.options.columns, {selected: true}).forEach(function(field) {
                fields[field.id] = {target: 'login'};
            });
            $filter('filter')($scope.applicable.options.questions, {selected: true}).forEach(function(field) {
                fields[field.id] = {target: 'main'};
            });

            return fields;
        }

        $scope.setApplicableOptions = function() {
            // var fields = getFields();
            var fields = [];

            if ($scope.applicable.extend.rule.conditionColumn_id) {
                $scope.disabled = true;
                $http({method: 'POST', url: 'setApplicableOptions', data:{
                    selected: {
                        'fieldsLimit': $scope.applicable.extend.rule.fieldsLimit,
                        'columnsLimit' : $scope.applicable.extend.rule.columnsLimit,
                        'fields': fields,
                        'conditionColumn_id': $scope.applicable.extend.rule.conditionColumn_id}
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

        $scope.getApplicableOptions();


        $scope.showQuestion = function(ev){
            $mdDialog.show({

                controller: function($scope, $mdDialog){
                    $scope.questions = {};
                    $scope.selected_questions = [];

                    $scope.getApplicableOptions = function() {
                        $http({method: 'POST', url: 'getApplicableOptions', data:{}})
                        .success(function(data, status, headers, config) {
                            angular.extend($scope.questions, data.options.questions);
                        })
                        .error(function(e){
                            console.log(e);
                        });
                    }
                    $scope.getApplicableOptions();

                    $scope.toggle = function (item, list) {
                        item.selected = !item.selected;
                        var idx = list.indexOf(item.id);
                        if(idx>-1){
                            list.splice(idx, 1);
                        }else {
                            list.push(item.id);
                        }
                        console.log(list);
                    };

                    $scope.selectAllPage = function(page){
                        var length = 0;
                        angular.forEach(page, function(value, key){
                            if(value.selected){
                                length++;
                            }
                        })

                        if(length == page.length){
                            angular.forEach(page, function(value, key){
                                value.selected = false;
                            })
                        }else if(length != page.length){
                            angular.forEach(page, function(value, key){
                                value.selected = true;
                            })
                        }
                    }

                    $scope.save = function(){

                        $mdDialog.cancel();
                    }
                    $scope.cancel = function() {
                        $mdDialog.cancel();
                    };

                    $scope.previousPage = function(page){
                        var questions = [];
                        angular.forEach($scope.questions, function(value,key){
                            questions.push(key);
                        })
                        var index = (questions.indexOf(page)-1) > -1 ? questions.indexOf(page)-1 : 0;
                        $scope.pages = questions[index];
                    }

                    $scope.nextPage = function(page){
                        var questions = [];
                        angular.forEach($scope.questions, function(value,key){
                            questions.push(key);
                        })
                        var index = (questions.indexOf(page)+1) < questions.length-1 ? questions.indexOf(page)+1 : questions.length-1;
                        $scope.pages = questions[index];
                    }
                },
                template: `
                <md-dialog aria-label="Mango (Fruit)" style="width:1000px">
                    <form>
                        <md-toolbar>
                            <div class="md-toolbar-tools">
                                <p flex md-truncate>目前已新增{{selected_length}}個欄位</p>
                            </div>
                        </md-toolbar>

                        <md-dialog-content>
                            <div class="md-dialog-content">
                                <h2>{{book.title}}</h2>
                                <div>
                                    <button class="ui small blue button" ng-click="selectAllPage(questions[pages])">全選此頁</button>
                                    <md-button ng-click="previousPage(pages)">前一頁</md-button>
                                    <md-button ng-click="nextPage(pages)">後一頁</md-button>
                                    <md-select ng-model="pages">
                                        <md-option ng-repeat="(key,page) in questions" ng-value="key" >{{$index+1}}</md-option>
                                    </md-select>
                                    <applicable-column ng-if="pages" pages="questions[pages]"></applicable-column>
                                </div>
                            </div>
                        </md-dialog-content>

                        <md-dialog-actions layout="row">
                            <md-button ng-click="save()">新增</md-button>
                            <md-button ng-click="cancel()">取消</md-button>
                        </md-dialog-actions>
                  </form>
                </md-dialog>
                `,
                parent: angular.element(document.body),
                targetEvent: ev,
                clickOutsideToClose:true,
                fullscreen: true,
                locals: {

                },
            })
        }

    });

    app.directive('applicableColumn', function(){
        return {
            restrict: 'E',
            replace: true,
            transclude: false,
            scope: {
                pages: '=',
            },
            template: `
                <div ng-repeat="question_column in pages">
                    {{question_column.title}}
                    <md-checkbox ng-click="toggle(question_column, selected_questions)" ng-checked="question_column.selected" aria-label=" ">
                </div>

            `,
            controller: function($scope){

            }
        }
    })

</script>
