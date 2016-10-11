
<div ng-cloak class="ui basic segment" ng-controller="editorController" ng-class="{loading: loading}">

    <div class="ui grid">

        <div class="six wide column">

            <div class="ui right labeled input">
                <input type="number" ng-model="page" ng-model-options="{updateOn: 'default blur', debounce:{default: 2000, blur: 0}}" placeholder="輸入頁數" ng-change="setPage()">
                <div class="ui basic label">總共 {{ lastPage }} 頁 </div>
            </div>
            <div class="ui basic buttons">
                <div class="ui button" ng-click="prevPage()">前一頁</div>
                <div class="ui button" ng-click="nextPage()">下一頁</div>
            </div>

            <div class="ui divider"></div>

            <div class="ui styled fluid accordion">
                <div class="title" ng-repeat-start="question in fQuestions" ng-if="!selecting||question.id==selecting.question_id" ng-class="{active:question.opening}" ng-click="question.opening=!question.opening">
                    <i class="dropdown icon" ng-class="{green:hasRule(question.answers)}"></i><span ng-bind-html="question.is.title"></span>
                </div>
                <div class="content" ng-repeat-end ng-class="{active:question.opening}" ng-if="!selecting||question.id==selecting.question_id">
                    <p ng-if="question.answers.length==0">無選項</p>

                    <div class="accordion">
                        <div class="title" ng-repeat-start="answer in question.answers" ng-class="{active:answer==selecting}" ng-click="setOrUpdateAnswer(answer);">
                            <i class="dropdown icon" ng-class="{green:answer.rule}" ng-if="!answer.saving"></i>
                            <i class="notched circle loading green icon" ng-if="answer.saving"></i>
                            {{answer.is.title}}
                            <span ng-if="!answer.rule">-尚未設定</span>
                        </div>
                        <div ng-repeat-end class="content" ng-class="{active:answer==selecting}">
                            <p class="ui teal ribbon label" ng-if="question.answers.length>0">選項</p>
                            <div class="ui middle aligned divided list">
                                <div class="item" ng-repeat="openWave in answer.rule.open_wave">
                                    <div class="ui mini yellow tag label icon">
                                        {{openWave.type_title}}--{{openWave.title}}--{{openWave.month}}月份
                                        <!-- {{openWave.title}} -->
                                        <i class="icon close" ng-click="deleteSkip(answer.rule.open_wave, openWave,'wave')"></i>
                                    </div>
                                </div>
                                <div class="item" ng-repeat="jumpBook in answer.rule.jump_book">
                                    <div class="ui mini blue tag label icon">
                                        {{ jumpBook.is.title }}
                                        <i class="icon close" ng-click="deleteSkip(answer.rule.jump_book, jumpBook,'book')"></i>
                                    </div>
                                </div>
                                <div class="item" ng-repeat="skip_ques in answer.rule.questions">
                                    <div class="ui mini green tag label icon">
                                        {{ skip_ques.is.title }}
                                        <i class="icon close" ng-click="deleteSkip(answer.rule.questions, skip_ques,'question')"></i>
                                    </div>
                                </div>
                                <div class="item" ng-repeat="skip_answer in answer.rule.skip_answers">
                                    <div class="ui mini orange tag label icon">
                                        {{skip_answer.question.is.title}} -- {{skip_answer.is.title }}
                                        <i class="icon close" ng-click="deleteSkip(answer.rule.skip_answers, skip_answer,'answer')"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ten wide column">
            <div class="ui pointing secondary menu" ng-if="selecting">
                <a class="item" ng-class="{active: jumpItem == 'jumpQues'}" ng-click="changeType('jumpQues')"><i class="teal browser icon"></i>跳答的題目 </a>
                <a class="item" ng-class="{active: jumpItem == 'jumpBook'}" ng-click="changeType('jumpBook')"><i class="blue book icon"></i>跳答的波次/題本 </a>
            </div>
            <div class="ui styled fluid accordion" ng-if="selecting" ng-show="jumpItem == 'jumpQues'">
                <div class="title" ng-repeat-start="page in pages" ng-class="{active:page.opening}" ng-click="page.opening=!page.opening;toggle_pool(selecting,page.questions,'question',page)">
                    <i class="dropdown icon"></i>
                    <div class="ui checkbox">
                        <input id="{{::$id}}" type="checkbox" class="hidden" ng-model="page.checked">
                        <label for="{{::$id}}">第{{page.page}}頁</label>
                    </div>
                </div>
                <div class="content" ng-repeat-end ng-class="{active:page.opening}" ng-if="page.opening">
                    <a class="ui green ribbon label">題目</a>
                    <div class="accordion">
                        <div class="title" ng-repeat-start="question in page.questions" ng-class="{active:question.opening}" >
                            <div ng-if="question.answers.length>0" ng-click="question.opening=!question.opening;toggle_pool(selecting,question.answers,'answer',question)"><i class="dropdown icon"></i>
                                <div class="ui checkbox">
                                    <input id="{{::$id}}" type="checkbox" class="hidden" ng-model="question.checked" ng-change="setSkipQuestion(question)" />
                                    <label for="{{::$id}}">{{question.is.title}}</label>
                                </div>
                            </div>
                            <div ng-class="{active:question.opening}" ng-if="question.answers.length==0">
                                <div class="ui checkbox">
                                    <input id="{{::$id}}" type="checkbox" class="hidden" ng-model="question.checked" ng-change="setSkipQuestion(question)" />
                                    <label for="{{::$id}}"></label>
                                </div>
                                <i class="icon"></i> {{question.is.title}}
                            </div>
                        </div>
                        <div class="content" ng-repeat-end ng-class="{active:question.opening}">
                            <a class="ui teal ribbon label">選項</a>
                            <div class="list">
                                <div class="item" ng-repeat="answer in question.answers">
                                    <div class="ui checkbox">
                                        <input id="{{::$id}}" type="checkbox" class="hidden" ng-model="answer.checked" ng-change="setSkipAnswer(answer,question)" />
                                        <label for="{{::$id}}">{{answer.is.title}}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ui styled fluid accordion" ng-if="selecting" ng-show="jumpItem == 'jumpBook'">
                <div class="grouped fields">
                <div class="accordion">
                    <div class="title" ng-repeat-start="wave in waves" ng-class="{active:wave.opening}" ng-click="wave.opening=!wave.opening;"><i class="dropdown icon"></i>
                        <div class="ui radio checkbox">
                            <input id="{{::$id}}" type="radio" class="hidden" name="waveBook" ng-value="true"  ng-model="wave.checked" ng-click="setSkipWaveBook(wave,'wave')">
                            <label for="{{::$id}}">{{wave.type_title}}--{{wave.title}}--{{wave.month}}月份</label>
                        </div>
                    </div>
                    <div class="content" ng-repeat-end ng-class="{active:wave.opening}">
                        <a class="ui blue ribbon label">選項</a>
                        <div class="list">
                            <div class="item" ng-repeat="book in wave.books">
                                <div class="ui radio checkbox">
                                    <input id="{{::$id}}" type="radio" name="waveBook" ng-value="true" class="hidden" ng-model="book.checked" ng-click="setSkipWaveBook(book,'book')">
                                    <label for="{{::$id}}">{{book.is.title}}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
