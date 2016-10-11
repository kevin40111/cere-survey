<div>
    <div class="ui icon fluid input">
        <input type="text" ng-model="searchText.title" placeholder="搜尋題庫..." ng-focus="getPoolQuestions('question')" ng-change="getPoolQuestions('question')">
        <i class="search icon"></i>
    </div>

    <div class="ui small fluid vertical accordion menu" ng-if="question.searching=='question'">
        <div class="item">
            <div class="list" style="overflow-y: auto;max-height:350px">
                <div class="header item" ng-if="(pQuestions | filter:{parent_question_id:false} | filter:searchText).length==0">找不到題目</div>
                <div class="item" ng-repeat="pQuestion in pQuestions | filter:{parent_question_id:false} | filter:searchText">
                    <i class="icon {{icons[pQuestion.is.type].icon}}"></i>
                    <div class="ui checkbox">
                        <input id="{{::$id}}" type="checkbox" class="hidden" ng-click="setPoolQuestion(pQuestion)" />
                        <label for="{{::$id}}" ng-bind-html="pQuestion.parent.title + ' - ' + pQuestion.title"></label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>