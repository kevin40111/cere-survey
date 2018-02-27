<div ng-controller="inviteCtrl">
    <div class="ui basic segment" ng-cloak style="overflow: auto">
        <md-input-container>
            <label>選擇群組</label>
            <md-select ng-model="search.position">
                <md-option ng-repeat="position in positions" value="{{position.id}}">
                    {{group.title}}
                </md-option>
                <md-option value="">教育評鑑中心</md-option>
            </md-select>
        </md-input-container>
        </br>

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
                    <th width="250">邀請狀態</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script>
app.controller('inviteCtrl', function($scope, $http, $filter, $mdDialog, $timeout, $q, $mdToast) {

});
</script>
