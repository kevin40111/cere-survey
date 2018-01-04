<md-content ng-cloak layout="column" ng-controller="demologin" layout-align="start center">
<form class="ui large form" method="post">
    <div class="ui middle aligned center aligned grid">
      <div class="column">
        <h2 class="ui teal image header">
            <div class="content">
                加掛題本登入條件
            </div>
        </h2>
        <div class="ui segment">
            <div class="field">
                <md-input-container>
                    <label>請選擇學校</label>
                    <md-select ng-model="option">
                        <md-option ng-repeat="option in options" ng-value="option">{{option.name}}</md-option>
                    </md-select>
                </md-input-container>
            </div>
            <md-button href="/surveyDemo/{{option.ext_book_id}}/demo/page"  class="ui fluid large teal button" target="_blank">登入加掛題本</md-button>
        </div>
      </div>
    </div>
</form>
</md-content>

<script >
    app.controller('demologin', function ($scope, $http){

        $scope.getDemoOption = function(application) {
            $http({method: 'POST', url: 'getDemoOption', data:{}})
            .success(function(data, status, headers, config) {
                $scope.options = data.options;
                console.log( $scope.options)

            })
            .error(function(e) {
                console.log(e);
            });
        };
        $scope.getDemoOption();
    });
</script>