app.controller('editorController', function($http, $scope, $sce, $interval, $filter, $timeout) {
    $scope.jumpItem = 'jumpQues';
    $scope.pages = [];
    $scope.pages_skips = [];
    $scope.selecting = false;
    $scope.open_question = false;
    $scope.poolPage = [];
    $scope.poolQuestion = [];
    $scope.wave_checked = '';

    $scope.nextPage = function() {
        if ($scope.page < $scope.lastPage)
            $scope.getQuestions(++$scope.page);
    };

    $scope.prevPage = function() {
        if ($scope.page > 1)
            $scope.getQuestions(--$scope.page);
    };

    $scope.setPage = function() {
        if ($scope.page > $scope.lastPage || $scope.page < 0)
            $scope.page = 1;
        $scope.getQuestions($scope.page);
    };

    $scope.changeType = function(type) {
        $scope.jumpItem = type;
    };

    $scope.reloadWaveBookPool = function(answer) {
        for(var i in $scope.waves) {
            $scope.waves[i].checked = false;
            for(var j in answer.rule.open_wave) {
                if($scope.waves[i].id == answer.rule.open_wave[j].id){
                    $scope.waves[i].checked = true;
                }
            }

            for(var k in $scope.waves[i].books) {
                $scope.waves[i].books[k].checked = false;
                for(var l in answer.rule.jump_book) {
                    if($scope.waves[i].books[k].id == answer.rule.jump_book[l].id){
                        $scope.waves[i].books[k].checked = true;
                    }
                }
            }
        }
    };

    /*$scope.changeWaveBook = function(type, event) {
        if ($scope.wave_checked == type.id) {
            type.checked = false;
            event.target.checked = false;
            $scope.wave_checked = '';
        }else{
            type.checked = type.id;
            $scope.wave_checked = type.id;
        }
    };*/

    $scope.getQuestions = function(page) {
        $scope.loading = true;
        $http({method: 'POST', url: 'getSkipQuestions', data:{page: page}})
        .success(function(data, status, headers, config) {
            console.log(data);
            $scope.page = page;
            $scope.fQuestions = data.fQuestions;
            $scope.pages = data.pages;
            $scope.lastPage = data.lastPage;
            $scope.loading = false;
        }).error(function(e) {
            console.log(e);
        });
    };

    $scope.getQuestions(page = 1);

    $scope.saveSkips = function(answer, callback) {
        answer.saving = true;
        var data = {answer: answer};
        $http({method: 'POST', url: 'save_skips', data:data })
        .success(function(data, status, headers, config) {
            // console.log(data);
            answer.saving = false;
            callback && callback(data.rule);
        }).error(function(e) {
            console.log(e);
        });
    };

    $scope.deleteSkip = function(skip_questions, questions,type) {
        skip_questions.splice(skip_questions.indexOf(questions), 1);
        if (type == 'wave' || type == 'book') {
            $scope.reloadWaveBookPool($scope.selecting);
        } else if (type == 'question') {
           if ($scope.poolPage) {
               for (var i in $scope.poolPage) {
                   for (var j in $scope.poolPage[i].questions) {
                       if ($scope.poolPage[i].questions[j].id == questions.id) {
                           $scope.poolPage[i].questions[j].checked = false;
                       }
                   }
               }
           }
       } else if (type == 'answer') {
            if ($scope.poolQuestion) {
                for (var i in $scope.poolQuestion) {
                    for (var j in $scope.poolQuestion[i].answers) {
                        if ($scope.poolQuestion[i].answers[j].id == questions.id) {
                            $scope.poolQuestion[i].answers[j].checked = false;
                        }
                    }
                }
            }
       }
    };

    $scope.addRule = function(page,question) {
        if (page.page == question.page) {
            if (page.questions.indexOf(question) < 0) {
                page.questions.push(question);
            } else {
                question.opening = true;
            }
        }
    };

    $scope.setOrUpdateAnswer = function(answer) {
        if (!$scope.selecting) {
            $scope.setRule(answer);
            $scope.open_question = answer;
        } else {
            $scope.saveSkips($scope.selecting, function(rules) {
                $scope.selecting.rule = rules;
                if ($scope.selecting == answer) {
                    $scope.selecting = false;
                } else {
                    $scope.setRule(answer);
                }
            });
        }

        if ($scope.poolPage) {
            for (var i in $scope.poolPage) {
                $scope.poolPage[i].opening = false;
            }
            if ($scope.poolQuestion) {
                for (var i in $scope.poolQuestion) {
                    $scope.poolQuestion[i].opening = false;
                }
            }
        }
    };

    $scope.setRule = function(answer) {
        $scope.selecting = answer;
        if (!$scope.selecting.rule) {
            $scope.selecting.rule = {skip_answers: [], questions: [],jump_book: [],open_wave: []};
        }
        answer.rule.open_wave = $scope.setWaveTypeTitle(answer.rule.open_wave);
        $scope.reloadWaveBookPool(answer);
    };

    $scope.setSkipWaveBook = function(skip,type) {
        if (type == 'wave') {
            $scope.selecting.rule.open_wave = [];
            $scope.selecting.rule.jump_book = [];
            $scope.selecting.rule.open_wave.push(skip);
        } else if (type == 'book') {
            $scope.selecting.rule.jump_book = [];
            $scope.selecting.rule.open_wave = [];
            $scope.selecting.rule.jump_book.push(skip);
        } else {
            return false;
        }
    };

    $scope.setSkipQuestion = function(question) {
        var check_item = [];
        if (question.checked) {
            $scope.selecting.rule.questions.push(question);
            for(var i in question.answers) {
                question.answers[i].checked = false;
                $scope.setSkipAnswer(question.answers[i],question);
            }
        } else {
            for(key in $scope.selecting.rule.questions){
                if ($scope.selecting.rule.questions[key]['id'] == question['id']) {
                    delete $scope.selecting.rule.questions[key];
                } else {
                    check_item.push($scope.selecting.rule.questions[key]);
                }
            }
            $scope.selecting.rule.questions = check_item;
        }
    };

    $scope.setSkipAnswer = function(answer,question) {
        var check_item = [];
        if (answer.checked) {
            $scope.selecting.rule.skip_answers.push(answer);
            question.checked = false;
            $scope.setSkipQuestion(question);
        } else {
            for(key in $scope.selecting.rule.skip_answers){
                if ($scope.selecting.rule.skip_answers[key]['id'] == answer['id']) {
                    delete $scope.selecting.rule.skip_answers[key];
                } else {
                    check_item.push($scope.selecting.rule.skip_answers[key]);
                }
            }
            $scope.selecting.rule.skip_answers = check_item;
        }
    };

    $scope.hasRule = function(answers) {
        var hasRule = false;
        for (var i in answers) {
            if (answers[i].group && answers[i].group.rule) {
                hasRule = true;
            };
        };
        return hasRule;
    };

    $scope.toggle_pool = function(answer,skips,type,parent) {
        if (type == 'question') {
            if (parent.opening && $scope.poolPage) {
                var repeat = false;
                for(var i in $scope.poolPage) {
                    if ($scope.poolPage && ($scope.poolPage[i].id == parent.id)) {
                       repeat = true;
                    }
                }
                if (!repeat) {
                    $scope.poolPage.push(parent);
                }
            }
            if (answer.rule.questions) {
                var questions_id = [];
                for(key in answer.rule.questions){
                    questions_id.push(answer.rule.questions[key].id);
                }

                for (var i = 0; i < skips.length; i++) {
                    if (questions_id.indexOf(skips[i].id) > -1) {
                        skips[i].checked = true;
                    } else {
                        skips[i].checked = false;
                    }
                }
            }
        } else if (type == 'answer') {
            if (parent.opening && $scope.poolQuestion) {
                var repeat = false;
                for(var i in $scope.poolQuestion) {
                    if ($scope.poolQuestion[i] && ($scope.poolQuestion[i].id == parent.id)) {
                        repeat = true;
                    }
                }
                if (!repeat) {
                    $scope.poolQuestion.push(parent);
                }
            }
            if (answer.rule.skip_answers) {
                var answers_id = [];
                for (key in answer.rule.skip_answers) {
                    answers_id.push(answer.rule.skip_answers[key].id);
                }
                for (var i = 0; i < skips.length; i++) {
                    if (answers_id.indexOf(skips[i].id) > -1) {
                        skips[i].checked = true;
                    } else {
                        skips[i].checked = false;
                    }
                }
            }
        }
    }

    $scope.getWaves = function() {
        $http({method: 'POST', url: 'getWaves', data:{} })
        .success(function(data, status, headers, config) {
            data = $scope.setWaveTypeTitle(data);
            $scope.waves = data;
            console.log($scope.waves);
        }).error(function(e) {
            console.log(e);
        });
    };

    $scope.setWaveTypeTitle = function(waves) {
        for(var i in waves){
            if (waves[i].ques == '2') {
                waves[i].type_title = '家長調查';
            } else if (waves[i].ques == '3') {
                waves[i].type_title = '簡親調查';
            } else if (waves[i].ques == '4') {
                waves[i].type_title = '保母調查';
            } else if (waves[i].ques == '5') {
                waves[i].type_title = '機構調查';
            } else {
                waves[i].type_title = '';
            }
        }
        return waves;
    }

    $scope.getWaves();
});
</script>
