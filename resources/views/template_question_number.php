<md-card-content>
    <md-input-container ng-repeat="question in node.questions">
        <label></label>
        <input type="number" step="1" ng-model="contents[question.id]" ng-model-options="saveTextNgOptions" ng-disabled="isSkip(question)" string-converter ng-change="sync()" />
    </md-input-container>
</md-card-content>