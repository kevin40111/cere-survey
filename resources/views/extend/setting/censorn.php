<md-content ng-controller="confirm" layout-align="start center" style="height: 100%">
    <div class="ui basic segment" style="x-overflow: auto">
        <div layout="row">
            <div flex="85">
                <md-input-container flex="85">
                    <label>選擇頁數</label>
                    <md-select ng-model="currentPage" ng-change="getUsers(currentPage)">
                        <md-option ng-repeat="page in pages" value="{{page}}">{{page}}</md-option>
                    </md-select>
                </md-input-container>

                <div class="ui label">第 {{ currentPage }} 頁<div class="detail">共 {{ lastPage }} 頁</div></div>

                <div class="ui basic mini buttons">
                    <div class="ui button" ng-click="prev()"><i class="icon angle left arrow"></i></div>
                    <div class="ui button" ng-click="next()"><i class="icon angle right arrow"></i></div>
                </div>
            </div>
            <div layout="row">
                    <md-icon>find_in_page</md-icon>
                    <md-select ng-model="audit_status" placeholder="審核狀態" md-container-class="auditStatus">
                        <md-option value="all" selected>查看全部</md-option>
                        <md-option value="only_send">已送出</md-option>
                        <md-option ng-repeat="(key,status) in auditStatus" ng-value="key" >{{status.title}}</md-option>
                    </md-select>
            </div>
        </div>
        <div  layout="row">
            <b>加掛者申請期限 : {{start_at}} ~ {{close_at}}</b>
            <span flex></span>
        </div>
        <table class="ui very compact table">
            <thead>
                <tr class="bottom aligned">
                    <th width="60" ng-class="{descending: predicate==='-id'&&!reverse, ascending: predicate==='-id'&&reverse}" ng-click="predicate='-id';reverse=!reverse">
                        編號
                    </th>
                    <th width="350" ng-class="{sorted: false, descending: predicate==='-schools'&&!reverse, ascending: predicate==='-schools'&&reverse}" ng-click="predicate='-schools';reverse=!reverse">
                        學校
                    </th>
                    <th width="140">姓名</th>
                    <th width="250">email</th>
                    <th width="180">職稱</th>
                    <th width="180">電話、傳真</th>
                    <th width="140">申請者狀態</th>
                    <th width="100">檢視申請表</th>
                    <th width="120">檢視加掛問卷</th>
                    <th width="100">進入加掛題<br>判斷條件</th>
                    <th width="120">審核結果</th>
                    <th width="120">訊息</th>
                </tr>
            </thead>
            <tbody>
                <tr ng-show="applications.length==0">
                    <td colspan="11"><h4 class="md-headline" md-colors="{color:'grey-500'}">目前尚未有學校送出審核</md-display-1></h4>
                </tr>
                <tr ng-repeat="application in applications | filter:statusFilter(audit_status) |filter: search.organization.now.name |filter: search.username |filter: search.email" ng-click="application.focus=true" ng-class="{positive:application.focus}" ng-blur="application.focus=false">
                    <td>{{ $index+1 }}</td>
                    <td>
                        <div style="max-height:150px;overflow-y:scroll">
                            <div ng-repeat="organization in application.member.organizations">{{ organization.now.name }}({{ organization.now.id }})</div>
                        </div>
                    </td>
                    <td>{{ application.member.user.username }}</td>
                    <td>
                        {{ application.member.user.email }}
                        <div ng-if="application.members.user.email2">{{ application.member.user.email2 }}</div>
                    </td>
                    <td>{{ application.member.contact.title }}</td>
                    <td>
                        <div><i class="text telephone icon"></i>{{ application.member.contact.tel }}</div>
                        <div><i class="fax icon"></i>{{ application.member.contact.fax }}</div>
                    </td>
                    <td md-colors="{color:application.status_color}">
                        {{application.status_date}}<br>{{application.status_title}}
                    </td>
                    <td class="center aligned">
                        <md-button aria-label="檢視申請表" class="md-primary" ng-click="openApplication(application)" ng-blur="application.focus=false" md-colors="{background:selectStatus[application.individual_status.apply].color}" >
                            <div md-colors="{color:'grey-A100'}">{{selectStatus[application.individual_status.apply].title}}</div>
                        </md-button>
                    </td>
                    <td>
                        <div layout="row">
                            <md-button aria-label="加掛問卷" class="md-primary" ng-click="openBrowser(application)" ng-blur="application.focus=false" md-colors="{background:selectStatus[application.individual_status.book].color}">
                                <div md-colors="{color:'grey-A100'}">{{selectStatus[application.individual_status.book].title}}</div>
                            </md-button>
                            <md-icon ng-style="{color: application.book.lock ? 'green' : 'red'}">{{application.book.lock ? 'lock': 'lock_open'}}</md-icon>
                        </div>
                    </td>
                    <td><md-button ng-click="LoginConditions(application)" aria-label="進入加掛題判斷條件"><md-icon md-svg-icon="gallery"></md-icon></md-button></td>
                    <td>
                        <div layout="row" flex="noshrink">
                            <md-select ng-model="application.status" md-colors="{color: auditStatus[application.status].color}" ng-blur="application.focus=false" ng-change="setApplicationStatus(application)" class="md-no-underline" aria-label="審核結果">
                                <md-option ng-repeat="(key,status) in auditStatus|auditFilter:application track by key" ng-value="key">{{status.title}}</md-option>
                            </md-select>
                        </div>
                    </td>
                    <td>
                        <md-button md-colors="{'background':'cyan-700'}" ng-click="sendMsg($event, application)">訊息</md-button>
                    </td>
                </tr>
            <tbody>
        </table>
        <md-progress-linear md-mode="indeterminate" ng-disabled="sheetLoaded"></md-progress-linear>
    </div>
    </md-content>
