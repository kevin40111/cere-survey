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
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>加掛者可申請的母體名單數量</h4></md-subheader>
                    <md-list-item>
                        <md-select placeholder="請選擇" ng-model="applicable.extend.rule.conditionColumn_id" style="width: 920px">
                            <md-option ng-value="column.id" ng-repeat="column in applicable.options.columns">{{column.title}}</md-option>
                        </md-select>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>可申請母體欄位數量限制</h4></md-subheader>
                    <md-list-item>
                        <md-input-container>
                            <label>母體欄位數量限制</label>
                            <input type="number" ng-model="applicable.extend.rule.columnsLimit" />
                        </md-input-container>
                    </md-list-item>
                    <md-divider ></md-divider>
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
                    <md-subheader class="md-no-sticky" md-colors="{color: 'red'}">共釋出{{selected.length}}個欄位</md-subheader>
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
        $scope.columnselected = [];

        $scope.getApplicableOptions = function() {
            $http({method: 'POST', url: 'getApplicableOptions', data:{}})
            .success(function(data, status, headers, config) {
                angular.extend($scope.applicable, data);
            })
            .error(function(e){
                console.log(e);
            });
        }
        $scope.toggle = function(item, list, ev){
            var idx = list.indexOf(item);
            if (idx > -1) {
                list.splice(idx, 1);
            }
            else {
                list.push(item);
            }
            if($scope.selected.length > $scope.applicable.options.quantity){
                $mdDialog.show(
                    $mdDialog.alert()
                    .parent(angular.element(document.querySelector('#popupContainer')))
                    .clickOutsideToClose(true)
                    .title('超過可申請的母體名單數量')
                    .ok('確定')
                    .targetEvent(ev)
                 );
            }
        }
        $scope.exists = function(item, list){
            return list.indexOf(item) > -1;
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
                    $scope.applicable = {};
                    $scope.selected = [];
                    $scope.getApplicableOptions = function() {
                        $http({method: 'POST', url: 'getApplicableOptions', data:{}})
                        .success(function(data, status, headers, config) {
                            angular.extend($scope.applicable, data);
                        })
                        .error(function(e){
                            console.log(e);
                        });
                    }

                    $scope.getApplicableOptions();

                    $scope.toggle = function(item, list){
                        var idx = list.indexOf(item);
                        if (idx > -1) {
                            list.splice(idx, 1);
                        }
                        else {
                            list.push(item);
                        }
                    }
                    $scope.save = function(selected){
                        $mdDialog.cancel();
                    }
                    $scope.cancel = function() {
                        $scope.selected = [];
                        $mdDialog.cancel();
                    };
                    $scope.selectPage = function(page){
                        console.log(page.length);
                    }
                },
                template: `
                <md-dialog aria-label="Mango (Fruit)" style="width:1000px">
                    <form>
                        <md-toolbar>
                            <div class="md-toolbar-tools">
                                <p flex md-truncate>目前已新增{{selected.length}}個欄位</p>
                            </div>
                        </md-toolbar>

                        <md-dialog-content>
                            <div class="md-dialog-content">
                                <h2>{{book.title}}</h2>
                                <md-input-container >
                                    <button class="ui small blue button" ng-click="selectPage(page)">全選此頁</button>
                                    <md-select ng-model="page.select">
                                        <md-option ng-repeat="page in applicable.options.questions" ng-value="page">{{$index+1}}</md-option>
                                    </md-select>
                                    <div ng-repeat="field in page">
                                        <md-list-item ng-repeat="question in field">
                                            <p flex="80">{{question.title}}</p>
                                            <md-checkbox class="md-secondary" ng-checked="exists(question, selected)" ng-click="toggle(question, selected)" aria-label="question.title">{{question.title}}</md-checkbox>
                                        </md-list-item>
                                    </div>
                                </md-input-container>
                            </div>
                        </md-dialog-content>

                        <md-dialog-actions layout="row">
                            <md-button ng-click="save(question)">新增</md-button>
                            <md-button ng-click="cancel()">取消</md-button>
                        </md-dialog-actions>
                  </form>
                </md-dialog>
                `,
                parent: angular.element(document.body),
                targetEvent: ev,
                clickOutsideToClose:true,
                fullscreen: true,

              })
        }

    });
</script>
