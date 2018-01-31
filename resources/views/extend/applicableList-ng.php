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
                    <md-list-item ng-repeat="column in columns">
                        <p>{{column.title}}</p>
                        <md-checkbox class="md-secondary" ng-checked="column.selected" ng-click="toggle(column)"></md-checkbox>
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
                </md-list>
            </md-content>
        </md-card>
        <md-button class="md-raised md-primary md-display-2" ng-click="setApplicableOptions()" style="width: 100%;height: 50px;font-size: 18px" ng-disabled="disabled">儲存</md-button>
    </div>
</md-content>
<script>
    app.factory('fieldsFactory', function() {
        this.selected = [];
        this.columns = [];
        this.questions = [];

        return {
            columns: this.columns,
            questions: this.questions,
            selected: this.selected,
            toggle: function (item) {
                item.selected = !item.selected;
                var idx = this.selected.indexOf(item.id);
                if(idx>-1){
                    this.selected.splice(idx, 1);
                }else {
                    this.selected.push(item.id);
                }
                console.log(this.selected);
                return this.selected;
            }
        }
    });

    app.controller('application', function ($scope, $http, $filter, $mdDialog, fieldsFactory){
        $scope.empty = false;

        $scope.toggle = function (column) {
            fieldsFactory.toggle(column);
        }

        $scope.getApplicableOptions = function() {
            $http({method: 'POST', url: 'getApplicableOptions', data:{}})
            .success(function(data, status, headers, config) {
                fieldsFactory.selected = data.rule.fields;
                fieldsFactory.columns = data.options.columns;
                fieldsFactory.questions = data.options.questions;

                $scope.selected = fieldsFactory.selected;
                $scope.columns = fieldsFactory.columns;
                $scope.fieldsLimit = data.rule.fieldsLimit;
                $scope.columnsLimit = data.rule.columnsLimit;
                $scope.conditionColumn_id = data.rule.conditionColumn_id
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
            $mdDialog.show({
                controller: function($scope, $mdDialog, fieldsFactory){
                    $scope.questions = fieldsFactory.questions;
                    $scope.selected = fieldsFactory.selected;
                    console.log($scope.questions);
                    $scope.selectAllPage = function(page){
                        var selected = fieldsFactory.selected;
                        angular.forEach(page, function(question){
                            if(selected.indexOf(question.id) == -1) {
                                question.selected = true;
                                selected.push(question.id);
                            }
                        })
                    }

                    $scope.toggle = function(item) {
                        fieldsFactory.toggle(item);
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
                                    <button class="ui small blue button" ng-click="selectAllPage(questions[page])">全選此頁</button>
                                    <md-select ng-model="page">
                                        <md-option ng-repeat="(key,page) in questions" ng-value="key" >{{$index+1}}</md-option>
                                    </md-select>
                                    <applicable-column page="questions[page]"></applicable-column>
                                </div>
                            </div>
                        </md-dialog-content>
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
                <div ng-repeat="question in page">
                    {{question.title}}
                    <md-checkbox ng-click="toggle(question)" ng-checked="question.selected" aria-label=" ">
                </div>
            `
        }
    })

</script>
