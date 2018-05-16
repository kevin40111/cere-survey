'use strict';

angular.module('ngSurvey', ['ngSurvey.directives', 'ngSurvey.factories']);

angular.module('ngSurvey.factories', []).factory('surveyFactory', function($http, $q) {
    var answers = {};
    var skips = {};

    return {
        answers: answers,
        skips: skips,
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
        isSkip: function(target) {
            return target.skiper && skips[target.skiper.id];
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
                    <md-progress-linear md-mode="indeterminate"></md-progress-linear>
                </div>
                <div ng-if="page">
                    <survey-page page="page"></survey-page>
                    <md-button class="md-raised md-primary" ng-click="nextPage()" ng-disabled="book.saving" aria-label="繼續">繼續</md-button>
                </div>
                <md-card ng-if="!page && !book.saving" style="width:800px;margin:0 auto; text-align:center">
                    <md-card-title>
                        <md-card-title-text><span class="md-headline">本問卷填答完畢</span></md-card-title-text>
                    </md-card-title>
                    <md-button ng-repeat="url in urls" class="md-raised md-primary" href="{{url}}" target="_blank" aria-label="填寫加掛題本" >
                        填寫加掛題本
                    </md-button>
                </md-card>
                <div class="ql-editor" ng-bind-html="trustAsHtml(book.footer)"></div>
            </div>
        `,
        controller: function($scope, $sce) {
            $scope.trustAsHtml = function(string) {
                return $sce.trustAsHtml(string);
            };
            surveyFactory.get('getPage', {book: $scope.book}, $scope.book).then(function(response) {
                $scope.page = response.page;
                $scope.urls = response.urls;
                $scope.book.saving = false;
            });

            $scope.nextPage = function() {
                surveyFactory.get('nextPage', {page: $scope.page}, $scope.book).then(function(response) {
                    if (response.missings && response.missings.length > 0) {
                        alert('有尚未填答題目');
                    } else {
                        $scope.page = response.page;
                        $scope.urls = response.urls;
                        $scope.book.saving = false;
                    }
                });
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
                <survey-node ng-repeat="node in nodes" node="node" ng-if="!isSkip(node)"></survey-node>
            </div>
        `,
        controller: function($scope, $http) {

            $scope.isSkip = surveyFactory.isSkip;

            $scope.$watch('page', function() {
                surveyFactory.get('getNodes', {page: $scope.page}, $scope.page).then(function(response) {
                    $scope.nodes = response.nodes;
                    angular.extend(surveyFactory.answers, response.answers);
                    angular.extend(surveyFactory.skips, response.skips);
                });
            });
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
                <img ng-repeat="image in node.images" ng-src="upload/{{image.serial}}" alt="Description" style="width:940px" />
                <md-card>
                    <md-card-title>
                        <md-card-title-text>
                        <span class="md-headline ql-editor" ng-bind-html="trustAsHtml(node.title.split('\n').join('<br/>'))"></span>
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
        controller: function($scope, $sce) {
            $scope.trustAsHtml = function(string) {
                return $sce.trustAsHtml(string);
            };
            //$scope.node.saving = true;
            //$scope.node = {saving: true};

            this.addChildren = function(childrens) {
                $scope.childrens = childrens;
            };
        }
    };
})

.directive('surveyInput', function($compile, surveyFactory) {
    return {
        priority: 1,
        restrict: 'A',
        require: 'ngModel',
        controller: function($scope, $attrs, $mdToast) {
            $scope.saveTextNgOptions = {updateOn: 'default blur', debounce:{default: 1000, blur: 0}};
            $scope.answers = surveyFactory.answers;

            $scope.saveAnswer = function(value) {
                $scope.question.childrens = {};
                surveyFactory.get('saveAnswer', {question: $scope.question, value: value}, $scope.node).then(function(response) {
                    angular.extend(surveyFactory.answers, response.dirty);
                    if (response.messages) {
                        var txt = "";
                        response.messages.forEach(function(message) {
                            txt += message+'\n';
                        });
                        $mdToast.show(
                            $mdToast.simple().textContent(txt).hideDelay(3000)
                        );
                    } else {
                        angular.extend(surveyFactory.skips, response.skips);
                        getChildrens();
                    }
                });
            };

            if ($scope.answers[$scope.question.id]) {
                getChildrens();
            }

            function getChildrens() {
                surveyFactory.get('getChildrens', {question: $scope.question}, $scope.node).then(function(response) {
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
                scope.isSkip = surveyFactory.isSkip;
                scope.addChildren = ctrl.addChildren;
                //var contents = iElement.contents().remove();
                var type = scope.node.type;
                compiledContents[type] = $compile($templateCache.get(type));
                compiledContents[type](scope, function(clone, scope) {
                    iElement.append(clone);
                });
            };
        },
        controller: function($scope) {
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