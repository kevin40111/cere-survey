<div>
    <div ng-repeat="question in node.questions">
        <label>{{question.title}}</label>
        <md-select ng-model="answers[question.name]" ng-disabled="node.saving" survey-input parent="answers[question.name]" placeholder="請選擇" ng-change="saveAnswer(answers[question.id])">
            <md-option ng-repeat="answer in node.answers" ng-value="answer.value" ng-disabled="skips.answers.indexOf(answer.id) != -1">{{answer.title}}</md-option>
        </md-select>
        <survey-node ng-repeat="children in question.childrens" node="children" ng-hide="skips.nodes.indexOf(node.id) != -1"></survey-node>
    </div>
</div>