'use strict';

angular.module('ngEditor', ['ngEditor.directives', 'ngEditor.factories']);

angular.module('ngEditor.factories', []).factory('editorFactory', function($http, $q, $timeout) {

    var types = {};
    var typesInPage = [];

    return {
        types: types,
        typesInPage: typesInPage,
        ajax: ajax,
        move: move
    };

    function ajax(url, data, node = {}) {
        var deferred = $q.defer();

        node.saving = true;
        $http({method: 'POST', url: url, data: data, timeout: deferred.promise})
        .success(function(data) {
            deferred.resolve(data);
        }).error(function(e) {
            deferred.reject();
        });

        deferred.promise.finally(function() {
            node.saving = false;
        });

        return deferred.promise;
    }

    function move(items, item, offset) {
        item.move = {up: offset < 0, down: offset > 0, leave: true, active: false};
        ajax('setPosition', {item: item, offset: offset}, item).then(function() {
            var index = items.indexOf(item);
            item.move.active = true;
            $timeout(function () {
                items.splice(index, 1);
                items.splice(index + offset, 0, item);
                angular.extend(item.move, {leave: false, enter: true, active: false});
                $timeout(function () {
                    item.move.active = true;
                }, 0);
            }, 200);
        });
    }
});

angular.module('ngEditor.directives', ['ngQuill'])

.config(['ngQuillConfigProvider', function (ngQuillConfigProvider) {
    ngQuillConfigProvider.set(null, null, 'custom placeholder')
}])

.directive('surveyBook', function(editorFactory) {
    return {
        restrict: 'E',
        replace: true,
        transclude: true,
        scope: {
            book: '='
        },
        template:  `
            <md-content flex="100" layout="column">
                <md-toolbar md-colors="{background: 'grey-100'}">
                    <div class="md-toolbar-tools">
                        <span flex></span>
                        <div class="ui breadcrumb" style="width:960px">
                            <a class="section" ng-class="{active: $last}" ng-repeat-start="path in paths" ng-click="getNodes(path)"
                               style="max-width: 250px;text-overflow: ellipsis;overflow: hidden;white-space: nowrap">{{path.title}}</a>
                            <div ng-repeat-end class="divider"> / </div>
                        </div>
                        <span flex></span>
                        <div ng-transclude></div>
                        <md-button class="md-icon-button md-primary" href="/surveyDemo/{{book.id}}/page" target="_blank"><md-tooltip md-direction="bottom">預覽</md-tooltip><md-icon>visibility</md-icon></md-button>
                        <md-button class="md-icon-button md-primary" href="exportSheet" target="_blank"><md-tooltip md-direction="bottom">下載填答值</md-tooltip><md-icon>file_download</md-icon></md-button>
                    </div>
                </md-toolbar>
                <md-divider></md-divider>
                <div layout="column" layout-align="start center" style="height:100%;overflow-y:scroll">
                    <div style="width:960px">
                        <md-card ng-if="parent">
                            <md-card-header md-colors="{background: 'grey'}">
                                <div flex layout="row" layout-align="start center">
                                    <div>
                                        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="description"></md-icon>
                                    </div>
                                    <div style="margin: 0 0 0 16px">{{parent.title}}</div>
                                    <span flex></span>
                                </div>
                            </md-card-header>
                            <md-card-content>
                                <div>
                                    <md-list>
                                        <md-list-item ng-repeat="item in parent.items" md-colors="{'background-color': item.selected ? 'grey': 'grey-A100'}"">
                                            <div flex layout="row" layout-align="start center">
                                                選項{{$index+1}}:{{item.title}}
                                                <span flex></span>
                                                <div>
                                                <md-button class="md-raised md-warn md-button md-ink-ripple" ng-if="item.selected" ng-click="getNodes(paths[paths.length-2])">返回上一層</md-button>
                                                </div>
                                            </div>
                                        </md-list-item>
                                    </md-list>
                                </div>
                            </md-card-content>
                        </md-card>
                        <md-card ng-if="nodes.length === 0">
                            <md-card-actions>
                                <md-menu>
                                    <md-button aria-label="新增問題" ng-click="$mdMenu.open($event)">新增問題</md-button>
                                    <md-menu-content width="3">
                                        <md-menu-item ng-repeat="type in types">
                                            <md-button ng-click="addNode(type, 0)"><md-icon md-svg-icon="{{type.icon}}"></md-icon>{{type.title}}</md-button>
                                        </md-menu-item>
                                    </md-menu-content>
                                </md-menu>
                            </md-card-actions>
                        </md-card>
                        <survey-node ng-class="[{deleting: node.deleting}, node.move]" ng-repeat="node in nodes" node="node" index="$index" first="$first" last="$last"></survey-node>
                        <md-card ng-if="paths.length == 1">
                            <md-card-header md-colors="{background: 'blue'}">
                                <div flex layout="row" layout-align="start center">
                                    <div>
                                        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="info-outline"></md-icon>
                                    </div>
                                    <div style="margin: 0 0 0 16px">底頁說明欄</div>
                                    <span flex></span>
                                </div>
                            </md-card-header>
                            <md-card-content>
                                <ng-quill-editor placeholder="說明" ng-model="book.footer"></ng-quill-editor>
                            </md-card-content>
                            <md-card-actions>
                                <md-button ng-click="saveBookFooter()">儲存</md-button>
                            </md-card-actions>
                        </md-card>
                    </div>
                </div>
                <md-sidenav class="md-sidenav-right" md-component-id="survey-skips" md-is-open="skipSetting" style="min-width:40%">
                    <survey-skips ng-if="skipSetting" paths="paths" skip-target="skipTarget" book="book"></survey-skips>
                </md-sidenav>
            </md-content>
        `,
        controller: function($scope, $filter, $mdSidenav) {
            $scope.skipSetting = false;
            editorFactory.types = $scope.book.types;
            editorFactory.typesInPage = $filter('filter')(Object.values(editorFactory.types), {disabled: '!'})
            $scope.types = editorFactory.typesInPage;

            this.getNodes = function(root) {
                editorFactory.ajax('getNodes', {root: root}).then(function(response) {
                    $scope.root = root;
                    $scope.nodes = response.nodes;
                    $scope.paths = response.paths;
                    $scope.parent = response.parent;
                });
            };

            this.addNode = function(type, position) {
                editorFactory.ajax('createNode', {parent: $scope.root, attributes: {type: type.name, position: position}}, {}).then(function(response) {
                    $scope.nodes.splice(position, 0, response.node);
                });
            };
            $scope.addNode = this.addNode;

            this.removeNode = function(node) {
                node.deleting = true;
                editorFactory.ajax('removeNode', {node: node}, node).then(function(response) {
                    if (response.deleted) {
                        $scope.nodes.splice($scope.nodes.indexOf(node), 1);
                    }
                });
            };

            this.move = function(node, offset) {
                editorFactory.move($scope.nodes, node, offset);
            };

            $scope.getNodes = this.getNodes;

            $scope.getNodes($scope.book);

            $scope.lockBook = function() {
                editorFactory.ajax('lockBook', {}, $scope.book).then(function(response) {
                    $scope.book.lock = response.lock;
                });
            };

            $scope.saveBookFooter = function() {
                editorFactory.ajax('saveBookFooter',{'footer':$scope.book.footer}).then(function(response) {
                    $scope.book.footer = response.footer;
                });
            };

            this.toggleSidenavRight = function(skipTarget) {
                $scope.skipTarget = skipTarget;
                $mdSidenav('survey-skips').toggle();
            };
        }
    };
})

