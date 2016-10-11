<md-input-container class="md-block">
    <label>標題</label>
    <textarea ng-model="question.title" md-maxlength="500" rows="1" ng-model-options="{updateOn: 'blur'}" md-select-on-focus ng-change="saveQuestionTitle(question)"></textarea>
</md-input-container>