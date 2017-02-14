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
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>主題本進入加掛題本{{extColumn.title}}條件設定</h4></md-subheader>
                    <md-list-item>
                        <md-input-container style="width: 100%">
                            <label>請選擇{{extColumn.title}}</label>
                            <md-select ng-model="organization" ng-change="setOrganization(organization)" data-md-container-class="selectdemoSelectHeader" multiple>
                                <md-option ng-value="organization" ng-repeat="organization in organizations.lists">{{organization.name}}</md-option>
                            </md-select>
                        </md-input-container>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>變向選擇</h4></md-subheader>
                    <md-list-item ng-repeat="column in columns">
                        <p>{{column.survey_applicable_option.title}}</p>
                        <md-checkbox class="md-secondary" ng-model="column.selected" ng-true-value="true" ng-false-value="false" aria-label="{}"></md-checkbox>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>使用主題本題目</h4></md-subheader>
                    <md-list-item ng-repeat="question in questions">
                        <p>{{question.survey_applicable_option.title}}</p>
                        <md-checkbox class="md-secondary" ng-model="question.selected" ng-true-value="true" ng-false-value="false" aria-label="{}"></md-checkbox>
                    </md-list-item>
                </md-list>
                <md-list flex ng-if="edited">
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>主題本進入加掛題本{{extColumn.title}}條件設定</h4></md-subheader>
                    <md-list-item ng-repeat="organization in organizations.selected">
                        <p>{{organization.name}}</p>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>變向選擇</h4></md-subheader>
                    <md-list-item ng-repeat="column in columns">
                        <p>{{column.survey_applicable_option.title}}</p>
                    </md-list-item>
                    <md-divider ></md-divider>
                    <md-subheader class="md-no-sticky" md-colors="{color: 'indigo-800'}"><h4>使用主題本題目</h4></md-subheader>
                    <md-list-item ng-repeat="question in questions">
                        <p>{{question.survey_applicable_option.title}}</p>
                    </md-list-item>
                </md-list>
            </md-content>
        </md-card>
        <md-button class="md-raised md-primary md-display-2" ng-click="setAppliedOptions()" ng-if="!edited" ng-disabled="disabled" style="width: 100%;height: 50px;font-size: 18px">送出</md-button>
        <md-button class="md-raised md-primary md-display-2" ng-click="toExtBook()" ng-if="edited" style="width: 100%;height: 50px;font-size: 18px">前往編製加掛問卷</md-button>
        <md-button class="md-raised md-primary md-display-2" ng-click="resetApplication()" ng-if="edited" ng-disabled="disabled" style="width: 100%;height: 50px;font-size: 18px">重新申請</md-button>
    </div>
</md-content>
<style>
    .selectdemoSelectHeader .demo-header-searchbox {
        border: none;
        outline: none;
        height: 100%;
        width: 100%;
        padding: 0; }
    .selectdemoSelectHeader .demo-select-header {
        box-shadow: 0 1px 0 0 rgba(0, 0, 0, 0.1), 0 0 0 0 rgba(0, 0, 0, 0.14), 0 0 0 0 rgba(0, 0, 0, 0.12);
        padding-left: 10.667px;
        height: 48px;
        cursor: pointer;
        position: relative;
        display: flex;
        align-items: center;
        width: auto; }
    .selectdemoSelectHeader md-content._md {
        max-height: 240px; }
