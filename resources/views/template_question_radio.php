<md-radio-group ng-model="answers[question.id]" ng-disabled="node.saving" survey-input ng-repeat="question in node.questions" ng-change="saveAnswer(answers[question.id])">
	<label>{{question.title}}</label><br><br>
    <md-radio-button ng-repeat="answer in node.answers" ng-value="answer.value" class="md-primary" ng-disabled="compareRule(answer)">{{answer.title}}</md-radio-button>
    <div style="padding-left: 5px">
        <survey-node ng-repeat="children in question.childrens" node="children"></survey-node>
    </div>
</md-radio-group>