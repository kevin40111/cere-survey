<div flex layout="row" layout-align="start center">
    <md-input-container ng-if="question.changeType">
        <label>選擇題型</label>
        <md-select ng-model="question.type" ng-change="changeType(question)">
            <md-option ng-repeat="type in quesTypes | filter:{disabled:'!'}" value="{{ type.name }}">{{ type.title }}</md-option>
        </md-select>
    </md-input-container>

    <div>
        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="{{(quesTypes | filter:{name: question.type})[0].icon}}"></md-icon>
    </div>
    <div style="margin: 0 0 0 16px">{{(quesTypes | filter:{name: question.type})[0].title}}</div>           

    <span flex></span>

    <div class="ui input" ng-if="question.open.moving">
        <input type="text" ng-model="settedPage" placeholder="輸入移動到的頁數..." />
        <md-button class="md-icon-button no-animate" ng-disabled="question.saving" aria-label="移動到某頁" ng-click="setPage(question, settedPage)">
            <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="send"></md-icon>
        </md-button>
    </div>
    <md-button class="md-icon-button no-animate" ng-disabled="question.saving" aria-label="移動到某頁" ng-click="question.open.moving=!question.open.moving" ng-if="!question.open.moving">
        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="send"></md-icon>
    </md-button>

    <md-button class="md-icon-button" aria-label="上移" ng-disabled="question.saving" ng-if="!$first" ng-click="moveSort(question, -1)">
        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="arrow-drop-up"></md-icon>
    </md-button>
    <md-button class="md-icon-button" aria-label="下移" ng-disabled="question.saving" ng-if="!$last" ng-click="moveSort(question, 1)">
        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="arrow-drop-down"></md-icon>
    </md-button>
    <md-button class="md-icon-button" aria-label="刪除" ng-disabled="question.saving" ng-click="removeQuestion(question)">
        <md-icon md-colors="{color: 'grey-A100'}" md-svg-icon="delete"></md-icon>
    </md-button>    
</div>