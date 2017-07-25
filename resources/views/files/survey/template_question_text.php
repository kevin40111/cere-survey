<md-input-container ng-repeat="question in node.questions" class="md-block">
    <label>{{question.title}}</label>
    <input type="text" ng-model="answer" ng-model-options="saveTextNgOptions" ng-disabled="node.saving" survey-input ng-change="saveAnswer(null, answer)" />
</md-input-container>