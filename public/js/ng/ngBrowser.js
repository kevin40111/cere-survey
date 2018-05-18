angular.module('ngBrowser', [])

.directive("surveyBrowser",function($http, $mdSidenav){
    return {
        restrict: 'E',
        replace: true,
        template: `
            <div>
                <table class="ui very compact celled table" ng-repeat="(index, page) in pages">
                    <tr>
                        <td style="text-align: center" colspan="4">第{{index+1}}頁</td>
                    </tr>
                    <tr style="font-size: 18px">
                        <td><b>項目</b></td>
                        <td><b>題型</b></td>
                        <td><b>題目</b></td>
                        <td><b>選項</b></td>
                    </tr>

                    <tr ng-repeat="(key,node) in page.nodes">

                        <!-- 題號 -->
                        <td style="width: 60px; text-align: center">
                            {{$index+1}}
                        </td>

                        <!-- 題型 -->
                        <td style="width: 100px">
                            {{types[node.type]}}
                        </td>

                        <!-- 題目 -->
                        <td >
                            {{(node.title)}}<button ng-if="node.skipers.length > 0" ng-click="toggleSidenavRight(node.skipers)" class="ui left pointing red basic label">跳題</button>
                            <p style="width: 350px" md-truncate ng-repeat="question in node.questions">
                                {{question.id}} - {{question.title}}
                                <md-tooltip style="font-size: 18px">{{question.id}} - {{question.title}}</md-tooltip>
                            </p>
                        </td>

                        <!-- 選項 -->
                        <td style="width: 200px">
                            <span ng-repeat="(key,answer) in node.answers">
                                {{key+1}}.{{answer.title}}
                                <span ng-if="answer.rule.expressions.length > 0" ng-click="showPassQuestion(answer)" class="ui left pointing red basic label">
                                    {{answer.rule.expressions.length}}個跳答條件
                                </span>
                                </br>
                            </span>
                        </td>
                    </tr>
                </table>
                <md-sidenav class="md-sidenav-right" md-component-id="survey-skips-description" md-is-open="isOpenDescription">
                    <md-toolbar>
                        <div class="md-toolbar-tools">
                            <md-button aria-label="關閉" ng-click="toggleSidenavRight()">關閉</md-button>
                        </div>
                    </md-toolbar>
                    <survey-skip-description ng-if="isOpenDescription" skipers="skipers"></survey-skip-description>
                </md-sidenav>
            </div>
        `,
        scope: {
            book: '='
        },
        link: function(scope, element, attrs) {
            scope.types = {
                radio: '單選題',
                text: '文字填答',
                scale: '量表題',
                checkbox: '複選題',
                select: '下拉式選單',
                number: '數子題',
                explain: '說明文字'
            };
            scope.pages = [];
            scope.skipers = [];

            scope.toggleSidenavRight = function(skipers) {
                scope.skipers = skipers;
                $mdSidenav('survey-skips-description').toggle();
            };

            $http({method: 'POST', url: 'getBrowserQuestions', data:{book_id: scope.book}})
            .then(function(response) {
                scope.pages = response.data.pages;
            });
        }
    };
})

.directive("surveySkipDescription", function() {
    return {
        restrict: 'E',
        scope: {
            skipers: '='
        },
        template: `
            <div layout="column" ng-repeat="skiper in skipers">
                <rule-operation-description operation="skiper"></rule-operation-description>
            </div>
        `,
        controller: function($scope, $http) {
            $scope.skipers.forEach(function(skiper) {
                $http({method: 'POST', url: 'loadSkiper', data:{skiper: skiper}})
                .then(function(response) {
                    angular.extend(skiper, response.data.skiper);
                });
            });
        }
    };
})

.directive('ruleOperationDescription', function() {
    return {
        restrict: 'EA',
        replace: false,
        transclude: false,
        scope: {
            nestOperation: '=operation'
        },
        template: `
            <rule-factor-description ng-if="isOperator(nestOperation.operator)" operation="nestOperation" operators="operators"></rule-factor-description>

            <div flex ng-if="nestOperation.operations.length > 0" md-colors="boxColor()" ng-style="boxStyle()" style="position: relative; margin: 10px; padding: 10px; border-color: #eee">
                <div ng-repeat-start="operation in nestOperation.operations" layout="row" layout-align="start center">
                    <rule-operation-description operation="operation"></rule-operation-description>
                </div>
                <span ng-repeat-end md-colors="isLogistics(nestOperation.operator) ? {color: colors[nestOperation.operator]} : {}">{{logistics[nestOperation.operator]}}</span>
            </div>

        `,
        controller: function($scope, $http) {
            $scope.colors = {and: 'red', or: 'green'};
            $scope.logistics = {and: '而且', or: '或'};
            $scope.operators = {'>': '大於', '<': '小於', '==': '等於', '!=': '不等於'};

            $scope.isOperator = function(operator) {
                return $scope.operators.hasOwnProperty(operator);
            }

            $scope.isLogistics = function(operator) {
                return $scope.logistics.hasOwnProperty(operator);
            }
        }
    };
})

.directive('ruleFactorDescription', function() {
    return {
        restrict: 'E',
        replace: false,
        transclude: false,
        scope: {
            operation: '=',
            operators: '='
        },
        template: `
            當題目 "{{operation.factor.target.title}}" {{operators[operation.operator]}} {{operation.factor.value}} 時不須填答此題
        `
    };
});