.directive('surveyNode', function(editorFactory) {
    return {
        restrict: 'E',
        replace: true,
        transclude: false,
        scope: {
            node: '=',
            index: '=',
            first: '=',
            last: '='
        },
        template:  `
            <md-card>
                <div ng-repeat="image in node.images">
                    <banner-image node="node" image="image" index=$index></banner-image>
                </div>
                <md-card-header md-colors="{background: 'indigo'}">
                    <question-bar></question-bar>
                </md-card-header>
                <md-card-content>
                    <md-input-container class="md-block" ng-if="type.editor.title">
                        <label>{{type.editor.title}}</label>
                        <ng-quill-editor placeholder="{{type.editor.title}}" ng-if="node.type!='page'" ng-model="node.title" on-content-changed="contentChanged(editor, node)"></ng-quill-editor>
                        <textarea ng-model="node.title" ng-if="node.type=='page'" md-maxlength="2000" rows="1" ng-model-options="{updateOn: 'blur'}" md-select-on-focus ng-change="saveNodeTitle(node)"></textarea>
                    </md-input-container>
                    <div ng-if="type.editor.questions.amount" questions="node.questions" node="node"></div>
                    <md-divider ng-if="type.editor.questions.amount && type.editor.answers"></md-divider>
                    <div ng-if="type.editor.answers" answers="node.answers" node="node"></div>
                    <div ng-if="type.editor.uploadFile" gear-bar node="node"></div>
                </md-card-content>
                <md-card-actions>
                    <md-menu>
                        <md-button aria-label="新增" ng-click="$mdOpenMenu($event)">新增</md-button>
                        <md-menu-content width="3">
                        <md-menu-item ng-repeat="type in getTypesArray()">
                            <md-button ng-click="addNode(type, index+1)"><md-icon md-svg-icon="{{type.icon}}"></md-icon>{{type.title}}</md-button>
                        </md-menu-item>
                        </md-menu-content>
                    </md-menu>
                    <md-button ng-if="type.editor.enter" ng-click="getNodes(node)">編輯此頁</md-button>
                </md-card-actions>
                <md-progress-linear md-mode="indeterminate" ng-disabled="!node.saving"></md-progress-linear>
            </md-card>
        `,
        require: '^surveyBook',
        link: function(scope, iElement, iAttrs, surveyBookCtrl) {
            scope.addNode = surveyBookCtrl.addNode;
            scope.removeNode = surveyBookCtrl.removeNode;
            scope.getNodes = surveyBookCtrl.getNodes;
            scope.move = surveyBookCtrl.move;
            scope.toggleSidenavRight = surveyBookCtrl.toggleSidenavRight;
        },
        controller: function($scope, $timeout) {

            var pendingDebounce = null;

            $scope.contentChanged = function (editor, node) {
                $timeout.cancel(pendingDebounce);
                pendingDebounce = $timeout(function() {
                    $scope.saveNodeTitle(node);
                }, 2000);
            }
            $scope.type = editorFactory.types[$scope.node.type];

            $scope.getTypesArray = function() {
                return $scope.node.type == 'page' ? [editorFactory.types['page']] : editorFactory.typesInPage;
            };

            $scope.saveNodeTitle = function(node) {
                editorFactory.ajax('saveNodeTitle', {node: node}, node).then(function(response) {
                    angular.extend(node, response.node);
                });
            };

        }
    };
})
.directive('gearBar', function(editorFactory){
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        scope: {
            node: '=',
        },
        template:`
                <div >
                    <form style="display:none">
                        <input type="file" id="file_upload" nv-file-select uploader="uploader" />
                    </form>
                    <label for="file_upload" class="ui basic mini button" ng-class="{loading: uploading}"><i class="icon upload"></i>檔案上傳</label>
                </div>
                     `
         ,
         require: '^surveyBook',
         controller: function($scope, FileUploader) {
              $scope.uploader = new FileUploader({
                 alias: 'file_upload',
                 url: 'ajax/saveGearQuestion',
                 autoUpload: true,
                 removeAfterUpload: true
             });

             $scope.uploader.onBeforeUploadItem = function(item) {
                 $scope.loading = true;
                 var formData = [{
                     node_id: $scope.node.id
                 }];
                 Array.prototype.push.apply(item.formData, formData);
             };

             $scope.uploader.onCompleteItem = function(fileItem, response, status, headers) {
                 $scope.loading = false;
                 angular.extend($scope.node, response);
                 document.forms[0].reset();
             };
         }
    };
})

