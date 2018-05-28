<md-card-content>
    <md-input-container ng-repeat="question in node.questions">
        <label>{{question.title}}</label>
        <input type="text" ng-model="contents[question.id]" ng-model-options="saveTextNgOptions" ng-disabled="isSkip(question)" ng-change="sync()" />
    </md-input-container>
</md-card-content>