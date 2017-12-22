<md-radio-group ng-model="answers[question.id]" ng-disabled="node.saving || skips.questions.indexOf(question.id) != -1" survey-input ng-repeat="question in node.questions" ng-change="saveAnswer(answers[question.id])">
	<label>{{question.title}}</label><br><br>
    <md-radio-button ng-repeat="answer in node.answers" ng-disabled="skips.answers.indexOf(answer.id) != -1" ng-value="answer.value" class="md-primary" >{{answer.title}}</md-radio-button>
    <div style="padding-left: 5px">
        <survey-node ng-repeat="children in question.childrens" node="children"></survey-node>
    </div>
</md-radio-group>