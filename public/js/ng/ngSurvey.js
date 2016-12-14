'use strict';

angular.module('ngSurvey', ['ngSurvey.directives', 'ngSurvey.factories']);

angular.module('ngSurvey.factories', []).factory('surveyFactory', function($http, $q) {
    var answers = {};
    var book = {};
    var record = {};
    var types = {};
    return {
        types: types,
        get: function(url, data, node = {}) {
            var deferred = $q.defer();

            node.saving = true;
            $http({method: 'POST', url: url, data: data, timeout: deferred.promise})
            .success(function(data) {
                deferred.resolve(data);
            }).error(function() {
                deferred.reject();
            });

            deferred.promise.finally(function() {
                node.saving = false;
            });

            return deferred.promise;
        },
        answers: answers,
        setBook: function(v) { book = v; },
        setRecord: function(v) { record = v; },
        setAnswers: function(values) {
            for (var i in answers) delete answers[i];
            angular.forEach(values, function(answer, k) {
                answers[k] = (answer.string || answer.string=='') ? answer.string : answer.id;
            });
        },
        save: function(question, callback) {
            var answer;
            if (question.type=='text') {
                answer = {id: question.answers[0].id, string: answers[question.id]};
            } else {
                answer = {id: answers[question.id], string: null};
            }
            question.saving = true;
            $http({method: 'POST', url: 'saveAnswer', data:{book: book, record: record, question: question, answer: answer}})
            .success(function(data) {
                angular.forEach(data.deletedAnswers, function(deletedAnswer) {
                    if (answers[deletedAnswer]) {
                        delete answers[deletedAnswer];
                    }
                });
                if (answers[question.id] == data.string || data.id) {
                    question.saving = false;
                }
                (callback) && callback(data);
            }).error(function(){
            });
        }
    };
});

angular.module('ngSurvey.directives', [])

.factory('templates', function() {
    return {
        compact:   '<ng-include src="\'radio\'"></ng-include>'
    };
})

.directive('surveyBook', function(surveyFactory) {
    return {
        restrict: 'E',
        replace: true,
        transclude: false,
        scope: {
            book: '=',
        },
        template:  `
            <div>
                <div layout="row" layout-align="space-around" ng-if="book.saving">
                    <md-progress-circular md-mode="indeterminate"></md-progress-circular>
                </div>
                <div ng-if="!book.saving">
                    <survey-page ng-if="node" page="node"></survey-page>
                    <md-button class="md-raised md-primary" ng-click="getNextNode(true)" ng-disabled="book.saving">繼續</md-button>
                </div>
            </div>
        `,
        controller: function($scope) {

            surveyFactory.types = $scope.book.types;

            $scope.getNextNode = function(next = false) {
                surveyFactory.get('getNextNode', {next: next, book: $scope.book}, $scope.book).then(function(response) {
                    $scope.node = response.node;
                });
            };

            $scope.getNextNode();
        }
    };
})

.directive('surveyPage', function(surveyFactory) {
    return {
        restrict: 'E',
        replace: true,
        transclude: false,
        scope: {
            page: '=',
        },
        template:  `
            <div>
                <survey-node ng-repeat="node in nodes" node="node"></survey-node>
            </div>
        `,
        controller: function($scope) {

            $scope.$watch('page', function() {
                surveyFactory.get('getNextNodes', {page: $scope.page}, $scope.page).then(function(response) {
                    $scope.nodes = response.nodes;
                });
            });

        }
    };
})

.directive('surveyNode', function() {
    return {
        restrict: 'E',
        replace: true,
        transclude: false,
        scope: {
            node: '='
        },
        //require: '^surveyPage',
        template:  `
            <div>
                <md-card>
                    <md-card-title>
                        <md-card-title-text>
                        <span class="md-headline" ng-bind-html="node.title"></span>
                        </md-card-title-text>
                    </md-card-title>
                    <md-card-content>
                        <survey-question node="node"></survey-question>
                    </md-card-content>
                    <md-card-actions layout="row" layout-align="end center">

                    </md-card-actions>
                    <md-progress-linear md-mode="indeterminate" ng-disabled="!node.saving"></md-progress-linear>
                </md-card>
                <survey-node ng-if="childrens" ng-repeat="children in childrens" node="children"></survey-node>
            </div>
        `,
        controller: function($scope) {

            //$scope.node.saving = true;
            //$scope.node = {saving: true};

            this.addChildren = function(childrens) {
                $scope.childrens = childrens;
            };

        }
    };
})

.directive('surveyQuestion', function($compile, surveyFactory, $templateCache) {
    return {
        restrict: 'E',
        replace: true,
        transclude: false,
        scope: {
            node: '='
        },
        require: '^surveyNode',
        compile: function(tElement, tAttr) {
            tElement.contents().remove();
            var compiledContents = {};

            return function(scope, iElement, iAttr, ctrl) {
                scope.addChildren = ctrl.addChildren;
                //var contents = iElement.contents().remove();
                var type = surveyFactory.types[scope.node.type].name;
                compiledContents[type] = $compile($templateCache.get(type));
                compiledContents[type](scope, function(clone, scope) {
                    iElement.append(clone);
                });
            };
        },
        controller: function($scope, $http, $window, $filter, $rootScope) {
            $scope.saveTextNgOptions = {updateOn: 'default blur', debounce:{default: 10000, blur: 0}};
            //$scope.answers = surveyFactory.answers;
            $scope.answers = {};

            $scope.saveAnswer = function(question) {

                var answer = $scope.answers[question.id];
                var parent = $scope.node.type == 'checkbox' ? question : answer;

                surveyFactory.get('getChildren', {parent: parent, question: question, answer: answer}, $scope.node).then(function(response) {
                    question.childrens = response.nodes;
                });

                //question.error = true;
                //surveyFactory.save(question);
            };

            $scope.$on('$destroy', function() {
                // /$scope.setConfirm(false);
            });

            $scope.setConfirm = function(confirm) {
                if ($scope.question.type == 'select' || $scope.question.type == 'radio') {
                    $scope.question.confirm = confirm;
                }
                if ($scope.question.type == 'scales') {
                    angular.forEach($filter('filter')($scope.branchs, {parent_question_id: $scope.question.id}, true), function(question) {
                        question.confirm = confirm;
                    });
                }
            };

            $scope.compareRule = function(question) {
                //console.log(question);
                var show = true;
                if (('close' in question)) {show = false;}
                if (question.rules.length > 0) {
                    angular.forEach(question.rules, function(rule){
                        var parameter = rule.is.parameters[0];
                        var keys = Object.keys(parameter);
                        if ($scope.answers[keys[0]] == parameter[keys[0]]) {
                            show = false;
                        }
                    });
                }
                $scope.setConfirm(show);
                question.show = show;
                return show;
            };
        }
    };
});