<md-content ng-cloak layout="column" ng-controller="application" layout-align="start center">
        <div style="width:960px">
            <md-card style="width: 100%">
                <md-card-header md-colors="{background: 'indigo'}">
                    <md-card-header-text>
                        <span class="md-title">變向選擇</span>
                    </md-card-header-text>
                </md-card-header>
                <md-card-content>
                    <md-list>
                        <md-list-item ng-repeat="column in options.columns">
                            <p><md-checkbox ng-model="column.selected" ng-true-value="'1'" ng-false-value="" aria-label="{}">{{column.title}}</md-checkbox></p>
                        </md-list-item>
                    </md-list>
                </md-card-content>
            </md-card>
            <md-card style="width: 100%">
                <md-card-header md-colors="{background: 'indigo'}">
                    <md-card-header-text>
                        <span class="md-title">使用主題本題目</span>
                    </md-card-header-text>
                </md-card-header>
                <md-card-content>
                    <md-list>
                        <md-list-item ng-repeat="question in options.questions">
                            <p><md-checkbox ng-model="question.selected" ng-true-value="'1'" ng-false-value="" aria-label="{}">{{question.title}}</md-checkbox></p>
                        </md-list-item>
                    </md-list>
                </md-card-content>
            </md-card>
            <md-button class="md-raised md-primary md-display-2" ng-click="getApplication()" style="width: 100%;height: 50px;font-size: 18px">送出</md-button>
        </div>
</md-content>
<script src="/js/ng/ngSurvey.js"></script>
<script>
    app.controller('application', function ($scope, $http, $filter){
        $scope.items = {};
        $scope.getAppliedOptions = function() {
            $http({method: 'POST', url: 'getAppliedOptions', data:{}})
            .success(function(data, status, headers, config) {
               console.log(data);
               $scope.options = data.options;
            })
            .error(function(e){
                console.log(e);
            });
        }
        $scope.getAppliedOptions();
    });
</script>
