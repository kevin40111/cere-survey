<md-input-container ng-repeat="question in node.questions">
    <label></label>
    <input type="number" step="1" ng-model="answers[question.id]" ng-model-options="saveTextNgOptions" ng-disabled="node.saving || skips.questions.indexOf(question.id) != -1" survey-input string-converter ng-change="saveAnswer(answers[question.id])" />
</md-input-container>