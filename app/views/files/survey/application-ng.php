<md-content ng-cloak layout="column" ng-controller="application" layout-align="start center">
    <div style="width:960px">
        <md-card style="width: 100%">
            <md-card-header md-colors="{background: 'indigo'}">
                <md-card-header-text>
                    <span class="md-title">加掛題申請表</span>
                </md-card-header-text>
            </md-card-header>
            <md-content>
                <md-list flex ng-if="!edited">
                    <md-subheader class="md-no-sticky"><h4>變向選擇</h4></md-subheader>
                    <md-list-item ng-repeat="column in columns">
                        <p>{{column.survey_applicable_option.title}}</p>
                        <md-checkbox class="md-secondary" ng-model="column.selected" ng-true-value="true" ng-false-value="false" aria-label="{}"></md-checkbox>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky"><h4>使用主題本題目</h4></md-subheader>
                    <md-list-item ng-repeat="question in questions">
                        <p>{{question.survey_applicable_option.title}}</p>
                        <md-checkbox class="md-secondary" ng-model="question.selected" ng-true-value="true" ng-false-value="false" aria-label="{}"></md-checkbox>
                    </md-list-item>
                </md-list>
                <md-list flex ng-if="edited">
                    <md-subheader class="md-no-sticky"><h4>變向選擇</h4></md-subheader>
                    <md-list-item ng-repeat="column in columns">
                        <p>{{column.survey_applicable_option.title}}</p>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky"><h4>使用主題本題目</h4></md-subheader>
                    <md-list-item ng-repeat="question in questions">
                        <p>{{question.survey_applicable_option.title}}</p>
                    </md-list-item>
                </md-list>
            </md-content>
        </md-card>
        <md-button class="md-raised md-primary md-display-2" ng-click="setAppliedOptions()" ng-if="!edited" style="width: 100%;height: 50px;font-size: 18px">送出</md-button>
        <md-button class="md-raised md-primary md-display-2" ng-click="toExtBook()" ng-if="extBook.applied" style="width: 100%;height: 50px;font-size: 18px">前往編製加掛問卷</md-button>
        <md-button class="md-raised md-primary md-display-2" ng-click="resetApplication()" ng-if="edited" style="width: 100%;height: 50px;font-size: 18px">重新申請</md-button>
    </div>
</md-content>
<script>
    app.controller('application', function ($scope, $http, $filter, $location){
        $scope.columns = [];
        $scope.questions = [];
        $scope.edited = [];
        $scope.book = [];
        $scope.extBook = {applied: false};
        $scope.newDoc = {title: "加掛題本", type: 6};
        $scope.getAppliedOptions = function() {
            $http({method: 'POST', url: 'getAppliedOptions', data:{}})
            .success(function(data, status, headers, config) {
                console.log(data);
                $scope.book = data.book;
                $scope.setVar(data.columns, data.questions, data.edited, data.extBook);
            })
            .error(function(e){
                console.log(e);
            });
        }

        function getSeleted() {
            var columns = $filter('filter')($scope.columns, {selected: true}).map(function(column) {
                return column.id;
            });
            var questions = $filter('filter')($scope.questions, {selected: true}).map(function(question) {
                return question.id;
            });
            return columns.concat(questions);
        }

        $scope.setAppliedOptions = function() {
            var selected = getSeleted();
            $http({method: 'POST', url: 'setAppliedOptions', data:{selected: selected, book_id: $scope.columns[0].book_id}})
            .success(function(data, status, headers, config) {
                $scope.setVar(data.columns, data.questions, data.edited, data.extBook);
                if ($scope.extBook.length == 0) {
                    $scope.createExtBook();
                }
            })
            .error(function(e){
                console.log(e);
            });
        }

        $scope.resetApplication = function() {
            $http({method: 'POST', url: 'resetApplication', data:{extBook: $scope.extBook}})
            .success(function(data, status, headers, config) {
                $scope.setVar(data.columns, data.questions, data.edited, data.extBook);
                $scope.extBook.applied = false;
            })
            .error(function(e){
                console.log(e);
            });
        }

        $scope.setVar = function(columns, questions, edited, extBook) {
            $scope.columns = columns;
            $scope.questions = questions;
            $scope.edited = edited;
            $scope.extBook = extBook;
        }

        $scope.createExtBook = function() {
            $scope.newDoc.title = $scope.book.title + '(' + $scope.newDoc.title + ')';
            $http({method: 'POST', url: '/file/create', data:{fileInfo: $scope.newDoc}})
            .success(function(data, status, headers, config) {
                $scope.setExtBook(data.doc.id);
            }).error(function(e){
                console.log(e);
            });
        };

        $scope.setExtBook = function(doc_id) {
            $http({method: 'POST', url: 'setExtBook', data:{doc_id: doc_id}})
            .success(function(data, status, headers, config) {
                $scope.extBook = data;
            }).error(function(e){
                console.log(e);
            });
        };

        /*$scope.getExtBook = function() {
            $http({method: 'POST', url: 'getExtBook', data:{}})
            .success(function(data, status, headers, config) {
                $scope.extBook = data;
            }).error(function(e){
                console.log(e);
            });
        };*/

        $scope.toExtBook = function() {
            location.href = $scope.extBook.link;
        };

        $scope.getAppliedOptions();
        // $scope.getExtBook();
    });
</script>