.directive('bannerImage', function(editorFactory){
    return  {
        restrict: 'E',
        replace: true,
        transclude: false,
        scope: {
            node: '=',
            image: '=',
            index:'=',
        },
        template:`
            <md-card>
                <md-card-header md-colors="{background: 'teal'}">
                <span flex="" class="flex"></span>
                <md-button class="md-icon-button" aria-label="刪除" ng-click="removeBanner(ndoe)">
                        <md-icon md-svg-icon="clear" style="color:#ffffff"></md-icon>
                    </md-button>
                </md-card-header>
                <img ng-src=/upload/get/{{image.serial}} class="md-card-image" alt="image caption"/>
            </md-card>
        `,
        controller: function($scope) {
            $scope.removeBanner = function (node) {
                editorFactory.ajax('removeBanner', {image:$scope.image.pivot}).then(function(response) {
                    $scope.node.images.splice($scope.index, 1);
                });
            }
        }
    };
})

.directive('questionBar', function(editorFactory, FileUploader) {
    return {
        restrict: 'E',
        replace: true,
        transclude: false,
        template: `
            <div flex layout="row" layout-align="start center">
                <div>
                    <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="{{type.icon}}"></md-icon>
                </div>
                <div style="margin: 0 0 0 16px"><span ng-if="type.editor.enter">第 {{index+1}}</span> {{type.title}}</div>

                <span flex></span>

                <div>
                    <label ng-show="node.type" for="{{::$id}}">
                    <md-icon md-svg-icon="grally"></md-icon>
                        <input id="{{::$id}}"  style="display:none"  type="file" multiple nv-file-select uploader="uploader" />
                    </label>
                    <div class="ui input" ng-if="node.open.moving">
                        <input type="text" ng-model="settedPage" placeholder="輸入移動到的頁數..." />
                        <md-button class="md-icon-button no-animate" ng-disabled="node.saving" aria-label="移動到某頁" ng-click="setPage(node, settedPage)">
                            <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="send"></md-icon>
                        </md-button>
                    </div>
                    <md-button class="md-icon-button" aria-label="上移" ng-disabled="first" ng-click="move(node, -1)">
                        <md-tooltip md-direction="bottom">上移</md-tooltip>
                        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="arrow-drop-up"></md-icon>
                    </md-button>
                    <md-button class="md-icon-button" aria-label="下移" ng-disabled="last" ng-click="move(node, 1)">
                        <md-tooltip md-direction="bottom">下移</md-tooltip>
                        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="arrow-drop-down"></md-icon>
                    </md-button>
                    <md-button class="md-icon-button" md-colors="{backgroundColor: node.rule ? 'blue-300' : 'primary'}" aria-label="跳過此題" ng-disabled="node.saving" ng-click="toggleSidenavRight(node)">
                        <md-tooltip md-direction="bottom">跳過此題</md-tooltip>
                        <md-icon md-colors="{color: 'grey-A100'}">visibility_off</md-icon>
                    </md-button>
                    <md-button class="md-icon-button" aria-label="刪除" ng-disabled="node.saving || (node.type === 'page' && first && last)" ng-click="removeNode(node)">
                        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="delete"></md-icon>
                    </md-button>
                </div>
            </div>
        `,
        controller: function($scope){
            $scope.uploader = new FileUploader({
                alias: 'file_upload',
                url: 'ajax/uploaderBanner',
                autoUpload: true,
                removeAfterUpload: true,
            });

            $scope.uploader.onBeforeUploadItem = function (item) {
                item.formData.push($scope.node);
            }

            $scope.uploader.onCompleteItem = function(fileItem, response, status, headers) {
                $scope.node.images = response.images;
                $scope.uploader.destroy();
                alert(response.message)
            };
        }
    };
})

