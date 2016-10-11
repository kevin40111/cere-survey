        <div class="ui basic segment" ng-if="question.searching=='branch'">
            <div class="ui transparent fluid input">
                <input type="text" ng-model="searchText.title" placeholder="搜尋題庫..." ng-focus="getPoolQuestions('branch')">
            </div>
            <div class="ui small fluid vertical accordion menu">
                <div class="item">
                    <div class="list" style="overflow-y: auto;max-height:350px">
                        <div class="header item" ng-if="(pQuestions | filter:{is:{type:'scales'}} | filter:searchText).length==0">找不到題目</div>
                        <div class="item" ng-repeat="pQuestion in pQuestions | filter:{is:{type:'scales'}} | filter:searchText">
                            <i class="icon {{icons[pQuestion.is.type].icon}}"></i>
                            <div class="ui checkbox">
                                <input id="{{::$id}}" type="checkbox" class="hidden" ng-click="setPoolScaleBranchQuestion(pQuestion)" />
                                <label for="{{::$id}}" ng-bind-html="pQuestion.parent.title + ' - ' + pQuestion.title"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>