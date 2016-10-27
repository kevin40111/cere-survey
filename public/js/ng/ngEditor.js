angular.module('ngEditor', ['ngEditor.directives', 'ngEditor.factories']);

angular.module('ngEditor.factories', []).factory('editorFactory', function($http, $q) {

    return {
        ajax: function(url, data, node = {}) {
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
        }
    };

});

angular.module('ngEditor.directives', [])

.directive('questionNodes', function(editorFactory) {
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
                <div layout="row">
                    <md-button ng-repeat="path in paths" ng-click="getNodes(path)">{{path.title}}/</md-button>
                </div>
                <md-card ng-repeat="node in nodes">
                    <md-card-header md-colors="{background: 'indigo'}">
                        <question-bar></question-bar>
                    </md-card-header>
                    <md-card-content>
                        <md-input-container class="md-block" ng-if="node.type.editor.title">
                            <label>標題</label>
                            <textarea ng-model="node.title" md-maxlength="150" rows="1" ng-model-options="{updateOn: 'blur'}" md-select-on-focus ng-change="saveNodeTitle(node)"></textarea>
                        </md-input-container>
                        <div ng-if="node.type.editor.questions" questions="node.questions" node="node"></div>
                        <div ng-if="node.type.editor.answers" answers="node.answers" node="node"></div>
                    </md-card-content>
                    <md-card-actions>
                        <md-menu>
                            <md-button aria-label="新增題目" ng-click="$mdOpenMenu($event)">新增題目</md-button>
                            <md-menu-content width="2">
                            <md-menu-item ng-repeat="type in quesTypes | filter:{disabled:'!'}">
                                <md-button ng-click="addNode(type, node)"><md-icon md-svg-icon="{{type.icon}}"></md-icon>{{type.title}}</md-button>
                            </md-menu-item>
                            </md-menu-content>
                        </md-menu>
                    </md-card-actions>
                    <md-card-content layout="row" layout-align="space-around" ng-if="node.saving">
                        <md-progress-circular md-mode="indeterminate"></md-progress-circular>
                    </md-card-content>
                </md-card>
            </div>
        `,
        controller: function($scope, $filter) {

            $scope.nodes = [];

            $scope.quesTypes = [
                {name: 'explain', title: '說明文字', icon: 'info-outline'},
                {name: 'select', title: '單選題(下拉式)', icon: 'arrow-drop-down-circle'},
                {name: 'radio', title: '單選題(點選)', icon: 'radio-button-checked'},
                {name: 'checkbox', title: '複選題', icon: 'check-box'},
                {name: 'scale', title: '量表題', icon: 'list'},
                {name: 'text', title: '文字填答', icon: 'mode-edit'},
                {name: 'list', title: '題組', icon: 'sitemap icon', disabled: true},
                {name: 'textarea', title: '文字欄位(大型欄位)', disabled: true},
                {name: 'textscale', title: '文字欄位(表格)', disabled: true},
                {name: 'table', title: '表格', disabled: true},
                {name: 'jump', title: '開啟題本', type: 'rule'}
            ];

            this.getNodes = function(parent) {
                console.log(parent);
                editorFactory.ajax('getNodes', {parent: parent}).then(function(response) {
                    console.log(response);
                    $scope.root = parent;
                    $scope.nodes = response.nodes;
                    $scope.paths = response.paths;
                });
            };

            this.getNodes($scope.book);

            $scope.addNode = function(type, previous, parent = $scope.root) {
                console.log($scope.nodes);
                var node = {type: type.name};

                $scope.nodes.splice($scope.nodes.indexOf(previous)+1, 0, node);

                editorFactory.ajax('createNode', {node: node, parent: parent, previous: previous}, node).then(function(response) {
                    console.log(response);
                    angular.extend(node, response.node);
                });
            };

            $scope.saveNodeTitle = function(node) {
                editorFactory.ajax('saveNodeTitle', {node: node}, node).then(function(response) {
                    node.title = response.title;
                });
            };

            $scope.getNodes = this.getNodes;

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
                    <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="{{node.type.icon}}"></md-icon>
                </div>
                <div style="margin: 0 0 0 16px">{{node.type.title}}</div>

                <span flex></span>

                <div ng-if="node.previous_id">
                    <div class="ui input" ng-if="node.open.moving">
                        <input type="text" ng-model="settedPage" placeholder="輸入移動到的頁數..." />
                        <md-button class="md-icon-button no-animate" ng-disabled="node.saving" aria-label="移動到某頁" ng-click="setPage(node, settedPage)">
                            <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="send"></md-icon>
                        </md-button>
                    </div>
                    <md-button class="md-icon-button" aria-label="上移" ng-disabled="$index==1" ng-click="moveUp(node)">
                        <md-tooltip md-direction="bottom">上移</md-tooltip>
                        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="arrow-drop-up"></md-icon>
                    </md-button>
                    <md-button class="md-icon-button" aria-label="下移" ng-disabled="$last" ng-click="moveDown(node)">
                        <md-tooltip md-direction="bottom">下移</md-tooltip>
                        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="arrow-drop-down"></md-icon>
                    </md-button>
                    <md-button class="md-icon-button" aria-label="刪除" ng-disabled="node.saving" ng-click="removeNode(node)">
                        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="delete"></md-icon>
                    </md-button>
                </div>
            </div>
        `,

        require: ['^questionNodes'],
        link: function(scope, iElement, iAttrs, ctrls) {
            var pageCtrl = ctrls[0];
        },
        controller: function($scope, $http) {

            $scope.removeNode = function(node) {
                editorFactory.ajax('removeNode', {node: node}, node).then(function(response) {
                    console.log(response);
                    if (response.deleted) {
                        $scope.nodes.splice($scope.nodes.indexOf(node), 1);
                    };
                });
            };

            $scope.moveUp = function(node) {
                editorFactory.ajax('moveNodeUp', {item: node}, node).then(function(response) {
                    console.log(response);
                    angular.extend($scope.nodes[$scope.nodes.indexOf(node)-1], response.item);
                    angular.extend($scope.nodes[$scope.nodes.indexOf(node)], response.previous);
                });
            };

            $scope.moveDown = function(node) {
                editorFactory.ajax('moveNodeDown', {item: node}, node).then(function(response) {
                    console.log(response);
                    angular.extend($scope.nodes[$scope.nodes.indexOf(node)+1], response.item);
                    angular.extend($scope.nodes[$scope.nodes.indexOf(node)], response.next);
                });
            };

            this.moveSort = function(question, offset) {
                question.sorter = question.sorter+offset;
                question.saving = true;
                $http({method: 'POST', url: 'moveSort', data:{question: question}})
                .success(function(data, status, headers, config) {
                    console.log(data);
                    $scope.question.childrens = data.questions;
                    question.saving = false;
                }).error(function(e) {
                    console.log(e);
                });
            };

            //$scope.moveSort = this.moveSort;

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
                <md-subheader class="md-no-sticky">選項 ({{ answers.length || 0 }})</md-subheader>
                <md-list-item ng-repeat="answer in answers">
                    <md-icon ng-style="{fill: !answer.title ? 'red' : ''}" md-svg-icon="{{node.type.icon}}"></md-icon>
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
                    <md-icon class="md-secondary" aria-label="刪除選項" md-svg-icon="delete" ng-click="removeAnswer(answer)"></md-icon>
                </md-list-item>
                <md-list-item ng-click="createAnswer(answers[answers.length-1])">
                    <md-icon md-svg-icon="{{node.type.icon}}"></md-icon>
                    <p>新增選項</p>
                </md-list-item>
            </md-list>
        `,
        require: ['^questionNodes'],
        link: function(scope, iElement, iAttrs, ctrls) {
            var nodeCtrl = ctrls[0];
            scope.getNodes = nodeCtrl.getNodes;
        },
        controller: function($scope, $filter) {

            $scope.saveTitleNgOptions = {updateOn: 'default blur', debounce:{default: 2000, blur: 0}};

            $scope.createAnswer = function(previous) {
                editorFactory.ajax('createAnswer', {node: $scope.node, previous: previous}, $scope.node).then(function(response) {
                    console.log(response);
                    $scope.node.answers.push(response.answer);
                });
            };

            $scope.saveAnswerTitle = function(answer) {
                editorFactory.ajax('saveAnswerTitle', {answer: answer}, answer).then(function(response) {
                    console.log(response);
                    angular.extend(answer, response.answer);
                });
            };

            $scope.removeAnswer = function(answer) {
                editorFactory.ajax('removeAnswer', {answer: answer}, answer).then(function(response) {
                    console.log(response);
                    if (response.deleted) {
                        $scope.node.answers = response.answers;
                    };
                });
            };

            $scope.moveUp = function(answer) {
                editorFactory.ajax('moveUp', {item: answer}, answer).then(function(response) {
                    console.log(response);
                    $scope.node.answers = response.items;
                });
            };

            $scope.moveDown = function(answer) {
                editorFactory.ajax('moveDown', {item: answer}, answer).then(function(response) {
                    console.log(response);
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
                <md-subheader class="md-no-sticky">問題 ({{ questions.length || 0 }})</md-subheader>
                <md-list-item ng-repeat="question in questions">
                    <md-icon md-svg-icon="help"><md-tooltip md-direction="left">{{$index+1}}</md-tooltip></md-icon>
                    <p class="ui transparent fluid input" ng-class="{loading: question.saving}">
                        <input type="text" placeholder="輸入問題..." ng-model="question.title" ng-model-options="saveTitleNgOptions" ng-change="saveQuestionTitle(question)" />
                    </p>
                    <md-button class="md-secondary md-icon-button" ng-click="moveUp(question)" aria-label="上移" ng-disabled="$first">
                        <md-tooltip md-direction="left">上移</md-tooltip>
                        <md-icon md-svg-icon="arrow-drop-up"></md-icon>
                    </md-button>
                    <md-button class="md-secondary md-icon-button" ng-click="moveDown(question)" aria-label="下移" ng-disabled="$last">
                        <md-tooltip md-direction="left">下移</md-tooltip>
                        <md-icon md-svg-icon="arrow-drop-down"></md-icon>
                    </md-button>
                    <md-icon class="md-secondary" aria-label="刪除子題" md-svg-icon="delete" ng-click="removeQuestion(question)"></md-icon>
                </md-list-item>
                <md-list-item ng-click="createQuestion(questions[questions.length-1])">
                    <md-icon md-svg-icon="help"></md-icon>
                    <p>新增問題</p>
                </md-list-item>
            </md-list>
        `,
        controller: function($scope, $http, $filter) {

            $scope.saveTitleNgOptions = {updateOn: 'default blur', debounce:{default: 2000, blur: 0}};
            $scope.searchLoaded = '';
            $scope.searchText = {};

            $scope.createQuestion = function(previous) {
                console.log(previous);
                editorFactory.ajax('createQuestion', {node: $scope.node, previous: previous}, $scope.node).then(function(response) {
                    console.log(response);
                    $scope.questions.push(response.question);
                });
            };

            $scope.saveQuestionTitle = function(question) {
                editorFactory.ajax('saveQuestionTitle', {question: question}, question).then(function(response) {
                    console.log(response);
                    angular.extend(question, response.question);
                });
            };

            $scope.removeQuestion = function(question) {
                editorFactory.ajax('removeQuestion', {question: question}, question).then(function(response) {
                    console.log(response);
                    if (response.deleted) {
                        $scope.node.questions = response.questions;
                    };
                });
            };

            $scope.moveUp = function(question) {
                editorFactory.ajax('moveUp', {item: question}, question).then(function(response) {
                    console.log(response);
                    $scope.node.questions = response.items;
                });
            };

            $scope.moveDown = function(question) {
                editorFactory.ajax('moveDown', {item: question}, question).then(function(response) {
                    console.log(response);
                    $scope.node.questions = response.items;
                });
            };

            $scope.getBooks = function() {
                if (!$scope.books) {
                    var promise = $http({method: 'POST', url: 'getBooks', data:{}})
                    .success(function(data, status, headers, config) {
                        console.log(data);
                        $scope.books = data.books;
                    }).error(function(e) {
                        console.log(e);
                    });

                    return promise;
                }
            };

            $scope.getRowsFiles = function() {
                if (!$scope.rowsFiles) {
                    var promise = $http({method: 'POST', url: '/docs/lists', data:{}})
                    .success(function(data, status, headers, config) {
                                       console.log(data);
                        $scope.rowsFiles = $filter('filter')(data.docs, {type: '5'}, true);
                    }).error(function(e) {
                        console.log(e);
                    });

                    return promise;
                }
            };

            $scope.getColumns = function() {
                if (!$scope.columns) {
                    var promise = $http({method: 'POST', url: 'getColumns', data:{file_id: $scope.question.file}})
                    .success(function(data, status, headers, config) {
                        console.log(data);
                        $scope.columns = data.columns;
                    }).error(function(e) {
                        console.log(e);
                    });

                    return promise;
                }
            };

        }
    };
})