.directive('answers', function(editorFactory) {
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        scope: {
            answers: '=',
            node: '='
        },
        template:  `
            <md-list>
                <md-list-item ng-repeat="answer in answers" ng-class="[{deleting: answer.deleting}, answer.move]" style="margin-left:15px;">
                    <span style="font-style: oblique;margin-right: 10px">{{$index+1}}. </span>
                    <div flex>
                        <div class="ui transparent fluid input" ng-class="{loading: answer.saving}">
                            <input type="text" placeholder="輸入選項名稱..." ng-model="answer.title" ng-model-options="saveTitleNgOptions" ng-change="saveAnswerTitle(answer)" />
                        </div>
                    </div>
                    <md-button class="md-secondary"  ng-if="types[node.type].editor.answerChilderns"  aria-label="設定子題" ng-click="getNodes(answer)">設定子題</md-button>
                    <md-button class="md-secondary md-icon-button" ng-click="move(answer, -1)" aria-label="上移" ng-disabled="$first">
                        <md-tooltip md-direction="left">上移</md-tooltip>
                        <md-icon md-svg-icon="arrow-drop-up"></md-icon>
                    </md-button>
                    <md-button class="md-secondary md-icon-button" ng-click="move(answer, 1)" aria-label="下移" ng-disabled="$last">
                        <md-tooltip md-direction="left">下移</md-tooltip>
                        <md-icon md-svg-icon="arrow-drop-down"></md-icon>
                    </md-button>
                    <md-button md-colors="{backgroundColor: answer.rule ? 'blue-300' : 'grey-A100'}" class="md-secondary md-icon-button" ng-click="toggleSidenavRight(answer)" aria-label="設定限制">
                        <md-tooltip>設定限制</md-tooltip>
                        <md-icon md-colors="{color: answer.rule ? 'grey-A100' : 'grey-600'}">visibility_off</md-icon>
                    </md-button>
                    <md-icon class="md-secondary" aria-label="刪除選項" md-svg-icon="delete" ng-click="removeAnswer(answer)"></md-icon>
                </md-list-item>
                <md-list-item ng-if="node.answers.length < types[node.type].editor.answers" ng-click="createAnswer()">
                    <p md-colors="{color:'grey'}">新增選項</p>
                </md-list-item>
            </md-list>
        `,
        require: '^surveyBook',
        link: function(scope, iElement, iAttrs, surveyBookCtrl) {
            scope.getNodes = surveyBookCtrl.getNodes;
            scope.toggleSidenavRight = surveyBookCtrl.toggleSidenavRight;
        },
        controller: function($scope) {

            $scope.types = editorFactory.types;
            $scope.saveTitleNgOptions = {updateOn: 'default blur', debounce:{default: 2000, blur: 0}};

            $scope.createAnswer = function() {
                var attributes = {position: $scope.answers.length};
                editorFactory.ajax('createAnswer', {node: $scope.node, attributes: attributes}, $scope.node).then(function(response) {
                    $scope.node.answers.push(response.answer);
                });
            };

            $scope.saveAnswerTitle = function(answer) {
                editorFactory.ajax('saveAnswerTitle', {answer: answer}, $scope.node).then(function(response) {
                    angular.extend(answer, response.answer);
                });
            };

            $scope.removeAnswer = function(answer) {
                answer.deleting = true;
                editorFactory.ajax('removeAnswer', {answer: answer}, answer).then(function(response) {
                     if (response.deleted) {
                        $scope.node.answers.splice($scope.node.answers.indexOf(answer), 1);
                    }
                });
            };

            $scope.move = function(answer, offset) {
                editorFactory.move($scope.answers, answer, offset);
            };
        }
    };
})