<script src="/packages/cere/survey/js/ng/ngBrowser.js"></script>
<script>
    app.requires.push('ngBrowser');
    app.controller('confirm', function ($scope, $http, $filter, $q, $mdDialog, $mdPanel, $mdSidenav, $mdToast){
        $scope.sheetLoaded = false;
        $scope.currentPage = 1;
        $scope.lastPage = 0;
        $scope.pages = [];
        $scope.selectStatus = [
            {'title': '未審核', 'color':'blue-grey-200'},
            {'title': '合格', 'color':'cyan-900'},
            {'title': '不合格', 'color':'red-200'}
        ];

        $scope.auditStatus = [
            {'title': '未審核', 'color':'blue-grey-200', 'show':true},
            {'title': '通過', 'color':'cyan-900', 'show':false},
            {'title': '不通過', 'color':'red-200', 'show':true},
            {'title': '取消', 'color':'grey-700', 'show':true}
        ];

        $scope.$watch('lastPage', function(lastPage) {
            $scope.pages = [];
            for (var i = 1; i <= lastPage; i++) {
                $scope.pages.push(i);
            };
        });

        $scope.next = function() {
            if ($scope.currentPage < $scope.lastPage) {
                $scope.currentPage++;
                $scope.getUsers($scope.currentPage);
            }
        };

        $scope.prev = function() {
            if ($scope.currentPage > 1) {
                $scope.currentPage--;
                $scope.getUsers($scope.currentPage);
            }
        };

        $scope.sendMsg = function(ev, application) {
            $mdDialog.show({
                controller: function(scope){
                    scope.application = application;
                    scope.messages = [];

                    scope.close = function() {
                        $mdDialog.hide();
                    }
                    scope.getMessages = function() {
                        scope.load = false;
                        $http({method:'post', url:'getMessages', data:{id:scope.application.id}})
                        .success(function(data, status, header, config){
                            scope.messages = data.messages;
                            scope.load = true;
                        })
                        .error(function(e){
                            console.log(e);
                        })
                    }
                    scope.getMessages();
                },
                template: `<md-dialog arial-label="Message" style="width:600px;">
                    <md-toolbar>
                        <div class="md-toolbar-tools">
                            {{application.member.user.username}}&nbsp;的訊息內容
                            <span flex></span>
                            <md-button class="md-icon-button" ng-click="close()"><md-icon>clear</md-icon></md-button>
                        </div>
                    </md-toolbar>
                    <md-dialog-content>
                        <md-progress-linear md-mode="indeterminate" ng-disabled="load"></md-progress-linear>
                        <user-message messages="messages" application="application" load="load"></user-message>
                    </md-dialog-content>
                </md-dialog>
                `,
                parent: angular.element(document.body),
                targetEvent: ev,
                clickOutsideToClose: true
            })
        }

        $scope.getApplications = function() {
            $scope.sheetLoaded = false;
            $http({method: 'POST', url: 'getApplications', data:{}})
            .success(function(data, status, headers, config) {
                $scope.hook = data.hook
                $scope.applications = $filter('filter')(data.applications, {step:3});
                $scope.start_at = data.start_at;
                $scope.close_at = data.close_at;
                $scope.sheetLoaded = true;
                $scope.getApplicationPages();
                $scope.getApplicationStatus($scope.applications);
            })
            .error(function(e){
                console.log(e);
            });
        };

        $scope.setApplicationStatus = function(application) {
            $http({method: 'POST', url: 'setApplicationStatus', data:{id: application.id, status: application.status}})
            .success(function(data, status, headers, config) {

            })
            .error(function(e){
                console.log(e);
            });
        }

        $scope.getApplications();

        $scope.getApplicationStatus = function(applications){
            angular.forEach(applications, function(value, key){
                value.status_date = $filter('date')(Date.parse(value.updated_at), 'MM/dd/yyyy');
                if(value.status == 3){
                    value.status_title = '已取消';
                    value.status_color = 'grey';
                }else{
                    value.status_title = '已送出';
                    value.status_color = 'teal-700';
                }
            })
        }

        $scope.statusFilter = function(filter_audit){
            return function(application){
                if(filter_audit == 'all'){
                    return application;
                }else if(filter_audit == 'only_send'){
                    return application.step == 3 && application.status != 3
                }else{
                    return application.status == filter_audit;
                }
            }
        }

        $scope.getApplicationPages = function() {
            $http({method: 'POST', url: 'getApplicationPages', data:{}})
            .success(function(data, status, headers, config) {
                $scope.currentPage = data.current_page;
                $scope.lastPage = data.last_page;
            })
            .error(function(e){
                console.log(e);
            });
        };

        $scope.openApplication = function(application) {
            var confirmCtl = $scope;
            $mdDialog.show({
                controller: function(scope){
                    scope.individual_status = application.individual_status;
                    scope.member = application.member;
                    scope.selectStatus = $scope.selectStatus;
                    scope.hook = confirmCtl.hook;

                    scope.getAppliedOptions = function() {
                        scope.loading = true;
                        $http({method: 'POST', url: 'getAppliedOptions', data:{id: application.id}})
                        .success(function(data, status, headers, config) {
                            scope.mainListFields = data.mainListFields;
                            scope.mainBookPages = $filter('filter')(data.mainBookPages, function(page){
                                page.fields = $filter('filter')(page.fields, {selected:true});
                                return page.fields.length > 0;
                            });

                            scope.loading = false;
                        })
                        .error(function(e){
                            console.log(e);
                        });
                    }
                    scope.getAppliedOptions();

                    scope.updateIndividualStatus = function(){
                        scope.data = {id: application.id, data: application.individual_status}
                        application.status = 0;
                        $http({method: 'POST', url: 'setApplicationStatus', data:{id: application.id, status: application.status}})
                        .success(function(data, status, headers, config) {

                        })
                        .error(function(e){
                            console.log(e);
                        });

                        $http({method: 'POST', url: 'updateIndividualStatus', data: scope.data})
                        .success(function(data, status, headers, config) {

                        })
                        .error(function(e){
                            console.log(e);
                        });
                    }

                },
                templateUrl: 'userApplication',
                parent: angular.element(document.body),
                clickOutsideToClose: true
            })
        };

        $scope.openBrowser = function(application) {
            openDialog();

            function openDialog() {
                $mdDialog.show({
                    parent: angular.element(document.body),
                    controller: ['$scope', dialogController],
                    template: `
                        <md-dialog aria-label="檢視加掛問卷" class="demo-dialog-example">
                            <md-toolbar>
                                <div class="md-toolbar-tools">
                                    <h2>檢視加掛問卷</h2>
                                </div>
                            </md-toolbar>
                            <md-dialog-content ng-cloak class="demo-dialog-content">
                                <div layout="column" style="font-size:1em; color:grey; margin:15px;" layout-align="center start">
                                    <div layout="row">
                                        <md-icon>adjust</md-icon>
                                        <div>加掛學校: <span ng-repeat-start="organization in member.organizations">{{ organization.now.name }}</span><span ng-repeat-end ng-if="!$last">、</span></div>
                                    </div>
                                    <div layout="row" style="margin-top:8px;">
                                        <md-icon>account_circle</md-icon>
                                        <div>承辦人: {{member.user.username}} </div>
                                        <div>&emsp;Email: {{member.user.email}} </div>
                                        <div>&emsp;電話: {{member.contact.tel}}</div>
                                    </div>
                                </div>
                                <div layout="column" layout-align="start center">
                                    <node-browser ng-if="book" re-open="reOpen()" book="book"></node-browser>
                                </div>
                            </md-dialog-content>
                            <md-dialog-actions style="color:grey" layout="row">
                                <span flex="5"></span>
                                <md-input-container class="md-block" style="width:150px;">
                                    <label>加掛卷審核</label>
                                    <md-select ng-model="individual_status.book" ng-change="updateIndividualStatus()">
                                        <md-option ng-repeat="(key,status) in selectStatus" ng-value="key">{{status.title}}</md-option>
                                    </md-select>
                                </md-input-container>
                            </md-dialog-actions>
                        </md-dialog>
                    `,
                    clickOutsideToClose: true,
                });
            }

            function dialogController(scope) {
                scope.book = application.book_id;
                scope.member = application.member;
                scope.individual_status = application.individual_status;
                scope.selectStatus = $scope.selectStatus;

                scope.updateIndividualStatus = function(){
                    scope.data = {id: application.id, data: application.individual_status}
                    application.status = 0;
                    $http({method: 'POST', url: 'setApplicationStatus', data:{id: application.id, status: application.status}})
                    .success(function(data, status, headers, config) {

                    })
                    .error(function(e){
                        console.log(e);
                    })
                    $http({method: 'POST', url: 'updateIndividualStatus', data: scope.data})
                    .success(function(data, status, headers, config) {

                    })
                    .error(function(e){
                        console.log(e);
                    });
                }
            }

            function reOpen() {
                openDialog();
            }
        };

    $scope.LoginConditions = function(application){
        $mdDialog.show({
            parent: angular.element(document.body),
            controller: function(scope, $mdDialog){

                $http({method:'post', url:'getApplicationHangingRule', data:{id:application.id}})
                .success(function(data, status, headers, config){
                    scope.rule = data.rule;
                    scope.fields = data.fields;
                })
                .error(function(e){
                    console.log(e);
                })
                scope.compareOperators = [
                    {key: ' && ', title: '而且'},
                    {key: ' || ', title: '或者'}
                ];

                scope.close = function(){
                    $mdDialog.hide();
                }

                scope.saveRule = function(){
                    $http({method:'post', url:'setApplicationHangingRule', data:{id:application.id, rule:scope.rule.expressions}})
                    .success(function(data, status, headers, config){
                        console.log(data);
                    })
                    .error(function(e){
                        console.log(e);
                    })
                }

                scope.deleteRule = function(){
                    scope.rule.expressions = [{"conditions":[{'compareType':'question'}]}];
                    $http({method:'post', url:'setApplicationHangingRule', data:{id:application.id, rule: scope.rule.expressions}})
                    .success(function(data, status, headers, config){
                    })
                    .error(function(e){
                        console.log(e);
                    })
                }

                scope.removeCondition = function(index, expression){
                    expression.conditions.splice(index,1);
                    if(index==0 && expression.conditions[0]){
                        delete expression.conditions[0].compareOperator;
                    }
                }

                scope.createCondition = function(index, expression){
                    expression.conditions.splice(index+1, 0, {'compareType':'question'});
                }

                scope.createExpression = function(index, compareOperator){
                    scope.rule.expressions.splice(index+1, 0, {"compareLogic":compareOperator, "conditions":[{'compareType':'question'}]});
                }

                scope.removeExpression = function(index){
                    scope.rule.expressions.splice(index,1);
                    if(index == 0 && scope.rule.expressions[0]){
                        delete scope.rule.expressions[0].compareLogic;
                    }
                }
            },

            template: `
                <md-dialog aria-label="進入加掛題本判斷條件" class="demo-dialog-example">
                    <md-toolbar md-scroll-shrink>
                        <div class="md-toolbar-tools">
                            <h2>進入加掛題本判斷條件</h2>
                            <div flex></div>
                            <md-button aria-label="關閉" ng-click="close()">關閉</md-button>
                            <md-button aria-label="儲存設定" md-colors="{background: 'default-blue'}" style="float:right" ng-click="saveRule();">
                                儲存設定
                            </md-button>
                            <md-button aria-label="刪除設定" md-colors="{background: 'default-blue'}" style="float:right" ng-click="deleteRule()">
                                刪除設定
                            </md-button>
                        </div>
                    </md-toolbar>
                    <md-dialog-content style="height:800px;">
                        <md-card style="margin:10px;" ng-repeat="expression in rule.expressions">
                            <md-card-header md-colors="{background: 'default-indigo'}">
                                <div flex layout="row" layout-align="start center">
                                    <div ng-if="!expression.compareLogic" style="margin: 0 0 0 0px">
                                        請選擇名單，作為加掛的判斷條件
                                    </div>
                                    <div ng-if="expression.compareLogic">{{ (compareOperators | filter:{key:expression.compareLogic})[0].title }} </div>
                                    <div  style="margin: 0 0 0 0px">
                                    </div>
                                    <span flex></span>
                                    <div>
                                        <md-button class="md-icon-button" aria-label="刪除" ng-click="removeExpression($index)">
                                            <md-icon md-colors="{color: 'default-grey-A100'}" md-svg-icon="delete"></md-icon>
                                        </md-button>
                                    </div>
                                </div>
                            </md-card-header>
                            <md-card-content>
                                <login-condition ng-repeat="(key,condition) in expression.conditions" condition="condition" first="$first" fields="fields" remove-condition="removeCondition(key, expression)" create-condition="createCondition(key,expression)"></login-condition>
                            </md-card-content>
                            <md-card-actions>
                                <md-fab-toolbar md-direction="right">
                                    <md-fab-trigger class="align-with-text">
                                        <md-button aria-label="menu" class="md-fab md-primary">
                                            <md-icon md-svg-icon="list"></md-icon>
                                        </md-button>
                                    </md-fab-trigger>
                                    <md-toolbar>
                                        <md-fab-actions class="md-toolbar-tools" >
                                            <md-button aria-label="邏輯" ng-repeat="compareOperator in compareOperators" ng-click="createExpression($index, compareOperator.key)">
                                                {{compareOperator.title}}
                                            </md-button>
                                        </md-fab-actions>
                                    </md-toolbar>
                                </md-fab-toolbar>
                            </md-card-actions>
                        </md-card>
                    </md-dialog-content>
                </md-dialog>
            `,
            clickOutsideToClose: true,
            fullscreen: $scope.customFullscreen
        });
    }
});

