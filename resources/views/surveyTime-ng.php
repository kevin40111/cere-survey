<md-content ng-cloak layout="column" ng-controller="surveytime" layout-align="start center">
    <div style="width:960px">
        <md-card style="width: 100%">
            <md-card-header md-colors="{background: 'indigo'}">
                <md-card-header-text>
                    <span class="md-title">設定時間</span>
                </md-card-header-text>
            </md-card-header>
            <md-content>
                <md-list flex>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>開始時間</h4></md-subheader>
                    <md-list-item>
                        <input type="datetime-local" ng-model="start_at" placeholder="yyyy-MM-dd HH:mm:ss"/>
                    </md-list-item>
                </md-list>
                <md-list flex>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>結束時間</h4></md-subheader>
                    <md-list-item>
                        <input type="datetime-local" ng-model="close_at" placeholder="yyyy-MM-ddTHH:mm:ss"/>
                    </md-list-item>
                </md-list>
                <md-list ng-if="msg">
                    <p>{{msg}}</p>
                </md-list>
            </md-content>
        </md-card>
        <md-button class="md-raised md-primary" ng-click="setTime()" style="width: 100%;height: 50px;font-size: 18px" ng-disabled="disabled">送出</md-button>
    </div>
</md-content>
<script>
    app.controller('surveytime', function ($scope, $http, $filter){
        $scope.getTime = function() {
            $http({method: 'get', url: 'getTime', data:{}})
            .success(function(data, status, headers, config) {
                angular.extend($scope, data);
                $scope.start_at = new Date($scope.start_at);
                $scope.close_at = new Date($scope.close_at);
            })
            .error(function(e){
                console.log(e);
            });
        }
        $scope.setTime = function() {
            $scope.msg = '';
            $http({method: 'POST', url: 'setTime', data:{start_at: $filter('date')($scope.start_at, 'yyyy-MM-dd HH:mm:ss', '+0800'), close_at: $filter('date')($scope.close_at, 'yyyy-MM-dd HH:mm:ss', '+0800')}})
            .success(function(data, status, headers, config) {
                $scope.msg = '設定成功';
            })
            .error(function(e){
                $scope.msg = '設定失敗';
                console.log(e);
            });
        }
        $scope.getTime();
    });
</script>