.directive('questions', function(editorFactory) {
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        scope: {
            questions: '=',
            node: '='
        },
        template:  `
            <md-list>
                <md-subheader class="md-no-sticky" ng-if="node.type=='checkbox'">
                    <div md-colors="{color: 'grey-700'}" layout="row" layout-align="end center">
                        此題最多勾選
                        <md-select ng-model="node.limit_rule.expressions[0].value" aria-label="number" layout="row" style="text-align:center; margin:0px;" ng-change="saveRule(node, 'limit')">
                            <md-option ng-repeat="question in node.questions" ng-value="$index" ng-if="$index>0">{{$index}}</md-option>
                            <md-option ng-value="undefined">{{node.questions.length}}</md-option>
                        </md-select>
                        個選項
                    </div>
                </md-subheader>
                <md-list-item ng-repeat="question in node.questions" ng-class="[{deleting: question.deleting}, question.move]">
                    <p class="ui transparent fluid input" ng-class="{loading: question.saving}">
                        <input type="text" placeholder="輸入{{types[node.type].editor.questions.text}}" ng-model="question.title" ng-model-options="saveTitleNgOptions" ng-change="saveQuestionTitle(question)"/>
                    </p>
                    <md-switch class="md-primary" md-no-ink aria-label="all false" ng-model="question.none_above_rule.expressions[0].value" ng-false-value="undefined" ng-true-value="'noneAbove'" ng-if="node.type=='checkbox'" ng-class="{noneAbove: question.none_above_rule.expressions[0].value}" ng-change="saveRule(question, 'none_above')">
                        以上皆非
                    </md-switch>
                    <md-button class="md-secondary" ng-if="types[node.type].editor.questions.childrens" aria-label="設定子題" ng-click="getNodes(question)">設定子題</md-button>
                    <md-button class="md-secondary md-icon-button" ng-click="move(question, -1)" aria-label="上移" ng-disabled="$first">
                        <md-tooltip md-direction="left">上移</md-tooltip>
                        <md-icon md-svg-icon="arrow-drop-up"></md-icon>
                    </md-button>
                    <md-button class="md-secondary md-icon-button" ng-click="move(question, 1)" aria-label="下移" ng-disabled="$last">
                        <md-tooltip md-direction="left">下移</md-tooltip>
                        <md-icon md-svg-icon="arrow-drop-down"></md-icon>
                    </md-button>
                    <md-button  md-colors="{backgroundColor: question.rule ? 'blue-300' : 'grey-A100'}" class="md-secondary md-icon-button" ng-click="toggleSidenavRight(question)" aria-label="設定限制" ng-if="(node.type == 'scale') || (node.type == 'checkbox')">
                        <md-tooltip>設定限制</md-tooltip>
                        <md-icon md-colors="{color: question.rule ? 'grey-A100' : 'grey-600'}">visibility_off</md-icon>
                    </md-button>
                    <md-icon class="md-secondary" aria-label="刪除子題" md-svg-icon="delete" ng-click="removeQuestion(question)"></md-icon>
                </md-list-item>
                <md-list-item ng-if="node.questions.length < types[node.type].editor.questions.amount" ng-click="createQuestion()">
                    <p md-colors="{color:'grey'}">新增{{types[node.type].editor.questions.text}}</p>
                </md-list-item>
            </md-list>
        `,
        require: '^surveyBook',
        link: function(scope, iElement, iAttrs, surveyBookCtrl) {
            scope.getNodes = surveyBookCtrl.getNodes;
            scope.toggleSidenavRight = surveyBookCtrl.toggleSidenavRight;
        },
        controller: function($scope, $http, $filter) {
            $scope.types = editorFactory.types;
            $scope.saveTitleNgOptions = {updateOn: 'default blur', debounce:{default: 2000, blur: 0}};
            $scope.searchLoaded = '';
            $scope.searchText = {};

            $scope.saveRule = function(target, type) {
                if (target[type + '_rule'].expressions[0].value) {
                    $http({method: 'POST', url: 'saveRule', data:{expressions: target[type + '_rule'].expressions, skipTarget: target, type: type}})
                    .success(function(data) {
                        target[type + '_rule'] = data.rule;
                    }).error(function(e) {
                        console.log(e)
                    });
                } else {
                    $http({method: 'POST', url: 'deleteRule', data:{rule_id: target[type + '_rule'].id, skipTarget: target}})
                    .success(function(data) {
                        target[type + '_rule'] = undefined;
                    }).error(function(e) {
                        console.log(e)
                    });
                }
            }

            $scope.createQuestion = function(position) {
                var attributes = {position: $scope.node.questions.length};
                editorFactory.ajax('createQuestion', {node: $scope.node, attributes: attributes}, $scope.node).then(function(response) {
                    $scope.questions.push(response.question);
                });
            };

            $scope.saveQuestionTitle = function(question) {
                editorFactory.ajax('saveQuestionTitle', {question: question}, $scope.node).then(function(response) {
                    angular.extend(question, response.question);
                });
            };

            $scope.removeQuestion = function(question) {
                question.deleting = true;
                editorFactory.ajax('removeQuestion', {question: question}, question).then(function(response) {
                    if (response.deleted) {
                        $scope.node.questions.splice($scope.node.questions.indexOf(question), 1);
                    }
                });
            };

            $scope.move = function(question, offset) {
                editorFactory.move($scope.questions, question, offset);
            };

            $scope.getBooks = function() {
                if (!$scope.books) {
                    var promise = $http({method: 'POST', url: 'getBooks', data:{}})
                    .success(function(data) {
                        $scope.books = data.books;
                    }).error(function() {

                    });

                    return promise;
                }
            };

            $scope.getRowsFiles = function() {
                if (!$scope.rowsFiles) {
                    var promise = $http({method: 'POST', url: '/docs/lists', data:{}})
                    .success(function(data) {
                        $scope.rowsFiles = $filter('filter')(data.docs, {type: '5'}, true);
                    }).error(function() {

                    });

                    return promise;
                }
            };

            $scope.getColumns = function() {
                if (!$scope.columns) {
                    var promise = $http({method: 'POST', url: 'getColumns', data:{file_id: $scope.question.file}})
                    .success(function(data) {
                        $scope.columns = data.columns;
                    }).error(function() {

                    });

                    return promise;
                }
            };

        }
    };
})

