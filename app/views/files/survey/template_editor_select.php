<div>
    <div class="ui accordion field">
        <md-list>
            <md-subheader class="md-no-sticky">選項 ({{ question.answers.length || 0 }})</md-subheader>
            <md-list-item ng-repeat="answer in question.answers">
                <md-icon ng-if="question.is.type=='radio'" ng-style="{fill: !answer.title ? 'red' : ''}" md-svg-icon="radio-button-checked"></md-icon>
                <md-icon ng-if="question.is.type=='select'" ng-style="{fill: !answer.title ? 'red' : ''}" ng-style="" md-svg-icon="arrow-drop-down-circle"></md-icon>
                <div flex>
                    <div class="ui transparent fluid input" ng-class="{loading: answer.saving}">
                        <input type="text" placeholder="輸入選項名稱..." ng-model="answer.title" ng-model-options="saveTitleNgOptions" ng-change="saveAnswerTitle(answer)" />
                    </div>
                </div>
                <md-button class="md-secondary" aria-label="設定子題" ng-click="getChildrens(answer)">設定子題</md-button>
                <md-icon class="md-secondary" aria-label="刪除選項" md-svg-icon="delete" ng-click="removeAnswer(answer)"></md-icon>
            </md-list-item>
            <md-list-item ng-click="createAnswer(question)">
                <md-icon ng-if="question.is.type=='radio'" md-svg-icon="radio-button-checked"></md-icon>
                <md-icon ng-if="question.is.type=='select'" md-svg-icon="arrow-drop-down-circle"></md-icon>
                <p>新增選項</p>
            </md-list-item>
        </md-list>
    </div>
</div>