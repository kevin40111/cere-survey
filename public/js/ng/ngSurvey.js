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
            }).error(function(e) {
                console.log(e);
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
        },
        compareRule: function(target) {
            var answers = this.answers;

            if (target.rule) {
                var expressions = target.rule.expressions;
                var result ='';
                for (var i in expressions) {
                    var expression = expressions[i];
                    if (expression.compareLogic) {
                        result = result + expression.compareLogic;
                    }
                    result = result + '(';
                    for (var j in expression.conditions) {
                        var condition = expression.conditions[j];
                        if (condition.compareOperator) {
                            result += condition.compareOperator;
                        }
                        result += answers[condition.question] + condition.logic + condition.value;
                    }
                    result = result + ')';
                }

                return eval(result);
            } else {
                return false;
            }

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
                <div ng-if="!book.saving && !book.done">
                    <survey-page ng-if="node" page="node" ng-hide="compareRule(node)"></survey-page>
                    <md-button class="md-raised md-primary" ng-click="getNextNode(true)" ng-disabled="book.saving" aria-label="繼續">
                        <p ng-if="!book.done">繼續</p>
                    </md-button>
                </div>
                <div class="ui segment" style="width:800px;margin:0 auto" ng-if="!book.saving && book.done">
                    <div class="ui basic segment">
                        <h3>本問卷填答完畢</h3>
                        <h3>祝您一切順利、中大獎</strong>！</font></h3>
                    </div>
                    <md-button ng-if="ext_book_url" class="md-raised md-primary" href="{{ext_book_url}}" target="_blank" ng-disabled="book.saving" aria-label="跳至加掛題本" >
                        填寫加掛題本
                    </md-button>
                </div>
            </div>
        `,
        controller: function($scope) {

            surveyFactory.types = $scope.book.types;

            $scope.getNextNode = function(next = false) {
                surveyFactory.get('getNextNode', {next: next, book: $scope.book}, $scope.book).then(function(response) {console.log(response);
                    if (response.missings.length > 0) {
                        alert('有尚未填答題目');
                    }

                    $scope.node = response.node;
                    surveyFactory.answers = response.answers;
                    $scope.book.saving = false;
                    $scope.book.done = false;
                    $scope.ext_book_url = null;
                    if (response.url != null) {
                        $scope.ext_book_url = response.url;
                    }
                    if ($scope.node == null) {
                        $scope.book.done = true;
                    }
                });
            };

            $scope.getNextNode();

            $scope.compareRule = function(target) {
                return surveyFactory.compareRule(target);
            };
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
                <survey-node ng-repeat="node in nodes" node="node" ng-hide="compareRule(node)"></survey-node>
            </div>
        `,
        controller: function($scope, $http) {

            $scope.$watch('page', function() {
                surveyFactory.get('getNextNodes', {page: $scope.page}, $scope.page).then(function(response) {
                    $scope.nodes = response.nodes;
                });
            });
            $scope.compareRule = function(target) {
                return surveyFactory.compareRule(target);
            };
        }
    };
})

.directive('surveyNode', function(surveyFactory) {
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
                        <span class="md-headline" ng-bind-html="node.title.split('\n').join('<br/>')"></span>
                        </md-card-title-text>
                    </md-card-title>
                    <md-card-content>
                        <survey-question node="node"  ng-hide="compareRule(node)"></survey-question>
                    </md-card-content>
                    <md-card-actions layout="row" layout-align="end center">

                    </md-card-actions>
                    <md-progress-linear md-mode="indeterminate" ng-disabled="!node.saving"></md-progress-linear>
                </md-card>
                <survey-node ng-if="childrens" ng-repeat="children in childrens" node="children" ng-hide="compareRule(children)"></survey-node>
            </div>
        `,
        controller: function($scope) {

            //$scope.node.saving = true;
            //$scope.node = {saving: true};

            this.addChildren = function(childrens) {
                $scope.childrens = childrens;
            };

            $scope.compareRule = function(target) {
                return surveyFactory.compareRule(target);
            };

        }
    };
})

.directive('surveyInput', function($compile, surveyFactory) {
    return {
        priority: 1,
        restrict: 'A',
        require: 'ngModel',
        controller: function($scope, $attrs) {
            $scope.saveAnswer = function(parent, value) {
                $scope.question.childrens = {};
                surveyFactory.get('getChildren', {question: $scope.question, parent: parent, value: value, trigger: 'saveAnswer'}, $scope.node).then(function(response) {
                    $scope.question.childrens = response.nodes;
                    surveyFactory.answers = response.answers;
                });
            };

            var oldAnswer = surveyFactory.answers['C' + $scope.question.id] ? surveyFactory.answers['C' + $scope.question.id] : null;

            $scope.answer = $scope.node.answers.length > 0 ? $scope.node.answers.find(function(answer) {
                return answer.value == oldAnswer;
            }) : oldAnswer;

            var parent = $scope.$eval($attrs.parent);

            if (parent) {
                surveyFactory.get('getChildren', {question: $scope.question, parent: parent, trigger: 'getNode'}, $scope.node).then(function(response) {
                    $scope.question.childrens = response.nodes;
                });
            }

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
            $scope.saveTextNgOptions = {updateOn: 'default blur', debounce:{default: 1000, blur: 0}};
            $scope.answers = surveyFactory.answers;
            //$scope.answers = $filter('filter')(node.answers, {id: });

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

            /*$scope.compareRule = function(question) {
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
            };*/

            $scope.compareRule = function(target) {
                return surveyFactory.compareRule(target);
            };
        }
    };
})

.directive('stringConverter', function() {
    return {
        priority: 1,
        restrict: 'A',
        require: 'ngModel',
        link: function(scope, element, attr, ngModel) {
            function toView(value) {
                return value*1;
            }

            ngModel.$formatters.push(toView);
        }
    };
});