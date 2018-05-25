<md-input-container ng-repeat="question in node.questions">
    <label></label>
    <input type="number" step="1" ng-model="answers[question.id]" ng-model-options="saveTextNgOptions" ng-disabled="isSkip(question)" string-converter ng-change="sync()" />
</md-input-container>