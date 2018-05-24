<div>
    <div ng-repeat="question in node.questions">
        <label>{{question.title}}</label>
        <md-select ng-model="answers[question.id]" ng-disabled="node.saving" placeholder="請選擇" ng-change="sync()">
            <md-option ng-repeat="answer in node.answers" ng-value="answer.value" ng-disabled="isSkip(answer)">{{answer.title}}</md-option>
        </md-select>
        <survey-node ng-repeat="children in question.childrens" node="children"></survey-node>
    </div>
</div>