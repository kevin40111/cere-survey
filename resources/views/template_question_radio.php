
<md-card-content>
    <md-radio-group ng-model="contents[question.id]" ng-disabled="node.saving" ng-change="sync()" ng-repeat="question in node.questions">
        <md-radio-button ng-repeat-start="answer in node.answers" ng-disabled="isSkip(answer)" ng-value="answer.value">{{answer.title}}</md-radio-button>
        <div style="padding-left: 5px" ng-repeat-end>
            <survey-node ng-repeat="children in node.childrens[answer.id]" node="children"></survey-node>
        </div>
    </md-radio-group>
</md-card-content>