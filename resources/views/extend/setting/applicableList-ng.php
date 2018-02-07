<md-content ng-cloak layout="column" ng-controller="application" layout-align="start center">
    <div style="width:960px">
        <md-tabs  md-dynamic-height md-border-bottom md-selected="selectIndex">
            <md-tab label="同意書及注意事項">
                <md-card>
                    <md-card-header md-colors="{background: 'indigo'}">
                        <md-card-header-text>
                            <span class="md-title">設定加掛申請同意書</span>
                        </md-card-header-text>
                    </md-card-header>
                    <md-content>
                        <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>編輯加掛申請注意事項</h4></md-subheader>
                        <div style="margin:10px">
                            <ng-quill-editor placeholder="編輯加掛申請注意事項" ng-model="consent.content"></ng-quill-editor>
                        </div>
                        <md-divider ></md-divider>
                        <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>編輯加掛申請同意書</h4></md-subheader>
                        <div style="margin:10px">
                            <ng-quill-editor placeholder="編輯加掛申請同意書" ng-model="consent.precaution"></ng-quill-editor>
                        </div>
                    </md-content>
                </md-card>
                <md-button class="md-raised md-primary" ng-click="setConsent()" style="width: 100%;height: 50px;font-size: 18px">儲存</md-button>
            </md-tab>
            <md-tab label="設定加掛申請">
                <md-card>
                    <md-card-header md-colors="{background: 'indigo'}">
                        <md-card-header-text>
                            <span class="md-title">設定加掛申請選項</span>
                        </md-card-header-text>
                    </md-card-header>
                    <md-content>
                        <md-list flex>
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
                                <md-checkbox class="md-secondary" ng-model="column.selected"></md-checkbox>
                            </md-list-item>
                            <md-divider ></md-divider>
                            <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>可申請題目數量限制</h4></md-subheader>
                            <md-list-item>
                                <md-input-container>
                                    <label>題目數量限制</label>
                                    <input type="number" ng-model="fieldsLimit" />
                                </md-input-container>
                            </md-list-item>
                            <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>釋出的母體問卷之題目欄位 (請勾選)</h4></md-subheader>
                            <md-list-item>
                                <button class="ui blue button" flex="30" ng-click="showQuestion($event)">新增題目</button>
                            </md-list-item>
                            <md-divider ></md-divider>
                            <md-subheader class="md-no-sticky">
                                <button class="ui small blue button" ng-click="selectAllPage(pages[page])">全選此頁</button>
                                <button class="ui small blue button" ng-click="delete()">刪除</button>
                                <md-button ng-click="prePage(page)">上一頁</md-button>
                                <md-input-container>
                                    <md-select placeholder="請選擇" ng-model="page">
                                        <md-option ng-repeat="(key,page) in pages" ng-value="key" >{{$index+1}}</md-option>
                                    </md-select>
                                </md-input-container>
                                <md-button ng-click="nextPage(page)">下一頁</md-button>
                                <span md-colors="{color: 'red'}">共釋出{{getFields().length}}個欄位(含母體)</span>
                            </md-subheader>
                            <div style="height:300px; overflow:scroll;">
                                <md-list>
                                    <md-list-item ng-repeat="question in pages[page] | filter:{selected: true}">
                                        {{question.title}}
                                        <md-checkbox class="md-secondary" ng-model="question.delete" aria-label="{{question.title}}"></md-checkbox>
                                    </md-list-item>
                                </md-list>
                            </div>
                        </md-list>
                    </md-content>
                </md-card>
                <div layout="row">
                    <md-button class="md-raised md-primary " ng-click="selectIndex=0" style="width: 49%;height: 50px;font-size: 18px">上一頁</md-button>
                    <md-button class="md-raised md-primary " ng-click="setApplicableOptions()" style="width: 49%;height: 50px;font-size: 18px" ng-disabled="disabled">儲存</md-button>
                </div>
            </md-tab>
        </md-tabs>
    </div>
</md-content>
<script>

    app.controller('application', function ($scope, $http, $filter, $mdDialog){
        $scope.columns = [];
        $scope.questions = [];
        $scope.consent = {};

        $scope.delete = function(){
            angular.forEach($scope.pages, function(questions){
                $filter('filter')(questions, {delete: true}).forEach(function(question){
                    question.selected = false;
                    question.delete = false;
                })
            })
        }

        $scope.selectAllPage = function(page){
            angular.forEach(page, function(question){
                question.delete = true;
            })
        }

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
                $scope.columns = data.options.columns;
                $scope.pages = data.options.pages;

                $scope.fieldsLimit = data.rule.fieldsLimit;
                $scope.columnsLimit = data.rule.columnsLimit;
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

            angular.forEach($scope.pages, function(questions){
                fields = $filter('filter')(questions, {selected: true}).map(function(question){
                    return question.id;
                }).concat(fields);
            })

            return fields;
        }

        $scope.showQuestion = function(ev){
            var application = $scope;
            $mdDialog.show({
                controller: function($scope, $mdDialog){
                    $scope.pages = application.pages;

                    $scope.selectAllPage = function(page){
                        angular.forEach(page, function(question){
                            question.picked = true;
                        })
                    }

                    $scope.save = function() {
                        angular.forEach($scope.pages, function(questions){
                            $filter('filter')(questions, {picked: true}).forEach(function(question){
                                question.selected = true;
                                question.picked = false;
                            });
                        })

                        $mdDialog.hide();
                    }

                },
                template: `
                <md-dialog aria-label="新增欄位" style="width:1000px;">
                    <form>
                        <md-toolbar>
                            <div class="md-toolbar-tools">
                                <p flex md-truncate>目前已新增{{selected_length}}個欄位</p>
                            </div>
                        </md-toolbar>

                        <md-dialog-content style=" height:600px; overflow:scroll">
                            <div class="md-dialog-content">
                                <div>
                                    <md-button class="md-raised md-primary" ng-click="selectAllPage(pages[page])">全選此頁</md-button>
                                    <md-button ng-click="prePage(page)">上一頁</md-button>
                                    <md-input-container>
                                        <md-select placeholder="請選擇" ng-model="page">
                                            <md-option ng-repeat="(key,page) in pages" ng-value="key" >{{$index+1}}</md-option>
                                        </md-select>
                                    </md-input-container>
                                    <md-button ng-click="nextPage(page)">下一頁</md-button>
                                    <applicable-column page="pages[page]"></applicable-column>
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
                <md-list>
                    <md-list-item ng-repeat="question in page">
                        {{question.title}}
                        <md-checkbox class="md-secondary" ng-hide="question.selected" ng-model="question.picked" aria-label="question"></md-checkbox>
                    </md-list-item>
                </md-list>
            `
        }
    })

</script>
