<div ng-repeat="question in node.questions">
    <md-checkbox ng-model="answers[question.id]" ng-true-value="'{{answer.id}}'" ng-false-value="null" ng-change="save_answers(cQuestion)" class="md-primary">
        {{ question.title }}
    </md-checkbox>
</div>
