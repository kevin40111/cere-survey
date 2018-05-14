'use strict';

angular.module('surveyRule', [])

.factory('conditionService', function() {
    var categories = [];

    return {
        categories: categories
    };
})

.directive('ruleOperation', function() {
    return {
        restrict: 'EA',
        replace: false,
        transclude: false,
        scope: {
            nestOperation: '=operation'
        },
        template: `
            <rule-factor ng-if="isOperator(nestOperation.operator)" operation="nestOperation" operators="operators"></rule-factor>

            <div flex ng-if="nestOperation.operations.length > 0" md-colors="boxColor()" ng-style="boxStyle()" style="position: relative; margin: 10px; padding: 10px; border-color: #eee">
                <div ng-repeat-start="operation in nestOperation.operations" layout="row" layout-align="start center">
                    <rule-operation operation="operation"></rule-operation>

                    <md-button ng-if="isOperator(operation.operator) && nestOperation.operations.length > 1" class="md-icon-button" ng-click="delete(operation)"><md-icon>delete</md-icon></md-button>

                    <md-menu ng-if="isOperator(operation.operator) && operation.factor">
                        <md-button class="md-icon-button" ng-click="$mdMenu.open($event)"><md-icon>more_vert</md-icon></md-button>
                        <md-menu-content width="4">
                            <md-menu-item ng-if="nestOperation.operator !== 'or'" ng-click="wrap(operation, 'or')"><md-button><md-icon>delete</md-icon>或</md-button></md-menu-item>
                            <md-menu-item ng-if="nestOperation.operator !== 'and'" ng-click="wrap(operation, 'and')"><md-button><md-icon>delete</md-icon>而且</md-button></md-menu-item>
                        </md-menu-content>
                    </md-menu>
                </div>
                <span ng-repeat-end md-colors="isLogistics(nestOperation.operator) ? {color: colors[nestOperation.operator]} : {}">{{logistics[nestOperation.operator]}}</span>
                <md-button ng-if="isLogistics(nestOperation.operator)" class="md-icon-button" ng-click="append()"><md-icon>add</md-icon></md-button>
            </div>

        `,
        controller: function($scope, $http) {
            $scope.colors = {and: 'red', or: 'green'};
            $scope.logistics = {and: '而且', or: '或'};
            $scope.operators = {'>': '大於', '<': '小於', '==': '等於', '!=': '不等於'};

            $scope.boxColor = function() {
                return $scope.colors.hasOwnProperty($scope.nestOperation.operator) ? {borderLeftColor: $scope.colors[$scope.nestOperation.operator]} : {};
            }

            $scope.boxStyle = function() {
                return $scope.logistics.hasOwnProperty($scope.nestOperation.operator) ? {borderStyle: 'solid', borderWidth: '2px 2px 2px 5px'} : {};
            }

            $scope.isOperator = function(operator) {
                return $scope.operators.hasOwnProperty(operator);
            }

            $scope.isLogistics = function(operator) {
                return $scope.logistics.hasOwnProperty(operator);
            }

            $scope.append = function() {
                $http({method: 'POST', url: 'appendOperation', data:{operation: $scope.nestOperation}})
                .then(function(response) {
                    $scope.nestOperation.operations.push(response.data.operation);
                });
            }

            $scope.wrap = function(operation, logistic) {
                $http({method: 'POST', url: 'wrapOperation', data:{operation: operation, logistic: logistic}})
                .then(function(response) {
                    $scope.nestOperation.operations.splice($scope.nestOperation.operations.indexOf(operation), 1, response.data.wraper);
                });
            }

            $scope.delete = function(operation) {
                if ($scope.nestOperation.operations.length > 2) {
                    remove(operation);
                } else {
                    unwrap(operation);
                }
            }

            function remove(operation) {
                $http({method: 'POST', url: 'removeOperation', data:{operation: operation}})
                .then(function(response) {
                    $scope.nestOperation.operations.splice($scope.nestOperation.operations.indexOf(operation), 1);
                });
            }

            function unwrap(operation) {
                $http({method: 'POST', url: 'unwrapOperation', data:{operation: operation}})
                .then(function(response) {
                    angular.extend($scope.nestOperation, response.data.replacement);
                });
            }
        }
    }
})