.directive('questionPool', function($compile, FileUploader) {
    return {
        restrict: 'E',
        replace: true,
        transclude: false,
        templateUrl: 'pool',
        controller: function($scope, $http, $filter) {

            $scope.getPoolQuestions = function(position) {
                var sBooks = $filter('filter')($scope.sbooks, {checked: true});
                if ($scope.searchLoaded != angular.toJson(sBooks)) {
                    $http({method: 'POST', url: 'getPoolQuestions', data:{type: $scope.question.type, sBooks: sBooks}})
                    .success(function(data) {
                        $scope.pQuestions = data.questions;
                        $scope.question.searching = position;
                        $scope.searchLoaded == angular.toJson(sBooks);
                    }).error(function() {

                    });
                }
            };

            $scope.setPoolQuestion = function(pQuestion) {
                $scope.question.saving = true;
                $scope.question.searching = false;
                $scope.searchText = {};
                if ($scope.question.parent_question_id) {
                    $scope.setPoolBranchNormalQuestion(pQuestion);
                }

                if ($scope.question.parent_answer_id) {
                    $scope.setPoolChildrenQuestion(pQuestion);
                }

                if (!$scope.question.parent_question_id && !$scope.question.parent_answer_id) {
                    $scope.setPoolRootQuestion(pQuestion);
                }
            };

            $scope.setPoolRootQuestion = function(pQuestion) {
                var roots = $filter('filter')($scope.questions, {parent_answer_id:false, parent_question_id:false});
                pQuestion.page = $scope.page;
                pQuestion.sorter = roots.indexOf($scope.question);
                pQuestion.parent_answer_id = $scope.question.parent_answer_id || null;
                $http({method: 'POST', url: 'setPoolRootQuestion', data:{sbook: $scope.sbook, pQuestion: pQuestion}})
                .success(function(data) {
                    angular.extend($scope.question, data.sQuestion);
                    angular.forEach(data.csQuestions, function(csQuestion) {
                        $scope.questions.push(csQuestion);
                    });
                    $scope.question.saving = false;
                    $scope.question.open = {questions: true, answers: true};
                }).error(function() {

                });
            };

            $scope.setPoolChildrenQuestion = function(pQuestion) {
                pQuestion.page = $scope.page;
                pQuestion.sorter = $scope.question.sorter;
                pQuestion.parent_answer_id = $scope.question.parent_answer_id;
                $http({method: 'POST', url: 'setPoolRootQuestion', data:{pQuestion: pQuestion}})
                .success(function(data) {
                    angular.extend($scope.question, data.sQuestion);
                    angular.forEach(data.csQuestions, function(csQuestion) {
                        $scope.questions.push(csQuestion);
                    });
                    $scope.question.saving = false;
                    $scope.question.open = {questions: true, answers: true};
                }).error(function() {

                });
            };

            $scope.setPoolBranchNormalQuestion = function(pQuestion) {
                $http({method: 'POST', url: 'setPoolBranchNormalQuestion', data:{bQuestion: $scope.question, pQuestion: pQuestion}})
                .success(function(data) {
                    angular.extend($scope.question, data.question);
                    angular.forEach(data.bbQuestions, function(question) {
                        $scope.questions.push(question);
                    });
                    $scope.question.saving = false;
                    $scope.question.open = {questions: true, answers: true};
                }).error(function() {

                });
            };

            $scope.setPoolScaleBranchQuestion = function(pQuestion) {
                pQuestion.page = $scope.page;
                $http({method: 'POST', url: 'setPoolScaleBranchQuestion', data:{question: $scope.question, pQuestion: pQuestion}})
                .success(function(data) {
                    angular.forEach(data.questions, function(question) {
                        $scope.questions.push(question);
                    });
                    $scope.question.saving = false;
                    $scope.question.open = {questions: true, answers: true};
                }).error(function() {

                });
            };

            $scope.save_img_db = function(ques_id, path) {
                var data = {ques_id:ques_id, path:path};
                $http({method: 'POST', url: 'save_img_db', data:data })
                .success(function(data) {
                }).error(function() {
                });
            };

            $scope.uploader = new FileUploader({
                alias: 'CDBimg',
                url: 'img_upload',
                autoUpload: true,
                formData: $scope.question.id
            });

            $scope.uploader.onAfterAddingFile = function(fileItem) {
                $scope.item = fileItem;
                $scope.progress = 0;
            };

            $scope.uploader.onProgressItem = function(fileItem, progress) {
                $scope.progress = fileItem.progress;
            };

            $scope.uploader.onErrorItem = function(fileItem) {
                $scope.error = fileItem.isError == true;
            };

            $scope.uploader.onCompleteItem  = function(fileItem, response, status) {
                if(response.result == 1){
                    $scope.success = fileItem.isSuccess == true;
                    $scope.save_img_db(fileItem.formData, response.path);
                }
                else{
                    if(status == 500) {
                        $scope.overSize = null;
                    }
                    else{
                        $scope.overSize = response.CDBimg[0];
                    }
                }
                $scope.uploader.destroy();
            };

            $scope.ques_import_var = function(question) {
                question.answers.length = 0;
                var list = question.importText.split('\n');
                for(var index in list){
                    var itemn = list[index].split(' ');
                    question.answers.push({value:itemn[0], title:itemn[1]});
                }
                question.code = 'manual';
                question.importText = null;
                question.is_import = false;
            };

        }
    };
})

