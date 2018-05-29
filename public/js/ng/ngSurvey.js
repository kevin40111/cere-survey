'use strict';

angular.module('ngSurvey', ['ngSurvey.directives', 'ngSurvey.factories']);

angular.module('ngSurvey.factories', []).factory('surveyFactory', function($http) {
    var answers = {};
    var skipers = {};
    var page = {};

    return {
        page: page,
        get: function() {
            page.loading = true;
            return $http({method: 'POST', url: 'getPage', data: {}}).then(function(response) {
                page.closed = response.data.nodes.length === 0;
                page.loading = false;
                return response.data;
            });
        },
        next: function() {
            page.loading = true;
            return $http({method: 'POST', url: 'nextPage', data: {answers: answers}}).then(function(response) {
                page.closed = response.data.closed;
                page.loading = false;
                return response.data;
            });
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
        scope: true,
        template:  `
            <div flex layout="row" layout-align="center center" ng-if="page.loading">
                <md-progress-circular md-mode="indeterminate"></md-progress-circular >
            </div>
            <md-content flex ng-hide="page.loading">
                <div flex layout="row" layout-align="center start">
                    <survey-page ng-if="!page.closed" flex-xs="100" flex-gt-sm="80" flex-gt-md="50" flex-gt-lg="40"></survey-page>
                    <survey-extend ng-if="page.closed" flex-xs="100" flex-gt-sm="80" flex-gt-md="50" flex-gt-lg="40"></survey-extend>
                </div>
                <div>
                    <div class="ql-editor" ng-bind-html="trustAsHtml(book.footer)"></div>
                </div>
            </md-content>
        `,
        controller: function($scope, $http, $sce) {
            $scope.page = surveyFactory.page;
            $scope.trustAsHtml = function(string) {
                return $sce.trustAsHtml(string);
            };

            $http({method: 'POST', url: 'getBook', data: {}}).then(function(response) {
                $scope.book = response.data.book
            });
        }
    };
})

.directive('surveyPage', function(surveyFactory) {
    return {
        restrict: 'E',
        scope: true,
        template:  `
            <md-card>
                <md-card-header ng-repeat-start="node in nodes" ng-if="false"></md-card-header>
                <img ng-repeat="image in node.images" ng-src="upload/{{image.serial}}" alt="標頭圖片" />
                <survey-node ng-repeat-end node="node" ng-if="!isSkip(node)"></survey-node>
                <md-card-actions layout="column" layout-align="start">
                    <md-button class="md-raised md-primary" ng-click="nextPage($event)" ng-disabled="page.loading" aria-label="繼續">繼續</md-button>
                </md-card-actions>
            </md-card>
        `,
        controller: function($scope, $element, $mdDialog) {
            $scope.isSkip = surveyFactory.isSkip;
            $scope.page = surveyFactory.page;

            surveyFactory.get().then(function(data) {
                $scope.nodes = data.nodes;
            });

            $scope.nextPage = function(event) {
                surveyFactory.next().then(function(data) {
                    if (data.missings && data.missings.length > 0) {
                        $mdDialog.show(
                            $mdDialog.alert().textContent('有題目尚未填答').openFrom(event).targetEvent(event).ok('返回')
                        );
                    } else {
                        $scope.nodes = data.nodes;
                    }
                });
            };
        }
    };
})

.directive('surveyNode', function($compile, $templateCache, surveyFactory) {
    return {
        restrict: 'E',
        scope: {
            node: '='
        },
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
        controller: function($scope, $element, $sce, $mdToast) {
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
                            $mdToast.simple({parent: $element}).textContent(txt).position('left top').hideDelay(1000)
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
