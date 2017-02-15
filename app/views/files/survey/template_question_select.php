<div>
    <div ng-repeat="question in node.questions">
        <label>{{question.title}}</label>
        <md-select ng-model="answer" ng-disabled="node.saving" survey-input parent="answer" placeholder="請選擇" ng-change="saveAnswer(answer, answer.value)">
            <md-option ng-repeat="answer in node.answers" ng-value="answer" ng-disabled="compareRule(answer)">{{answer.title}}</md-option>
        </md-select>
        <div style="padding-left: 5px">
            <survey-node ng-repeat="children in question.childrens" node="children" ng-hide="compareRule(children)"></survey-node>
        <div>
    </div>
</div>