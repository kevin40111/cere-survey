<md-select ng-repeat="question in node.questions" ng-model="answers[question.id]" placeholder="請選擇">
    <md-option ng-repeat="answer in node.answers" ng-value="answer.id">{{answer.title}}</md-option>
</md-select>