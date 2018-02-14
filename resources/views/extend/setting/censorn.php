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
            <div  layout="row">
                <md-input-container style="width:150px;">
                    <md-icon md-svg-src="find-in-page" md-container-class="myclass"></md-icon>
                    <label>審核狀態</label>
                    <md-select ng-model="audit_status" placeholder="審核狀態" md-container-class="auditStatus">
                        <md-option value="all" selected>查看全部</md-option>
                        <md-option value="only_send">已送出</md-option>
                        <md-option ng-repeat="(key,status) in auditStatus" ng-value="key" >{{status.title}}</md-option>
                    </md-select>
                </md-input-container>
            </div>
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
                    <th width="140">申請者狀態</th>
                    <th width="100">檢視申請表</th>
                    <th width="120">檢視加掛問卷</th>
                    <th width="120">審核結果</th>
                    <th width="180">職稱</th>
                    <th width="180">電話、傳真</th>
                </tr>
            </thead>
            <thead>
                <tr>
                    <th></th>
                    <th>
                        <md-autocomplete
                            md-selected-item="search.organization"
                            md-selected-item-change="getUsers(1)"
                            md-search-text="searchText"
                            md-items="item in queryOrganizations(searchText)"
                            md-item-text="item.now.name"
                            md-min-length="2"
                            md-delay="500"
                            placeholder="搜尋學校名稱">
                            <md-item-template>
                                <span md-highlight-text="searchText" md-highlight-flags="^i">{{item.now.name}}</span>
                            </md-item-template>
                            <md-not-found>沒有找到與 "{{searchText}}" 相關的機構</md-not-found>
                        </md-autocomplete>
                    </th>
                    <th>
                        <md-autocomplete
                            md-selected-item="search.username"
                            md-selected-item-change="getUsers(1)"
                            md-search-text="searchTextUsername"
                            md-items="item in queryUsernames(searchTextUsername)"
                            md-item-text="item"
                            md-min-length="1"
                            md-delay="500"
                            placeholder="搜尋姓名">
                            <md-item-template>
                                <span md-highlight-text="searchTextUsername" md-highlight-flags="^i">{{item}}</span>
                            </md-item-template>
                            <md-not-found>沒有找到與 "{{searchTextUsername}}" 相關的姓名</md-not-found>
                        </md-autocomplete>
                    </th>
                    <th>
                        <md-autocomplete
                            md-selected-item="search.email"
                            md-selected-item-change="getUsers(1)"
                            md-search-text="searchTextEmail"
                            md-items="item in queryEmails(searchTextEmail)"
                            md-item-text="item"
                            md-min-length="3"
                            md-delay="500"
                            placeholder="搜尋電子郵件信箱">
                            <md-item-template>
                                <span md-highlight-text="searchTextEmail" md-highlight-flags="^i">{{item}}</span>
                            </md-item-template>
                            <md-not-found>沒有找到與 "{{searchTextEmail}}" 相關的電子郵件信箱</md-not-found>
                        </md-autocomplete>
                    </th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr ng-show="applications.length==0">
                    <td colspan="11"><h4 class="md-headline" md-colors="{color:'grey-500'}">目前尚未有學校送出審核</md-display-1></h4>
                </tr>
                <tr ng-repeat="application in applications | filter:statusFilter(audit_status) |filter: search.organization.now.name |filter: search.username">
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
                    <td md-colors="{color:getApplicationStatus(application).color}">
                        {{getApplicationStatus(application).date}}<br>{{getApplicationStatus(application).title}}
                    </td>
                    <td class="center aligned">
                        <md-button aria-label="檢視申請表" class="md-primary" ng-click="openApplication(application)" md-colors="{background:selectStatus[application.individual_status.apply].color}" >
                            <div md-colors="{color:'grey-A100'}">{{selectStatus[application.individual_status.apply].title}}</div>
                        </md-button>
                    </td>
                    <td>
                        <div layout="row">
                            <md-button aria-label="加掛問卷" class="md-primary" ng-click="openBrowser(application)" md-colors="{background:selectStatus[application.individual_status.book].color}">
                                <div md-colors="{color:'grey-A100'}">{{selectStatus[application.individual_status.book].title}}</div>
                            </md-button>
                            <md-icon md-svg-src="{{application.book.lock ? 'lock' : 'unlock'}}" ng-style="{color: application.book.lock ? 'green' : 'red'}" aria-label="lockStatus ">
                        </div>

                    </td>
                    <td>
                        <div layout="row"  flex="noshrink">
                            <md-input-container class="md-block">
                                <label>審核結果</label>
                                <md-select ng-model="application.status" md-colors="{color: auditStatus[application.status].color}" ng-disabled="application.individual_status.book==1 && application.individual_status.apply==1 ? false: true ">
                                    <md-option ng-repeat="(key,status) in auditStatus" ng-value="key">{{status.title}}</md-option>
                                </md-select>
                            </md-input-container>
                        </div>
                    </td>
                    <td>{{ application.member.contact.title }}</td>
                    <td>
                        <div><i class="text telephone icon"></i>{{ application.member.contact.tel }}</div>
                        <div><i class="fax icon"></i>{{ application.member.contact.fax }}</div>
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
            {'title': '未審核', 'color':'grey-400'},
            {'title': '合格', 'color':'teal-900'},
            {'title': '不合格', 'color':'red-400'}
        ];

        $scope.auditStatus = [
            {'title': '未審核', 'color':'grey-400'},
            {'title': '通過', 'color':'teal-900'},
            {'title': '不通過', 'color':'red-400'},
            {'title': '取消', 'color':'grey-800'}
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
        $scope.getApplications = function() {
            $scope.sheetLoaded = false;
            $http({method: 'POST', url: 'getApplications', data:{}})
            .success(function(data, status, headers, config) {
               $scope.applications = $filter('filter')(data.applications, {step:3});
               $scope.sheetLoaded = true;
               $scope.getApplicationPages();
            })
            .error(function(e){
                console.log(e);
            });
        };

        $scope.getApplications();

        $scope.getApplicationStatus = function(application){
            var status = {};
            status.date = $filter('date')(Date.parse(application.updated_at), 'MM/dd/yyyy');
            if(application.step == 3){
                if(application.status == 3){
                    status.title = '已取消';
                    status.color = 'grey';
                }else {
                    status.title = '已送出';
                    status.color = 'teal-700';
                }
            }
            return status;
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
        $scope.lockStatus = function(application){
            return application.book.lock ? 'lock' : 'unlock';
        }

        $scope.queryOrganizations = function(query) {
            if (!query) {
                return [];
            }

            deferred = $q.defer();
            $http({method: 'POST', url: 'queryOrganizations', data:{query: query}})
            .success(function(data, status, headers, config) {
                deferred.resolve(data.organizations);
            })
            .error(function(e) {
                console.log(e);
            });

            return deferred.promise;
        };

        $scope.queryUsernames = function(query) {
            if (!query) {
                return [];
            }

            deferred = $q.defer();
            $http({method: 'POST', url: 'queryUsernames', data:{query: query}})
            .success(function(data, status, headers, config) {
                deferred.resolve(data.usernames);
            })
            .error(function(e) {
                console.log(e);
            });

            return deferred.promise;
        };

        $scope.queryEmails = function(query) {
            if (!query) {
                return [];
            }

            deferred = $q.defer();
            $http({method: 'POST', url: 'queryEmails', data:{query: query}})
            .success(function(data, status, headers, config) {
                deferred.resolve(data.emails);
            })
            .error(function(e) {
                console.log(e);
            });

            return deferred.promise;
        };

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
            $mdDialog.show({
                controller: function(scope){
                    scope.individual_status = application.individual_status;
                    scope.member = application.member;
                    scope.selectStatus = $scope.selectStatus;

                    scope.getAppliedOptions = function() {
                        $http({method: 'POST', url: 'getAppliedOptions', data:{id: application.id}})
                        .success(function(data, status, headers, config) {
                            scope.columns = data.fields.mainList;
                            scope.pages = $filter('filter')(data.fields.mainBookPages, function(page){return page.length>0});
                        })
                        .error(function(e){
                            console.log(e);
                        });
                    }
                    scope.getAppliedOptions();

                    scope.updateIndividualStatus = function(){
                        scope.data = {id: application.id, data: application.individual_status}

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
                                <div layout="row" style="font-size:1em; margin-right:10px; color:grey" layout-align="center center">
                                    <div ng-repeat="organization in member.organizations" layout="row">加掛學校: {{ organization.now.name }} </div>
                                    <div>&emsp;承辦人: {{member.user.username}} </div>
                                    <div>&emsp;Email: {{member.user.email}} </div>
                                    <div>&emsp;電話: {{member.contact.tel}}</div>
                                </div>
                                <div layout="column" layout-align="start center">
                                    <node-browser ng-if="book" re-open="reOpen()" book="book"></node-browser>
                                </div>
                            </md-dialog-content>
                            <md-dialog-actions style="color:grey" layout="row">
                                <md-button aria-label="加掛卷意見" class="md-primary"><md-icon md-svg-icon="assignment"></md-icon><span>加掛卷意見</span></md-button>
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

    });
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