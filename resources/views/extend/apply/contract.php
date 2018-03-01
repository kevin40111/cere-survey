<md-content ng-cloak layout="column" ng-controller="contract" layout-align="start center">
    <div ng-include="'stepsTemplate'"></div>
        <div style="width:960px">
            <md-card style="width: 100%">
                <md-card-header md-colors="{background: 'indigo'}">
                    <md-card-header-text>
                        <span class="md-title">加掛申請同意書</span>
                    </md-card-header-text>
                </md-card-header>
                <md-content>
                    <md-list flex>
                        <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>加掛申請期限： <span md-colors="{color:'grey'}">123</span></h4></md-subheader>
                        <md-divider></md-divider>
                        <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>加掛申請注意事項: </h4></md-subheader>
                        <md-list-item ng-bind-html="trustAsHtml(consent.content)"></md-list-item>
                        <md-divider></md-divider>
                        <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>加掛申請同意書: </h4></md-subheader>
                        <md-list-item>
                            <div ng-bind-html="trustAsHtml(consent.precaution)"></div>
                        </md-list-item>
                        <md-list-item>
                            <div>
                                <md-checkbox ng-model="consent.agree" class="md-primary">同意</md-checkbox>
                            </div>
                        </md-list-item>
                    </md-list>
                </md-content>
                <md-card-actions layout="column">
                    <md-button flex href="agreeContract" ng-disabled="!consent.agree">送出</md-button>
                </md-card-actions>
            </md-card>
        </div>
</md-content>
<script>
app.controller('contract', function ($scope, $sce, $http, $window){
    $scope.trustAsHtml = function(string) {
        return $sce.trustAsHtml(string);
    };
    $scope.getConsent = function() {
        $http({method: 'POST', url: 'getConsent', data:{}})
        .success(function(data, status, headers, config) {
            console.log(data);
            $scope.consent = data.consent;
        })
        .error(function(e){
            console.log(e);
        });
    }
    $scope.getConsent();
});
</script>
