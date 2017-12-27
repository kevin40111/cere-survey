<md-input-container ng-repeat="question in node.questions" class="md-block">
    <label>{{question.title}}</label>
    <input type="text" ng-model="answers[question.id]" ng-model-options="saveTextNgOptions" ng-disabled="node.saving || skips.answers.indexOf(answer.id) != -1" survey-input ng-change="saveAnswer(answers[question.id])" />
</md-input-container>