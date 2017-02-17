'use strict';

angular.module('ngEditor', ['ngEditor.directives', 'ngEditor.factories']);

angular.module('ngEditor.factories', []).factory('editorFactory', function($http, $q) {

    var types = {};
    var typesInPage = [];

    return {
        types: types,
        typesInPage: typesInPage,
        ajax: function(url, data, node = {}) {
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
    };

});

angular.module('ngEditor.directives', [])

.directive('surveyBook', function(editorFactory) {
    return {
        restrict: 'E',
        replace: true,
        transclude: false,
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
                        <md-button ng-click="createTable()">完成</md-button>
                        <md-button href="/survey/{{book.id}}/page" ng-disabled="!book.lock" target="_blank">預覽</md-button>
                    </div>
                </md-toolbar>
                <md-divider></md-divider>
                <div layout="column" layout-align="start center" style="height:100%;overflow-y:scroll">
                    <div style="width:960px">
                        <survey-node ng-repeat="node in nodes" node="node" index="$index" first="$first" last="$last"></survey-node>
                    </div>
                </div>
                <md-sidenav class="md-sidenav-right" md-is-open="skipSetting" md-disable-backdrop layout="column" style="min-width:40%">
                    <md-toolbar>
                        <div class="md-toolbar-tools">
                            <h4>跳題設定</h4>
                            <div flex></div>
                            <md-button aria-label="關閉" ng-click="skipSetting=false">關閉</md-button>
                        </div>
                    </md-toolbar>
                    <md-content  ng-if="skipSetting">
                        <survey-skips skip-target="skipTarget"></survey-skips>
                    </md-content>
                </md-sidenav>
            </md-content>
        `,
        controller: function($scope, $filter) {

            $scope.skipSetting = false;
            editorFactory.types = $scope.book.types;
            editorFactory.typesInPage = $filter('filter')(Object.values(editorFactory.types), {disabled: '!'})

            this.getNodes = function(root) {
                editorFactory.ajax('getNodes', {root: root}).then(function(response) {
                    $scope.root = root;
                    $scope.nodes = response.nodes;
                    $scope.paths = response.paths;
                });
            };

            this.addNode = function(type, previous, offset) {

                var node = {type: type.name, title: Math.random()};

                editorFactory.ajax('createNode', {node: node, parent: $scope.root, previous: previous}, node).then(function(response) {
                    angular.extend(node, response.node);
                    $scope.nodes.splice(offset, 0, node);
                });
            };

            this.removeNode = function(node) {
                editorFactory.ajax('removeNode', {node: node}, node).then(function(response) {
                    if (response.deleted) {
                        $scope.nodes.splice($scope.nodes.indexOf(node), 1);
                    }
                });
            };

            this.moveUp = function(node, offset) {
                editorFactory.ajax('moveNodeUp', {item: node}, node).then(function(response) {
                    angular.extend($scope.nodes[$scope.nodes.indexOf(node)-1], response.item);
                    angular.extend($scope.nodes[$scope.nodes.indexOf(node)], response.previous);
                });
            };

            this.moveDown = function(node) {
                editorFactory.ajax('moveNodeDown', {item: node}, node).then(function(response) {
                    angular.extend($scope.nodes[$scope.nodes.indexOf(node)+1], response.item);
                    angular.extend($scope.nodes[$scope.nodes.indexOf(node)], response.next);
                });
            };

            $scope.getNodes = this.getNodes;

            $scope.getNodes($scope.book);

            $scope.createTable = function() {
                editorFactory.ajax('createTable', {}, $scope.book).then(function(response) {
                    $scope.book.lock = response.lock;
                });
            };
            
            this.toggleSidenavRight = function(skipTarget) {
                $scope.skipTarget = skipTarget;
                $scope.skipSetting = true;
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
                <md-card-header md-colors="{background: 'indigo'}">
                    <question-bar></question-bar>
                </md-card-header>
                <md-card-content>
                    <md-input-container class="md-block" ng-if="type.editor.title">
                        <label>{{type.editor.title}}</label>
                        <textarea ng-model="node.title" md-maxlength="150" rows="1" ng-model-options="{updateOn: 'blur'}" md-select-on-focus ng-change="saveNodeTitle(node)"></textarea>
                    </md-input-container>
                    <div ng-if="type.editor.questions.amount" questions="node.questions" node="node"></div>
                    <div ng-if="type.editor.answers" answers="node.answers" node="node"></div>
                </md-card-content>
                <md-card-actions>
                    <md-menu>
                        <md-button aria-label="新增" ng-click="$mdOpenMenu($event)">新增</md-button>
                        <md-menu-content width="3">
                        <md-menu-item ng-repeat="type in getTypesArray()">
                            <md-button ng-click="addNode(type, node, index+1)"><md-icon md-svg-icon="{{type.icon}}"></md-icon>{{type.title}}</md-button>
                        </md-menu-item>
                        </md-menu-content>
                    </md-menu>
                    <md-button ng-if="type.editor.enter" ng-click="getNodes(node)">編輯</md-button>
                </md-card-actions>
                <md-progress-linear md-mode="indeterminate" ng-disabled="!node.saving"></md-progress-linear>
            </md-card>
        `,
        require: '^surveyBook',
        link: function(scope, iElement, iAttrs, surveyBookCtrl) {
            scope.addNode = surveyBookCtrl.addNode;
            scope.removeNode = surveyBookCtrl.removeNode;
            scope.getNodes = surveyBookCtrl.getNodes;
            scope.moveUp = surveyBookCtrl.moveUp;
            scope.moveDown = surveyBookCtrl.moveDown;
            scope.toggleSidenavRight = surveyBookCtrl.toggleSidenavRight;
        },
        controller: function($scope) {

            $scope.type = editorFactory.types[$scope.node.type];

            $scope.getTypesArray = function() {
                return $scope.node.type == 'page' ? [editorFactory.types['page']] : editorFactory.typesInPage;
            };

            $scope.saveNodeTitle = function(node) {
                editorFactory.ajax('saveNodeTitle', {node: node}, node).then(function(response) {
                    node.title = response.title;
                });
            };

        }
    };
})

.directive('questionBar', function(editorFactory) {
    return {
        restrict: 'E',
        replace: true,
        transclude: false,
        template: `
            <div flex layout="row" layout-align="start center">
                <div>
                    <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="{{type.icon}}"></md-icon>
                </div>
                <div style="margin: 0 0 0 16px">{{type.title}}</div>

                <span flex></span>

                <div>
                    <div class="ui input" ng-if="node.open.moving">
                        <input type="text" ng-model="settedPage" placeholder="輸入移動到的頁數..." />
                        <md-button class="md-icon-button no-animate" ng-disabled="node.saving" aria-label="移動到某頁" ng-click="setPage(node, settedPage)">
                            <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="send"></md-icon>
                        </md-button>
                    </div>
                    <md-button class="md-icon-button" aria-label="上移" ng-disabled="first" ng-click="moveUp(node)">
                        <md-tooltip md-direction="bottom">上移</md-tooltip>
                        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="arrow-drop-up"></md-icon>
                    </md-button>
                    <md-button class="md-icon-button" aria-label="下移" ng-disabled="last" ng-click="moveDown(node)">
                        <md-tooltip md-direction="bottom">下移</md-tooltip>
                        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="arrow-drop-down"></md-icon>
                    </md-button>
                    <md-button class="md-icon-button" aria-label="跳題" ng-disabled="node.saving" ng-click="toggleSidenavRight(node)">
                        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="settings"></md-icon>
                    </md-button>
                    <md-button class="md-icon-button" aria-label="刪除" ng-disabled="node.saving" ng-click="removeNode(node)">
                        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="delete"></md-icon>
                    </md-button>
                </div>
            </div>
        `
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
                <md-subheader class="md-no-sticky">選項 ({{ answers.length || 0 }})</md-subheader>
                <md-list-item ng-repeat="answer in answers">
                    <md-icon ng-style="{fill: !answer.title ? 'red' : ''}" md-svg-icon="{{types[node.type].icon}}"></md-icon>
                    <div flex>
                        <div class="ui transparent fluid input" ng-class="{loading: answer.saving}">
                            <input type="text" placeholder="輸入選項名稱..." ng-model="answer.title" ng-model-options="saveTitleNgOptions" ng-change="saveAnswerTitle(answer)" />
                        </div>
                    </div>
                    <md-button class="md-secondary" aria-label="設定子題" ng-click="getNodes(answer)">設定子題</md-button>
                    <md-button class="md-secondary md-icon-button" ng-click="moveUp(answer)" aria-label="上移" ng-disabled="$first">
                        <md-tooltip md-direction="left">上移</md-tooltip>
                        <md-icon md-svg-icon="arrow-drop-up"></md-icon>
                    </md-button>
                    <md-button class="md-secondary md-icon-button" ng-click="moveDown(answer)" aria-label="下移" ng-disabled="$last">
                        <md-tooltip md-direction="left">下移</md-tooltip>
                        <md-icon md-svg-icon="arrow-drop-down"></md-icon>
                    </md-button>
                    <md-button class="md-secondary md-icon-button" ng-click="toggleSidenavRight(answer)" aria-label="設定限制">
                        <md-tooltip>設定限制</md-tooltip>
                        <md-icon md-svg-icon="settings"></md-icon>
                    </md-button>
                    <md-icon class="md-secondary" aria-label="刪除選項" md-svg-icon="delete" ng-click="removeAnswer(answer)"></md-icon>
                </md-list-item>
                <md-list-item ng-if="node.answers.length < types[node.type].editor.answers" ng-click="createAnswer(answers[answers.length-1])">
                    <md-icon md-svg-icon="{{types[node.type].icon}}"></md-icon>
                    <p>新增選項</p>
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

            $scope.createAnswer = function(previous) {
                editorFactory.ajax('createAnswer', {node: $scope.node, previous: previous}, $scope.node).then(function(response) {
                    $scope.node.answers.push(response.answer);
                });
            };

            $scope.saveAnswerTitle = function(answer) {
                editorFactory.ajax('saveAnswerTitle', {answer: answer}, answer).then(function(response) {
                    angular.extend(answer, response.answer);
                });
            };

            $scope.removeAnswer = function(answer) {
                editorFactory.ajax('removeAnswer', {answer: answer}, answer).then(function(response) {
                     if (response.deleted) {
                        $scope.node.answers = response.answers;
                    }
                });
            };

            $scope.moveUp = function(answer) {
                editorFactory.ajax('moveUp', {item: answer}, answer).then(function(response) {
                    $scope.node.answers = response.items;
                });
            };

            $scope.moveDown = function(answer) {
                editorFactory.ajax('moveDown', {item: answer}, answer).then(function(response) {
                    $scope.node.answers = response.items;
                });
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
                <md-subheader class="md-no-sticky">問題 ({{ node.questions.length || 0 }})</md-subheader>
                <md-list-item ng-repeat="question in node.questions">
                    <md-icon md-svg-icon="help"><md-tooltip md-direction="left">{{$index+1}}</md-tooltip></md-icon>
                    <p class="ui transparent fluid input" ng-class="{loading: question.saving}">
                        <input type="text" placeholder="輸入問題..." ng-model="question.title" ng-model-options="saveTitleNgOptions" ng-change="saveQuestionTitle(question)" />
                    </p>
                    <md-button class="md-secondary" ng-if="types[node.type].editor.questions.childrens" aria-label="設定子題" ng-click="getNodes(question)">設定子題</md-button>
                    <md-button class="md-secondary md-icon-button" ng-click="moveUp(question)" aria-label="上移" ng-disabled="$first">
                        <md-tooltip md-direction="left">上移</md-tooltip>
                        <md-icon md-svg-icon="arrow-drop-up"></md-icon>
                    </md-button>
                    <md-button class="md-secondary md-icon-button" ng-click="moveDown(question)" aria-label="下移" ng-disabled="$last">
                        <md-tooltip md-direction="left">下移</md-tooltip>
                        <md-icon md-svg-icon="arrow-drop-down"></md-icon>
                    </md-button>
                    <md-button class="md-secondary md-icon-button" ng-click="toggleSidenavRight(question)" aria-label="設定限制" ng-if="(question.node.type == 'scale') || (question.node.type == 'checkbox')">
                        <md-tooltip>設定限制</md-tooltip>
                        <md-icon md-svg-icon="settings"></md-icon>
                    </md-button>
                    <md-icon class="md-secondary" aria-label="刪除子題" md-svg-icon="delete" ng-click="removeQuestion(question)"></md-icon>
                </md-list-item>
                <md-list-item ng-if="node.questions.length < types[node.type].editor.questions.amount" ng-click="createQuestion(node.questions[node.questions.length-1])">
                    <md-icon md-svg-icon="help"></md-icon>
                    <p>新增問題</p>
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

            $scope.createQuestion = function(previous) {
                editorFactory.ajax('createQuestion', {node: $scope.node, previous: previous}, $scope.node).then(function(response) {
                    $scope.questions.push(response.question);
                });
            };

            $scope.saveQuestionTitle = function(question) {
                editorFactory.ajax('saveQuestionTitle', {question: question}, question).then(function(response) {
                    angular.extend(question, response.question);
                });
            };

            $scope.removeQuestion = function(question) {
                editorFactory.ajax('removeQuestion', {question: question}, question).then(function(response) {
                    if (response.deleted) {
                        $scope.node.questions = response.questions;
                    }
                });
            };

            $scope.moveUp = function(question) {
                editorFactory.ajax('moveUp', {item: question}, question).then(function(response) {
                    $scope.node.questions = response.items;
                });
            };

            $scope.moveDown = function(question) {
                editorFactory.ajax('moveDown', {item: question}, question).then(function(response) {
                    $scope.node.questions = response.items;
                });
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
            skipTarget: '='
        },
        template: `
            <div>
                <div flex layout="row">
                    <div flex></div>
                    <md-button aria-label="儲存設定" md-colors="{background: 'blue'}" style="float:right" ng-click="saveRules()">
                        儲存設定
                    </md-button>
                    <md-button aria-label="刪除設定" md-colors="{background: 'blue'}" style="float:right" ng-click="deleteRules()">
                        刪除設定
                    </md-button>
                </div>
                <div layout-align="start center" ng-repeat="(index,rule) in rules">
                    <md-card>
                        <md-card-header md-colors="{background: 'indigo'}">
                            <div flex layout="row" layout-align="start center">
                                <div  style="margin: 0 0 0 16px">
                                    二階條件設定:{{compareBooleans[rules[index].compareLogic]}}
                                </div>
                                <span flex></span>
                                <div>
                                    <md-button class="md-icon-button" aria-label="刪除" ng-click="removeRule(index)">
                                        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="delete"></md-icon>
                                    </md-button>
                                </div>
                            </div>
                        </md-card-header>
                        <md-card-content>
                            <survey-skip skip-target="skipTarget" rule="rule" rule-setted="ruleSetted" key="key" condition="condition" ng-repeat="(key,condition) in rule.conditions"></survey-skip>
                        </md-card-content>
                        <md-card-actions>
                            
                        </md-card-actions>
                        <md-progress-linear md-mode="indeterminate" ng-disabled="!node.saving"></md-progress-linear>
                    </md-card>
                    <div flex layout="row" >
                        <md-fab-toolbar md-direction="right" style="width: 100%">
                            <md-fab-trigger class="align-with-text">
                                <md-button aria-label="menu" class="md-fab md-primary">
                                    <md-icon md-svg-icon="list"></md-icon>
                                </md-button>
                            </md-fab-trigger>

                            <md-toolbar style="width: 100%">
                                <md-fab-actions class="md-toolbar-tools" >
                                    <md-button aria-label="邏輯" ng-repeat="(logic,compareBoolean) in compareBooleans" ng-click="createRule(index,logic)">
                                        {{compareBoolean}}
                                    </md-button>
                                </md-fab-actions>
                            </md-toolbar>
                        </md-fab-toolbar>
                    </div>
                </div>
            </div>
        `,
        link: function(scope) {
        },
        controller: function($scope, $http) {
            $scope.ruleSetted = false;
            
            $scope.getRules = function() {
                $http({method: 'POST', url: 'getRules', data:{skipTarget: $scope.skipTarget}})
                .success(function(data) {
                    if (data == null) {
                        $scope.rules = [{'conditions':[{'compareType':'1'}]}];
                    } else {
                        $scope.rules = data;
                        $scope.ruleSetted = true;
                    }
                }).error(function(e) {
                    console.log(e)
                });
            };
            $scope.getRules();
            
            $scope.compareBooleans = {' != ':'不等於', ' && ':'而且', ' || ':'或者'};

            
            $scope.createRule = function(index,logic) {
                $scope.rules.splice(index+1, 0,{'compareLogic':logic, 'conditions':[{'compareType':'1'}]});
            };

            $scope.removeRule = function(index) {
                if (index == 0) {
                    delete $scope.rules[1].compareLogic;
                }
                $scope.rules.splice(index, 1);
            };

            $scope.saveRules = function() {
               $http({method: 'POST', url: 'saveRules', data:{rules: $scope.rules, skipTarget: $scope.skipTarget}})
                .success(function(data) {
                   
                }).error(function(e) {
                   console.log(e)
                });
            };

            $scope.deleteRules = function() {
               $http({method: 'POST', url: 'deleteRules', data:{skipTarget: $scope.skipTarget}})
                .success(function(data) {
                     $scope.getRules();
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
            rule: '=',
            ruleSetted: '=',
            key: '=',
            condition: '='
        },
        template: `
            <div layout-align="start center">
                <md-card>
                    <md-card-header md-colors="{background: 'indigo'}">
                        <div flex layout="row" layout-align="start center">
                            <div  style="margin: 0 0 0 16px">
                                一階條件設定
                            </div>
                            <span flex></span>
                            <div>
                                <md-button class="md-icon-button" aria-label="刪除" ng-click="removeCondition(key)">
                                    <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="delete"></md-icon>
                                </md-button>
                                <md-button class="md-icon-button" aria-label="新增" ng-click="createCondition(key)">
                                    <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="add-circle-outline"></md-icon>
                                </md-button>
                            </div>
                        </div>
                    </md-card-header>
                    <md-card-content>
                        <div layout="row" ng-if="key">
                            <md-input-container style="margin-right: 10px">
                                <label>比較邏輯</label>
                                <md-select ng-model="logic" ng-change="setCondition(key,logic,'l')" placeholder="{{compareBooleans[condition.logic]}}">
                                <md-option ng-repeat="(logic,compareBoolean) in compareBooleans" ng-value="logic">{{compareBoolean}}</md-option>
                                </md-select>
                            </md-input-container>
                            <md-input-container style="margin-right: 10px">
                                <label>比較對象</label>
                                <md-select ng-model="type" ng-change="setCondition(key,type,'t')"  placeholder="{{types[condition.compareType]}}">
                                <md-option ng-repeat="type in types" ng-value="type">{{type}}</md-option>
                                </md-select>
                            </md-input-container>
                        </div>
                        <div layout="row" ng-if="condition.compareType==0">
                            <md-input-container>
                                <label></label>
                                <input ng-model="value" survey-input string-converter ng-change="setCondition(key,value,'v')" placeholder="{{condition.value}}"/>
                            </md-input-container>
                        </div>
                        <div layout="row" ng-if="condition.compareType==1">
                            <md-input-container style="margin-right: 10px">
                                <label>題目</label>
                                <md-select ng-model="question" ng-change="setCondition(key,question,'q')" placeholder="{{condition.question.node.title}}-{{condition.question.title}}">
                                <md-option ng-repeat="question in questions" ng-value="question" >{{question.node.title}}-{{question.title}}</md-option>
                                </md-select>
                            </md-input-container>
                            <md-input-container style="margin-right: 10px" ng-if="answers.length || condition.answer">
                                <label>答案</label>
                                <md-select ng-model="answer" ng-change="setCondition(key,answer,'a')" placeholder="{{condition.answer.title}}">
                                <md-option ng-repeat="answer in answers" ng-value="answer" >{{answer.title}}</md-option>
                                </md-select>
                            </md-input-container>
                        </div>
                       
                    </md-card-content>
                    <md-progress-linear md-mode="indeterminate" ng-disabled="!node.saving"></md-progress-linear>
                </md-card>
            </div>
        `,
        link: function(scope) {
        },
        controller: function($scope, $http) {
            
            $scope.createCondition = function(key) {
                $scope.rule.conditions.splice(key+1, 0,{});
            };
            $scope.removeCondition = function(key) {
                if (key == 0) {
                    delete $scope.rule.conditions[1].logic;
                }
                $scope.rule.conditions.splice(key, 1);
            };

            $scope.types = ['數值', '其它答案'];
            $scope.compareBooleans = {' > ':'大於', ' < ':'小於', ' == ':'等於', ' != ':'不等於', ' && ':'而且', ' || ':'或者'};

            
            $http({method: 'POST', url: 'getQuestion', data:{}})
            .success(function(data, status, headers, config) {
                $scope.questions = data.questions;
            })
            .error(function(e){
                console.log(e);
            });
            
            $scope.setCondition = function(key,rule,type) {
                if (type == 'l') {
                    $scope.rule.conditions[key]['logic'] = rule;
                }
                if (type == 't') {
                    $scope.rule.conditions[key]['compareType'] = (rule == '數值' ?  0 : 1);
                    delete $scope.rule.conditions[key].value;
                    delete $scope.rule.conditions[key].type;
                    delete $scope.rule.conditions[key].id;
                    delete $scope.rule.conditions[key].question;
                    delete $scope.rule.conditions[key].answer;
                }
                if (type == 'q') {
                    $scope.answers = rule.node.answers;
                    $scope.rule.conditions[key]['type'] = rule.node.type;
                    $scope.rule.conditions[key]['id'] = rule.id;
                    $scope.rule.conditions[key]['question'] = rule;
                }
                if (type == 'a') {
                    $scope.rule.conditions[key]['value'] = rule.value;
                    $scope.rule.conditions[key]['answer'] = rule;
                }
                if (type == 'v') {
                    $scope.rule.conditions[key]['value'] = rule;
                }                
            };
         }
    };
});