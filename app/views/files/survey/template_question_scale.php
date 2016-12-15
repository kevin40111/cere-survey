<div layout="column">
    <div layout="row" layout-padding>
        <div flex></div>
        <div flex style="max-width:65px" ng-repeat="answer in node.answers">{{answer.title}}</div>
    </div>
    <md-radio-group ng-model="answer" ng-disabled="node.saving" survey-input ng-repeat="question in node.questions" ng-change="saveAnswer(answer, answer.id)">
        <div layout="row" layout-padding>
            <div flex>{{question.title}}</div>            
            <div flex style="max-width:65px" ng-repeat="answer in node.answers" layout="column" layout-align="start center">            
                <md-radio-button ng-value="answer" aria-label="{{answer.title}}"></md-radio-button>
            </div>
        </div>
    </md-radio-group>
</div>