.directive('ruleFactor', function(conditionService) {
    return {
        restrict: 'E',
        replace: true,
        transclude: false,
        scope: {
            operation: '=',
            operators: '='
        },
        template: `
            <span layout="row">
            <md-input-container style="width: 200px">
                <label>當題目</label>
                <md-select ng-model="target" ng-model-options="{trackBy: '$value.id'}" ng-change="updateOrCreateFactor()">
                    <md-optgroup label="{{category.title}}" ng-repeat="category in categories">
                        <md-option ng-repeat="question in category.questions" ng-value="::question">{{question.node.title}}-{{question.title}}</md-option>
                    </md-optgroup>
                </md-select>
            </md-input-container>
            <md-input-container>
                <label>條件</label>
                <md-select ng-model="operation.operator" ng-change="updateOperation()">
                    <md-option ng-repeat="(operator, title) in operators" ng-value="operator">{{title}}</md-option>
                </md-select>
            </md-input-container>
            <md-input-container ng-if="target.node.answers" style="width: 100px">
                <label>選項</label>
                <md-select ng-model="operation.factor.value" ng-disabled="! target" ng-change="updateFactor()">
                    <md-option ng-repeat="answer in target.node.answers" ng-value="answer.value">{{answer.title}}</md-option>
                </md-select>
            </md-input-container>
            <md-input-container ng-if="! target.node.answers" style="width: 100px">
                <label>選項</label>
                <input ng-model="operation.factor.value" ng-model-options="{updateOn: 'default blur', debounce: {default: 1000, blur: 0}}" ng-change="updateFactor()" />
            </md-input-container>
            </span>
        `,
        controller: function($scope, $http, $timeout) {
            $scope.categories = conditionService.categories;

            if ($scope.operation.factor) {
                $scope.target = $scope.operation.factor.target;
            }

            $timeout(function() {
                $scope.updateOrCreateFactor = updateOrCreateFactor;
            }, 0);

            $scope.updateOperation = function() {
                $http({method: 'POST', url: 'updateOperation', data:{operation: $scope.operation}})
                .then(function(response) {
                });
            }

            $scope.updateFactor = function() {
                $http({method: 'POST', url: 'updateFactor', data:{factor: $scope.operation.factor}})
                .then(function(response) {
                });
            }

            function updateOrCreateFactor() {
                ! $scope.operation.factor ? create() : update();
            }

            function create() {
                $http({method: 'POST', url: 'createFactor', data:{operation: $scope.operation, target: $scope.target}})
                .then(function(response) {
                    $scope.operation.factor = response.data.factor;
                });
            }

            function update() {
                $http({method: 'POST', url: 'updateFactorTarget', data:{factor: $scope.operation.factor, target: $scope.target}})
                .then(function(response) {
                    $scope.operation.factor.target = $scope.target;
                });
            }
        }
    }
})

.directive('ruleLessThan', function() {
    return {
        restrict: 'E',
        replace: false,
        transclude: false,
        scope: {
            operation: '=',
            target: '='
        },
        template: `
            <div layout="row" layout-align="start center" style="margin: 10px; padding: 10px">
                <md-select ng-model="operation.operator" aria-label="條件" ng-change="updateOperation(operation)">
                    <md-option value="<=">最多</md-option>
                </md-select>
                <md-select ng-model="operation.factor.value" aria-label="數量" ng-change="updateOrCreateFactor()">
                    <md-option ng-repeat="question in target.questions" ng-value="::$index" ng-if="$index>0">{{$index}}</md-option>
                </md-select>
                <span>個選項</span>
            </div>
        `,
        controller: function($scope, $http) {
            $scope.updateOperation = function(factor) {
                $http({method: 'POST', url: 'updateOperation', data:{operation: $scope.operation}})
                .then(function(response) {
                });
            }

            $scope.updateOrCreateFactor = function() {
                ! $scope.operation.factor.id ? create() : update();
            }

            function create() {
                $http({method: 'POST', url: 'createFactor', data:{operation: $scope.operation, factor: $scope.operation.factor, target: $scope.target}})
                .then(function(response) {
                    $scope.operation.factor = response.data.factor;
                });
            }

            function update() {
                $http({method: 'POST', url: 'updateFactor', data:{factor: $scope.operation.factor}})
                .then(function(response) {
                });
            }
        }
    }
})

.directive('ruleExclusion', function() {
    return {
        restrict: 'E',
        replace: false,
        transclude: false,
        scope: {
            operation: '=',
            target: '='
        },
        template: `
            <div layout-align="start center" style="margin: 10px; padding: 10px">
                <div>選擇選項時，會清除其他勾選的項目</div>
                <md-checkbox ng-repeat="question in target.questions" ng-checked="question.id === operation.factor.target.id" aria-label="{{question.title}}" ng-click="updateOrCreateFactor(question)">{{question.title}}</md-checkbox>
            </div>
        `,
        controller: function($scope, $http, $filter) {
            $scope.updateOperation = function(factor) {
                $http({method: 'POST', url: 'updateOperation', data:{operation: $scope.operation}})
                .then(function(response) {
                });
            }

            $scope.updateOrCreateFactor = function(target) {
                ! $scope.operation.factor ? create(target) : update(target);
            }

            function create(target) {
                $http({method: 'POST', url: 'createFactor', data:{operation: $scope.operation, factor: {value: 1}, target: target}})
                .then(function(response) {
                    $scope.operation.factor = response.data.factor;
                });
            }

            function update(target) {
                console.log($scope.operation.factor);
                $http({method: 'POST', url: 'updateFactorTarget', data:{factor: $scope.operation.factor, target: target}})
                .then(function(response) {
                    if (response.data.updated) {
                        $scope.operation.factor.target = target;
                    }
                });
            }
        }
    }
});
