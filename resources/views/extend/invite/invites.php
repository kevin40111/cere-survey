<div ng-controller="inviteCtrl">
    <div class="ui basic segment" ng-cloak style="overflow: auto">
        <md-input-container>
            <label>選擇群組</label>
            <md-select ng-model="group_selected" ng-change="getUsers()">
                <md-option ng-repeat="group in groups" value="{{group.id}}">
                    {{group.description}}
                </md-option>
            </md-select>
        </md-input-container>
        <md-button ng-click="sendInvite()" class="md-raised md-primary">送出邀請</md-button>
        <table class="ui very compact table">
            <thead>
                <tr class="bottom aligned">
                    <th>
                        <md-button ng-click="toggleAll()" class="md-raised md-primary">全選</md-button>
                    </th>
                    <th>同意書狀態</th>
                    <th>邀請狀態</th>
                    <th>編號</th>
                    <th>學校</th>
                    <th>姓名</th>
                    <th>職稱</th>
                    <th>email</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bottom aligned" ng-repeat="user in users">
                    <td>
                        <md-checkbox ng-model="user.selected">
                        {{ item }}
                        </md-checkbox>
                    </td>
                    <td>
                        <span ng-if="user.application.used" style="color:green">已同意</span>
                        <span ng-if="!user.application.used" style="color:red">未同意</span>
                    </td>
                    <td>
                        <span ng-if="user.hasRequested" style="color:green">已邀請</span>
                        <span ng-if="!user.hasRequested" style="color:red">未邀請</span>
                    </td>
                    <td>
                        {{user.id}}
                    </td>
                    <td>
                        <div style="max-height:150px;overflow-y:scroll">
                            <div ng-repeat="organization in user.members[0].organizations">{{ organization.now.name }}({{ organization.now.id }})</div>
                        </div>
                    </td>
                    <td>{{user.username}}</td>
                    <td>{{user.members[0].contact.title}}</td>
                    <td>{{user.email}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
app.controller('inviteCtrl', function($scope, $http, $filter, $mdDialog, $timeout, $q, $mdToast) {
    $scope.getGroups = function() {
        $http({method: 'POST', url: 'getGroups'})
            .success(function(data) {
                $scope.groups = data.groups
            }).error(function(e) {
        });
    }
    $scope.getGroups();

    $scope.getUsers = function() {
        $http({method: 'POST', url: 'getUsers', data:{'group_id' :  $scope.group_selected}})
            .success(function(data) {
                $scope.users = data.users;
            }).error(function(e) {
        });
    }

    $scope.toggleAll = function() {
        angular.forEach($scope.users, function(user) {
            user.selected = true;
        });
    }

    $scope.sendInvite = function() {
        var members = $filter('filter')($scope.users, {selected: true}).map(function(user){
            return user.members[0].id;
        });

        $http({method: 'POST', url: 'invite', data:{'members' :  members}})
            .success(function(data) {
                $mdToast.show(
                  $mdToast.simple()
                    .textContent('加掛邀請成功!')
                    .hideDelay(1000)
                );
                $filter('filter')($scope.users, {selected: true}).forEach(function(user){
                    user.hasRequested =  true;
                });
            }).error(function(e) {
        });
    }
});
</script>
