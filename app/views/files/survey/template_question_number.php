<md-input-container ng-repeat="question in node.questions">
    <label></label>
    <input type="number" step="1" ng-model="answer" ng-model-options="saveTextNgOptions" ng-disabled="node.saving" survey-input string-converter ng-change="saveAnswer(null, answer)" />
</md-input-container>