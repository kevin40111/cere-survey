<div ng-repeat="question in node.questions">
    <md-checkbox ng-model="contents[question.id]" ng-disabled="isSkip(question)" ng-true-value="'1'" ng-false-value="'0'" ng-change="sync()">
        {{ question.title }}
    </md-checkbox>
    <div style="padding-left: 5px">
        <survey-node ng-repeat="children in node.childrens[question.id]" node="children"></survey-node>
    <div>
</div>
