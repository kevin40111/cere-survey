<md-input-container ng-repeat="question in node.questions" class="md-block">
    <label>{{question.title}}</label>
    <input type="text" ng-model="contents[question.id]" ng-model-options="saveTextNgOptions" ng-disabled="isSkip(question)" ng-change="sync()" />
</md-input-container>