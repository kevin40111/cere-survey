'use strict';

angular.module('ngSurvey', ['ngSurvey.directives', 'ngSurvey.factories']);

angular.module('ngSurvey.factories', []).factory('surveyFactory', function($http, $q) {
    var answers = {};
    var skipers = {};

    return {
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
        next: function(page) {
            return $http({method: 'POST', url: 'nextPage', data: {page: page, answers: answers}});
        },
        sync: function(node, contents) {
            var deferred = $q.defer();

            node.saving = true;
            $http({method: 'POST', url: 'sync', data: {node: node, contents: contents, answers: answers}})
            .then(function(response) {
                node.saving = false;
                angular.extend(answers, response.data.dirty);
                angular.extend(skipers, response.data.skipers);
                if (response.data.childrens) {
                    node.childrens = response.data.childrens;
                }
                deferred.resolve({contents: response.data.contents, messages: response.data.messages});
            });

            return deferred.promise;
        },
        isSkip: function(target) {
            return target.skiper && skipers[target.skiper.id];
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
                surveyFactory.next($scope.page).then(function(response) {
                    if (response.data.missings && response.data.missings.length > 0) {
                        alert('有尚未填答題目');
                    } else {
                        $scope.page = response.data.page;
                        $scope.urls = response.data.urls;
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
                <md-card>
                    <img ng-repeat="image in node.images" ng-src="upload/{{image.serial}}" alt="標頭圖片" />
                    <md-card-title>
                        <md-card-title-text>
                        <span class="md-headline ql-editor" ng-bind-html="trustAsHtml(node.title.split('\n').join('<br/>'))"></span>
                        </md-card-title-text>
                    </md-card-title>
                    <md-card-content>
                        <survey-question node="node"></survey-question>
                    </md-card-content>
                    <md-progress-linear md-mode="indeterminate" ng-disabled="!node.saving"></md-progress-linear>
                </md-card>
            </div>
        `,
        controller: function($scope, $sce) {
            $scope.trustAsHtml = function(string) {
                return $sce.trustAsHtml(string);
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
                //var contents = iElement.contents().remove();
                var type = scope.node.type;
                compiledContents[type] = $compile($templateCache.get(type));
                compiledContents[type](scope, function(clone, scope) {
                    iElement.append(clone);
                });
            };
        },
        controller: function($scope, $mdToast) {
            $scope.saveTextNgOptions = {updateOn: 'default blur', debounce:{default: 1000, blur: 0}};
            $scope.isSkip = surveyFactory.isSkip;
            $scope.contents = {};

            $scope.sync = function() {
                surveyFactory.sync($scope.node, $scope.contents).then(function(data) {
                    angular.extend($scope.contents, data.contents);
                    if (data.messages) {
                        var txt = "";
                        data.messages.forEach(function(message) {
                            txt += message+'\n';
                        });
                        $mdToast.show(
                            $mdToast.simple().textContent(txt).hideDelay(3000)
                        );
                    }
                });
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