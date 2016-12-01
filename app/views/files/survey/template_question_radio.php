<md-radio-group ng-repeat="question in node.questions" ng-model="answers[question.id]" ng-change="saveAnswer(question)">
    <md-radio-button ng-repeat="answer in node.answers" ng-value="answer" class="md-primary">{{answer.title}}</md-radio-button>
    <div style="padding-left: 30px">
        <survey-node ng-repeat="children in question.childrens" node="children"></survey-node>
    <div>
</md-radio-group>