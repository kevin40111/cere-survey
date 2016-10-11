<div>
    <md-input-container class="md-block">
        <label>標題</label>
        <textarea ng-model="question.title" md-maxlength="150" rows="1" ng-model-options="{updateOn: 'blur'}" md-select-on-focus ng-change="saveQuestionTitle(question)"></textarea>
    </md-input-container>
    <div class="ui accordion field">
        <md-list>
            <md-subheader class="md-no-sticky">題項 ({{ question.childrens.length || 0 }})</md-subheader>
            <md-list-item ng-repeat="cQuestion in question.childrens">
                <md-icon ng-style="{fill: !cQuestion.title ? 'red' : ''}" md-svg-icon="mode-edit"></md-icon>
                <div flex>
                    <div class="ui transparent fluid input" ng-class="{loading: cQuestion.saving}">
                        <input type="text" placeholder="輸入題項..." ng-model="cQuestion.title" ng-model-options="saveTitleNgOptions" ng-change="saveQuestionTitle(cQuestion)" />
                    </div>
                </div>
                <md-icon class="md-secondary" aria-label="刪除選項" md-svg-icon="delete" ng-click="removeQuestion(cQuestion)"></md-icon>
            </md-list-item>
            <md-list-item ng-click="addQuestion(question.childrens.length, question)">
                <md-icon md-svg-icon="mode-edit"></md-icon>
                <p>新增題項</p>
            </md-list-item>
        </md-list>
    </div>
</div>