</style>
<script>
    app.controller('application', function ($scope, $http, $filter, $location, $element){
        $scope.columns = [];
        $scope.questions = [];
        $scope.edited = [];
        $scope.book = [];
        $scope.extBook = {applied: false};
        $scope.extColumn = {};
        $scope.newDoc = {title: "加掛題本", type: 6};
        $scope.selected = {'organizations': []};
        $scope.organizations = {'lists': [], 'selected': []};
        $scope.skipTarget = {class: null, id: null};
        $scope.disabled = false;

        $scope.setOrganization = function(organization) {
            $scope.selected.organizations = organization;
        };

        $scope.getAppliedOptions = function() {
            $http({method: 'POST', url: 'getAppliedOptions', data:{}})
            .success(function(data, status, headers, config) {
                $scope.book = data.book;
                $scope.setVar(data.columns, data.questions, data.edited, data.extBook, data.organizations, data.extColumn);
                if (data.edited == true) {
                    $scope.getRuleOrganizations();
                }
            })
            .error(function(e){
                console.log(e);
            });
        }

        function getSelected() {
            var columns = $filter('filter')($scope.columns, {selected: true}).map(function(column) {
                return column.id;
            });
            var questions = $filter('filter')($scope.questions, {selected: true}).map(function(question) {
                return question.id;
            });

            var rules = new Array({'conditions': []});
            for(key in $scope.selected.organizations) {
                var condition = {'type': $scope.extColumn.class, 'id': $scope.book.column_id, 'value': $scope.selected.organizations[key].id};
                if(key > 0) {
                    condition.logic = ' || ';
                }
                rules[0].conditions.push(condition);
            }
            return {'columns': columns.concat(questions), 'rules': rules};
        }

        $scope.setAppliedOptions = function() {
            $scope.disabled = true;
            $scope.organizations.selected = null;
            var selected = getSelected();
            $http({method: 'POST', url: 'setAppliedOptions', data:{columns: selected.columns, book_id: $scope.columns[0].book_id}})
            .success(function(data, status, headers, config) {
                $scope.setVar(data.columns, data.questions, data.edited, data.extBook, data.organizations, data.extColumn);
                $scope.saveRules(selected.rules);
            })
            .error(function(e){
                console.log(e);
            });
        }

        $scope.resetApplication = function() {
            $scope.disabled = true;
            $http({method: 'POST', url: 'resetApplication', data:{extBook: $scope.extBook}})
            .success(function(data, status, headers, config) {
                $scope.deleteRules(data.columns, data.questions, data.edited, data.extBook, data.organizations, data.extColumn);
            })
            .error(function(e){
                console.log(e);
            });
        }

        $scope.setVar = function(columns, questions, edited, extBook, organizations, extColumn) {
            $scope.columns = columns;
            $scope.questions = questions;
            $scope.edited = edited;
            $scope.extBook = extBook;
            $scope.extColumn = extColumn;
            $scope.organizations.lists = organizations;
            $scope.skipTarget.class = $scope.book.class;
            $scope.skipTarget.id = extBook.id;
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

        $scope.toExtBook = function() {
            location.href = $scope.extBook.link;
        };

        $scope.saveRules = function(rules) {
            $http({method: 'POST', url: 'saveRules', data:{skipTarget: $scope.skipTarget, rules: rules}})
            .success(function(data, status, headers, config) {
                $scope.getRuleOrganizations();
                if ($scope.extBook.length == 0) {
                    $scope.createExtBook();
                }
                $scope.disabled = false;
            }).error(function(e){
                console.log(e);
            });
        };

        $scope.deleteRules = function(columns, questions, edited, extBook, organizations, extColumn) {
            $http({method: 'POST', url: 'deleteRules', data:{skipTarget: $scope.skipTarget}})
            .success(function(data, status, headers, config) {
                $scope.setVar(columns, questions, edited, extBook, organizations, extColumn);
                $scope.extBook.applied = false;
                $scope.disabled = false;
            }).error(function(e){
                console.log(e);
            });
        };

        $scope.getRuleOrganizations = function() {
            $http({method: 'POST', url: 'getRuleOrganizations', data:{skipTarget: $scope.skipTarget}})
            .success(function(data, status, headers, config) {
                $scope.organizations.selected = data.organizations;
            }).error(function(e){
                console.log(e);
            });
        };

        $scope.getAppliedOptions();
    });
</script>
