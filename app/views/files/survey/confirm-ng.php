<md-content ng-controller="confirm" layout-align="start center" style="height: 100%">
    <div class="ui basic segment" style="x-overflow: auto">
        <md-input-container>
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
                    <th width="100">加掛審核</th>
                    <th width="100">退件</th>
                    <th width="100">檢視申請表</th>
                    <th width="100">加掛問卷</th>
                    <th>職稱</th>
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
                <tr ng-repeat="application in applications">
                    <td>{{ $index+1 }}</td>
                    <td>
                        <div style="max-height:150px;overflow-y:scroll">
                            <div ng-repeat="organization in application.members.organizations">{{ organization.now.name }}({{ organization.now.id }})</div>
                        </div>
                    </td>
                    <td>{{ application.members.user.username }}</td>
                    <td>
                        {{ application.members.user.email }}
                        <div ng-if="application.members.user.email2">{{ application.members.user.email2 }}</div>
                    </td>
                    <td class="center aligned">
                        <md-checkbox ng-model="application.extension" ng-disabled="application.saving || application.reject" aria-label="加掛審核" ng-change="activeExtension(application)" ng-click="sendMail(application.members.user.email)"></md-checkbox>
                    </td>
                    <td class="center aligned">
                        <md-checkbox ng-model="application.reject" ng-disabled="application.saving || application.extension" aria-label="退件" ng-change="reject(application)" ng-click="sendMail(application.members.user.email)"></md-checkbox>
                    </td>
                    <td class="center aligned">
                        <md-button ng-click="openApplication(application.members.id)" aria-label="檢視申請表"><md-icon md-svg-icon="assignment"></md-icon></md-button>
                    </td>
                    <td>
                        <md-button aria-label="加掛問卷" class="md-icon-button" ng-click="openBrowser(application.ext_book_id)">
                            <md-icon md-menu-origin md-svg-icon="description" ng-style="{color: application.ext_book_locked }"></md-icon>
                        </md-button>
                    </td>
                    <td>{{ application.members.contact.title }}</td>
                    <td>
                        <div><i class="text telephone icon"></i>{{ application.members.contact.tel }}</div>
                        <div><i class="fax icon"></i>{{ application.members.contact.fax }}</div>
                    </td>

                </tr>
            <tbody>
        </table>
        <md-progress-linear md-mode="indeterminate" ng-disabled="sheetLoaded"></md-progress-linear>
    </div>
     <md-sidenav class="md-sidenav-right" md-component-id="" md-is-open="emailSender" style="min-width:40%">
        <md-toolbar class="md-theme-light">
        <h1 class="md-toolbar-tools">通知信寄送</h1>
      </md-toolbar>
      <md-content layout-padding flex="100%">
        <form class="ui form">
            <div class="field">
                <label>寄送對象</label>
                <input type="text" ng-model="email" size="100" />
            </div>
            <div class="field">
                <label>標題</label>
                <input type="text" ng-model="title" size="100" />
            </div>
            <div class="field">
                <label>內文</label>
                <textarea ng-model="context" cols="100" rows="15"></textarea>
            </div>
            <md-button ng-click="sendStart()">送出</md-button>
        </form>
    </md-content>
    </md-sidenav>
    </md-content>
