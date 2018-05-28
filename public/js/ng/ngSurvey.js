'use strict';

angular.module('ngSurvey', ['ngSurvey.directives', 'ngSurvey.factories']);

angular.module('ngSurvey.factories', []).factory('surveyFactory', function($http) {
    var answers = {};
    var skipers = {};

    return {
        get: function(url, data, node = {}) {
            node.saving = true;
            return $http({method: 'POST', url: url, data: data, timeout: deferred.promise})
            .then(function(response) {
                node.saving = false;
                return response.data;
            });
        },
        next: function(page) {
            return $http({method: 'POST', url: 'nextPage', data: {page: page, answers: answers}});
        },
        sync: function(node, contents) {
            node.saving = true;
            return $http({method: 'POST', url: 'sync', data: {node: node, contents: contents, answers: answers}})
            .then(function(response) {
                node.saving = false;
                angular.extend(answers, response.data.dirty);
                angular.extend(skipers, response.data.skipers);
                if (response.data.childrens) {
                    node.childrens = response.data.childrens;
                }
                return {contents: response.data.contents, messages: response.data.messages};
            });
        },
        isSkip: function(target) {
            return target.skiper && skipers[target.skiper.id];
        }
    };
});

angular.module('ngSurvey.directives', [])

.directive('surveyBook', function(surveyFactory) {
    return {
        restrict: 'E',
        scope: {
            book: '=',
        },
        template:  `
            <md-content flex>
                <div layout="row" layout-align="space-around" ng-if="book.saving">
                    <md-progress-linear md-mode="indeterminate"></md-progress-linear>
                </div>
                <div flex layout="row" layout-align="center start" ng-if="page">
                    <survey-page page="!page.closed" flex-xs="100" flex-gt-sm="80" flex-gt-md="50" flex-gt-lg="40"></survey-page>
                    <survey-extend ng-if="page.closed" flex-xs="100" flex-gt-sm="80" flex-gt-md="50" flex-gt-lg="40"></survey-extend>
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
            </md-content>
        `,
        controller: function($scope, $http, $sce) {
            $scope.trustAsHtml = function(string) {
                return $sce.trustAsHtml(string);
            };
            surveyFactory.get('getPage', {book: $scope.book}, $scope.book).then(function(response) {
                $scope.page = response.page;
                $scope.book.saving = false;
            });

            $http({method: 'POST', url: 'getBook', data: {}}).then(function(response) {
                $scope.book = response.data.book
            });
        }
    };
})

.directive('surveyPage', function(surveyFactory) {
    return {
        restrict: 'E',
        scope: {
            page: '=',
        },
        template:  `
            <md-card>
                <md-card-header ng-repeat-start="node in nodes" ng-if="false"></md-card-header>
                <img ng-repeat="image in node.images" ng-src="upload/{{image.serial}}" alt="標頭圖片" />
                <survey-node ng-repeat-end node="node" ng-if="!isSkip(node)"></survey-node>
                <md-card-actions layout="column" layout-align="start">
                    <md-button class="md-raised md-primary" ng-click="nextPage()" ng-disabled="page.loading" aria-label="繼續">繼續</md-button>
                </md-card-actions>
            </md-card>
        `,
        controller: function($scope, $http) {

            $scope.isSkip = surveyFactory.isSkip;

            $scope.$watch('page', function() {
                surveyFactory.get('getNodes', {page: $scope.page}, $scope.page).then(function(response) {
                    $scope.nodes = response.nodes;
                    $scope.page.closed = response.nodes.length === 0;
                });
            });

            $scope.nextPage = function() {
                surveyFactory.next($scope.page).then(function(response) {
                    if (response.data.missings && response.data.missings.length > 0) {
                        alert('有尚未填答題目');
                    } else {
                        $scope.page = response.data.page;
                        $scope.book.saving = false;
                    }
                });
            };
        }
    };
})

.directive('surveyNode', function($compile, surveyFactory, $templateCache) {
    return {
        restrict: 'E',
        scope: {
            node: '='
        },
        require: '^surveyNode',
        template:  `
            <md-card-title>
                <md-card-title-text>
                    <span class="md-title ql-editor" ng-bind-html="trustAsHtml(node.title.split('\n').join('<br/>'))"></span>
                </md-card-title-text>
            </md-card-title>
        `,
        compile: function(tElement, tAttr) {
            var compiledContents = {};
            return function(scope, iElement, iAttr, ctrl) {
                var type = scope.node.type;
                if (!compiledContents[type]) {
                    compiledContents[type] = $compile($templateCache.get(type));
                }
                compiledContents[type](scope, function(clone, scope) {
                    iElement.append(clone);
                });
            };
        },
        controller: function($scope, $sce, $mdToast) {
            $scope.saveTextNgOptions = {updateOn: 'default blur', debounce:{default: 1000, blur: 0}};
            $scope.isSkip = surveyFactory.isSkip;
            $scope.contents = {};

            $scope.trustAsHtml = function(string) {
                return $sce.trustAsHtml(string);
            };

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

.directive('surveyExtend', function() {
    return {
        restrict: 'E',
        scope: true,
        template:  `
            <md-card>
                <md-card-title>
                    <md-card-title-text><span class="md-headline">本問卷填答完畢</span></md-card-title-text>
                </md-card-title>
                <md-button ng-repeat="url in urls" class="md-raised md-primary" href="{{url}}" target="_blank" aria-label="填寫加掛題本" >
                    填寫加掛題本
                </md-button>
            </md-card>
        `,
        controller: function($scope, $http) {
            $http({method: 'GET', url: 'getUrls', data: {}}).then(function(response) {
                $scope.urls = response.data.urls
            });
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
