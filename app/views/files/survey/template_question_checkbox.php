<div ng-repeat="question in node.questions">
    <md-checkbox ng-model="answers[question.id]" ng-true-value="{id: 1}" ng-false-value="null" ng-change="saveAnswer(question)" class="md-primary">
        {{ question.title }}
    </md-checkbox>
    <div style="padding-left: 30px">
        <survey-node ng-repeat="children in question.childrens" node="children"></survey-node>
    <div>
</div>
