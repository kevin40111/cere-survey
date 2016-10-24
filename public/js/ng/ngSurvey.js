angular.module('ngSurvey', [
    'ngSurvey.factories',
    'ngSurvey.directives'
]);

angular.module('ngSurvey.factories', []).factory('surveyFactory', function($http, $q) {
    var answers = {};
    var book = {};
    var record = {};
    return {
        get: function(url, data, node = {}) {
            var deferred = $q.defer();

            node.saving = true;
            $http({method: 'POST', url: url, data: data, timeout: deferred.promise})
            .success(function(data, status, headers, config) {
                deferred.resolve(data);
            }).error(function(e) {
                deferred.reject();
                console.log(e);
            });

            deferred.promise.finally(function() {
                node.saving = false
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
            if (question.type=='text') {
                var answer = {id: question.answers[0].id, string: answers[question.id]};
            } else {
                var answer = {id: answers[question.id], string: null};
            }
            question.saving = true;
            $http({method: 'POST', url: 'saveAnswer', data:{book: book, record: record, question: question, answer: answer}})
            .success(function(data, status, headers, config) {
                angular.forEach(data.deletedAnswers, function(deletedAnswer) {
                    if (answers[deletedAnswer]) {
                        delete answers[deletedAnswer];
                    };
                });
                if (answers[question.id] == data.string || data.id) {
                    question.saving = false;
                };
                (callback) && callback(data);
            }).error(function(e){
                console.log(e);
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

.directive('questionNodes', function(surveyFactory) {
    return {
        restrict: 'E',
        replace: true,
        transclude: false,
        scope: {
            book: '=',
            page: '='
        },
        template:  `
            <div>
                <md-card ng-repeat="node in nodes">
                    <md-card-title>
                        <md-card-title-text>
                        <span class="md-headline" ng-bind-html="node.title"></span>
                        </md-card-title-text>
                    </md-card-title>
                    <md-card-content>
                        <question node="node"></question>
                    </md-card-content>
                    <md-card-content layout="row" layout-align="space-around" ng-if="node.saving">
                        <md-progress-circular md-mode="indeterminate"></md-progress-circular>
                    </md-card-content>
                </md-card>
            </div>
        `,
        controller: function($scope, $http, $filter) {

            $scope.getNodes = function(parent) {
                console.log(parent);
                surveyFactory.get('getNodes', {parent: parent}).then(function(response) {
                    console.log(response);
                    $scope.root = parent;
                    $scope.nodes = response.nodes;
                });
            };

            $scope.getNodes($scope.book);

        }
    };
})

.directive('question', function($compile, surveyFactory, $templateCache, templates) {
    return {
        restrict: 'E',
        replace: true,
        transclude: false,
        scope: {
            node: '='
        },
        compile: function(tElement, tAttr) {
            var contents = tElement.contents().remove();
            var compiledContents = {};

            return function(scope, iElement, iAttr) {
                scope.$watch('question.type', function(newType, oldType) {
                    var contents = iElement.contents().remove();
                    var type = scope.node.type;
                    compiledContents[type] = $compile($templateCache.get(type));
                    compiledContents[type](scope, function(clone, scope) {
                        iElement.append(clone);
                    });
                });
            };
        },
        controller: function($scope, $http, $window, $filter, $rootScope) {
            $scope.saveTextNgOptions = {updateOn: 'default blur', debounce:{default: 10000, blur: 0}};
            $scope.answers = surveyFactory.answers;
            $scope.answers = {};console.log();

            $scope.save_answers = function(question) {
                question.error = true;
                surveyFactory.save(question);
            }

            $scope.$on('$destroy', function() {
                $scope.setConfirm(false);
            });

            $scope.setConfirm = function(confirm) {
                if ($scope.question.type == 'select' || $scope.question.type == 'radio') {
                    $scope.question.confirm = confirm;
                };
                if ($scope.question.type == 'scales') {
                    angular.forEach($filter('filter')($scope.branchs, {parent_question_id: $scope.question.id}, true), function(question) {
                        question.confirm = confirm;
                    });
                };
            };

            $scope.compareRule = function(question) {
                //console.log(question);
                var show = true;
                if (('close' in question)) {show = false;};
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