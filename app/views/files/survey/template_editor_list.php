<div>
    <md-input-container class="md-block">
        <label>標題</label>
        <textarea ng-model="question.title" md-maxlength="150" rows="1" ng-model-options="{updateOn: 'blur'}" md-select-on-focus ng-change="saveQuestionTitle(question)"></textarea>
    </md-input-container>
    <div>
        <md-list>
            <md-subheader class="md-no-sticky">問題 ({{ question.childrens.length || 0 }})</md-subheader>
            <md-list-item ng-repeat="cQuestion in question.childrens">
                <md-icon md-svg-icon="list"><md-tooltip md-direction="left">{{$index+1}}</md-tooltip></md-icon>
                <p class="ui transparent fluid input" ng-class="{loading: cQuestion.saving}">
                    <input type="text" placeholder="輸入問題..." ng-model="cQuestion.title" ng-model-options="saveTitleNgOptions" ng-change="saveQuestionTitle(cQuestion)" />
                </p>
                <md-button class="md-secondary md-icon-button" ng-click="moveSort(cQuestion, -1)" aria-label="上移"><md-icon md-svg-icon="arrow-drop-up"></md-icon></md-button>
                <md-button class="md-secondary md-icon-button" ng-click="moveSort(cQuestion, 1)" aria-label="下移"><md-icon md-svg-icon="arrow-drop-down"></md-icon></md-button>
                <md-icon class="md-secondary" aria-label="刪除子題" md-svg-icon="delete" ng-click="removeQuestion(cQuestion)"></md-icon>
            </md-list-item>
            <md-list-item ng-click="addQuestion(question.childrens.length, question)">
                <md-icon md-svg-icon="list"></md-icon>
                <p>新增問題</p>
            </md-list-item>
        </md-list>
    </div>
</div>