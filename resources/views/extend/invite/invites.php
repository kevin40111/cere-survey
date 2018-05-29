<div ng-controller="inviteCtrl">
    <div class="ui basic segment" ng-cloak style="overflow: auto">
        <md-input-container>
            <label>選擇群組</label>
            <md-select ng-model="group_selected" ng-change="getMembers()">
                <md-option ng-repeat="group in groups" value="{{group.id}}">
                    {{group.description}}
                </md-option>
            </md-select>
        </md-input-container>
        <md-button ng-click="invite()" class="md-raised md-primary">送出邀請</md-button>
        <table class="ui very compact table">
            <thead>
                <tr class="left aligned">
                    <th style="width: 50px">
                        <md-checkbox aria-label="全選" ng-checked="isChecked()" md-indeterminate="isIndeterminate()" ng-click="toggleAll()">
                    </th>
                    <th style="width: 120px">邀請狀態</th>
                    <th style="width: 120px">同意書狀態</th>
                    <th>編號</th>
                    <th>學校</th>
                    <th>姓名</th>
                    <th>職稱</th>
                    <th>email</th>
                </tr>
            </thead>
            <tbody>
                <tr class="left aligned" ng-repeat="member in members">
                    <td>
                        <md-checkbox ng-model="member.selected" ng-disabled="member.application"></md-checkbox>
                    </td>
                    <td>
                        <span ng-if="member.application" style="color:green">已邀請</span>
                        <span ng-if="!member.application" style="color:red">未邀請</span>
                    </td>
                    <td>
                        <span ng-if="member.application.agree" style="color:green">已同意</span>
                        <span ng-if="!member.application.agree" style="color:red">未同意</span>
                    </td>
                    <td>
                        {{member.user.id}}
                    </td>
                    <td>
                        <div style="max-height:150px;overflow-y:scroll">
                            <div ng-repeat="organization in member.organizations">{{ organization.now.name }}({{ organization.now.id }})</div>
                        </div>
                    </td>
                    <td>{{member.user.username}}</td>
                    <td>{{member.contact.title}}</td>
                    <td>{{member.user.email}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
app.controller('inviteCtrl', function($scope, $http, $filter, $mdDialog, $timeout, $q, $mdToast) {
    $scope.members = [];
    $scope.getGroups = function() {
        $http({method: 'POST', url: 'getGroups'})
        .then(function(response) {
            $scope.groups = response.data.groups
        });
    }
    $scope.getGroups();

    $scope.getMembers = function() {
        $http({method: 'POST', url: 'getMembers', data:{'group_id' :  $scope.group_selected}})
        .then(function(response) {
            $scope.members = response.data.members;
        });
    }

    $scope.invite = function() {
        var members = $filter('filter')($scope.members, {selected: true}).map(function(member){
            return member.id;
        });

        $http({method: 'POST', url: 'invite', data:{'members' :  members}})
        .then(function(response) {
            $mdToast.show(
                $mdToast.simple()
                .textContent('加掛邀請成功!')
                .hideDelay(1000)
            );
            $filter('filter')($scope.users, {selected: true}).forEach(function(user){
                user.hasRequested =  true;
            });
        });
    };

    $scope.isIndeterminate = function() {
        var selected = $filter('filter')($scope.members, {selected: true});
        return selected.length > 0 && selected.length !== $scope.members.length;
    };

    $scope.isChecked = function() {
        var selected = $filter('filter')($scope.members, {selected: true});
        return selected.length === $scope.members.length && $scope.members.length > 0;
    };

    $scope.toggleAll = function() {
        var selected = $filter('filter')($scope.members, {selected: true});
        var checked = selected.length === $scope.members.length;
        angular.forEach($scope.members, function(member) {
            if (! member.application) {
                member.selected = !checked;
            }
        });
    };
});
</script>
