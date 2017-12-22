<div layout="column">
    <div layout="row" layout-padding>
        <div flex></div>
        <div flex style="max-width:65px" ng-repeat="answer in node.answers">{{answer.title}}</div>
    </div>
    <md-radio-group ng-model="answers[question.id]" ng-disabled="node.saving" survey-input ng-repeat="question in node.questions" ng-change="saveAnswer(answers[question.id])">
        <div layout="row" layout-padding>
            <div flex>{{question.title}}</div>
            <div flex style="max-width:65px" ng-repeat="answer in node.answers" layout="column" layout-align="start center">
                <md-radio-button ng-value="answer.value" aria-label="{{answer.title}}" ng-disabled="skips.answers.indexOf(answer.id) != -1"></md-radio-button>
            </div>
        </div>
    </md-radio-group>
</div>