.directive('questionPool', function($compile, FileUploader, $templateCache) {
    return {
        restrict: 'E',
        replace: true,
        transclude: false,
        templateUrl: 'pool',
        controller: function($scope, $http) {

            $scope.getPoolQuestions = function(position) {
                var sBooks = $filter('filter')($scope.sbooks, {checked: true});
                console.log(angular.toJson(sBooks));
                if ($scope.searchLoaded != angular.toJson(sBooks)) {
                    $http({method: 'POST', url: 'getPoolQuestions', data:{type: $scope.question.type, sBooks: sBooks}})
                    .success(function(data, status, headers, config) {
                        console.log(data);
                        $scope.pQuestions = data.questions;
                        $scope.question.searching = position;
                        $scope.searchLoaded == angular.toJson(sBooks);
                    }).error(function(e) {
                        console.log(e);
                    });
                };
            };

            $scope.setPoolQuestion = function(pQuestion) {
                $scope.question.saving = true;
                $scope.question.searching = false;
                $scope.searchText = {};
                if ($scope.question.parent_question_id) {
                    $scope.setPoolBranchNormalQuestion(pQuestion);
                };

                if ($scope.question.parent_answer_id) {
                    $scope.setPoolChildrenQuestion(pQuestion);
                };

                if (!$scope.question.parent_question_id && !$scope.question.parent_answer_id) {
                    $scope.setPoolRootQuestion(pQuestion);
                };
            };

            $scope.setPoolRootQuestion = function(pQuestion) {
                var roots = $filter('filter')($scope.questions, {parent_answer_id:false, parent_question_id:false});
                pQuestion.page = $scope.page;
                pQuestion.sorter = roots.indexOf($scope.question);
                pQuestion.parent_answer_id = $scope.question.parent_answer_id || null;
                $http({method: 'POST', url: 'setPoolRootQuestion', data:{sbook: $scope.sbook, pQuestion: pQuestion}})
                .success(function(data, status, headers, config) {
                    console.log(data);
                    angular.extend($scope.question, data.sQuestion);
                    angular.forEach(data.csQuestions, function(csQuestion) {
                        $scope.questions.push(csQuestion);
                    });
                    $scope.question.saving = false;
                    $scope.question.open = {questions: true, answers: true};
                }).error(function(e) {
                    console.log(e);
                });
            };

            $scope.setPoolChildrenQuestion = function(pQuestion) {
                pQuestion.page = $scope.page;
                pQuestion.sorter = $scope.question.sorter;
                pQuestion.parent_answer_id = $scope.question.parent_answer_id;
                $http({method: 'POST', url: 'setPoolRootQuestion', data:{pQuestion: pQuestion}})
                .success(function(data, status, headers, config) {
                    console.log(data);
                    angular.extend($scope.question, data.sQuestion);
                    angular.forEach(data.csQuestions, function(csQuestion) {
                        $scope.questions.push(csQuestion);
                    });
                    $scope.question.saving = false;
                    $scope.question.open = {questions: true, answers: true};
                }).error(function(e) {
                    console.log(e);
                });
            };

            $scope.setPoolBranchNormalQuestion = function(pQuestion) {
                $http({method: 'POST', url: 'setPoolBranchNormalQuestion', data:{bQuestion: $scope.question, pQuestion: pQuestion}})
                .success(function(data, status, headers, config) {
                    console.log(data);
                    angular.extend($scope.question, data.question);
                    angular.forEach(data.bbQuestions, function(question) {
                        $scope.questions.push(question);
                    });
                    $scope.question.saving = false;
                    $scope.question.open = {questions: true, answers: true};
                }).error(function(e) {
                    console.log(e);
                });
            }

            $scope.setPoolScaleBranchQuestion = function(pQuestion) {
                pQuestion.page = $scope.page;
                $http({method: 'POST', url: 'setPoolScaleBranchQuestion', data:{question: $scope.question, pQuestion: pQuestion}})
                .success(function(data, status, headers, config) {
                    console.log(data);
                    angular.forEach(data.questions, function(question) {
                        $scope.questions.push(question);
                    });
                    $scope.question.saving = false;
                    $scope.question.open = {questions: true, answers: true};
                }).error(function(e) {
                    console.log(e);
                });
            };

            $scope.save_img_db = function(ques_id, path) {
                var data = {ques_id:ques_id, path:path}
                $http({method: 'POST', url: 'save_img_db', data:data })
                .success(function(data, status, headers, config) {
                    console.log(data);
                }).error(function(e) {
                    console.log(e);
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

            $scope.uploader.onErrorItem = function(fileItem, response, status, headers) {
                $scope.error = fileItem.isError == true;
            };

            $scope.uploader.onCompleteItem  = function(fileItem, response, status, headers) {
                //console.log(array_key_exists('CDBimg', response));
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
                for(index in list){
                    var itemn = list[index].split(' ');
                    question.answers.push({value:itemn[0], title:itemn[1]});
                }
                question.code = 'manual';
                question.importText = null;
                question.is_import = false;
            };

        }
    };
});