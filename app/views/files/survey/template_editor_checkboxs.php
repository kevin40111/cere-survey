<div>
    <md-input-container class="md-block">
        <label>標題</label>
        <textarea ng-model="question.title" md-maxlength="150" rows="1" ng-model-options="{updateOn: 'blur'}" md-select-on-focus ng-change="saveQuestionTitle(question)"></textarea>
    </md-input-container>
    <div class="ui accordion">
        <md-list>
            <md-subheader class="md-no-sticky">問項 ({{ question.childrens.length || 0 }})</md-subheader>
            <md-list-item ng-repeat="cQuestion in question.childrens">
                <md-icon ng-style="{fill: !cQuestion.title ? 'red' : ''}" md-svg-icon="check-box"></md-icon>
                <div flex>
                    <div class="ui transparent fluid input">
                        <input type="text" placeholder="輸入問項標題..." ng-model="cQuestion.title" ng-model-options="saveTitleNgOptions" ng-change="saveQuestionTitle(cQuestion)" />
                    </div>
                </div>
                <md-button class="md-secondary" aria-label="設定子題" ng-click="getChildrens(cQuestion)">設定子題</md-button>
                <md-icon class="md-secondary" aria-label="刪除子題" md-svg-icon="delete" ng-click="removeQuestion(cQuestion)"></md-icon>
                <div class="ui button disabled" ng-click="checkbox.reset=!checkbox.reset" ng-if="question.open.button[$index] || checkbox.reset" title="清除勾選項目"></div>
            </md-list-item>
            <md-list-item ng-click="addQuestion(0, question)">
                <md-icon md-svg-icon="check-box"></md-icon>
                <p>新增問項</p>
            </md-list-item>
        </md-list>
    </div>
</div>