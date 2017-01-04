
<md-content ng-cloak layout="column" ng-controller="confirm" layout-align="start center">
        <div class="ui basic segment" ng-cloak style="overflow: auto">

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
                        <md-checkbox ng-model="application.actived" ng-disabled="application.saving" aria-label="加掛審核" ng-change="activeExtension(application)"></md-checkbox>
                    </td>
                    <td class="center aligned">
                        <md-button ng-click="" aria-label="檢視申請表"><md-icon md-svg-icon="assignment"></md-icon></md-button>
                    </td>
                    <td>
                        <md-button aria-label="加掛問卷" class="md-icon-button" ng-click="">
                            <md-icon md-menu-origin md-svg-icon="description"></md-icon>
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
</md-content>
<script src="/js/ng/ngSurvey.js"></script>
<script>
    app.controller('confirm', function ($scope, $http, $filter, $q){
        $scope.sheetLoaded = false;
        $scope.currentPage = 1;
        $scope.lastPage = 0;
        $scope.pages = [];

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
                   ($scope.applications[i].extension == '1') ? true : false;
               }
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
                application.actived = (data.extension == '1') ? true : false ;
                application.saving = false;
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
    });
</script>