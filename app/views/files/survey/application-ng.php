<md-content ng-cloak layout="column" ng-controller="application" layout-align="start center">
        <div style="width:960px">
            <md-card ng-repeat="(key, item) in items" style="width: 100%">
                <md-card-header md-colors="{background: 'indigo'}">
                    <md-card-header-text>
                        <span class="md-title">{{item.title}}</span>
                    </md-card-header-text>
                </md-card-header>
                <md-card-content>
                    <md-list>
                    <!-- ng-repeat="subItem in item.subItems" -->
                        <md-list-item ng-repeat="(subKey, subItem) in item.subItems">
                            <p><md-checkbox ng-model="item.subItems[subKey].selected" ng-true-value="1" ng-false-value="null" aria-label="{item.subItems[subKey].name}">{{item.subItems[subKey].name}}</md-checkbox></p>
                        </md-list-item>
                    </md-list>
                </md-card-content>
            </md-card>
            <md-button class="md-raised md-primary md-display-2" style="width: 100%;height: 50px;font-size: 18px">送出</md-button>
        </div>
</md-content>
<script src="/js/ng/ngSurvey.js"></script>
<script>
    app.requires.push('ngSurvey');
    app.controller('application', function ($scope, $http, $filter, surveyFactory){
        /*$scope.items = {
            '是否加掛問卷': ['是'],
            '變向選擇': ['性別', '科別代碼', '核定管道'],
            '使用主題本題目': ['OO', 'XX', 'ZZ']
        };*/

        $scope.items = {
            'isHang': {
                'title': '是否加掛問卷',
                'subItems': {
                    'yes': {
                        'name': '是',
                        'selected': ''
                    }
                }
            }
            /*'變向選擇': ['性別', '科別代碼', '核定管道'],
            '使用主題本題目': ['OO', 'XX', 'ZZ']*/
        };



        $scope.selected = {
            'isHang': {'yes': ''},
            'changeDir': {'sex': '', 'class': '', 'source': ''},
            'questions': {}
        };
        console.log($scope.items);
    });
</script>
