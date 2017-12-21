<div ng-repeat="question in node.questions">
    <md-checkbox ng-model="answers[question.id]" ng-disabled="node.saving" survey-input ng-true-value="'1'" ng-false-value="'0'" ng-change="saveAnswer(answers[question.id])" class="md-primary">
        {{ question.title }}
    </md-checkbox>
    <div style="padding-left: 5px">
        <survey-node ng-repeat="children in question.childrens" node="children"></survey-node>
    <div>
</div>
