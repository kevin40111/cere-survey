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
        <md-button ng-if="userSelected.length > 0" ng-click="sendInvite()" class="md-raised md-primary">送出邀請</md-button>
        <table class="ui very compact table">
            <thead>
                <tr class="bottom aligned">
                    <th>
                        <md-button ng-click="toggleAll()" class="md-raised md-primary">全選</md-button>
                    </th>
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
                        <md-checkbox ng-checked="isChecked(user)" ng-click="userSelect(user)">
                        {{ item }}
                        </md-checkbox>
                    </td>
                    <td>{{checkRequest(user)}}</td>
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
    $scope.userSelected = [];
    $scope.hasRequest = [];

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
                $scope.hasRequest = data.hasRequest;
            }).error(function(e) {
        });
    }

    $scope.toggleAll = function() {
        angular.forEach($scope.users, function(user) {
            if($scope.userSelected.indexOf(user.id) == -1) {
                $scope.userSelected.push(user.id);
            }
        });
    }

    $scope.isChecked = function(user) {
        return $scope.userSelected.indexOf(user.id) > -1;
    }

    $scope.userSelect = function(user) {
        if ($scope.userSelected.indexOf(user.id) == -1) {
            $scope.userSelected.push(user.id);
        } else {
            $scope.userSelected.splice($scope.userSelected.indexOf(user.id), 1);
        }
        console.log($scope.userSelected);
    }

    $scope.sendInvite = function() {
        $http({method: 'POST', url: 'invite', data:{'users' :  $scope.userSelected}})
            .success(function(data) {
                $mdToast.show(
                  $mdToast.simple()
                    .textContent('加掛邀請成功!')
                    .hideDelay(1000)
                );
                angular.forEach(data.requested, function(user_id) {
                    if($scope.hasRequest.indexOf(user_id) == -1) {
                        $scope.hasRequest.push(user_id);
                    }
                });
            }).error(function(e) {
        });
    }

    $scope.checkRequest = function(user) {
        if($scope.hasRequest.indexOf(user.id) > -1) {
            return "已邀請";
        } else {
            return "尚未邀請";
        }
    }
});
</script>
