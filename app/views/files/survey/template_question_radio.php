<md-radio-group ng-model="answer" ng-disabled="node.saving" survey-input parent="answer" ng-repeat="question in node.questions" ng-change="saveAnswer(answer, answer.id)">
    <md-radio-button ng-repeat="answer in node.answers" ng-value="answer" class="md-primary">{{answer.title}}</md-radio-button>
    <div style="padding-left: 5px">
        <survey-node ng-repeat="children in question.childrens" node="children"></survey-node>
    <div>
</md-radio-group>