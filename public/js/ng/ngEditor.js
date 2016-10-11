angular.module('ngEditor', ['ngEditor.directives', 'ngEditor.factories']);

angular.module('ngEditor.factories', []).factory('editorFactory', function($http, $q) {

    return {
        ajax: function(url, data, question = {}) {
            var deferred = $q.defer();

            question.saving = true;
            $http({method: 'POST', url: url, data: data, timeout: deferred.promise})
            .success(function(data, status, headers, config) {
                deferred.resolve(data);
            }).error(function(e) {
                deferred.reject();
                console.log(e);
            });

            deferred.promise.finally(function() {
                question.saving = false
            });

            return deferred.promise;
        }
    };

});

angular.module('ngEditor.directives', [])

.directive('questionPage', function(editorFactory) {
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
                <md-button class="md-secondary" aria-label="加入題目" ng-click="addQuestion(0)">加入題目</md-button>
                <md-card ng-repeat="question in questions">
                    <md-card-header md-colors="{background: 'indigo'}">
                        <question-bar></question-bar>
                    </md-card-header>
                    <md-card-content>
                        <div question="question"></div>
                    </md-card-content>
                    <md-card-actions>
                        <md-button class="md-secondary" aria-label="加入題目" ng-click="addQuestion($index+1)">加入題目</md-button>
                    </md-card-actions>
                    <md-progress-linear md-mode="buffer" ng-disabled="!question.saving"></md-progress-linear>
                </md-card>
            </div>
        `,
        controller: function($scope, $http, $filter) {

            $scope.root = $scope.book;
            $scope.questions = [];

            $scope.quesTypes = [
                {name: 'explain', title: '文字標題'},
                {name: 'select', title: '單選題(下拉式)', icon: 'arrow-drop-down-circle'},
                {name: 'radio', title: '單選題(點選)', icon: 'radio-button-checked'},
                {name: 'checkboxs', title: '複選題', icon: 'check-box'},
                {name: 'scales', title: '量表題', icon: 'list'},
                {name: 'texts', title: '文字填答', icon: 'mode-edit'},
                {name: 'list', title: '題組', icon: 'sitemap icon'},
                {name: 'textarea', title: '文字欄位(大型欄位)', disabled: true},
                {name: 'textscale', title: '文字欄位(表格)', disabled: true},
                {name: 'table', title: '表格', disabled: true},
                {name: 'jump', title: '開啟題本', type: 'rule'}
            ];

            this.getChildrens = function(parent) {
                editorFactory.ajax('getChildrens', {parent: parent}).then(function(response) {
                    console.log(response);
                    $scope.root = parent;
                    $scope.questions = response.childrens;
                });
            };

            this.getChildrens($scope.root);

            this.addQuestion = function(sorter, parent = null) {
                console.log($scope.questions);
                var question = {
                    type: '?',
                    page: $scope.page,
                    sorter: sorter,
                    changeType : true
                };

                if (sorter >= 0) {
                    $scope.questions.splice(sorter, 0, question);
                } else {
                    $scope.questions.push(question);
                }

                // if (parent) {
                //     $scope.createQuestion(question, {type: 'question', target: parent});
                // }
            };

            this.removeQuestion = function(question) {
                console.log($scope.questions);
                if (!question.id) {
                    $scope.questions.splice($scope.questions.indexOf(question), 1);
                    return;
                }

                editorFactory.ajax('removeQuestion', {question: question}, question).then(function(response) {
                    if (response.deleted) {
                        $scope.questions.splice($scope.questions.indexOf(question), 1);
                    };
                });
            };

            $scope.addQuestion = this.addQuestion;

            $scope.changeType = function(question) {
                question.changeType = false;
                if (!question.id) {
                    $scope.createQuestion(question, $scope.root);
                };
            };

            $scope.createQuestion = function(question, parent) {
                editorFactory.ajax('createQuestion', {question: question, parent: parent}, question).then(function(response) {
                    console.log(response);
                    angular.extend(question, response.question);
                    //question.saving = false;
                });
            };

        }
    };
})

.directive('questionBar', function() {
    return {
        restrict: 'E',
        replace: true,
        transclude: false,
        templateUrl: 'bar',
        require: ['^questionPage'],
        link: function(scope, iElement, iAttrs, ctrls) {
            var pageCtrl = ctrls[0];
            scope.removeQuestion = pageCtrl.removeQuestion;
        },
        controller: function($scope, $http) {

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

.directive('question', function($compile, FileUploader, $templateCache) {
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        scope: {
            question: '=',
            sbooks:'=',
            page: '='
        },
        require: ['^questionPage'],
        compile: function(tElement, tAttr) {
            var contents = tElement.contents().remove();
            var compiledContents = {};

            return function(scope, iElement, iAttrs, ctrls) {
                scope.$watch('question.type', function(newType, oldType) {
                    var contents = iElement.contents().remove();
                    var type = newType == '?' ? 'search' : newType;
                    compiledContents[type] = $compile($templateCache.get(type));
                    compiledContents[type](scope, function(clone, scope) {
                        iElement.append(clone);
                    });
                });

                var pageCtrl = ctrls[0];
                //var barCtrl = ctrls[1];
                scope.getChildrens = pageCtrl.getChildrens;
                scope.addQuestion = pageCtrl.addQuestion;
                scope.removeQuestion = pageCtrl.removeQuestion;
                //scope.moveSort = barCtrl.moveSort;
            };
        },
        controller: function($scope, $http, $interval, $timeout, $filter) {

            $scope.saveTitleNgOptions = {updateOn: 'default blur', debounce:{default: 2000, blur: 0}};
            $scope.searchLoaded = '';
            $scope.searchText = {};

            $scope.icons = {
                radio: {icon: 'selected radio', title: '單選題'},
                select: {icon: 'arrow circle down', title: '下拉選單'},
                checkboxs: {icon: 'checkmark box', title: '複選題'},
                scales: {icon: 'ordered list', title: '量表題'},
                texts: {icon: 'write', title: '文字填答'},
                list: {icon: 'sitemap', title: '題組'}
                // textarea: {icon: 'write square', title: '文字填答(多行)'}
            };

            $scope.createAnswer = function(question) {
                $http({method: 'POST', url: 'createAnswer', data:{question: question}})
                .success(function(data, status, headers, config) {
                    console.log(data);
                    question.answers.push(data.answer);
                }).error(function(e) {
                    console.log(e);
                });
            };

            $scope.addScaleAnswer = function(question) {
                $http({method: 'POST', url: 'addScaleAnswer', data:{question: question}})
                .success(function(data, status, headers, config) {
                    console.log(data);
                    question.answers.push(data.answer);
                }).error(function(e) {
                    console.log(e);
                });
            };

            $scope.removeAnswer = function(answer) {
                answer.saving = true;
                $http({method: 'POST', url: 'removeAnswer', data:{answer: answer}})
                .success(function(data, status, headers, config) {
                    console.log(data);
                    if (data.deleted) {
                        $scope.question.answers = data.answers;
                    };
                }).error(function(e) {
                    console.log(e);
                });
            };

            $scope.saveQuestionTitle = function(question) {
                question.saving = true;
                $http({method: 'POST', url: 'saveQuestionTitle', data:{question: question}})
                .success(function(data, status, headers, config) {
                    console.log(data);
                    question.title = data.title;
                    question.saving = false;
                }).error(function(e) {
                    console.log(e);
                });
            };

            $scope.saveAnswerTitle = function(answer) {
                answer.saving = true;
                $http({method: 'POST', url: 'saveAnswerTitle', data:{answer: answer}})
                .success(function(data, status, headers, config) {
                    console.log(data);
                    answer.title = data.title;
                    answer.saving = false;
                }).error(function(e) {
                    console.log(e);
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
                    $scope.question.changeType = false;
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
                    $scope.question.changeType = false;
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
                    $scope.question.changeType = false;
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
                    $scope.question.changeType = false;
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