.directive('surveySkips', function(editorFactory) {
    return {
        restrict: 'E',
        replace: true,
        transclude: false,
        scope: {
            skipTarget: '=',
            book: '=',
            paths: '='
        },
        template: `
            <div layout="column">
                <md-toolbar md-scroll-shrink>
                    <div class="md-toolbar-tools">
                        <h4>跳過此題</h4>
                        <div flex></div>
                        <md-button aria-label="關閉" ng-click="toggleSidenavRight()">關閉</md-button>
                        <md-button aria-label="儲存設定" md-colors="{background: 'blue'}" style="float:right" ng-click="saveRule('jump');">
                            儲存設定
                        </md-button>
                        <md-button aria-label="刪除設定" md-colors="{background: 'blue'}" style="float:right" ng-click="deleteRule()">
                            刪除設定
                        </md-button>
                    </div>
                </md-toolbar>
                <md-content>
                    <md-card ng-repeat="expression in rule.expressions">
                        <md-card-header md-colors="{background: 'indigo'}">
                            <div flex layout="row" layout-align="start center">
                                <div  style="margin: 0 0 0 16px" ng-if="!expression.compareLogic">
                                    請問是從哪個題目的選項而跳過此題? 請輸入。
                                </div>
                                <div  style="margin: 0 0 0 16px" ng-if="expression.compareLogic">
                                    {{ (compareOperators | filter: {key: expression.compareLogic}:true)[0].title }}
                                </div>
                                <span flex></span>
                                <div>
                                    <md-button class="md-icon-button" aria-label="刪除" ng-click="removeExpression($index)">
                                        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="delete"></md-icon>
                                    </md-button>
                                </div>
                            </div>
                        </md-card-header>
                        <md-card-content ng-repeat="(key,condition) in expression.conditions">
                            <survey-skip first="$first" book="book" condition="condition" remove-condition="removeCondition(expression, key)" create-condition="createCondition(expression, key)"></survey-skip>
                        </md-card-content>
                        <md-card-actions>
                            <md-fab-toolbar md-direction="right">
                                <md-fab-trigger class="align-with-text">
                                    <md-button aria-label="menu" class="md-fab md-primary">
                                        <md-icon md-svg-icon="list"></md-icon>
                                    </md-button>
                                </md-fab-trigger>
                                <md-toolbar>
                                    <md-fab-actions class="md-toolbar-tools" >
                                        <md-button aria-label="邏輯" ng-repeat="compareOperator in compareOperators" ng-click="createExpression($index, compareOperator.key)">
                                            {{compareOperator.title}}
                                        </md-button>
                                    </md-fab-actions>
                                </md-toolbar>
                            </md-fab-toolbar>
                        </md-card-actions>
                    </md-card>
                </md-content>
            </div>
        `,
        link: function(scope) {
        },
        controller: function($scope, $http, $mdSidenav) {
            $scope.ran = Math.random();
            $scope.getRule = function() {
                $http({method: 'POST', url: 'getRule', data:{skipTarget: $scope.skipTarget}})
                .success(function(data) {
                    $scope.rule = data.rule;
                }).error(function(e) {
                    console.log(e)
                });
            };
            $scope.getRule();

            $scope.toggleSidenavRight = function() {
                $mdSidenav('survey-skips').close();
            };

            $scope.compareOperators = [
                {key: ' && ', title: '而且'},
                {key: ' || ', title: '或者'}
            ];

            $scope.createCondition = function(expression, index) {
                expression.conditions.splice(index+1, 0, {});
            };

            $scope.removeCondition = function(expression, index) {
                expression.conditions.splice(index, 1);
                if (index == 0 && expression.conditions[0]) {
                    delete expression.conditions[0].compareOperator;
                }
            };

            $scope.removeExpression = function(index) {
                $scope.rule.expressions.splice(index, 1);
                if (index == 0 && $scope.rule.expressions[0]) {
                    delete $scope.rule.expressions[0].compareLogic;
                }
            };

            $scope.createExpression = function(index, logic) {
                $scope.rule.expressions.splice(index+1, 0, {'compareLogic':logic, 'conditions':[{'compareType':'question'}]});
            };

            $scope.saveRule = function(type) {
               $http({method: 'POST', url: 'saveRule', data:{paths: $scope.paths, expressions: $scope.rule.expressions, skipTarget: $scope.skipTarget, type:type}})
                .success(function(data) {
                    $scope.skipTarget.rule = data.rule;
                    $mdSidenav('survey-skips').close();
                }).error(function(e) {
                   console.log(e)
                });
            };

            $scope.deleteRule = function() {
               $http({method: 'POST', url: 'deleteRule', data:{skipTarget: $scope.skipTarget}})
                .success(function(data) {
                    $scope.skipTarget.rule = undefined;
                    $mdSidenav('survey-skips').close();
                }).error(function(e) {
                    console.log(e)
                });
            };
        }
    };
})