<script src="/js/ng/ngBrowser.js"></script>
<script>
    app.requires.push('ngBrowser');
    app.controller('confirm', function ($scope, $http, $filter, $q, $mdDialog, $mdPanel, $mdSidenav, $mdToast){
        $scope.sheetLoaded = false;
        $scope.currentPage = 1;
        $scope.lastPage = 0;
        $scope.pages = [];
        $scope.emailSender = false;

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
               $scope.applications = data.applications;
               for (var i in $scope.applications) {
                   $scope.checkExtBookLocked($scope.applications[i]);
               };
               $scope.sheetLoaded = true;
               $scope.getApplicationPages();
            })
            .error(function(e){
                console.log(e);
            });
        };

        $scope.getApplications();

        $scope.activeExtension = function(application) {
            application.saving = true;
            $http({method: 'POST', url: 'activeExtension', data:{application_id: application.id}})
            .success(function(data, status, headers, config) {
                application.saving = false;
                angular.extend(application, data.application);
                $scope.checkExtBookLocked(application);
            })
            .error(function(e) {
                console.log(e);
            });
        };

        $scope.reject = function(application) {
            application.saving = true;
            $http({method: 'POST', url: 'reject', data:{application_id: application.id}})
            .success(function(data, status, headers, config) {
                application.saving = false;
                angular.extend(application, data.application);
                $scope.checkExtBookLocked(application);
            })
            .error(function(e) {
                console.log(e);
            });
        };

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
                $scope.currentPage = data.currentPage;
                $scope.lastPage = data.lastPage;
            })
            .error(function(e){
                console.log(e);
            });
        };

        $scope.openApplication = function(member_id) {
            $mdDialog.show({
                controller: function($scope){
                    $scope.columns = [];
                    $scope.questions = [];
                    $scope.edited = [];
                    $scope.getAppliedOptions = function() {
                        $http({method: 'POST', url: 'getAppliedOptions', data:{member_id: member_id}})
                        .success(function(data, status, headers, config) {
                            $scope.setVar(data.columns, data.questions, data.edited);
                        })
                        .error(function(e){
                            console.log(e);
                        });
                    }

                    function getSeleted() {
                        var columns = $filter('filter')($scope.columns, {selected: true}).map(function(column) {
                            return column.id;
                        });
                        var questions = $filter('filter')($scope.questions, {selected: true}).map(function(question) {
                            return question.id;
                        });
                        return columns.concat(questions);
                    }

                    $scope.setAppliedOptions = function() {
                        var selected = getSeleted();
                        $http({method: 'POST', url: 'setAppliedOptions', data:{selected: selected, book_id: $scope.columns[0].book_id}})
                        .success(function(data, status, headers, config) {
                            $scope.setVar(data.columns, data.questions, data.edited);
                        })
                        .error(function(e){
                            console.log(e);
                        });
                    }

                    $scope.resetApplication = function() {
                        $http({method: 'POST', url: 'resetApplication', data:{}})
                        .success(function(data, status, headers, config) {
                            $scope.setVar(data.columns, data.questions, data.edited);
                        })
                        .error(function(e){
                            console.log(e);
                        });
                    }

                    $scope.setVar = function(columns, questions, edited) {
                        $scope.columns = columns;
                        $scope.questions = questions;
                        $scope.edited = edited;
                    }

                    $scope.getAppliedOptions();
                },
                templateUrl: 'userApplication',
                parent: angular.element(document.body),
                clickOutsideToClose: true
            })
        };

        $scope.openBrowser = function(book) {

            openDialog();

            function openDialog() {
                $mdPanel.open({
                    attachTo: angular.element(document.body),
                    controller: ['$scope', dialogController],
                    controllerAs: 'ctrl',
                    template: `
                        <md-content ng-cloak layout="column" layout-align="start center" class="demo-dialog-content">
                            <node-browser ng-if="book" re-open="reOpen()" book="book"></node-browser>
                        </md-content>
                    `,
                    panelClass: 'demo-dialog-example',
                    position: $mdPanel.newPanelPosition().absolute().center(),
                    trapFocus: true,
                    zIndex: 150,
                    clickOutsideToClose: true,
                    clickEscapeToClose: true,
                    hasBackdrop: true,
                });
            }

            function dialogController(scope) {
                   scope.book = book;
            }

            function reOpen() {
                openDialog();
            }
        };

        $scope.checkExtBookLocked = function(application) {
             $http({method: 'POST', url: 'checkExtBookLocked', data:{book_id:application.ext_book_id}})
            .success(function(data, status, headers, config) {
               application.ext_book_locked = data.ext_locked ? 'green' : 'gray';
            })
            .error(function(e){
                console.log(e);
            });
        };

        $scope.sendMail = function(email) {
            $scope.emailSender = !$scope.emailSender;
            $scope.email = email;
        };

        $scope.sendStart = function() {
            $http({method: 'POST', url: 'ajax/sendMail', data:{
                email: $scope.email,
                title: $scope.title,
                context: $scope.context,
            }})
            .success(function(data, status, headers, config) {
                $mdToast.show(
                  $mdToast.simple()
                    .textContent('送出成功!')
                    .hideDelay(1000)
                );
            })
            .error(function(e){
                console.log(e);
            });
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
    width: 500px;
}
</style>