app.directive("loginCondition", function(){
    return {
        restrict: 'E',
        scope: {
            condition:'=',
            first:'=',
            fields:'=',
            removeCondition:'&removeCondition',
            createCondition:'&'
        },
        controller: function($scope){
            $scope.compareBooleans = [
                {key: ' > ', title: '大於'},
                {key: ' < ', title: '小於'},
                {key: ' == ', title: '等於'},
                {key: ' != ', title: '不等於'}
            ];

            $scope.compareOperators = [
                {key: ' && ', title: '而且'},
                {key: ' || ', title: '或者'}
            ];
        },

        template:`
            <div layout="row">
                <div layout="row">
                    <md-input-container ng-if="!first">
                        <label>+</label>
                        <md-select ng-model="condition.compareOperator">
                            <md-option ng-repeat="compareOperator in compareOperators" ng-value="compareOperator.key">{{compareOperator.title}}</md-option>
                        </md-select>
                    </md-input-container>
                    <md-input-container>
                        <label>選擇名單</label>
                        <md-select ng-model="condition.question">
                            <md-option ng-repeat="field in fields" ng-value="field.id">{{field.title}}</md-option>
                        </md-select>
                    </md-input-container>
                    <md-input-container>
                        <label>比較邏輯</label>
                        <md-select ng-model="condition.logic">
                            <md-option ng-repeat="compareBoolean in compareBooleans" ng-value="compareBoolean.key">{{compareBoolean.title}}</md-option>
                        </md-select>
                    </md-input-container>
                    <md-input-container>
                        <label>數值</label>
                        <input ng-model="condition.value">
                    </md-input-container>
                    <md-input-container>
                        <md-button aria-label="刪除" class="md-icon-button" ng-click="removeCondition()">
                            <md-icon md-svg-icon="delete"></md-icon>
                        </md-button>
                        <md-button aria-label="新增" class="md-icon-button" ng-click="createCondition()">
                            <md-icon md-svg-icon="add-circle-outline"></md-icon>
                        </md-button>
                    <md-input-container>
                </div>
            </div>
        `
    }
})
.directive('userMessage',function(){
    return {
        restrict: 'E',
        scope: {
            messages:'=',
            application:'=',
            load:'='
        },
        template: `
        <md-content style="height:500px;">
            <div layout="row">
                <span flex></span>
                <md-button class="md-primary" ng-click="addMsg()" ng-disabled="addMsgStatus"><md-icon>add_circle</md-icon>新增訊息</button>
            </div>
            <md-card ng-repeat="message in messages | orderBy:'updated_at':true">
                <md-card-content>
                    <div class="ui form">
                        <div class="field">
                            <label>主旨</label>
                            <input type="text" placeholder="主旨" ng-model="message.title">
                        </div>
                        <div class="field">
                            <label>內容</label>
                            <textarea type="text" placeholder="內容" ng-model="message.content" rows="3"></textarea>
                        </div>
                        <div class="field">
                            <span>更新時間:{{message.updated_at}}</span>
                        </div>
                    </div>
                </md-card-content>
                <md-card-actions>
                    <div layout="row">
                        <span flex></span>
                        <md-button class="md-primary md-raised" ng-click="saveMessage(message)" ng-if="!message.id">儲存</md-button>
                        <md-button class="md-primary md-raised" ng-click="updateMessage(message)" ng-if="message.id">更新</md-button>
                        <md-button class="md-primary md-raised" ng-click="deleteMessage(message, $index)" ng-if="message.id">刪除</md-button>
                    </div>
                </md-card-actions>
            </md-card>
            <div class="ui info message" style="margin:15px;" ng-if="messages.length==0 && load">尚未新增訊息</div>
        <md-content>
        `,
        controller: function($scope, $http){
            $scope.saveMessage = function(message) {
                $http({method:'post', url:'saveMessage', data:{id: $scope.application.id, title: message.title, content: message.content}})
                .success(function(data, status, header, config){
                    angular.extend(message, data.message);
                    $scope.addMsgStatus = false;
                })
                .error(function(e){
                    console.log(e);
                })
            }

            $scope.addMsg = function(){
                $scope.messages.unshift({title:'', content:''});
                $scope.addMsgStatus = true;
            }

            $scope.updateMessage = function(message) {
                $http({method:'post', url:'updateMessage', data:{message_id: message.id, title: message.title, content: message.content}})
                .success(function(data, status, header, config){
                    angular.extend(message, data.message);
                })
                .error(function(e){
                    console.log(e);
                })
            }

            $scope.deleteMessage = function(message, index) {
                $http({method:'post', url:'deleteMessage', data:{message_id: message.id}})
                .success(function(data, status, header, config){
                    $scope.messages.splice(index, 1);
                })
                .error(function(e){
                    console.log(e);
                })
            }
        }
    }
})
.filter('auditFilter', function(){
    return function(input, application){
        var output = [];
        if(application.individual_status.apply == 1 && application.individual_status.book == 1){
            angular.extend(output, input);
        } else {
            angular.forEach(input, function(value){
                if(value.show == true){
                    output.push(value);
                }
            })
        }
        return output;
    }
})
</script>

<style>
.demo-dialog-example {
    background: white;
    border-radius: 4px;
    box-shadow: 0 7px 8px -4px rgba(0, 0, 0, 0.2),
      0 13px 19px 2px rgba(0, 0, 0, 0.14),
      0 5px 24px 4px rgba(0, 0, 0, 0.12);
    width: 800px;
}
.demo-dialog-content {
    height: 600px;
    overflow: scroll;
}
.auditStatus md-select-menu{
    max-height:100%;
    margin-top:30px;
}
.auditStatus md-content{
    max-height:100%;
}
</style>