.directive('surveySkip', function(editorFactory) {
    return {
        restrict: 'E',
        replace: true,
        transclude: false,
        scope: {
            first: '=',
            condition: '=',
            book: '=',
            createCondition: '&',
            removeCondition: '&',
        },
        template: `
            <div layout="row">
                <div ng-if="!first">
                    <md-input-container>
                        <label>+</label>
                        <md-select ng-model="condition.compareOperator">
                            <md-option ng-repeat="compareOperator in compareOperators" ng-value="compareOperator.key">{{compareOperator.title}}</md-option>
                        </md-select>
                    </md-input-container>
                </div>
                <div>
                    <md-input-container>
                        <label>當題目</label>
                        <md-select ng-model="condition.question" ng-change="resetAnswers()">
                            <md-option ng-repeat="question in questions" ng-value="question.id" >{{question.node.title}}-{{question.title}}</md-option>
                        </md-select>
                    </md-input-container>
                </div>
                <div>
                    <md-input-container>
                        <label>比較邏輯</label>
                        <md-select ng-model="condition.logic">
                            <md-option ng-repeat="compareBoolean in compareBooleans" ng-value="compareBoolean.key">{{compareBoolean.title}}</md-option>
                        </md-select>
                    </md-input-container>
                </div>
                <div>
                    <md-input-container>
                        <label>比較對象</label>
                        <md-select ng-model="condition.compareType" ng-change="changeCompareType()">
                            <md-option ng-repeat="compareType in compareTypes" ng-value="compareType.key">{{compareType.title}}</md-option>
                        </md-select>
                    </md-input-container>
                </div>
                <div ng-if="condition.compareType=='value'">
                    <md-input-container>
                        <label>數值</label>
                        <input ng-model="condition.value" />
                    </md-input-container>
                </div>
                <div ng-if="condition.compareType=='answer'">
                    <md-input-container>
                        <label>選項</label>
                        <md-select ng-model="condition.value" md-on-open="getAnswers()" ng-init="resetAnswers()">
                            <md-option ng-repeat="answer in answers" ng-value="answer.value">{{answer.title}}</md-option>
                        </md-select>
                    </md-input-container>
                </div>
                <md-button aria-label="刪除" class="md-icon-button" ng-click="removeCondition()">
                    <md-icon md-svg-icon="delete"></md-icon>
                </md-button>
                <md-button aria-label="新增" class="md-icon-button" ng-click="createCondition()">
                    <md-icon md-svg-icon="add-circle-outline"></md-icon>
                </md-button>
            </div>
        `,
        link: function(scope) {
        },
        controller: function($scope, $http, $filter) {

            $scope.compareTypes = [
                {key: 'value', title: '數值'},
                {key: 'answer', title: '選項'}
            ];
            $scope.compareBooleans = [
                {key: ' > ', title: '大於'},
                {key: ' < ', title: '小於'},
                {key: ' == ', title: '等於'},
                {key: ' != ', title: '不等於'}
            ];
            $scope.compareOperators = [
                {key: ' && ', title: '而且'},
                {key: ' || ', title: '或者'}
            ];

            $http({method: 'POST', url: 'getQuestion', data:{book_id: $scope.book.id}})
            .success(function(data, status, headers, config) {
                $scope.questions = data.questions;
            })
            .error(function(e) {
                console.log(e);
            });

            $scope.resetAnswers = function() {
                $scope.answers = [];
                $scope.promiseAnswers = $http({method: 'POST', url: 'getAnswers', data:{question_id: $scope.condition.question}})
                .then(function(response) {
                    $scope.answers = response.data.answers;
                });
            };

            $scope.getAnswers = function() {
                return $scope.promiseAnswers;
            };

         }
    };
});