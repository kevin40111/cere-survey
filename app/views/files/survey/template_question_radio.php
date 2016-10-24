<md-radio-group ng-repeat="question in node.questions" ng-model="answers[question.id]" ng-change="save_answers(question)">
    <md-radio-button ng-repeat="answer in node.answers" value="{{answer.id}}" class="md-primary">{{answer.title}}</md-radio-button>
</md-radio-group>